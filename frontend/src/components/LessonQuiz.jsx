import { useEffect, useState } from 'react';
import api from '../services/api';

export default function LessonQuiz({ lessonSlug, lessonId }) {
  const [quizzes, setQuizzes] = useState([]);
  const [answers, setAnswers] = useState({});
  const [feedback, setFeedback] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!lessonSlug) return;
    setLoading(true);
    api
      .get(`/lessons/${lessonSlug}/quizzes`)
      .then((res) => setQuizzes(res.data.data || []))
      .catch(() => setQuizzes([]))
      .finally(() => setLoading(false));
  }, [lessonSlug]);

  const submitAnswer = async (quiz) => {
    const selected = answers[quiz.id];
    if (!selected) return;

    try {
      const res = await api.post('/quizzes/submit', {
        lesson_id: lessonId,
        question: quiz.question,
        selected_answer: selected,
      });
      const data = res.data.data;
      setFeedback((prev) => ({
        ...prev,
        [quiz.id]: {
          correct: data.correct,
          explanation: data.explanation,
        },
      }));
    } catch {
      setFeedback((prev) => ({
        ...prev,
        [quiz.id]: { correct: false, explanation: 'Could not check your answer. Try again.' },
      }));
    }
  };

  if (loading) return null;
  if (quizzes.length === 0) return null;

  return (
    <section className="lesson-quiz-panel page-card">
      <div className="section-title">
        <h2>Quick check</h2>
        <p className="section-subtitle">Answer these questions to reinforce what you just learned.</p>
      </div>

      {quizzes.map((quiz) => (
        <article key={quiz.id} className="quiz-card">
          <p className="quiz-question"><strong>{quiz.question}</strong></p>
          <div className="quiz-options">
            {(quiz.options || []).map((option) => (
              <label key={option} className="quiz-option">
                <input
                  type="radio"
                  name={`quiz-${quiz.id}`}
                  value={option}
                  checked={answers[quiz.id] === option}
                  onChange={() => setAnswers((prev) => ({ ...prev, [quiz.id]: option }))}
                  disabled={Boolean(feedback[quiz.id])}
                />
                <span>{option}</span>
              </label>
            ))}
          </div>
          {!feedback[quiz.id] ? (
            <button
              type="button"
              className="button-secondary"
              onClick={() => submitAnswer(quiz)}
              disabled={!answers[quiz.id]}
            >
              Check answer
            </button>
          ) : (
            <div className={`quiz-feedback ${feedback[quiz.id].correct ? 'correct' : 'incorrect'}`}>
              <strong>{feedback[quiz.id].correct ? 'Correct!' : 'Not quite — try reviewing the lesson.'}</strong>
              {feedback[quiz.id].explanation && <p>{feedback[quiz.id].explanation}</p>}
            </div>
          )}
        </article>
      ))}
    </section>
  );
}
