<?php

namespace Database\Seeders;

use App\Models\SuddenTestQuestion;
use App\Models\SuddenTestSetting;
use Illuminate\Database\Seeder;

class SuddenTestSeeder extends Seeder
{
    public function run(): void
    {
        SuddenTestSetting::firstOrCreate([], [
            'enabled' => true,
            'min_interval_seconds' => 120,
            'max_interval_seconds' => 300,
            'default_timer_seconds' => 90,
            'base_difficulty' => 2,
        ]);

        $questions = [
            [
                'type' => 'mcq',
                'title' => 'Python print',
                'prompt' => 'Which function displays output in Python?',
                'options' => ['echo()', 'print()', 'console.log()', 'display()'],
                'correct_answer' => 'print()',
                'difficulty' => 1,
                'time_limit_seconds' => 60,
            ],
            [
                'type' => 'mcq',
                'title' => 'List mutation',
                'prompt' => 'What does my_list.append(5) do?',
                'options' => ['Removes 5', 'Adds 5 to the end', 'Sorts the list', 'Returns length'],
                'correct_answer' => 'Adds 5 to the end',
                'difficulty' => 2,
                'time_limit_seconds' => 75,
            ],
            [
                'type' => 'mcq',
                'title' => 'JS typeof',
                'prompt' => 'In JavaScript, typeof [] returns:',
                'options' => ['"array"', '"object"', '"list"', 'undefined'],
                'correct_answer' => '"object"',
                'difficulty' => 2,
                'time_limit_seconds' => 75,
            ],
            [
                'type' => 'coding',
                'title' => 'Hello Python',
                'prompt' => 'Print exactly: Hello, CodeCraft!',
                'starter_code' => "# Print the greeting\n",
                'expected_output' => 'Hello, CodeCraft!',
                'language' => 'python',
                'difficulty' => 1,
                'time_limit_seconds' => 90,
            ],
            [
                'type' => 'coding',
                'title' => 'Sum in Python',
                'prompt' => 'Print the sum of 3 and 4 (output should be 7)',
                'starter_code' => "# print(3 + 4)\n",
                'expected_output' => '7',
                'language' => 'python',
                'difficulty' => 2,
                'time_limit_seconds' => 90,
            ],
            [
                'type' => 'coding',
                'title' => 'C++ Hello',
                'prompt' => 'Print exactly: Hi',
                'starter_code' => "#include <iostream>\nusing namespace std;\n\nint main() {\n    // your code\n    return 0;\n}\n",
                'expected_output' => 'Hi',
                'language' => 'cpp',
                'difficulty' => 3,
                'time_limit_seconds' => 120,
            ],
        ];

        foreach ($questions as $q) {
            SuddenTestQuestion::updateOrCreate(
                ['title' => $q['title'], 'type' => $q['type']],
                array_merge($q, ['is_active' => true])
            );
        }
    }
}
