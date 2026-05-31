import { useCallback, useContext, useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { sendAiChatMessage } from '../services/ai';

const WELCOME = {
  id: 'welcome',
  role: 'assistant',
  content:
    "Hi! I'm your CodeCraft AI Instructor. Ask me anything about programming — variables, loops, functions, debugging, or how to learn a language. I'll explain things step by step, like we're pair-programming together.",
};

function ChatBubble({ message }) {
  const isUser = message.role === 'user';
  return (
    <div className={`ai-chat-row ${isUser ? 'ai-chat-row-user' : 'ai-chat-row-assistant'}`}>
      <div className={`ai-chat-bubble ${isUser ? 'ai-chat-bubble-user' : 'ai-chat-bubble-assistant'}`}>
        {!isUser && <span className="ai-chat-avatar" aria-hidden="true">AI</span>}
        <div className="ai-chat-bubble-body">
          <p>{message.content}</p>
        </div>
      </div>
    </div>
  );
}

export default function AIChat() {
  const { user } = useContext(AuthContext);
  const [messages, setMessages] = useState([WELCOME]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const scrollRef = useRef(null);
  const inputRef = useRef(null);

  const scrollToBottom = useCallback(() => {
    const el = scrollRef.current;
    if (el) {
      el.scrollTop = el.scrollHeight;
    }
  }, []);

  useEffect(() => {
    scrollToBottom();
  }, [messages, loading, scrollToBottom]);

  const sendMessage = async () => {
    const text = input.trim();
    if (!text || loading) return;

    const userMessage = {
      id: `user-${Date.now()}`,
      role: 'user',
      content: text,
    };

    setMessages((prev) => [...prev, userMessage]);
    setInput('');
    setError(null);
    setLoading(true);

    try {
      const data = await sendAiChatMessage(text);

      if (!data.success || !data.reply) {
        throw new Error(data.message || 'No reply from AI Instructor.');
      }

      setMessages((prev) => [
        ...prev,
        {
          id: `assistant-${Date.now()}`,
          role: 'assistant',
          content: data.reply,
        },
      ]);
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        err.message ||
        'Something went wrong. Please try again.';
      setError(msg);
    } finally {
      setLoading(false);
      inputRef.current?.focus();
    }
  };

  const onKeyDown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  };

  return (
    <div className="ai-chat-page">
      <section className="page-card ai-chat-card fade-up" aria-label="AI Instructor chat">
        <header className="ai-chat-header">
          <div className="ai-chat-header-text">
            <span className="eyebrow">AI Instructor</span>
            <h1>CodeCraft AI Teacher</h1>
          </div>
          <p className="ai-chat-header-meta">
            Programming help only.
            {user ? ` · ${user.name}` : ''}
            {!user && (
              <>
                {' '}
                · <Link to="/login">Sign in</Link>
              </>
            )}
          </p>
        </header>

        {error && <div className="alert-error ai-chat-error">{error}</div>}

        <div className="ai-chat-panel glow-panel">
          <div className="ai-chat-messages" ref={scrollRef} role="log" aria-live="polite">
            {messages.map((msg) => (
              <ChatBubble key={msg.id} message={msg} />
            ))}
            {loading && (
              <div className="ai-chat-row ai-chat-row-assistant">
                <div className="ai-chat-bubble ai-chat-bubble-assistant">
                  <span className="ai-chat-avatar" aria-hidden="true">AI</span>
                  <div className="ai-chat-bubble-body ai-chat-typing">
                    <span />
                    <span />
                    <span />
                  </div>
                </div>
              </div>
            )}
          </div>

          <div className="ai-chat-composer">
            <textarea
              ref={inputRef}
              className="ai-chat-input form-input"
              rows={2}
              placeholder="Ask a coding question… (Enter to send, Shift+Enter for new line)"
              value={input}
              onChange={(e) => setInput(e.target.value)}
              onKeyDown={onKeyDown}
              disabled={loading}
              maxLength={4000}
              aria-label="Message to AI Instructor"
            />
            <button
              type="button"
              className="button-primary ai-chat-send"
              onClick={sendMessage}
              disabled={loading || !input.trim()}
            >
              {loading ? 'Thinking…' : 'Send'}
            </button>
          </div>
        </div>

        <p className="ai-chat-disclaimer">
          The AI only answers coding and software-development questions. It does not replace hands-on practice — try the{' '}
          <Link to="/playground">playground</Link> after each explanation.
        </p>
      </section>
    </div>
  );
}
