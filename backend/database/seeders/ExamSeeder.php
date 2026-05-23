<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $exams = [
            [
                'title' => 'Python Basics Checkpoint',
                'track' => 'python',
                'description' => 'Quick check on variables, types, and your first programs.',
                'duration_minutes' => 15,
                'passing_score' => 70,
                'order' => 1,
                'questions' => [
                    [
                        'question' => 'Which function prints text to the screen in Python?',
                        'options' => ['echo()', 'print()', 'console.log()', 'printf()'],
                        'correct_answer' => 'print()',
                        'explanation' => 'print() is the built-in way to show output in Python.',
                    ],
                    [
                        'question' => 'What type is the value True?',
                        'options' => ['string', 'boolean', 'integer', 'list'],
                        'correct_answer' => 'boolean',
                        'explanation' => 'True and False are boolean values.',
                    ],
                    [
                        'question' => 'How do you start a comment in Python?',
                        'options' => ['//', '#', '/*', '--'],
                        'correct_answer' => '#',
                        'explanation' => 'A # starts a comment in Python.',
                    ],
                ],
            ],
            [
                'title' => 'JavaScript Fundamentals Quiz',
                'track' => 'javascript',
                'description' => 'Test your knowledge of JS syntax, variables, and console output.',
                'duration_minutes' => 15,
                'passing_score' => 70,
                'order' => 2,
                'questions' => [
                    [
                        'question' => 'Which keyword declares a block-scoped variable?',
                        'options' => ['var', 'let', 'define', 'static'],
                        'correct_answer' => 'let',
                        'explanation' => 'let creates block-scoped variables in modern JavaScript.',
                    ],
                    [
                        'question' => 'How do you log output in Node/browser JS?',
                        'options' => ['print()', 'console.log()', 'echo()', 'System.out.println()'],
                        'correct_answer' => 'console.log()',
                        'explanation' => 'console.log() is the standard logging method in JavaScript.',
                    ],
                ],
            ],
            [
                'title' => 'Java Starter Assessment',
                'track' => 'java',
                'description' => 'Classes, main method, and basic Java syntax.',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'order' => 3,
                'questions' => [
                    [
                        'question' => 'Every Java application starts execution in which method?',
                        'options' => ['start()', 'main()', 'run()', 'init()'],
                        'correct_answer' => 'main()',
                        'explanation' => 'The JVM calls public static void main(String[] args).',
                    ],
                    [
                        'question' => 'Which is the correct file extension for Java source code?',
                        'options' => ['.class', '.java', '.jar', '.js'],
                        'correct_answer' => '.java',
                        'explanation' => 'Source files use the .java extension.',
                    ],
                ],
            ],
            [
                'title' => 'C Programming Quick Test',
                'track' => 'c',
                'description' => 'Pointers intro, printf, and program structure basics.',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'order' => 4,
                'questions' => [
                    [
                        'question' => 'Which header is commonly used for printf in C?',
                        'options' => ['<iostream>', '<stdio.h>', '<string>', '<python.h>'],
                        'correct_answer' => '<stdio.h>',
                        'explanation' => 'printf is declared in stdio.h.',
                    ],
                ],
            ],
            [
                'title' => 'React Concepts Check',
                'track' => 'react',
                'description' => 'Components, props, and JSX fundamentals.',
                'duration_minutes' => 15,
                'passing_score' => 70,
                'order' => 5,
                'questions' => [
                    [
                        'question' => 'What is JSX?',
                        'options' => ['A database', 'A syntax extension that looks like HTML in JS', 'A CSS framework', 'A package manager'],
                        'correct_answer' => 'A syntax extension that looks like HTML in JS',
                        'explanation' => 'JSX lets you write UI-like markup inside JavaScript.',
                    ],
                    [
                        'question' => 'In React, data passed from parent to child is called:',
                        'options' => ['state', 'props', 'hooks', 'refs'],
                        'correct_answer' => 'props',
                        'explanation' => 'Props are read-only inputs passed to components.',
                    ],
                ],
            ],
        ];

        foreach ($exams as $examData) {
            $questions = $examData['questions'];
            unset($examData['questions']);

            $exam = Exam::firstOrCreate(
                ['slug' => Str::slug($examData['title'])],
                array_merge($examData, ['is_published' => true])
            );

            foreach ($questions as $index => $q) {
                ExamQuestion::firstOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'question' => $q['question'],
                    ],
                    [
                        'type' => 'mcq',
                        'options' => $q['options'],
                        'correct_answer' => $q['correct_answer'],
                        'explanation' => $q['explanation'] ?? null,
                        'points' => 1,
                        'order' => $index + 1,
                    ]
                );
            }
        }
    }
}
