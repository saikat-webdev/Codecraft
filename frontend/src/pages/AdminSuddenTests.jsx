import { useContext, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { suddenTestApi } from '../services/suddenTest';

const emptyQuestion = {
  type: 'mcq',
  title: '',
  prompt: '',
  options: ['', '', '', ''],
  correct_answer: '',
  starter_code: '',
  expected_output: '',
  language: 'python',
  difficulty: 2,
  time_limit_seconds: 90,
  is_active: true,
};

export default function AdminSuddenTests() {
  const { user } = useContext(AuthContext);
  const [settings, setSettings] = useState(null);
  const [questions, setQuestions] = useState([]);
  const [form, setForm] = useState(emptyQuestion);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const load = () => {
    suddenTestApi.adminGetSettings().then((res) => setSettings(res.data.data)).catch(() => {});
    suddenTestApi.adminListQuestions().then((res) => setQuestions(res.data.data || [])).catch(() => {});
  };

  useEffect(() => {
    if (user?.is_admin) load();
  }, [user]);

  if (!user?.is_admin) {
    return (
      <div className="app-shell">
        <div className="page-card">Admin access required.</div>
      </div>
    );
  }

  const saveSettings = async () => {
    setError('');
    try {
      await suddenTestApi.adminUpdateSettings(settings);
      setMessage('Settings saved.');
      load();
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to save settings.');
    }
  };

  const createQuestion = async (e) => {
    e.preventDefault();
    setError('');
    try {
      const payload = {
        ...form,
        options: form.type === 'mcq' ? form.options.filter(Boolean) : null,
      };
      await suddenTestApi.adminCreateQuestion(payload);
      setForm(emptyQuestion);
      setMessage('Question added.');
      load();
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to create question.');
    }
  };

  const toggleEnabled = async () => {
    const next = { ...settings, enabled: !settings.enabled };
    setSettings(next);
    await suddenTestApi.adminUpdateSettings({ enabled: next.enabled });
    setMessage(next.enabled ? 'Sudden tests enabled.' : 'Sudden tests disabled.');
  };

  return (
    <div className="app-shell admin-sudden-page">
      <section className="page-card fade-up">
        <div className="page-header">
          <div>
            <span className="eyebrow">Admin</span>
            <h1>Sudden test control</h1>
            <p>Enable pop-up quizzes during lessons and manage the question pool.</p>
          </div>
          <Link to="/profile" className="button-secondary">Back to profile</Link>
        </div>

        {message && <div className="alert-success">{message}</div>}
        {error && <div className="alert-error">{error}</div>}

        {settings && (
          <div className="admin-settings-panel">
            <div className="admin-toggle-row">
              <strong>Sudden tests globally</strong>
              <button
                type="button"
                className={`button-primary ${settings.enabled ? '' : 'muted-btn'}`}
                onClick={toggleEnabled}
              >
                {settings.enabled ? 'Enabled' : 'Disabled'}
              </button>
            </div>
            <div className="admin-settings-grid">
              <label className="form-field">
                <span>Min interval (sec)</span>
                <input
                  type="number"
                  className="form-input"
                  value={settings.min_interval_seconds}
                  onChange={(e) =>
                    setSettings({ ...settings, min_interval_seconds: Number(e.target.value) })
                  }
                />
              </label>
              <label className="form-field">
                <span>Max interval (sec)</span>
                <input
                  type="number"
                  className="form-input"
                  value={settings.max_interval_seconds}
                  onChange={(e) =>
                    setSettings({ ...settings, max_interval_seconds: Number(e.target.value) })
                  }
                />
              </label>
              <label className="form-field">
                <span>Default timer (sec)</span>
                <input
                  type="number"
                  className="form-input"
                  value={settings.default_timer_seconds}
                  onChange={(e) =>
                    setSettings({ ...settings, default_timer_seconds: Number(e.target.value) })
                  }
                />
              </label>
              <label className="form-field">
                <span>Base difficulty (1–5)</span>
                <input
                  type="number"
                  min={1}
                  max={5}
                  className="form-input"
                  value={settings.base_difficulty}
                  onChange={(e) =>
                    setSettings({ ...settings, base_difficulty: Number(e.target.value) })
                  }
                />
              </label>
            </div>
            <button type="button" className="button-secondary" onClick={saveSettings}>
              Save timing settings
            </button>
          </div>
        )}
      </section>

      <section className="page-card fade-up delay-1">
        <h2>Add question</h2>
        <form onSubmit={createQuestion} className="admin-question-form space-y-5">
          <label className="form-field">
            <span>Type</span>
            <select
              className="language-select form-input"
              value={form.type}
              onChange={(e) => setForm({ ...form, type: e.target.value })}
            >
              <option value="mcq">MCQ</option>
              <option value="coding">Coding</option>
            </select>
          </label>
          <label className="form-field">
            <span>Title</span>
            <input className="form-input" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
          </label>
          <label className="form-field">
            <span>Prompt</span>
            <textarea className="form-input" rows={2} value={form.prompt} onChange={(e) => setForm({ ...form, prompt: e.target.value })} required />
          </label>
          {form.type === 'mcq' ? (
            <>
              {form.options.map((opt, i) => (
                <label key={i} className="form-field">
                  <span>Option {i + 1}</span>
                  <input
                    className="form-input"
                    value={opt}
                    onChange={(e) => {
                      const options = [...form.options];
                      options[i] = e.target.value;
                      setForm({ ...form, options });
                    }}
                  />
                </label>
              ))}
              <label className="form-field">
                <span>Correct answer (must match option text)</span>
                <input className="form-input" value={form.correct_answer} onChange={(e) => setForm({ ...form, correct_answer: e.target.value })} />
              </label>
            </>
          ) : (
            <>
              <label className="form-field">
                <span>Starter code</span>
                <textarea className="form-input" rows={4} value={form.starter_code} onChange={(e) => setForm({ ...form, starter_code: e.target.value })} />
              </label>
              <label className="form-field">
                <span>Expected output</span>
                <input className="form-input" value={form.expected_output} onChange={(e) => setForm({ ...form, expected_output: e.target.value })} />
              </label>
              <label className="form-field">
                <span>Language</span>
                <select className="form-input language-select" value={form.language} onChange={(e) => setForm({ ...form, language: e.target.value })}>
                  <option value="python">Python</option>
                  <option value="javascript">JavaScript</option>
                  <option value="cpp">C++</option>
                </select>
              </label>
            </>
          )}
          <label className="form-field">
            <span>Difficulty (1–5)</span>
            <input type="number" min={1} max={5} className="form-input" value={form.difficulty} onChange={(e) => setForm({ ...form, difficulty: Number(e.target.value) })} />
          </label>
          <button type="submit" className="form-button">Add to pool</button>
        </form>
      </section>

      <section className="page-card fade-up delay-2">
        <h2>Question pool ({questions.length})</h2>
        <div className="lesson-list">
          {questions.map((q) => (
            <article key={q.id} className="lesson-card">
              <div>
                <h3>{q.title}</h3>
                <p className="lesson-meta">
                  {q.type} · difficulty {q.difficulty} · {q.is_active ? 'active' : 'inactive'}
                </p>
              </div>
              <button
                type="button"
                className="button-secondary"
                onClick={async () => {
                  await suddenTestApi.adminDeleteQuestion(q.id);
                  load();
                }}
              >
                Remove
              </button>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
