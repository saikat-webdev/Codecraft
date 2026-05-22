/**
 * Supported playground languages mapped to Monaco editor + Judge0 language IDs.
 * IDs match Judge0 CE (https://ce.judge0.com/languages).
 */
export const PLAYGROUND_LANGUAGES = [
  {
    id: 'python',
    label: 'Python',
    monaco: 'python',
    judge0Id: 71,
    starterCode: `# Write Python here
print("Hello, CodeCraft!")
`,
  },
  {
    id: 'javascript',
    label: 'JavaScript',
    monaco: 'javascript',
    judge0Id: 63,
    starterCode: `// Node.js — use console.log for output
console.log("Hello, CodeCraft!");
`,
  },
  {
    id: 'cpp',
    label: 'C++',
    monaco: 'cpp',
    judge0Id: 54,
    starterCode: `#include <iostream>
using namespace std;

int main() {
    cout << "Hello, CodeCraft!" << endl;
    return 0;
}
`,
  },
];

export const DEFAULT_LANGUAGE_ID = 'python';

export function getLanguageById(id) {
  return PLAYGROUND_LANGUAGES.find((lang) => lang.id === id) ?? PLAYGROUND_LANGUAGES[0];
}
