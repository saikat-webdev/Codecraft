import api from './api';

export function fetchModules(track) {
  return api.get('/modules', { params: track ? { track } : {} });
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
