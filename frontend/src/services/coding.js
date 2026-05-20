import api from './api';

export const codingApi = {
  runCode: (code, language = 'python', timeout = 5) =>
    api.post('/code/run', { code, language, timeout }),

  getExercise: (id) =>
    api.get(`/exercises/${id}`),

  getLessonExercises: (lessonId) =>
    api.get(`/lessons/${lessonId}/exercises`),

  submitExercise: (exerciseId, data) =>
    api.post(`/exercises/${exerciseId}/submit`, data),

  evaluateCode: (exerciseId, data) =>
    api.post(`/exercises/${exerciseId}/evaluate`, data),

  getHints: (exerciseId) =>
    api.get(`/exercises/${exerciseId}/hints`),

  getSubmissionHistory: () =>
    api.get('/submissions/history'),
};