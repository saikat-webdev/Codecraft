import api from './api';

/**
 * Load the signed-in user's AI Instructor chat history.
 * @returns {Promise<{ success: boolean, messages?: Array<{ id: string, role: string, content: string }> }>}
 */
export async function fetchAiChatHistory() {
  const { data } = await api.get('/ai/history');
  return data;
}

/**
 * Send a message to the CodeCraft AI Instructor (proxied via Laravel → n8n).
 * @param {string} message
 * @returns {Promise<{ success: boolean, reply?: string, message?: string }>}
 */
export async function sendAiChatMessage(message) {
  const { data } = await api.post('/ai/chat', { message });
  return data;
}

/**
 * Clear the signed-in user's saved AI chat history.
 */
export async function clearAiChatHistory() {
  const { data } = await api.delete('/ai/history');
  return data;
}
