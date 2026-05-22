import { useEffect, useState } from 'react';
import Editor from '@monaco-editor/react';
import { codingApi } from '../services/coding';

/**
 * Full-screen sudden test popup with countdown timer.
 * Supports MCQ and lightweight coding challenges (run via existing Judge0 proxy).
 */
export default function SuddenTestModal({
  challenge,
  timerSeconds = 90,
  difficulty = 2,
  onClose,
  onSubmit,
}) {
  const question = challenge?.question;
  const [secondsLeft, setSecondsLeft] = useState(timerSeconds);
  const [mcqAnswer, setMcqAnswer] = useState('');
  const [code, setCode] = useState(question?.starter_code || '');
  const [output, setOutput] = useState('');
  const [runError, setRunError] = useState('');
  const [isRunning, setIsRunning] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [result, setResult] = useState(null);
  const [timedOut, setTimedOut] = useState(false);

  useEffect(() => {
    setSecondsLeft(timerSeconds);
    setMcqAnswer('');
    setCode(question?.starter_code || '');
    setOutput('');
    setRunError('');
    setResult(null);
    setTimedOut(false);
  }, [question?.id, timerSeconds, question?.starter_code]);

  useEffect(() => {
    if (result || timedOut) return undefined;
    if (secondsLeft <= 0) {
      setTimedOut(true);
      return undefined;
    }
    const id = setInterval(() => setSecondsLeft((s) => s - 1), 1000);
    return () => clearInterval(id);
  }, [secondsLeft, result, timedOut]);

  const timeTaken = timerSeconds - Math.max(0, secondsLeft);

  const handleRunCode = async () => {
    setIsRunning(true);
    setRunError('');
    try {
      const res = await codingApi.runCode(code, question?.language || 'python');
      if (res.data.success) setOutput(res.data.output);
      else setRunError(res.data.error);
    } catch (err) {
      setRunError(err.response?.data?.error || 'Run failed');
    } finally {
      setIsRunning(false);
    }
  };

  const handleSubmit = async () => {
    if (submitting || result) return;
    setSubmitting(true);
    try {
      const payload =
        question?.type === 'mcq'
          ? { answer: mcqAnswer, time_taken_seconds: timeTaken }
          : { code, time_taken_seconds: timeTaken };
      const data = await onSubmit(payload);
      setResult(data);
    } catch {
      setResult({ passed: false, feedback: 'Could not save your attempt.' });
    } finally {
      setSubmitting(false);
    }
  };

  if (!question) return null;

  const timerPct = (secondsLeft / timerSeconds) * 100;

  return (
    <div className="sudden-test-overlay" role="dialog" aria-modal="true">
      <div className="sudden-test-modal page-card">
        <div className="sudden-test-modal-header">
          <div>
            <span className="eyebrow">Sudden test</span>
            <h2>{question.title}</h2>
            <p className="sudden-test-difficulty">
              Difficulty {difficulty}/5 · {question.type === 'mcq' ? 'MCQ' : 'Coding'}
            </p>
          </div>
          <div className={`sudden-test-timer ${secondsLeft <= 15 ? 'urgent' : ''}`}>
            <span className="timer-value">{Math.max(0, secondsLeft)}s</span>
            <div className="timer-bar">
              <div className="timer-fill" style={{ width: `${timerPct}%` }} />
            </div>
          </div>
        </div>

        <p className="sudden-test-prompt">{question.prompt}</p>

        {!result && !timedOut && (
          <>
            {question.type === 'mcq' ? (
              <div className="sudden-mcq-options">
                {(question.options || []).map((opt) => (
                  <label key={opt} className={`sudden-mcq-option ${mcqAnswer === opt ? 'selected' : ''}`}>
                    <input
                      type="radio"
                      name="sudden-mcq"
                      value={opt}
                      checked={mcqAnswer === opt}
                      onChange={() => setMcqAnswer(opt)}
                    />
                    {opt}
                  </label>
                ))}
              </div>
            ) : (
              <div className="sudden-coding-block">
                <div className="playground-editor sudden-editor">
                  <Editor
                    height="180px"
                    language={question.language === 'cpp' ? 'cpp' : question.language || 'python'}
                    value={code}
                    onChange={(v) => setCode(v || '')}
                    theme="vs-dark"
                    options={{
                      fontSize: 13,
                      minimap: { enabled: false },
                      automaticLayout: true,
                      wordWrap: 'on',
                    }}
                  />
                </div>
                <div className="playground-actions">
                  <button type="button" className="button-secondary" onClick={handleRunCode} disabled={isRunning}>
                    {isRunning ? 'Running…' : 'Run'}
                  </button>
                </div>
                {(output || runError) && (
                  <pre className={`console-output ${runError ? 'error' : ''}`}>{runError || output}</pre>
                )}
              </div>
            )}

            <div className="sudden-test-actions">
              <button
                type="button"
                className="button-primary"
                disabled={submitting || (question.type === 'mcq' && !mcqAnswer)}
                onClick={handleSubmit}
              >
                {submitting ? 'Submitting…' : 'Submit answer'}
              </button>
              <button type="button" className="button-secondary" onClick={onClose}>
                Dismiss
              </button>
            </div>
          </>
        )}

        {(result || timedOut) && (
          <div className={`sudden-result ${result?.passed ? 'pass' : 'fail'}`}>
            <h3>{timedOut && !result ? "Time's up!" : result?.passed ? 'Correct!' : 'Not quite'}</h3>
            <p>{result?.feedback || 'The timer ended before you submitted.'}</p>
            {result?.xp_awarded > 0 && <p className="xp-gain">+{result.xp_awarded} XP</p>}
            <button type="button" className="button-primary" onClick={onClose}>
              Continue learning
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
