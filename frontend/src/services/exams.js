import api from './api';

export const examsApi = {
  list: (track) => api.get('/exams', { params: track ? { track } : {} }),
  get: (slug) => api.get(`/exams/${slug}`),
  submit: (slug, payload) => api.post(`/exams/${slug}/submit`, payload),
  myAttempts: () => api.get('/exam-attempts'),
};
