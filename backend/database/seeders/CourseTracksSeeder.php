<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseTracksSeeder extends Seeder
{
    public function run(): void
    {
        Module::query()->whereNull('track')->orWhere('track', '')->update(['track' => 'python', 'icon' => '🐍']);

        $tracks = [
            'javascript' => [
                'icon' => '⚡',
                'modules' => [
                    [
                        'title' => 'JavaScript Basics',
                        'description' => 'Learn variables, strings, and your first console programs.',
                        'order' => 101,
                        'lessons' => [
                            ['slug' => 'js-what-is-javascript', 'title' => 'What is JavaScript?', 'description' => 'Discover where JS runs and why beginners love it.', 'content' => '<h2>What is JavaScript?</h2><p>JavaScript powers interactive websites and runs in browsers and Node.js.</p>', 'estimated_minutes' => 8, 'order' => 1],
                            ['slug' => 'js-variables-and-let', 'title' => 'Variables with let and const', 'description' => 'Store values and update them safely.', 'content' => '<h2>Variables</h2><pre><code>let name = "Learner";\nconst year = 2026;\nconsole.log(name);</code></pre>', 'estimated_minutes' => 10, 'order' => 2],
                            ['slug' => 'js-console-output', 'title' => 'Console output', 'description' => 'Use console.log to see results.', 'content' => '<h2>Output</h2><pre><code>console.log("Hello, CodeCraft!");</code></pre>', 'estimated_minutes' => 7, 'order' => 3],
                        ],
                    ],
                    [
                        'title' => 'JavaScript Functions',
                        'description' => 'Reusable blocks of code with parameters and return values.',
                        'order' => 102,
                        'lessons' => [
                            ['slug' => 'js-functions-intro', 'title' => 'Creating functions', 'description' => 'Define functions with the function keyword or arrows.', 'content' => '<h2>Functions</h2><pre><code>function greet(name) {\n  return `Hello, ${name}!`;\n}\nconsole.log(greet("Mia"));</code></pre>', 'estimated_minutes' => 10, 'order' => 1],
                            ['slug' => 'js-arrow-functions', 'title' => 'Arrow functions', 'description' => 'A shorter syntax for small functions.', 'content' => '<h2>Arrow functions</h2><pre><code>const add = (a, b) => a + b;\nconsole.log(add(2, 3));</code></pre>', 'estimated_minutes' => 9, 'order' => 2],
                        ],
                    ],
                ],
            ],
            'java' => [
                'icon' => '☕',
                'modules' => [
                    [
                        'title' => 'Java Getting Started',
                        'description' => 'Classes, main method, and your first Java program.',
                        'order' => 201,
                        'lessons' => [
                            ['slug' => 'java-hello-world', 'title' => 'Hello World in Java', 'description' => 'Structure of a minimal Java application.', 'content' => '<h2>Hello World</h2><pre><code>public class Main {\n  public static void main(String[] args) {\n    System.out.println("Hello, CodeCraft!");\n  }\n}</code></pre>', 'estimated_minutes' => 12, 'order' => 1],
                            ['slug' => 'java-variables', 'title' => 'Variables and types', 'description' => 'int, String, and declaring variables.', 'content' => '<h2>Variables</h2><pre><code>int age = 20;\nString name = "Alex";\nSystem.out.println(name + " is " + age);</code></pre>', 'estimated_minutes' => 10, 'order' => 2],
                        ],
                    ],
                ],
            ],
            'c' => [
                'icon' => '🔧',
                'modules' => [
                    [
                        'title' => 'C Programming Foundations',
                        'description' => 'printf, variables, and compiling your first C program.',
                        'order' => 301,
                        'lessons' => [
                            ['slug' => 'c-hello-world', 'title' => 'Hello World in C', 'description' => 'The classic first C program.', 'content' => '<h2>Hello World</h2><pre><code>#include &lt;stdio.h&gt;\nint main() {\n  printf("Hello, CodeCraft!\\n");\n  return 0;\n}</code></pre>', 'estimated_minutes' => 12, 'order' => 1],
                            ['slug' => 'c-variables', 'title' => 'Variables in C', 'description' => 'int, float, and char basics.', 'content' => '<h2>Variables</h2><pre><code>int count = 3;\nprintf("Count: %d\\n", count);</code></pre>', 'estimated_minutes' => 10, 'order' => 2],
                        ],
                    ],
                ],
            ],
            'react' => [
                'icon' => '⚛️',
                'modules' => [
                    [
                        'title' => 'React Essentials',
                        'description' => 'Components, JSX, and props for beginner UI building.',
                        'order' => 401,
                        'lessons' => [
                            ['slug' => 'react-what-is-react', 'title' => 'What is React?', 'description' => 'Why React helps you build user interfaces.', 'content' => '<h2>React</h2><p>React lets you build UIs from reusable components. Practice JSX thinking even before a full build setup.</p>', 'estimated_minutes' => 8, 'order' => 1],
                            ['slug' => 'react-jsx-basics', 'title' => 'JSX basics', 'description' => 'Write markup inside JavaScript.', 'content' => '<h2>JSX</h2><pre><code>const title = "CodeCraft";\n// JSX-like practice in JS:\nconst element = `&lt;h1&gt;${title}&lt;/h1&gt;`;\nconsole.log(element);</code></pre>', 'estimated_minutes' => 10, 'order' => 2],
                            ['slug' => 'react-props-concept', 'title' => 'Props concept', 'description' => 'Pass data into components.', 'content' => '<h2>Props</h2><p>Think of props as function arguments for UI components.</p>', 'estimated_minutes' => 9, 'order' => 3],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($tracks as $track => $config) {
            foreach ($config['modules'] as $moduleData) {
                $module = Module::firstOrCreate(
                    ['slug' => Str::slug($moduleData['title'])],
                    [
                        'title' => $moduleData['title'],
                        'description' => $moduleData['description'],
                        'order' => $moduleData['order'],
                        'track' => $track,
                        'icon' => $config['icon'],
                    ]
                );

                $module->update(['track' => $track, 'icon' => $config['icon']]);

                foreach ($moduleData['lessons'] as $lessonData) {
                    Lesson::firstOrCreate(
                        ['slug' => $lessonData['slug']],
                        array_merge($lessonData, [
                            'module_id' => $module->id,
                            'language' => ucfirst($track === 'react' ? 'JavaScript' : ($track === 'c' ? 'C' : ucfirst($track))),
                            'difficulty' => 'beginner',
                        ])
                    );
                }
            }
        }
    }
}
