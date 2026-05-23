<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Runs code locally when Judge0 is unavailable (no paid API required).
 * Supports Python, JavaScript (Node), Java, and C on machines with those tools installed.
 */
class LocalCodeRunnerService
{
    protected int $timeoutSeconds;

    public function __construct()
    {
        $this->timeoutSeconds = (int) config('services.code_runner.timeout', 8);
    }

    public function supports(string $language): bool
    {
        return in_array(strtolower($language), ['python', 'python3', 'javascript', 'js', 'java', 'c'], true);
    }

    public function run(string $code, string $language, int $cpuTimeLimit = 5): array
    {
        $language = strtolower($language);
        $timeout = min(max($cpuTimeLimit, 1), 15);

        return match ($language) {
            'python', 'python3' => $this->runPython($code, $timeout),
            'javascript', 'js' => $this->runJavaScript($code, $timeout),
            'java' => $this->runJava($code, $timeout),
            'c' => $this->runC($code, $timeout),
            default => [
                'success' => false,
                'error' => 'Local execution is not available for this language.',
            ],
        };
    }

    protected function runPython(string $code, int $timeout): array
    {
        $python = $this->findExecutable(['python', 'python3', 'py']);

        if (!$python) {
            return $this->unavailable('Python is not installed on the server. Install Python or use the online runner.');
        }

        $dir = $this->makeTempDir();
        $file = $dir . DIRECTORY_SEPARATOR . 'main.py';
        File::put($file, $code);

        $command = [$python, $file];

        return $this->execute($command, $dir, $timeout);
    }

    protected function runJavaScript(string $code, int $timeout): array
    {
        $node = $this->findExecutable(['node', 'nodejs']);

        if (!$node) {
            return $this->unavailable('Node.js is not installed on the server.');
        }

        $dir = $this->makeTempDir();
        $file = $dir . DIRECTORY_SEPARATOR . 'main.js';
        File::put($file, $code);

        return $this->execute([$node, $file], $dir, $timeout);
    }

    protected function runJava(string $code, int $timeout): array
    {
        $javac = $this->findExecutable(['javac']);
        $java = $this->findExecutable(['java']);

        if (!$javac || !$java) {
            return $this->unavailable('Java JDK is not installed on the server.');
        }

        if (!preg_match('/\bclass\s+(\w+)/', $code, $matches)) {
            return [
                'success' => false,
                'error' => 'Java code must declare a public class (e.g. public class Main).',
            ];
        }

        $className = $matches[1];
        $dir = $this->makeTempDir();
        $file = $dir . DIRECTORY_SEPARATOR . $className . '.java';
        File::put($file, $code);

        $compile = $this->execute([$javac, $file], $dir, $timeout);
        if (!$compile['success']) {
            $this->cleanup($dir);

            return $compile;
        }

        $run = $this->execute([$java, '-cp', $dir, $className], $dir, $timeout);
        $this->cleanup($dir);

        return $run;
    }

    protected function runC(string $code, int $timeout): array
    {
        $gcc = $this->findExecutable(['gcc', 'cc']);

        if (!$gcc) {
            return $this->unavailable('GCC is not installed on the server.');
        }

        $dir = $this->makeTempDir();
        $source = $dir . DIRECTORY_SEPARATOR . 'main.c';
        $binary = $dir . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'main.exe' : 'main');

        File::put($source, $code);

        $compile = $this->execute([$gcc, $source, '-o', $binary], $dir, $timeout);
        if (!$compile['success']) {
            $this->cleanup($dir);

            return $compile;
        }

        $run = $this->execute([$binary], $dir, $timeout);
        $this->cleanup($dir);

        return $run;
    }

    protected function execute(array $command, string $cwd, int $timeout): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $cwd);

        if (!is_resource($process)) {
            return [
                'success' => false,
                'error' => 'Could not start the code runner process.',
            ];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $start = time();

        while (true) {
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }

            if ((time() - $start) >= $timeout) {
                proc_terminate($process);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                return [
                    'success' => false,
                    'error' => 'Code execution timed out.',
                ];
            }

            usleep(100000);
        }

        $stdout .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $stdout = trim($stdout);
        $stderr = trim($stderr);

        if ($exitCode === 0) {
            return [
                'success' => true,
                'output' => $stdout !== '' ? $stdout : 'Program finished with no output.',
                'error' => null,
                'status' => 'success',
                'status_label' => 'Accepted (local)',
                'runner' => 'local',
            ];
        }

        return [
            'success' => false,
            'output' => $stdout,
            'error' => $stderr !== '' ? $stderr : "Process exited with code {$exitCode}.",
            'status' => 'error',
            'status_label' => 'Runtime Error (local)',
            'runner' => 'local',
        ];
    }

    protected function findExecutable(array $names): ?string
    {
        foreach ($names as $name) {
            $path = $this->which($name);
            if ($path) {
                return $path;
            }
        }

        return null;
    }

    protected function which(string $command): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $check = shell_exec('where ' . escapeshellarg($command) . ' 2>nul');

            if ($check) {
                $line = trim(explode("\n", trim($check))[0]);

                return $line !== '' ? $line : null;
            }

            return null;
        }

        $check = shell_exec('command -v ' . escapeshellarg($command) . ' 2>/dev/null');

        return $check ? trim($check) : null;
    }

    protected function makeTempDir(): string
    {
        $dir = storage_path('app/code-runner/' . Str::uuid());
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    protected function cleanup(string $dir): void
    {
        if (File::isDirectory($dir)) {
            File::deleteDirectory($dir);
        }
    }

    protected function unavailable(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'runner' => 'local',
        ];
    }
}
