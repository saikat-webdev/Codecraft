import api from './api';

export const adminApi = {
  // Dashboard
  getDashboardStats: () => api.get('/admin/dashboard/stats'),
  getRecentActivity: () => api.get('/admin/dashboard/activity'),
  getLeaderboard: () => api.get('/admin/dashboard/leaderboard'),

  // Users
  getUsers: (params) => api.get('/admin/users', { params }),
  getUser: (id) => api.get(`/admin/users/${id}`),
  updateUser: (id, data) => api.put(`/admin/users/${id}`, data),
  suspendUser: (id) => api.post(`/admin/users/${id}/suspend`),
  activateUser: (id) => api.post(`/admin/users/${id}/activate`),
  assignRole: (id, data) => api.post(`/admin/users/${id}/role`, data),

  // Courses (Modules)
  getCourses: (params) => api.get('/admin/courses', { params }),
  createCourse: (data) => api.post('/admin/courses', data),
  updateCourse: (id, data) => api.put(`/admin/courses/${id}`, data),
  deleteCourse: (id) => api.delete(`/admin/courses/${id}`),
  publishCourse: (id) => api.post(`/admin/courses/${id}/publish`),
  unpublishCourse: (id) => api.delete(`/admin/courses/${id}/unpublish`),

  // Challenges (Exercises)
  getChallenges: (params) => api.get('/admin/challenges', { params }),
  createChallenge: (data) => api.post('/admin/challenges', data),
  updateChallenge: (id, data) => api.put(`/admin/challenges/${id}`, data),
  deleteChallenge: (id) => api.delete(`/admin/challenges/${id}`),

  // Sudden Tests (already exists, but adding more)
  adminGetSettings: () => api.get('/admin/sudden-tests/settings'),
  adminUpdateSettings: (data) => api.put('/admin/sudden-tests/settings', data),
  adminListQuestions: () => api.get('/admin/sudden-tests/questions'),
  adminCreateQuestion: (data) => api.post('/admin/sudden-tests/questions', data),
  adminUpdateQuestion: (id, data) => api.put(`/admin/sudden-tests/questions/${id}`, data),
  adminDeleteQuestion: (id) => api.delete(`/admin/sudden-tests/questions/${id}`),

  // Settings
  getSettings: () => api.get('/admin/settings'),
  updateSettings: (data) => api.put('/admin/settings', data),
  updateJudge0Settings: (data) => api.put('/admin/settings/judge0', data),
  updateAvatarMode: (data) => api.put('/admin/settings', data),

  // Activity Logs
  getActivityLogs: (params) => api.get('/admin/activity-logs', { params }),

  // Notifications
  sendNotification: (data) => api.post('/admin/notifications', data),
  getNotifications: (params) => api.get('/admin/notifications', { params }),
};