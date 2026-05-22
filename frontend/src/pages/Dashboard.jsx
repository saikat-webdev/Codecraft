import { useContext, useEffect, useState } from 'react';
import { AuthContext } from '../context/AuthProvider';
import api from '../services/api';

export default function Dashboard() {
  const { user } = useContext(AuthContext);
  const [progressData, setProgressData] = useState({ progress: [] });

  useEffect(() => {
    let mounted = true;
    api.get('/progress').then((res) => {
      if (mounted) setProgressData(res.data.data || { progress: [] });
    }).catch(() => {});
    return () => (mounted = false);
  }, []);

  const completedLessons = progressData.completed_count ?? 0;
  const totalLessons = progressData.total_lessons ?? 0;
  const completionRate = progressData.progress_percentage ?? 0;
  const lessonsRemaining = Math.max(0, totalLessons - completedLessons);
  const currentModule = progressData.current_module?.title || 'Python fundamentals';
  const nextLessonTitle = progressData.next_lesson?.title || 'Keep going with the next lesson';
  const learningGoal = user?.learning_goal || `${lessonsRemaining} lessons to reach your next milestone`;
  const dailyTime = user?.daily_learning_time ? `${user.daily_learning_time} min/day` : '30 min/day';
  const skillLevel = user?.skill_level || 'Beginner';
  const weeklyTarget = lessonsRemaining > 0 ? `Finish ${Math.min(3, lessonsRemaining)} lessons this week` : 'You are ahead of schedule!';

  return (
    <div className="app-shell">
      <section className="page-card space-y-8 dashboard-card fade-up">
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
            <p>{learningGoal}</p>
            <span className="status-pill">Goal tracker</span>
          </article>
          <article className="metric-card highlight-card">
            <h3>Daily Time</h3>
            <p>{dailyTime}</p>
            <span className="status-pill">Daily practice</span>
          </article>
          <article className="metric-card highlight-card">
            <h3>Skill Level</h3>
            <p>{skillLevel}</p>
            <span className="status-pill">Progress track</span>
          </article>
          <article className="metric-card highlight-card">
            <h3>Completion</h3>
            <p>{completionRate}% complete</p>
            <span className="status-pill">{completedLessons} of {totalLessons} lessons</span>
          </article>
          <article className="metric-card highlight-card">
            <h3>Weekly plan</h3>
            <p>{weeklyTarget}</p>
            <span className="status-pill">Next milestone</span>
          </article>
        </div>

        <section className="dashboard-summary-grid">
          <div className="section-title">
            <h2>Your Progress</h2>
            <p className="section-subtitle">Check completed lessons, earned confidence, and what to try next.</p>
          </div>

          <div className="page-card progress-summary-card">
            <div>
              <strong>Current module</strong>
              <p>{currentModule}</p>
            </div>
            <div>
              <strong>Next lesson</strong>
              <p>{nextLessonTitle}</p>
            </div>
          </div>

          <div className="progress-bar dashboard-progress-bar">
            <div className="progress-fill" style={{ width: `${completionRate}%` }} />
          </div>

          {(!progressData.progress || progressData.progress.length === 0) ? (
            <div className="lesson-card">No progress has been recorded yet. Start a lesson to begin tracking your performance.</div>
          ) : (
            <div className="lesson-list">
              {progressData.progress.map((p) => (
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
