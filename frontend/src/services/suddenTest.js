import api from './api';

export const suddenTestApi = {
  getConfig: () => api.get('/sudden-tests/config'),
  fetchChallenge: () => api.get('/sudden-tests/challenge'),
  submit: (questionId, payload) =>
    api.post(`/sudden-tests/${questionId}/submit`, payload),
  adminGetSettings: () => api.get('/admin/sudden-tests/settings'),
  adminUpdateSettings: (data) => api.put('/admin/sudden-tests/settings', data),
  adminListQuestions: () => api.get('/admin/sudden-tests/questions'),
  adminCreateQuestion: (data) => api.post('/admin/sudden-tests/questions', data),
  adminUpdateQuestion: (id, data) => api.put(`/admin/sudden-tests/questions/${id}`, data),
  adminDeleteQuestion: (id) => api.delete(`/admin/sudden-tests/questions/${id}`),
};
