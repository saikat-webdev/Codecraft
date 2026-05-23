<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Module;
use Database\Seeders\Support\LessonHtml;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FullCourseModulesSeeder extends Seeder
{
    public function run(): void
    {
        Module::query()->whereNull('track')->orWhere('track', '')->update(['track' => 'python', 'icon' => '🐍']);

        foreach ($this->tracks() as $track => $config) {
            $this->seedTrack($track, $config['icon'], $config['modules']);
        }

        $this->call(FixLessonContentSeeder::class);
    }

    protected function seedTrack(string $track, string $icon, array $modules): void
    {
        foreach ($modules as $moduleData) {
            $module = Module::updateOrCreate(
                ['slug' => Str::slug($moduleData['title'])],
                [
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'],
                    'order' => $moduleData['order'],
                    'track' => $track,
                    'icon' => $icon,
                ]
            );

            foreach ($moduleData['lessons'] as $lessonData) {
                Lesson::updateOrCreate(
                    ['slug' => $lessonData['slug']],
                    [
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'description' => $lessonData['description'],
                        'content' => $lessonData['content'],
                        'estimated_minutes' => $lessonData['estimated_minutes'],
                        'order' => $lessonData['order'],
                        'difficulty' => $lessonData['difficulty'] ?? 'beginner',
                        'language' => $lessonData['language'] ?? ucfirst($track === 'react' ? 'JavaScript' : $track),
                    ]
                );
            }
        }
    }

    protected function tracks(): array
    {
        $H = LessonHtml::class;

        return [
            'javascript' => [
                'icon' => '⚡',
                'modules' => [
                    $this->module('JavaScript Basics', 'Variables, output, and your first scripts.', 101, [
                        $this->lesson('js-what-is-javascript', 'What is JavaScript?', 'Discover where JS runs.', 1, 8, $H::join([$H::h2('What is JavaScript?'), $H::p('JavaScript powers the web.')])),
                        $this->lesson('js-variables-and-let', 'Variables with let and const', 'Store and reuse values.', 2, 10),
                        $this->lesson('js-console-output', 'Console output', 'See results with console.log.', 3, 7),
                    ]),
                    $this->module('JavaScript Functions', 'Reusable logic with functions and arrows.', 102, [
                        $this->lesson('js-functions-intro', 'Creating functions', 'Define reusable blocks.', 1, 10),
                        $this->lesson('js-arrow-functions', 'Arrow functions', 'Short function syntax.', 2, 9),
                    ]),
                    $this->module('JavaScript Control Flow', 'Conditions and loops.', 103, [
                        $this->lesson('js-conditionals', 'if / else', 'Make decisions in code.', 1, 9),
                        $this->lesson('js-loops', 'for loops', 'Repeat with for...of.', 2, 10),
                    ]),
                    $this->module('JavaScript Data', 'Arrays and list processing.', 104, [
                        $this->lesson('js-arrays', 'Arrays', 'Ordered collections.', 1, 10),
                    ]),
                    $this->module('JavaScript in the Browser', 'DOM concepts for beginners.', 105, [
                        $this->lesson('js-dom-intro', 'The DOM (concept)', 'How JS connects to HTML.', 1, 12),
                        $this->lesson('js-mini-project-greeting', 'Mini project: Greeting app', 'Combine what you learned.', 2, 15),
                    ]),
                ],
            ],
            'java' => [
                'icon' => '☕',
                'modules' => [
                    $this->module('Java Getting Started', 'Classes, main, and println.', 201, [
                        $this->lesson('java-hello-world', 'Hello World in Java', 'Your first Java app.', 1, 12),
                        $this->lesson('java-variables', 'Variables and types', 'int, String, and more.', 2, 10),
                        $this->lesson('java-input-output', 'Printing values', 'Format output in the console.', 3, 8),
                    ]),
                    $this->module('Java Control Flow', 'if statements and loops.', 202, [
                        $this->lesson('java-if-else', 'if / else', 'Branch your program.', 1, 9),
                        $this->lesson('java-loops', 'for loops', 'Repeat with for.', 2, 10),
                    ]),
                    $this->module('Java Methods', 'Organize code with methods.', 203, [
                        $this->lesson('java-methods', 'Methods', 'Reusable blocks in a class.', 1, 11),
                    ]),
                ],
            ],
            'c' => [
                'icon' => '🔧',
                'modules' => [
                    $this->module('C Programming Foundations', 'printf, types, and main.', 301, [
                        $this->lesson('c-hello-world', 'Hello World in C', 'Classic first program.', 1, 12),
                        $this->lesson('c-variables', 'Variables in C', 'int and printf.', 2, 10),
                        $this->lesson('c-data-types', 'Data types', 'int, float, char.', 3, 10),
                    ]),
                    $this->module('C Control Flow', 'Decisions and loops in C.', 302, [
                        $this->lesson('c-if-else', 'if / else', 'Conditional logic.', 1, 9),
                        $this->lesson('c-loops', 'for loops', 'Count with for.', 2, 10),
                    ]),
                    $this->module('C Functions', 'Split programs into functions.', 303, [
                        $this->lesson('c-functions', 'Functions', 'Reusable C functions.', 1, 11),
                    ]),
                ],
            ],
            'react' => [
                'icon' => '⚛️',
                'modules' => [
                    $this->module('React Essentials', 'Components, JSX, and props.', 401, [
                        $this->lesson('react-what-is-react', 'What is React?', 'UI from components.', 1, 8),
                        $this->lesson('react-jsx-basics', 'JSX basics', 'Markup inside JS.', 2, 10),
                        $this->lesson('react-props-concept', 'Props concept', 'Pass data to components.', 3, 9),
                    ]),
                    $this->module('React State & UI', 'State and rendering lists.', 402, [
                        $this->lesson('react-state-concept', 'State (concept)', 'Changing data over time.', 1, 10),
                        $this->lesson('react-components', 'Components', 'Build small UI pieces.', 2, 10),
                        $this->lesson('react-lists', 'Rendering lists', 'Show collections of data.', 3, 11),
                    ]),
                ],
            ],
        ];
    }

    protected function module(string $title, string $description, int $order, array $lessons): array
    {
        return compact('title', 'description', 'order', 'lessons');
    }

    protected function lesson(
        string $slug,
        string $title,
        string $description,
        int $order,
        int $minutes,
        ?string $content = null
    ): array {
        return [
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'order' => $order,
            'estimated_minutes' => $minutes,
            'content' => $content ?? '<p>Content loading…</p>',
        ];
    }
}
