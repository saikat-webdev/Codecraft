import { useContext, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { fetchModules, fetchProgress } from '../services/learning';
import { COURSE_TRACKS } from '../constants/playgroundLanguages';

export default function Roadmap() {
  const { user } = useContext(AuthContext);
  const [track, setTrack] = useState('python');
  const [modules, setModules] = useState([]);
  const [progress, setProgress] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;
    setLoading(true);

    const loadModules = fetchModules(track === 'all' ? undefined : track)
      .then((res) => {
        if (mounted) setModules(res.data.data || []);
      })
      .catch(() => {
        if (mounted) setModules([]);
      });

    const loadProgress = user
      ? fetchProgress()
          .then((res) => {
            if (mounted) setProgress(res.data.data || null);
          })
          .catch(() => {
            if (mounted) setProgress(null);
          })
      : Promise.resolve().then(() => {
          if (mounted) setProgress(null);
        });

    Promise.all([loadModules, loadProgress]).finally(() => {
      if (mounted) setLoading(false);
    });

    return () => {
      mounted = false;
    };
  }, [track, user]);

  const activeTrack = COURSE_TRACKS.find((t) => t.id === track) || COURSE_TRACKS[0];
  const percentage = progress?.progress_percentage ?? 0;
  const completedCount = progress?.completed_count ?? 0;
  const totalLessons = progress?.total_lessons ?? modules.reduce((sum, m) => sum + (m.lessons?.length || 0), 0);

  return (
    <div className="app-shell">
      <section className="page-card space-y-8 roadmap-card">
        <div className="page-header roadmap-header">
          <div>
            <span className="eyebrow">{activeTrack.icon} {activeTrack.label} roadmap</span>
            <h1>Structured beginner learning paths</h1>
            <p>Pick a language track, follow modules in order, and practice in the built-in coding playground.</p>
          </div>
          {user && (
            <div className="roadmap-summary">
              <div className="summary-top">
                <strong>Overall progress</strong>
                <span>{percentage}%</span>
              </div>
              <div className="progress-bar">
                <div className="progress-fill" style={{ width: `${percentage}%` }} />
              </div>
              <div className="summary-tags">
                <span>{completedCount} completed</span>
                <span>{totalLessons} total lessons</span>
              </div>
              {progress?.next_lesson && (
                <Link to={`/lessons/${progress.next_lesson.slug}`} className="button-primary">
                  Continue: {progress.next_lesson.title}
                </Link>
              )}
            </div>
          )}
        </div>

        <div className="track-filter-row">
          {COURSE_TRACKS.map((t) => (
            <button
              key={t.id}
              type="button"
              className={`filter-btn ${track === t.id ? 'active' : ''}`}
              onClick={() => setTrack(t.id)}
            >
              {t.icon} {t.label}
            </button>
          ))}
        </div>

        {loading ? (
          <div className="lesson-card">Loading roadmap…</div>
        ) : modules.length === 0 ? (
          <div className="lesson-card">No modules for this track yet. Try another language or run database seeders.</div>
        ) : (
          <div className="module-grid">
            {modules.map((module) => (
              <article key={module.id} className="module-card module-card-premium">
                <div>
                  <span className="module-label">{module.icon || activeTrack.icon} Module {module.order}</span>
                  <h2>{module.title}</h2>
                  <p>{module.description}</p>
                </div>
                <div className="module-meta module-card-footer">
                  <span>{module.lessons?.length ?? 0} lessons</span>
                  <Link to={`/modules/${module.slug}`} className="button-secondary">Open module</Link>
                </div>
              </article>
            ))}
          </div>
        )}

        <div className="roadmap-exam-cta">
          <p>Ready to test what you learned?</p>
          <Link to="/exams" className="button-primary">Take a practice exam</Link>
        </div>
      </section>
    </div>
  );
}
