import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchModules, fetchProgress } from '../services/learning';

export default function Roadmap() {
  const [modules, setModules] = useState([]);
  const [progress, setProgress] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;

    Promise.all([fetchModules(), fetchProgress()])
      .then(([modulesRes, progressRes]) => {
        if (!mounted) return;
        setModules(modulesRes.data.data || []);
        setProgress(progressRes.data.data || null);
      })
      .catch(() => {})
      .finally(() => {
        if (mounted) setLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, []);

  const percentage = progress?.progress_percentage ?? 0;
  const completedCount = progress?.completed_count ?? 0;
  const totalLessons = progress?.total_lessons ?? modules.reduce((sum, m) => sum + (m.lessons?.length || 0), 0);

  return (
    <div className="app-shell">
      <section className="page-card space-y-8 roadmap-card">
        <div className="page-header roadmap-header">
          <div>
            <span className="eyebrow">Python Roadmap</span>
            <h1>Structured beginner Python learning</h1>
            <p>Follow a clear path from Python basics to small projects with a friendly AI instructor by your side.</p>
          </div>
          <div className="roadmap-summary">
            <div className="summary-top">
              <strong>Learning progress</strong>
              <span>{percentage}%</span>
            </div>
            <div className="progress-bar">
              <div className="progress-fill" style={{ width: `${percentage}%` }} />
            </div>
            <div className="summary-tags">
              <span>{completedCount} completed</span>
              <span>{totalLessons} total lessons</span>
            </div>
            {progress?.next_lesson ? (
              <Link to={`/lessons/${progress.next_lesson.slug}`} className="button-primary">
                Continue: {progress.next_lesson.title}
              </Link>
            ) : (
              <p className="text-muted">Ready for your next lesson.</p>
            )}
          </div>
        </div>

        {loading ? (
          <div className="lesson-card">Loading roadmap…</div>
        ) : modules.length === 0 ? (
          <div className="lesson-card">No roadmap modules are currently available.</div>
        ) : (
          <div className="module-grid">
            {modules.map((module) => (
              <article key={module.id} className="module-card module-card-premium">
                <div>
                  <span className="module-label">Module {module.order}</span>
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
      </section>
    </div>
  );
}
