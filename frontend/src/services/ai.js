import api from './api';

/**
 * Send a message to the CodeCraft AI Instructor (proxied via Laravel → n8n).
 * @param {string} message
 * @returns {Promise<{ success: boolean, reply?: string, message?: string }>}
 */
export async function sendAiChatMessage(message) {
  const { data } = await api.post('/ai/chat', { message });
  return data;
}
