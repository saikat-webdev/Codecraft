/**
 * Playground languages — executed via Laravel (Judge0 CE public API + optional local fallback).
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
    id: 'java',
    label: 'Java',
    monaco: 'java',
    judge0Id: 62,
    starterCode: `public class Main {
    public static void main(String[] args) {
        System.out.println("Hello, CodeCraft!");
    }
}
`,
  },
  {
    id: 'c',
    label: 'C',
    monaco: 'c',
    judge0Id: 50,
    starterCode: `#include <stdio.h>

int main() {
    printf("Hello, CodeCraft!\\n");
    return 0;
}
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
  {
    id: 'react',
    label: 'React (JS)',
    monaco: 'javascript',
    judge0Id: 63,
    runAs: 'javascript',
    starterCode: `// React-style practice runs as JavaScript (console output)
const appName = "CodeCraft";
console.log("Welcome to " + appName + " React track!");
`,
  },
];

export const DEFAULT_LANGUAGE_ID = 'python';

export const COURSE_TRACKS = [
  { id: 'python', label: 'Python', icon: '🐍' },
  { id: 'javascript', label: 'JavaScript', icon: '⚡' },
  { id: 'java', label: 'Java', icon: '☕' },
  { id: 'c', label: 'C', icon: '🔧' },
  { id: 'react', label: 'React', icon: '⚛️' },
];

export function getLanguageById(id) {
  return PLAYGROUND_LANGUAGES.find((lang) => lang.id === id) ?? PLAYGROUND_LANGUAGES[0];
}

export function getRunLanguageId(id) {
  const lang = getLanguageById(id);
  return lang.runAs ?? lang.id;
}

export function trackToLanguageId(trackOrLanguage) {
  const key = String(trackOrLanguage || '').toLowerCase();
  const map = {
    python: 'python',
    javascript: 'javascript',
    java: 'java',
    c: 'c',
    react: 'react',
    'c++': 'cpp',
    cpp: 'cpp',
  };
  return map[key] ?? 'python';
}
