<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Judge0Service;
use Illuminate\Http\Request;

class CodeExecutionController extends Controller
{
    protected Judge0Service $judge0;

    public function __construct(Judge0Service $judge0)
    {
        $this->judge0 = $judge0;
    }

    public function run(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50000',
            'language' => 'nullable|string|in:python,python3,javascript,js,cpp,c++,java,c,react',
            'timeout' => 'nullable|integer|min:1|max:30',
        ]);

        $timeout = $validated['timeout'] ?? 5;
        $language = $validated['language'] ?? 'python';
        $code = $validated['code'];

        $result = $this->judge0->runCode($code, $language, $timeout);

        return response()->json($result);
    }
}