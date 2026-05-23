<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Database\Seeders\Support\LessonHtml;
use Illuminate\Database\Seeder;

/**
 * Re-seeds lesson HTML for track courses with proper code formatting (real newlines, not literal \n).
 */
class FixLessonContentSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = $this->lessonDefinitions();

        foreach ($lessons as $slug => $content) {
            Lesson::where('slug', $slug)->update(['content' => $content]);
        }

        $this->command?->info('Updated ' . count($lessons) . ' lesson content blocks.');
    }

    protected function lessonDefinitions(): array
    {
        $H = LessonHtml::class;

        return array_merge(
            $this->javascriptLessons($H),
            $this->javaLessons($H),
            $this->cLessons($H),
            $this->reactLessons($H),
        );
    }

    protected function javascriptLessons(string $H): array
    {
        return [
            'js-what-is-javascript' => $H::join([
                $H::h2('What is JavaScript?'),
                $H::p('JavaScript (JS) is the language of the web. It runs in every browser and can also run on servers with Node.js.'),
                $H::p('You use JS to make pages interactive: buttons, forms, animations, and apps.'),
                $H::ul([
                    'Runs in the browser — no install needed to start learning',
                    'Also powers mobile apps and servers (Node.js)',
                    'Friendly syntax for beginners who know HTML basics',
                ]),
            ]),
            'js-variables-and-let' => $H::join([
                $H::h2('Variables with let and const'),
                $H::p('Use let for values that change, and const for values that stay the same.'),
                $H::code(<<<'CODE'
let name = "Learner";
const year = 2026;
console.log(name);
console.log(year);
CODE),
                $H::p('Try changing name and running the code in the playground.'),
            ]),
            'js-console-output' => $H::join([
                $H::h2('Console output'),
                $H::p('console.log prints messages — perfect for learning and debugging.'),
                $H::code(<<<'CODE'
console.log("Hello, CodeCraft!");
console.log(2 + 2);
CODE),
            ]),
            'js-functions-intro' => $H::join([
                $H::h2('Creating functions'),
                $H::p('Functions group steps so you can reuse them.'),
                $H::code(<<<'CODE'
function greet(name) {
  return `Hello, ${name}!`;
}

console.log(greet("Mia"));
CODE),
            ]),
            'js-arrow-functions' => $H::join([
                $H::h2('Arrow functions'),
                $H::p('A shorter way to write small functions.'),
                $H::code(<<<'CODE'
const add = (a, b) => a + b;
console.log(add(2, 3));
CODE),
            ]),
            'js-conditionals' => $H::join([
                $H::h2('if / else'),
                $H::p('Let your program make decisions.'),
                $H::code(<<<'CODE'
const score = 85;

if (score >= 90) {
  console.log("Excellent!");
} else if (score >= 70) {
  console.log("Good job!");
} else {
  console.log("Keep practicing!");
}
CODE),
            ]),
            'js-loops' => $H::join([
                $H::h2('for loops'),
                $H::p('Repeat actions for each item in a list.'),
                $H::code(<<<'CODE'
const colors = ["red", "green", "blue"];

for (const color of colors) {
  console.log(color);
}
CODE),
            ]),
            'js-arrays' => $H::join([
                $H::h2('Arrays'),
                $H::p('Store ordered lists of values.'),
                $H::code(<<<'CODE'
const fruits = ["apple", "banana", "cherry"];
console.log(fruits[0]);
console.log(fruits.length);
CODE),
            ]),
            'js-dom-intro' => $H::join([
                $H::h2('The DOM (concept)'),
                $H::p('The DOM is how JavaScript sees the HTML page. In the browser you can select elements and change text.'),
                $H::code(<<<'CODE'
// In a browser console (not Node):
// document.querySelector("h1").textContent = "Hello!";
console.log("DOM = Document Object Model");
CODE),
            ]),
            'js-mini-project-greeting' => $H::join([
                $H::h2('Mini project: Greeting app'),
                $H::p('Combine variables, functions, and console output.'),
                $H::code(<<<'CODE'
function buildGreeting(name, mood) {
  return `Hi ${name}! Hope you're feeling ${mood}.`;
}

console.log(buildGreeting("Sam", "curious"));
CODE),
            ]),
        ];
    }

    protected function javaLessons(string $H): array
    {
        return [
            'java-hello-world' => $H::join([
                $H::h2('Hello World in Java'),
                $H::p('Every Java program needs a class and a main method.'),
                $H::code(<<<'CODE'
public class Main {
    public static void main(String[] args) {
        System.out.println("Hello, CodeCraft!");
    }
}
CODE),
            ]),
            'java-variables' => $H::join([
                $H::h2('Variables and types'),
                $H::p('Java uses types like int and String.'),
                $H::code(<<<'CODE'
public class Main {
    public static void main(String[] args) {
        int age = 20;
        String name = "Alex";
        System.out.println(name + " is " + age);
    }
}
CODE),
            ]),
            'java-input-output' => $H::join([
                $H::h2('Printing values'),
                $H::p('Use System.out.println for output.'),
                $H::code(<<<'CODE'
public class Main {
    public static void main(String[] args) {
        double price = 9.99;
        System.out.println("Price: " + price);
    }
}
CODE),
            ]),
            'java-if-else' => $H::join([
                $H::h2('if / else'),
                $H::code(<<<'CODE'
public class Main {
    public static void main(String[] args) {
        int temp = 18;
        if (temp >= 25) {
            System.out.println("Hot day");
        } else {
            System.out.println("Cool day");
        }
    }
}
CODE),
            ]),
            'java-loops' => $H::join([
                $H::h2('for loops'),
                $H::code(<<<'CODE'
public class Main {
    public static void main(String[] args) {
        for (int i = 1; i <= 5; i++) {
            System.out.println(i);
        }
    }
}
CODE),
            ]),
            'java-methods' => $H::join([
                $H::h2('Methods'),
                $H::p('Methods are functions inside a class.'),
                $H::code(<<<'CODE'
public class Main {
    static int add(int a, int b) {
        return a + b;
    }

    public static void main(String[] args) {
        System.out.println(add(3, 4));
    }
}
CODE),
            ]),
        ];
    }

    protected function cLessons(string $H): array
    {
        return [
            'c-hello-world' => $H::join([
                $H::h2('Hello World in C'),
                $H::p('C programs use #include and a main function.'),
                $H::code(<<<'CODE'
#include <stdio.h>

int main() {
    printf("Hello, CodeCraft!\n");
    return 0;
}
CODE),
            ]),
            'c-variables' => $H::join([
                $H::h2('Variables in C'),
                $H::code(<<<'CODE'
#include <stdio.h>

int main() {
    int count = 3;
    printf("Count: %d\n", count);
    return 0;
}
CODE),
            ]),
            'c-data-types' => $H::join([
                $H::h2('Data types'),
                $H::code(<<<'CODE'
#include <stdio.h>

int main() {
    int age = 21;
    float height = 1.75f;
    char grade = 'A';
    printf("Age %d, height %.2f, grade %c\n", age, height, grade);
    return 0;
}
CODE),
            ]),
            'c-if-else' => $H::join([
                $H::h2('if / else'),
                $H::code(<<<'CODE'
#include <stdio.h>

int main() {
    int score = 72;
    if (score >= 70) {
        printf("Pass\n");
    } else {
        printf("Review needed\n");
    }
    return 0;
}
CODE),
            ]),
            'c-loops' => $H::join([
                $H::h2('for loops'),
                $H::code(<<<'CODE'
#include <stdio.h>

int main() {
    for (int i = 1; i <= 5; i++) {
        printf("%d\n", i);
    }
    return 0;
}
CODE),
            ]),
            'c-functions' => $H::join([
                $H::h2('Functions'),
                $H::code(<<<'CODE'
#include <stdio.h>

int add(int a, int b) {
    return a + b;
}

int main() {
    printf("%d\n", add(2, 3));
    return 0;
}
CODE),
            ]),
        ];
    }

    protected function reactLessons(string $H): array
    {
        return [
            'react-what-is-react' => $H::join([
                $H::h2('What is React?'),
                $H::p('React helps you build user interfaces from reusable components.'),
                $H::ul([
                    'Components are like custom HTML tags you define',
                    'When data changes, React updates the screen efficiently',
                    'Used by companies like Meta, Netflix, and many startups',
                ]),
            ]),
            'react-jsx-basics' => $H::join([
                $H::h2('JSX basics'),
                $H::p('JSX looks like HTML inside JavaScript. Practice the idea with strings first:'),
                $H::code(<<<'CODE'
const title = "CodeCraft";
const element = `<h1>${title}</h1>`;
console.log(element);
CODE),
            ]),
            'react-props-concept' => $H::join([
                $H::h2('Props concept'),
                $H::p('Props pass data into components — like function arguments.'),
                $H::code(<<<'CODE'
function Welcome(props) {
  return `Hello, ${props.name}!`;
}

console.log(Welcome({ name: "Learner" }));
CODE),
            ]),
            'react-state-concept' => $H::join([
                $H::h2('State (concept)'),
                $H::p('State is data that can change over time and updates the UI.'),
                $H::code(<<<'CODE'
let count = 0;
count = count + 1;
console.log("Clicks:", count);
CODE),
            ]),
            'react-components' => $H::join([
                $H::h2('Components'),
                $H::p('Split UI into small, named pieces.'),
                $H::code(<<<'CODE'
function Card({ title, body }) {
  return `${title}: ${body}`;
}

console.log(Card({ title: "Tip", body: "Practice daily" }));
CODE),
            ]),
            'react-lists' => $H::join([
                $H::h2('Rendering lists'),
                $H::code(<<<'CODE'
const tasks = ["Learn JSX", "Build a component", "Ship a mini app"];

tasks.forEach((task, index) => {
  console.log(index + 1, task);
});
CODE),
            ]),
        ];
    }
}
