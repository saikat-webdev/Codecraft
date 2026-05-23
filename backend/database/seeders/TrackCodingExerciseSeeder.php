<?php

namespace Database\Seeders;

use App\Models\CodingExercise;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class TrackCodingExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            'js-console-output' => [
                'title' => 'Print your name',
                'description' => 'Use console.log to print your name.',
                'starter_code' => "const name = \"Learner\";\nconsole.log(name);",
                'expected_output' => 'Learner',
            ],
            'java-hello-world' => [
                'title' => 'Run Hello World',
                'description' => 'Print Hello, CodeCraft! from main.',
                'starter_code' => "public class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hello, CodeCraft!\");\n    }\n}",
                'expected_output' => 'Hello, CodeCraft!',
            ],
            'c-hello-world' => [
                'title' => 'Hello in C',
                'description' => 'Print Hello, CodeCraft! using printf.',
                'starter_code' => "#include <stdio.h>\n\nint main() {\n    printf(\"Hello, CodeCraft!\\n\");\n    return 0;\n}",
                'expected_output' => 'Hello, CodeCraft!',
            ],
            'react-jsx-basics' => [
                'title' => 'JSX string practice',
                'description' => 'Log a greeting built with a template string.',
                'starter_code' => 'const name = "Learner";' . "\n" . 'console.log(`Hello, ${name}!`);',
                'expected_output' => 'Hello, Learner!',
            ],
        ];

        foreach ($exercises as $slug => $data) {
            $lesson = Lesson::where('slug', $slug)->first();
            if (!$lesson) {
                continue;
            }

            CodingExercise::firstOrCreate(
                ['lesson_id' => $lesson->id, 'title' => $data['title']],
                array_merge($data, ['difficulty' => 'beginner', 'order' => 1])
            );
        }
    }
}
