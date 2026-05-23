<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $quizzes = [
            'what-is-python' => [
                [
                    'question' => 'What best describes Python?',
                    'options' => ['A compiled language only', 'A readable, versatile programming language', 'A database system', 'A web browser'],
                    'correct_answer' => 'A readable, versatile programming language',
                    'explanation' => 'Python is known for clear syntax and is used for web, data, automation, and more.',
                ],
            ],
            'variables' => [
                [
                    'question' => 'Which line creates a variable named age with value 20?',
                    'options' => ['age := 20', 'age = 20', 'var age = 20', 'int age 20'],
                    'correct_answer' => 'age = 20',
                    'explanation' => 'In Python you assign with a single equals sign: age = 20',
                ],
            ],
            'if-statements' => [
                [
                    'question' => 'When does code inside an if block run?',
                    'options' => ['Always', 'When the condition is True', 'Only on Sundays', 'Never'],
                    'correct_answer' => 'When the condition is True',
                    'explanation' => 'The if block runs only when the condition evaluates to True.',
                ],
            ],
        ];

        foreach ($quizzes as $slug => $items) {
            $lesson = Lesson::where('slug', $slug)->first();
            if (!$lesson) {
                continue;
            }

            foreach ($items as $item) {
                Quiz::firstOrCreate(
                    [
                        'lesson_id' => $lesson->id,
                        'question' => $item['question'],
                    ],
                    [
                        'options' => $item['options'],
                        'correct_answer' => $item['correct_answer'],
                        'explanation' => $item['explanation'],
                    ]
                );
            }
        }
    }
}
