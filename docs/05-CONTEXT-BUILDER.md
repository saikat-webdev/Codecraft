# Module 05 — Context Builder

## Purpose

Assembles all retrieved data — RAG chunks, user progress, and conversation memory — into a structured message array ready to send to the Grok API. This is the glue layer between data retrieval and LLM generation.

---

## File Location

```
src/services/contextBuilder.js       (Node.js)
app/Services/AiTeacherService.php    (Laravel — inline in buildMessages())
```

---

## Inputs

| Input | Source | Used for |
|-------|--------|---------|
| `question` | Client request | Final user message |
| `ragChunks[]` | RAG Service | Lesson content context |
| `progress[]` | RAG Service | Student progress summary |
| `memory[]` | RAG Service | Last N conversation turns |

---

## Output

A `messages` array in OpenAI/xAI chat format:

```json
[
  { "role": "system",    "content": "..." },
  { "role": "user",      "content": "What is a variable?" },
  { "role": "assistant", "content": "A variable is..." },
  { "role": "user",      "content": "<current question>" }
]
```

---

## System Prompt Structure

The system prompt is built dynamically on every request:

```
[IDENTITY + RULES]
  You are an expert, patient, and encouraging AI teacher...
  Teaching rules:
    1. Explain simply
    2. Use code examples
    3. Numbered steps
    4. End with Next Step
    5. Be positive

[STUDENT PROGRESS]
  Completed lessons: 12
  Current lesson: Async/Await in JavaScript
  Active roadmap: Full Stack Developer

[RELEVANT COURSE CONTENT]
  [Source 1]
  Closures capture variables from their surrounding scope...

  [Source 2]
  A function returned from another function retains access...
```

---

## Node.js Implementation

```js
// src/services/contextBuilder.js

function buildSystemPrompt(progress, ragChunks) {
  // ── RAG context ──────────────────────────────────────────
  const ragContext = ragChunks.length > 0
    ? ragChunks
        .map((c, i) => `[Source ${i + 1}]\n${c.content}`)
        .join('\n\n')
    : 'No specific lesson content matched. Answer from general knowledge.';

  // ── Progress summary ──────────────────────────────────────
  const completed = progress.filter(p => p.status === 'completed');
  const active    = progress.find(p => p.status === 'in_progress');

  const progressSummary = [
    `Completed lessons: ${completed.length}`,
    `Current lesson:    ${active?.lessons?.title ?? 'None'}`,
    `Active roadmap:    ${active?.lessons?.modules?.roadmaps?.title ?? 'Unknown'}`,
  ].join('\n');

  // ── Assemble system prompt ────────────────────────────────
  return `You are an expert, patient, and encouraging AI teacher for Codecraft — a programming education platform.

Teaching rules:
1. Explain simply — assume a smart beginner, not an expert
2. Use short, runnable code examples (wrap in \`\`\`language blocks)
3. Break complex ideas into numbered steps
4. End EVERY response with a "## Next Step" section
5. Be positive and encouraging
6. Maximum 600 words unless a test/quiz is requested

Response format:
## Explanation
[Clear explanation with analogy if helpful]

## Example
\`\`\`[language]
[runnable code example]
\`\`\`

## Key Takeaway
[One-sentence summary]

## Next Step
[Specific, actionable recommendation based on their progress]

---

Student Progress:
${progressSummary}

Relevant Course Content:
${ragContext}`;
}

function buildMessages(systemPrompt, memory, question) {
  return [
    { role: 'system', content: systemPrompt },
    ...memory,                               // chronological past messages
    { role: 'user', content: question },     // current question last
  ];
}

module.exports = { buildSystemPrompt, buildMessages };
```

---

## Laravel Implementation (inline in `AiTeacherService`)

```php
private function buildMessages(
    string $question,
    array  $progress,
    array  $ragChunks,
    array  $memory
): array {
    $ragContext = count($ragChunks) > 0
        ? collect($ragChunks)
            ->map(fn($c, $i) => "[Source " . ($i + 1) . "]\n{$c['content']}")
            ->implode("\n\n")
        : 'No specific lesson content matched. Answer from general knowledge.';

    $completed = collect($progress)->where('status', 'completed')->count();
    $active    = collect($progress)->firstWhere('status', 'in_progress');

    $systemPrompt = <<<PROMPT
You are an expert, patient, and encouraging AI teacher for Codecraft.

Teaching rules:
1. Explain simply
2. Use short code examples
3. Numbered steps for complex topics
4. End with ## Next Step
5. Max 600 words

Student Progress:
Completed lessons: {$completed}
Current lesson: {$active?->lessons?->title ?? 'None'}

Relevant Course Content:
{$ragContext}
PROMPT;

    $messages = [['role' => 'system', 'content' => $systemPrompt]];
    foreach ($memory as $msg) {
        $messages[] = ['role' => $msg->role, 'content' => $msg->content];
    }
    $messages[] = ['role' => 'user', 'content' => $question];

    return $messages;
}
```

---

## Fallback Behaviour

| Condition | Fallback text injected |
|-----------|----------------------|
| `ragChunks.length === 0` | `"No specific lesson content matched. Answer from general knowledge."` |
| `progress` empty | Shows `Completed: 0`, `Current: None` |
| `memory` empty | Only system + user message sent (no history) |

---

## Token Budget Guidance

| Section | Approx tokens |
|---------|--------------|
| System prompt (identity + rules) | ~200 |
| Progress summary | ~50 |
| RAG context (5 chunks × 200 chars) | ~500 |
| Memory (5 turns × 100 words) | ~650 |
| User question | ~50 |
| **Total input** | **~1450** |
| Grok response (`max_tokens: 1500`) | ~1500 |
| **Total per request** | **~2950 tokens** |

Keep RAG chunks trimmed to ~800 chars each to stay within budget. See [10-ERROR-PERFORMANCE.md](./10-ERROR-PERFORMANCE.md).
