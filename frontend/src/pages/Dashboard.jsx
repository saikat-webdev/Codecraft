import { useContext, useEffect, useState } from 'react';
import { AuthContext } from '../context/AuthProvider';
import api from '../services/api';

export default function Dashboard() {
  const { user } = useContext(AuthContext);
  const [progress, setProgress] = useState([]);

  useEffect(() => {
    let mounted = true;
    api.get('/progress').then((res) => {
      if (mounted) setProgress(res.data.data || []);
    }).catch(() => {});
    return () => (mounted = false);
  }, []);

  return (
    <div className="app-shell">
      <section className="page-card space-y-8 dashboard-card">
        <div className="page-header">
          <div>
            <span className="eyebrow">Dashboard</span>
            <h1>Welcome{user ? `, ${user.name}` : ''}</h1>
            <p>Your instructor-style dashboard keeps the next coding step clear, bright, and motivating.</p>
          </div>
        </div>

        <div className="metric-grid">
          <article className="metric-card highlight-card">
            <h3>Learning Goal</h3>
            <p>{user?.learning_goal || 'Not set'}</p>
            <span className="status-pill">Goal tracker</span>
          </article>
          <article className="metric-card highlight-card">
            <h3>Daily Time</h3>
            <p>{user?.daily_learning_time || '—'} minutes</p>
            <span className="status-pill">Daily practice</span>
          </article>
          <article className="metric-card highlight-card">
            <h3>Skill Level</h3>
            <p>{user?.skill_level || '—'}</p>
            <span className="status-pill">Progress track</span>
          </article>
        </div>

        <section>
          <div className="section-title">
            <h2>Your Progress</h2>
            <p className="section-subtitle">Check completed lessons, earned confidence, and what to try next.</p>
          </div>

          {progress.length === 0 ? (
            <div className="lesson-card">No progress has been recorded yet. Start a lesson to begin tracking your performance.</div>
          ) : (
            <div className="lesson-list">
              {progress.map((p) => (
                <article key={p.id} className="lesson-card">
                  <div>
                    <h3>{p.lesson?.title || `Lesson ${p.lesson_id}`}</h3>
                    <p className="lesson-meta">Score: {p.score ?? '—'} · Completed: {p.completed ? 'Yes' : 'No'}</p>
                  </div>
                  <span className={`status-pill ${p.completed ? 'pill-success' : 'pill-warning'}`}>
                    {p.completed ? 'Complete' : 'In progress'}
                  </span>
                </article>
              ))}
            </div>
          )}
        </section>
      </section>
    </div>
  );
}
