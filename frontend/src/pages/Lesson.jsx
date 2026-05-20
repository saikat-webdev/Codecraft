import { useContext, useEffect, useState } from 'react';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { fetchLesson, fetchModule, fetchProgress, submitProgress } from '../services/learning';
import CodingPlayground from '../components/CodingPlayground';

export default function LessonPage() {
  const { slug } = useParams();
  const navigate = useNavigate();
  const { user } = useContext(AuthContext);
  const [lesson, setLesson] = useState(null);
  const [moduleData, setModuleData] = useState(null);
  const [helpText, setHelpText] = useState('Loading lesson guidance...');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [progress, setProgress] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    let mounted = true;
    setLoading(true);

    fetchLesson(slug)
      .then((res) => {
        if (!mounted) return;
        setLesson(res.data.data);
        return res.data.data;
      })
      .then((lessonData) => {
        if (!lessonData || !lessonData.module?.slug) return;
        fetchModule(lessonData.module.slug).then((res) => {
          if (!mounted) return;
          setModuleData(res.data.data);
        }).catch(() => {});
      })
      .catch(() => {
        if (mounted) setError('Could not load the lesson.');
      })
      .finally(() => {
        if (mounted) setLoading(false);
      });

    // AI support is temporarily disabled while the feature is paused.
    if (mounted) setHelpText('AI instructor support is temporarily disabled.');

    if (user) {
      fetchProgress()
        .then((res) => {
          if (mounted) setProgress(res.data.data);
        })
        .catch(() => {});
    }

    return () => {
      mounted = false;
    };
  }, [slug, user]);

  const handleProgress = async () => {
    if (!user) {
      navigate('/login');
      return;
    }

    if (!lesson) return;

    setSaving(true);
    setError('');

    try {
      await submitProgress({ lesson_id: lesson.id, completed: true, score: 100 });
      setProgress((current) => {
        const next = current?.progress || [];
        const existing = next.find((item) => item.lesson_id === lesson.id);
        if (existing) {
          existing.completed = true;
          return { ...current, progress: [...next] };
        }
        return { ...current, progress: [...next, { lesson_id: lesson.id, completed: true }] };
      });
    } catch (err) {
      setError('Unable to save your progress. Please try again.');
    } finally {
      setSaving(false);
    }
  };

  const completedLessonIds = new Set((progress?.progress || []).filter((item) => item.completed).map((item) => item.lesson_id));
  const isCompleted = lesson && completedLessonIds.has(lesson.id);
  const nextLesson = moduleData?.lessons?.find((next) => next.order > lesson?.order);

  if (loading && !lesson) return <div className="app-shell">Loading lesson...</div>;

  if (!lesson) return <div className="app-shell"><div className="lesson-card">Lesson not found.</div></div>;

  return (
    <div className="app-shell lesson-page-grid">
      <main className="page-card prose max-w-none lesson-detail-card">
        <div className="page-header">
          <div>
            <span className="eyebrow">Lesson {lesson.order}</span>
            <h1>{lesson.title}</h1>
            <p className="lesson-description">{lesson.description}</p>
          </div>
          <div className="lesson-meta-panel">
            <p><strong>Module:</strong> {moduleData?.title || 'Python Roadmap'}</p>
            <p><strong>Estimated time:</strong> {lesson.estimated_minutes} min</p>
            <p><strong>Difficulty:</strong> {lesson.difficulty}</p>
          </div>
        </div>

        <div className="lesson-content" dangerouslySetInnerHTML={{ __html: lesson.content }} />

        <div className="lesson-actions-row">
          <button className="button-primary" onClick={handleProgress} disabled={saving || isCompleted}>
            {isCompleted ? 'Lesson complete' : 'Mark lesson complete'}
          </button>
          {nextLesson && (
            <Link to={`/lessons/${nextLesson.slug}`} className="button-secondary">
              Continue to next lesson
            </Link>
          )}
        </div>
        {error && <div className="alert-error">{error}</div>}
      </main>

      <aside className="page-card code-sidebar-card">
        {lesson.exercises && lesson.exercises.length > 0 ? (
          <div className="sidebar-header">
            <span className="eyebrow">Practice Playground</span>
            <h2>Try the code</h2>
          </div>
        ) : (
          <div>
            <span className="eyebrow">AI Instructor</span>
            <h2>Lesson support</h2>
            <p>{helpText}</p>
          </div>
        )}

        {lesson.exercises && lesson.exercises.length > 0 ? (
          <div className="playground-sidebar">
            {lesson.exercises.slice(0, 1).map((exercise) => (
              <CodingPlayground 
                key={exercise.id}
                exercise={exercise} 
                onSubmissionComplete={(submission) => {
                  console.log('Submission:', submission);
                  if (submission.is_correct) {
                    alert('Great job! Exercise completed successfully! 🎉');
                  }
                }}
                compact={true}
              />
            ))}
          </div>
        ) : (
          <div className="chat-history lesson-chat">
            <div className="chat-message assistant">AI chat is disabled for now. Please continue through the lesson content and projects.</div>
          </div>
        )}
      </aside>
    </div>
  );
}
