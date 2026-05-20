<?php

namespace Database\Seeders;

use App\Models\CodingExercise;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class CodingExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
            if (str_contains(strtolower($lesson->title), 'loop') || str_contains(strtolower($lesson->description), 'loop')) {
                CodingExercise::create([
                    'lesson_id' => $lesson->id,
                    'title' => 'Print numbers from 1 to 10',
                    'description' => 'Use a for loop to print numbers from 1 to 10, each on a new line.',
                    'starter_code' => '# Your code here\nfor i in range(1, 11):\n    print(i)',
                    'expected_output' => '1\n2\n3\n4\n5\n6\n7\n8\n9\n10',
                    'difficulty' => 'beginner',
                    'order' => 1,
                ]);
            }

            if (str_contains(strtolower($lesson->title), 'function') || str_contains(strtolower($lesson->description), 'function')) {
                CodingExercise::create([
                    'lesson_id' => $lesson->id,
                    'title' => 'Create a greeting function',
                    'description' => 'Write a function called greet that takes a name as parameter and returns "Hello, {name}!"',
                    'starter_code' => '# Define your function here\ndef greet(name):\n    return f"Hello, {name}!"\n\n# Test it\nprint(greet("Alice"))',
                    'expected_output' => 'Hello, Alice!',
                    'difficulty' => 'beginner',
                    'order' => 1,
                ]);
            }

            if (str_contains(strtolower($lesson->title), 'variable') || str_contains(strtolower($lesson->description), 'variable')) {
                CodingExercise::create([
                    'lesson_id' => $lesson->id,
                    'title' => 'Variables practice',
                    'description' => 'Create variables for your name, age, and favorite color. Then print them in one sentence.',
                    'starter_code' => '# Create your variables\nname = "Your Name"\nage = 25\nfavorite_color = "blue"\n\n# Print your sentence\nprint(f"My name is {name}, I am {age} years old, and I love {favorite_color}.")',
                    'expected_output' => 'My name is Your Name, I am 25 years old, and I love blue.',
                    'difficulty' => 'beginner',
                    'order' => 1,
                ]);
            }
        }
    }
}