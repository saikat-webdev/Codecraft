import { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';

const initialAssistant = [
  {
    id: 'intro',
    role: 'assistant',
    text: 'Welcome to your AI Teacher. I guide beginners through Python lessons with simple examples, small checkpoints, and friendly encouragement.',
  },
];

const quickPrompts = [
  { label: 'Explain variables simply', value: 'Can you explain variables in Python like I am a beginner?' },
  { label: 'Show me a small Python example', value: 'Show me a very simple Python example I can run.' },
  { label: 'Help me start the first lesson', value: 'What should I focus on in the first Python lesson?' },
];

export default function AiInstructor() {
  const [messages, setMessages] = useState(initialAssistant);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const sendPrompt = async (message) => {
    const trimmed = message.trim();
    if (!trimmed) return;

    setError('');
    const userMessage = { id: Date.now().toString(), role: 'user', text: trimmed };
    setMessages((current) => [...current, userMessage]);
    setLoading(true);

    try {
      const response = await api.post('/ai/prompt', { message: trimmed });
      const reply = response.data?.data?.reply || 'Sorry, the instructor could not answer right now.';
      const assistantMessage = { id: `${Date.now()}-assistant`, role: 'assistant', text: reply };
      setMessages((current) => [...current, assistantMessage]);
    } catch (err) {
      setError('Unable to connect to the AI instructor. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const submitMessage = async (event) => {
    event.preventDefault();
    await sendPrompt(input);
    setInput('');
  };

  const submitQuickPrompt = async (value) => {
    await sendPrompt(value);
  };

  return (
    <div className="app-shell">
      <section className="page-card space-y-8 ai-instructor-card">
        <div className="page-header">
          <div>
            <span className="eyebrow">AI Instructor</span>
            <h1>Meet your AI Teacher for Python</h1>
            <p>Get beginner-friendly Python guidance, code examples, and step-by-step support for the Python Promise course.</p>
          </div>
          <div className="assistant-card assistant-onboarding-card">
            <strong>Python Promise</strong>
            <p>Introductory Python course designed for learners who want clear explanations, instant feedback, and a tiny first project.</p>
            <div className="onboarding-steps">
              <span>1. Start with a small lesson</span>
              <span>2. Ask the AI when you need help</span>
              <span>3. Mark completion and keep going</span>
            </div>
            <Link to="/modules/introduction-to-python" className="button-secondary">Browse roadmap</Link>
          </div>
        </div>

        <div className="ai-chat-grid">
          <div className="chat-panel">
            <div className="chat-header">
              <div>
                <p className="eyebrow">Live instructor chat</p>
                <h2>Ask anything about Python.</h2>
              </div>
              <span className="status-pill">AI Teacher</span>
            </div>

            <div className="chat-history">
              {messages.map((message) => (
                <div key={message.id} className={`chat-message ${message.role}`}>
                  <div className="message-role">{message.role === 'assistant' ? 'Instructor' : 'You'}</div>
                  <div className="message-text">{message.text}</div>
                </div>
              ))}
            </div>

            <div className="quick-prompt-panel">
              <p className="eyebrow">Start with a quick prompt</p>
              <div className="quick-prompt-list">
                {quickPrompts.map((prompt) => (
                  <button
                    key={prompt.value}
                    type="button"
                    className="quick-prompt-button"
                    onClick={() => submitQuickPrompt(prompt.value)}
                    disabled={loading}
                  >
                    {prompt.label}
                  </button>
                ))}
              </div>
            </div>

            <form onSubmit={submitMessage} className="chat-form">
              {error && <div className="alert-error">{error}</div>}
              <div className="form-field">
                <span>Type your question</span>
                <textarea
                  className="form-input"
                  rows="3"
                  value={input}
                  onChange={(event) => setInput(event.target.value)}
                  placeholder="Ask the AI instructor how to start with Python..."
                />
              </div>
              <button type="submit" className="form-button" disabled={loading}>
                {loading ? 'Thinking...' : 'Send to AI'}
              </button>
            </form>
          </div>

          <aside className="course-panel">
            <div className="section-title">
              <h2>Python Promise syllabus</h2>
              <p>Build confidence with bite-sized lessons, real examples, and a guided mini project.</p>
            </div>

            <div className="course-badge">Beginner</div>
            <div className="course-list">
              <div className="course-step">
                <strong>Step 1</strong>
                <p>Understand Python syntax, variables, and printing output.</p>
              </div>
              <div className="course-step">
                <strong>Step 2</strong>
                <p>Learn control flow with loops and conditions for simple programs.</p>
              </div>
              <div className="course-step">
                <strong>Mini project</strong>
                <p>Create a friendly calculator or greeting script with AI guidance.</p>
              </div>
            </div>
          </aside>
        </div>
      </section>
    </div>
  );
}
