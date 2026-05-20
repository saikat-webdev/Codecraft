import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { fetchModule, fetchProgress } from '../services/learning';

export default function ModulePage() {
  const { slug } = useParams();
  const [module, setModule] = useState(null);
  const [progress, setProgress] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;

    Promise.all([fetchModule(slug), fetchProgress()])
      .then(([moduleRes, progressRes]) => {
        if (!mounted) return;
        setModule(moduleRes.data.data);
        setProgress(progressRes.data.data);
      })
      .catch(() => {})
      .finally(() => {
        if (mounted) setLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, [slug]);

  const completedLessonIds = new Set((progress?.progress || []).filter((item) => item.completed).map((item) => item.lesson_id));
  const moduleCompleted = module?.lessons?.length ? Math.round((completedLessonIds.size / module.lessons.length) * 100) : 0;

  if (loading) return <div className="app-shell">Loading module...</div>;

  if (!module) return <div className="app-shell"><div className="lesson-card">Module not found.</div></div>;

  return (
    <div className="app-shell">
      <section className="page-card space-y-8 module-detail-card">
        <div className="page-header">
          <div>
            <span className="eyebrow">Module {module.order}</span>
            <h1>{module.title}</h1>
            <p>{module.description}</p>
          </div>
          <div className="module-progress-box">
            <strong>{completedLessonIds.size} / {module.lessons?.length ?? 0} lessons complete</strong>
            <div className="progress-bar module-progress-bar">
              <div className="progress-fill" style={{ width: `${moduleCompleted}%` }} />
            </div>
            <p>{moduleCompleted}% of this module is done.</p>
          </div>
        </div>

        <div className="lesson-list module-lesson-grid">
          {module.lessons?.map((lesson) => (
            <article key={lesson.id} className="lesson-card lesson-card-grid lesson-card-premium">
              <div>
                <div className="lesson-badge">Lesson {lesson.order}</div>
                <h3>{lesson.title}</h3>
                <p>{lesson.description}</p>
                <p className="lesson-meta"><strong>{lesson.estimated_minutes} min</strong> · {lesson.difficulty}</p>
              </div>
              <div className="lesson-actions lesson-actions-module">
                <span className={`status-pill ${completedLessonIds.has(lesson.id) ? 'pill-success' : 'pill-secondary'}`}>
                  {completedLessonIds.has(lesson.id) ? 'Complete' : 'Continue'}
                </span>
                <Link to={`/lessons/${lesson.slug}`} className="button-secondary">Open lesson</Link>
              </div>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
