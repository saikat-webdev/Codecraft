import { useCallback, useContext, useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { clearAiChatHistory, fetchAiChatHistory, sendAiChatMessage } from '../services/ai';

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
  const { user, loading: authLoading } = useContext(AuthContext);
  const [messages, setMessages] = useState([WELCOME]);
  const [historyLoading, setHistoryLoading] = useState(false);
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

  useEffect(() => {
    if (authLoading) {
      return;
    }

    if (!user) {
      setMessages([WELCOME]);
      setHistoryLoading(false);
      return;
    }

    let cancelled = false;

    const loadHistory = async () => {
      setHistoryLoading(true);
      setError(null);
      try {
        const data = await fetchAiChatHistory();
        if (cancelled) {
          return;
        }
        if (data.success && Array.isArray(data.messages) && data.messages.length > 0) {
          setMessages(data.messages);
        } else {
          setMessages([WELCOME]);
        }
      } catch (err) {
        if (!cancelled) {
          setMessages([WELCOME]);
          const msg = err.response?.data?.message;
          if (msg) {
            setError(msg);
          }
        }
      } finally {
        if (!cancelled) {
          setHistoryLoading(false);
        }
      }
    };

    loadHistory();

    return () => {
      cancelled = true;
    };
  }, [user, authLoading]);

  const sendMessage = async () => {
    const text = input.trim();
    if (!text || loading || !user) return;

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
      if (err.response?.status === 401) {
        setError('Please sign in to use the AI Instructor and save your chat history.');
      }
    } finally {
      setLoading(false);
      inputRef.current?.focus();
    }
  };

  const handleClearHistory = async () => {
    if (!user || loading) {
      return;
    }
    if (!window.confirm('Clear your AI chat history? This cannot be undone.')) {
      return;
    }
    try {
      await clearAiChatHistory();
      setMessages([WELCOME]);
      setError(null);
    } catch (err) {
      setError(err.response?.data?.message || 'Could not clear chat history.');
    }
  };

  const onKeyDown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  };

  const canChat = Boolean(user) && !historyLoading;

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
                · <Link to="/login">Sign in</Link> to chat and save history
              </>
            )}
            {user && messages.length > 1 && (
              <>
                {' '}
                ·{' '}
                <button
                  type="button"
                  className="ai-chat-clear-link"
                  onClick={handleClearHistory}
                  disabled={loading || historyLoading}
                >
                  Clear history
                </button>
              </>
            )}
          </p>
        </header>

        {error && <div className="alert-error ai-chat-error">{error}</div>}

        {!user && !authLoading && (
          <div className="alert-info ai-chat-signin-hint">
            Sign in to chat with the AI Instructor. Your messages are saved to your account only.
          </div>
        )}

        <div className="ai-chat-panel glow-panel">
          <div className="ai-chat-messages" ref={scrollRef} role="log" aria-live="polite">
            {historyLoading && (
              <p className="ai-chat-history-loading" aria-live="polite">
                Loading your chat history…
              </p>
            )}
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
              placeholder={
                user
                  ? 'Ask a coding question… (Enter to send, Shift+Enter for new line)'
                  : 'Sign in to start chatting…'
              }
              value={input}
              onChange={(e) => setInput(e.target.value)}
              onKeyDown={onKeyDown}
              disabled={!canChat || loading}
              maxLength={4000}
              aria-label="Message to AI Instructor"
            />
            <button
              type="button"
              className="button-primary ai-chat-send"
              onClick={sendMessage}
              disabled={!canChat || loading || !input.trim()}
            >
              {loading ? 'Thinking…' : 'Send'}
            </button>
          </div>
        </div>

        <p className="ai-chat-disclaimer">
          The AI only answers coding and software-development questions. It does not replace hands-on practice — try the{' '}
          <Link to="/playground">playground</Link> after each explanation.
          {user && ' Chat history is private to your account.'}
        </p>
      </section>
    </div>
  );
}
