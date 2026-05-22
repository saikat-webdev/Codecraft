import api from './api';

export const profileApi = {
  getProfile: () => api.get('/profile'),
  updateProfile: (data) => api.put('/profile', data),
  uploadAvatar: (file) => {
    const form = new FormData();
    form.append('avatar', file);
    return api.post('/profile/avatar', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
  getStats: () => api.get('/profile/stats'),
  getAchievements: () => api.get('/profile/achievements'),
};
