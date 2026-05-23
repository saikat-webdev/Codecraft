import { useContext, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { examsApi } from '../services/exams';
import { COURSE_TRACKS } from '../constants/playgroundLanguages';

export default function Exams() {
  const { user } = useContext(AuthContext);
  const [track, setTrack] = useState('all');
  const [exams, setExams] = useState([]);
  const [attempts, setAttempts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;
    setLoading(true);

    const params = track === 'all' ? undefined : track;
    const requests = [examsApi.list(params)];

    if (user) {
      requests.push(examsApi.myAttempts().catch(() => ({ data: { data: [] } })));
    }

    Promise.all(requests)
      .then(([examsRes, attemptsRes]) => {
        if (!mounted) return;
        setExams(examsRes.data.data || []);
        setAttempts(attemptsRes?.data?.data || []);
      })
      .catch(() => {
        if (mounted) setExams([]);
      })
      .finally(() => {
        if (mounted) setLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, [track, user]);

  const bestByExam = attempts.reduce((acc, attempt) => {
    const slug = attempt.exam?.slug;
    if (!slug) return acc;
    if (!acc[slug] || attempt.percentage > acc[slug].percentage) {
      acc[slug] = attempt;
    }
    return acc;
  }, {});

  return (
    <div className="app-shell">
      <section className="page-card exams-page fade-up">
        <div className="page-header">
          <div>
            <span className="eyebrow">Practice exams</span>
            <h1>Test your skills</h1>
            <p>Short, beginner-friendly exams for each language track. Pass to earn XP and confidence.</p>
          </div>
        </div>

        <div className="track-filter-row">
          <button
            type="button"
            className={`filter-btn ${track === 'all' ? 'active' : ''}`}
            onClick={() => setTrack('all')}
          >
            All tracks
          </button>
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
          <p className="text-muted">Loading exams…</p>
        ) : exams.length === 0 ? (
          <p className="text-muted">No exams available for this track yet.</p>
        ) : (
          <div className="exam-grid">
            {exams.map((exam) => {
              const best = bestByExam[exam.slug];
              return (
                <article key={exam.id} className="exam-card module-card-premium">
                  <span className="module-label">{exam.track} · {exam.duration_minutes} min</span>
                  <h2>{exam.title}</h2>
                  <p>{exam.description}</p>
                  <div className="module-meta module-card-footer">
                    <span>{exam.questions_count ?? exam.questions?.length ?? 0} questions · Pass {exam.passing_score}%</span>
                    {best && (
                      <span className={`status-pill ${best.passed ? 'pill-success' : 'pill-warning'}`}>
                        Best: {best.percentage}%
                      </span>
                    )}
                  </div>
                  {user ? (
                    <Link to={`/exams/${exam.slug}`} className="button-primary">
                      {best?.passed ? 'Retake exam' : 'Start exam'}
                    </Link>
                  ) : (
                    <Link to="/login" className="button-secondary">Log in to take exam</Link>
                  )}
                </article>
              );
            })}
          </div>
        )}
      </section>
    </div>
  );
}
