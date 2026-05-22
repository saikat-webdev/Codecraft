<?php

namespace App\Services;

use App\Models\CodingExercise;

/**
 * Local, deterministic exercise feedback (no external AI).
 * Keeps lesson submissions useful without Gemini/OpenAI dependencies.
 */
class CodeEvaluationService
{
    public function evaluateCode(string $code, CodingExercise $exercise, ?string $output = null, ?string $error = null): string
    {
        return $this->getLocalFeedback($code, $exercise, $output, $error);
    }

    public function getHints(CodingExercise $exercise): array
    {
        return $this->getLocalHints($exercise);
    }

    protected function getLocalFeedback(string $code, CodingExercise $exercise, ?string $output, ?string $error): string
    {
        if ($error) {
            if (str_contains($error, 'SyntaxError')) {
                return "Check your syntax. Common issues: missing colons, mismatched quotes, or indentation.\n\nYou're almost there!";
            }

            if (str_contains($error, 'IndentationError')) {
                return "Indentation matters in Python. Use consistent spaces (4 recommended) and avoid mixing tabs and spaces.";
            }

            return "There's a runtime or compile error. Read the message above, fix one issue at a time, then run again.";
        }

        if ($output && $exercise->expected_output) {
            $normalizedOutput = trim($output);
            $normalizedExpected = trim($exercise->expected_output);

            if (str_contains($normalizedOutput, $normalizedExpected) || $normalizedOutput === $normalizedExpected) {
                return 'Great job! Your output matches the expected result.';
            }

            return "Your code runs, but the output doesn't match yet.\n\nExpected to see: {$exercise->expected_output}";
        }

        return 'Code executed successfully. Compare your output with the exercise requirements.';
    }

    protected function getLocalHints(CodingExercise $exercise): array
    {
        return [
            "Read \"{$exercise->title}\" carefully and list the inputs and outputs you need.",
            'Break the problem into small steps. Pseudocode on paper often helps before coding.',
            "Test with the expected output in mind: {$exercise->expected_output}",
        ];
    }
}
