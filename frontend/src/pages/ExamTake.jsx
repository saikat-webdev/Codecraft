import { useContext, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { examsApi } from '../services/exams';

export default function ExamTake() {
  const { slug } = useParams();
  const navigate = useNavigate();
  const { user } = useContext(AuthContext);
  const [exam, setExam] = useState(null);
  const [answers, setAnswers] = useState({});
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [startedAt] = useState(() => new Date().toISOString());
  const [error, setError] = useState('');

  useEffect(() => {
    if (!user) {
      navigate('/login');
      return;
    }

    examsApi
      .get(slug)
      .then((res) => setExam(res.data.data))
      .catch(() => setError('Could not load this exam.'))
      .finally(() => setLoading(false));
  }, [slug, user, navigate]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!exam) return;

    const payload = {
      started_at: startedAt,
      answers: exam.questions.map((q) => ({
        question_id: q.id,
        answer: answers[q.id] || '',
      })),
    };

    setSubmitting(true);
    setError('');

    try {
      const res = await examsApi.submit(slug, payload);
      setResult(res.data.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to submit exam.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) return <div className="app-shell">Loading exam…</div>;
  if (!exam) return <div className="app-shell"><div className="page-card">{error || 'Exam not found.'}</div></div>;

  if (result) {
    return (
      <div className="app-shell">
        <section className="page-card exam-result-card fade-up">
          <span className="eyebrow">Exam complete</span>
          <h1>{result.passed ? 'You passed!' : 'Keep practicing'}</h1>
          <p className="exam-score-display">{result.percentage}% · {result.score}/{result.max_score} points</p>
          <p>{result.passed ? 'Great work — your fundamentals are solid.' : `You need ${exam.passing_score}% to pass. Review the lessons and try again.`}</p>
          <div className="hero-actions">
            <Link to="/exams" className="button-primary">Back to exams</Link>
            <Link to="/modules" className="button-secondary">Study roadmap</Link>
          </div>
        </section>
      </div>
    );
  }

  const allAnswered = exam.questions?.every((q) => answers[q.id]);

  return (
    <div className="app-shell">
      <form onSubmit={handleSubmit} className="page-card exam-take-card">
        <div className="page-header">
          <div>
            <span className="eyebrow">{exam.track} exam · {exam.duration_minutes} min suggested</span>
            <h1>{exam.title}</h1>
            <p>{exam.description}</p>
          </div>
        </div>

        {exam.questions?.map((q, index) => (
          <article key={q.id} className="quiz-card">
            <p className="quiz-question"><strong>{index + 1}. {q.question}</strong></p>
            <div className="quiz-options">
              {(q.options || []).map((option) => (
                <label key={option} className="quiz-option">
                  <input
                    type="radio"
                    name={`exam-q-${q.id}`}
                    value={option}
                    checked={answers[q.id] === option}
                    onChange={() => setAnswers((prev) => ({ ...prev, [q.id]: option }))}
                  />
                  <span>{option}</span>
                </label>
              ))}
            </div>
          </article>
        ))}

        {error && <div className="alert-error">{error}</div>}

        <div className="lesson-actions-row">
          <button type="submit" className="button-primary" disabled={submitting || !allAnswered}>
            {submitting ? 'Submitting…' : 'Submit exam'}
          </button>
          <Link to="/exams" className="button-secondary">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
