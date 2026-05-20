<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $modules = [
            [
                'title' => 'Introduction to Python',
                'description' => 'Start with the basics: what Python is, how to install it, and how to run your first program.',
                'order' => 1,
                'lessons' => [
                    [
                        'slug' => 'what-is-python',
                        'title' => 'What is Python',
                        'description' => 'Explore what Python is and why it is a great first programming language for beginners.',
                        'content' => '<h2>What is Python</h2><p>Python is a friendly programming language that is easy to read and write. It is used for websites, data analysis, automation, and beginner coding practice.</p><p>Think of Python like a clear recipe language for telling a computer what to do.</p>',
                        'estimated_minutes' => 8,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'installing-python',
                        'title' => 'Installing Python',
                        'description' => 'Learn how to install Python and run the first script on your computer.',
                        'content' => '<h2>Installing Python</h2><p>Visit python.org and download the right version for your system. Then open a terminal or command prompt and run:</p><pre><code>python --version</code></pre><p>When Python is installed, use a text editor to create a file named <code>hello.py</code> and add:</p><pre><code>print("Hello, Python!")</code></pre><p>Then run it with:</p><pre><code>python hello.py</code></pre>',
                        'estimated_minutes' => 10,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'running-first-python-program',
                        'title' => 'Running first Python program',
                        'description' => 'Write and run a simple Python program to see how code works in practice.',
                        'content' => '<h2>Running first Python program</h2><p>A small program is a great first step. Create a file and write this code:</p><pre><code>name = "Learner"
print(f"Hello, {name}! Welcome to Python.")</code></pre><p>Try changing the text and running the file again. This is how you learn by doing.</p>',
                        'estimated_minutes' => 7,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'Variables and Data Types',
                'description' => 'Understand how Python stores information using variables, strings, numbers, and booleans.',
                'order' => 2,
                'lessons' => [
                    [
                        'slug' => 'variables',
                        'title' => 'Variables',
                        'description' => 'Learn how to store information in variables to reuse values across your program.',
                        'content' => '<h2>Variables</h2><p>Variables are like labeled containers for values. In Python, you can write:</p><pre><code>name = "Sofia"
age = 22
print(name)
print(age)</code></pre><p>Use variables to keep data tidy and easy to update.</p>',
                        'estimated_minutes' => 8,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'strings',
                        'title' => 'Strings',
                        'description' => 'See how text values work in Python and how to combine them with variables.',
                        'content' => '<h2>Strings</h2><p>Strings are text values. Use quotes around letters and words:</p><pre><code>message = "Hello, Python"
print(message)
print("Length:", len(message))</code></pre><p>You can also join strings with <code>+</code> or format text with f-strings.</p>',
                        'estimated_minutes' => 9,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'numbers',
                        'title' => 'Numbers',
                        'description' => 'Practice with integers and decimal numbers to do math in Python.',
                        'content' => '<h2>Numbers</h2><p>Python lets you work with numbers like a calculator.</p><pre><code>a = 5
b = 3
print(a + b)
print(a * b)
print(a / b)</code></pre><p>Try changing the numbers to see different results.</p>',
                        'estimated_minutes' => 10,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'boolean',
                        'title' => 'Boolean',
                        'description' => 'Learn about true and false values and how they help your program make decisions.',
                        'content' => '<h2>Boolean</h2><p>Booleans are either <code>True</code> or <code>False</code>.</p><pre><code>is_hungry = True
is_sunny = False
print(is_hungry)
print(is_sunny)</code></pre><p>Booleans are useful when checking answers or deciding what to do next.</p>',
                        'estimated_minutes' => 6,
                        'order' => 4,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'Conditions',
                'description' => 'Use if, else, and elif to let the computer make choices based on your data.',
                'order' => 3,
                'lessons' => [
                    [
                        'slug' => 'if-statements',
                        'title' => 'if statements',
                        'description' => 'Start writing conditions that run code only when something is true.',
                        'content' => '<h2>if statements</h2><p>Use <code>if</code> to check a condition:</p><pre><code>age = 18
if age >= 18:
    print("You can vote.")</code></pre><p>Only the code inside the <code>if</code> block runs when the condition is true.</p>',
                        'estimated_minutes' => 8,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'else',
                        'title' => 'else',
                        'description' => 'Add a fallback path with else when the first condition is not met.',
                        'content' => '<h2>else</h2><p>Use <code>else</code> to say what should happen when an <code>if</code> condition is false.</p><pre><code>age = 15
if age >= 18:
    print("You can vote.")
else:
    print("You are too young to vote yet.")</code></pre>',
                        'estimated_minutes' => 7,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'elif',
                        'title' => 'elif',
                        'description' => 'Check multiple conditions with elif so your program can choose between several options.',
                        'content' => '<h2>elif</h2><p>Use <code>elif</code> to add another check after an <code>if</code>:</p><pre><code>score = 75
if score >= 90:
    print("Excellent")
elif score >= 70:
    print("Good job")
else:
    print("Keep practicing")</code></pre>',
                        'estimated_minutes' => 8,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'Loops',
                'description' => 'Repeat actions automatically using for loops, while loops, and loop control statements.',
                'order' => 4,
                'lessons' => [
                    [
                        'slug' => 'for-loop',
                        'title' => 'for loop',
                        'description' => 'Repeat instructions using a for loop to process a list of values.',
                        'content' => '<h2>for loop</h2><p>For loops go through items one by one.</p><pre><code>for number in [1, 2, 3]:
    print(number)</code></pre><p>This prints each number in the list.</p>',
                        'estimated_minutes' => 9,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'while-loop',
                        'title' => 'while loop',
                        'description' => 'Use while loops to repeat instructions until a condition changes.',
                        'content' => '<h2>while loop</h2><p>A while loop keeps running while a condition is true.</p><pre><code>count = 0
while count < 3:
    print(count)
    count += 1</code></pre>',
                        'estimated_minutes' => 9,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'break-and-continue',
                        'title' => 'break and continue',
                        'description' => 'Control loops with break and continue to stop early or skip one loop cycle.',
                        'content' => '<h2>break and continue</h2><p>Use <code>break</code> to stop a loop early and <code>continue</code> to skip to the next item.</p><pre><code>for number in range(1, 6):
    if number == 3:
        continue
    if number == 5:
        break
    print(number)</code></pre>',
                        'estimated_minutes' => 10,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'Functions',
                'description' => 'Learn how to group code into reusable functions with parameters and return values.',
                'order' => 5,
                'lessons' => [
                    [
                        'slug' => 'creating-functions',
                        'title' => 'Creating functions',
                        'description' => 'Define a function to keep your code organized and reusable.',
                        'content' => '<h2>Creating functions</h2><p>Functions let you name a task and run it again later.</p><pre><code>def greet():
    print("Hello from a function")

greet()</code></pre>',
                        'estimated_minutes' => 9,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'parameters',
                        'title' => 'Parameters',
                        'description' => 'Send information into a function using parameters so it can work with different values.',
                        'content' => '<h2>Parameters</h2><p>Parameters are values a function can use from outside.</p><pre><code>def greet(name):
    print(f"Hello, {name}!")

greet("Mia")</code></pre>',
                        'estimated_minutes' => 8,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'return-values',
                        'title' => 'Return values',
                        'description' => 'Have functions send back results with return values so other code can use them.',
                        'content' => '<h2>Return values</h2><p>Use <code>return</code> to send a result back from a function.</p><pre><code>def add(a, b):
    return a + b

result = add(4, 5)
print(result)</code></pre>',
                        'estimated_minutes' => 9,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'Lists and Dictionaries',
                'description' => 'Discover how to store and use collections of data with lists, tuples, dictionaries, and loops.',
                'order' => 6,
                'lessons' => [
                    [
                        'slug' => 'lists',
                        'title' => 'Lists',
                        'description' => 'Store ordered items with lists and use loops to read them.',
                        'content' => '<h2>Lists</h2><p>Lists keep items in order.</p><pre><code>colors = ["red", "green", "blue"]
for color in colors:
    print(color)</code></pre>',
                        'estimated_minutes' => 9,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'tuples',
                        'title' => 'Tuples',
                        'description' => 'Use tuples when you want a collection that does not change.',
                        'content' => '<h2>Tuples</h2><p>Tuples are like lists but fixed in place.</p><pre><code>point = (3, 5)
print(point[0])
print(point[1])</code></pre>',
                        'estimated_minutes' => 7,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'dictionaries',
                        'title' => 'Dictionaries',
                        'description' => 'Store data with keys and values to look up information quickly.',
                        'content' => '<h2>Dictionaries</h2><p>Dictionaries store labeled values.</p><pre><code>student = {"name": "Alex", "age": 20}
print(student["name"])
print(student["age"])</code></pre>',
                        'estimated_minutes' => 10,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'loops-with-collections',
                        'title' => 'Loops with collections',
                        'description' => 'Loop over lists and dictionaries to work with groups of data efficiently.',
                        'content' => '<h2>Loops with collections</h2><p>Use loops with lists and dictionaries.</p><pre><code>shapes = ["circle", "square"]
for shape in shapes:
    print(shape)

student = {"name": "Nia", "level": "beginner"}
for key, value in student.items():
    print(key, value)</code></pre>',
                        'estimated_minutes' => 10,
                        'order' => 4,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'Object Oriented Programming',
                'description' => 'Meet classes, objects, and constructors for organizing code in a beginner-friendly way.',
                'order' => 7,
                'lessons' => [
                    [
                        'slug' => 'classes',
                        'title' => 'Classes',
                        'description' => 'Create a class to define a new type of object with data and behavior.',
                        'content' => '<h2>Classes</h2><p>A class is a blueprint for objects.</p><pre><code>class Person:
    def __init__(self, name):
        self.name = name</code></pre>',
                        'estimated_minutes' => 11,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'objects',
                        'title' => 'Objects',
                        'description' => 'Use objects to store state and run actions defined by a class.',
                        'content' => '<h2>Objects</h2><p>Objects are created from classes.</p><pre><code>class Person:
    def __init__(self, name):
        self.name = name

student = Person("Ari")
print(student.name)</code></pre>',
                        'estimated_minutes' => 9,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'constructors',
                        'title' => 'Constructors',
                        'description' => 'Build objects with constructors to set up their initial values automatically.',
                        'content' => '<h2>Constructors</h2><p>A constructor sets up a new object when it is created.</p><pre><code>class Book:
    def __init__(self, title):
        self.title = title

book = Book("Python Guide")
print(book.title)</code></pre>',
                        'estimated_minutes' => 10,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'APIs and JSON',
                'description' => 'Learn how Python works with APIs and how to read and write JSON data.',
                'order' => 8,
                'lessons' => [
                    [
                        'slug' => 'fetch-apis',
                        'title' => 'Fetch APIs',
                        'description' => 'Understand how to connect Python to web APIs and retrieve data from the internet.',
                        'content' => '<h2>Fetch APIs</h2><p>In Python, you can ask a web service for data. This lesson introduces the idea of fetching information from APIs.</p>',
                        'estimated_minutes' => 12,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'json-handling',
                        'title' => 'JSON handling',
                        'description' => 'Use JSON to store and work with structured data in Python programs.',
                        'content' => '<h2>JSON handling</h2><p>JSON is a common format for exchanging data online. Python can read and write JSON using its built-in library.</p>',
                        'estimated_minutes' => 12,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
            [
                'title' => 'Beginner Projects',
                'description' => 'Build three simple Python mini projects to practice the concepts you have learned.',
                'order' => 9,
                'lessons' => [
                    [
                        'slug' => 'calculator',
                        'title' => 'Calculator',
                        'description' => 'Create a simple calculator program that adds, subtracts, multiplies, and divides.',
                        'content' => '<h2>Calculator</h2><p>Build a friendly calculator that asks for two numbers and shows the result.</p>',
                        'estimated_minutes' => 15,
                        'order' => 1,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'todo-app',
                        'title' => 'Todo App',
                        'description' => 'Make a simple todo list in Python that stores tasks and marks them complete.',
                        'content' => '<h2>Todo App</h2><p>Create a small todo app that records a list of items and lets you add a new task.</p>',
                        'estimated_minutes' => 15,
                        'order' => 2,
                        'difficulty' => 'beginner',
                    ],
                    [
                        'slug' => 'weather-app',
                        'title' => 'Weather App',
                        'description' => 'Build a weather app concept that explains how you would fetch forecast data from an API.',
                        'content' => '<h2>Weather App</h2><p>Learn how a weather app works by organizing location, API data, and simple output.</p>',
                        'estimated_minutes' => 15,
                        'order' => 3,
                        'difficulty' => 'beginner',
                    ],
                ],
            ],
        ];

        foreach ($modules as $moduleData) {
            $module = Module::firstOrCreate([
                'slug' => Str::slug($moduleData['title']),
            ], [
                'title' => $moduleData['title'],
                'description' => $moduleData['description'],
                'order' => $moduleData['order'],
            ]);

            foreach ($moduleData['lessons'] as $lessonData) {
                Lesson::firstOrCreate([
                    'slug' => $lessonData['slug'],
                ], array_merge($lessonData, [
                    'module_id' => $module->id,
                    'language' => 'Python',
                ]));
            }
        }
    }
}
