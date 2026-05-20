import api from './api';

export function fetchModules() {
  return api.get('/modules');
}

export function fetchModule(slug) {
  return api.get(`/modules/${slug}`);
}

export function fetchLesson(slug) {
  return api.get(`/lessons/${slug}`);
}

export function fetchProgress() {
  return api.get('/progress');
}

export function submitProgress(payload) {
  return api.post('/progress', payload);
}

export function promptAi(message, lessonSlug) {
  // AI feature disabled temporarily
  return Promise.reject(new Error('AI feature disabled'));
}

export function fetchLessonHelp(slug) {
  // AI lesson help disabled temporarily
  return Promise.resolve({ data: { data: { reply: 'AI instructor support is temporarily disabled.' } } });
}
