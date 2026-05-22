import { useState, useCallback } from 'react';
import Editor from '@monaco-editor/react';
import { codingApi } from '../services/coding';
import {
  PLAYGROUND_LANGUAGES,
  DEFAULT_LANGUAGE_ID,
  getLanguageById,
} from '../constants/playgroundLanguages';

/**
 * Reusable Judge0-backed playground.
 * - Submits code through Laravel (never calls Judge0 from the browser)
 * - Poll result is handled server-side; UI shows running / success / error states
 */
export default function CodingPlayground({
  exercise = null,
  onSubmissionComplete,
  compact = false,
  showExerciseActions = true,
  initialLanguage = DEFAULT_LANGUAGE_ID,
}) {
  const exerciseLanguage = exercise?.language || initialLanguage;
  const initialLang = getLanguageById(exerciseLanguage);
  const [languageId, setLanguageId] = useState(exerciseLanguage);
  const [code, setCode] = useState(
    () => exercise?.starter_code ?? initialLang.starterCode
  );
  const [output, setOutput] = useState('');
  const [error, setError] = useState('');
  const [runStatus, setRunStatus] = useState('idle'); // idle | running | success | error
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitFeedback, setSubmitFeedback] = useState('');

  const activeLanguage = getLanguageById(languageId);

  const resetEditor = useCallback(
    (langId, starterOverride) => {
      const lang = getLanguageById(langId);
      const starter =
        starterOverride ??
        exercise?.starter_code ??
        lang.starterCode;
      setCode(starter);
      setOutput('');
      setError('');
      setRunStatus('idle');
      setSubmitFeedback('');
    },
    [exercise?.starter_code]
  );

  const handleLanguageChange = (event) => {
    const nextId = event.target.value;
    setLanguageId(nextId);
    resetEditor(nextId);
  };

  const handleRun = async () => {
    setRunStatus('running');
    setOutput('');
    setError('');
    setSubmitFeedback('');

    try {
      const response = await codingApi.runCode(code, languageId);
      const data = response.data;

      if (data.success) {
        setOutput(data.output ?? '');
        setRunStatus('success');
      } else {
        setError(data.error || 'Execution failed.');
        if (data.output) setOutput(data.output);
        setRunStatus('error');
      }
    } catch (err) {
      setError(
        err.response?.data?.error ||
          err.response?.data?.message ||
          'Failed to run code. Please try again.'
      );
      setRunStatus('error');
    }
  };

  const handleSubmit = async () => {
    if (!exercise?.id) return;

    setIsSubmitting(true);
    setSubmitFeedback('');

    try {
      const response = await codingApi.submitExercise(exercise.id, {
        code,
        output,
        error,
        language: languageId,
      });
      const submission = response.data?.data ?? response.data;
      setSubmitFeedback(submission?.feedback ?? submission?.ai_feedback ?? '');
      onSubmissionComplete?.(submission);
    } catch (err) {
      setError(
        err.response?.data?.message || 'Failed to submit. Please try again.'
      );
      setRunStatus('error');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleReset = () => resetEditor(languageId);

  const isRunning = runStatus === 'running';
  const editorHeight = compact ? '220px' : '360px';
  const fontSize = compact ? 12 : 14;

  const terminalContent =
    runStatus === 'running'
      ? 'Running your code…'
      : error || output || 'Write code, pick a language, then press Run.';

  return (
    <div className={`coding-playground ${compact ? 'sidebar-playground' : 'playground-full'}`}>
      <div className="playground-toolbar">
        <div className="playground-toolbar-left">
          {!compact && (
            <div className="playground-header">
              <h3>{exercise?.title || 'Coding Playground'}</h3>
              {exercise?.description && (
                <p className="exercise-description">{exercise.description}</p>
              )}
            </div>
          )}
          <label className="language-select-wrap">
            <span className="language-select-label">Language</span>
            <select
              className="language-select"
              value={languageId}
              onChange={handleLanguageChange}
              disabled={isRunning}
              aria-label="Programming language"
            >
              {PLAYGROUND_LANGUAGES.map((lang) => (
                <option key={lang.id} value={lang.id}>
                  {lang.label}
                </option>
              ))}
            </select>
          </label>
        </div>
        <span className={`run-status-badge status-${runStatus}`} aria-live="polite">
          {runStatus === 'idle' && 'Ready'}
          {runStatus === 'running' && 'Running…'}
          {runStatus === 'success' && 'Done'}
          {runStatus === 'error' && 'Error'}
        </span>
      </div>

      <div className={`playground-editor ${compact ? 'sidebar-editor' : ''}`}>
        <Editor
          height={editorHeight}
          language={activeLanguage.monaco}
          value={code}
          onChange={(value) => setCode(value || '')}
          theme="vs-dark"
          options={{
            fontSize,
            fontFamily: "'JetBrains Mono', monospace",
            minimap: { enabled: !compact },
            automaticLayout: true,
            tabSize: activeLanguage.monaco === 'python' ? 4 : 2,
            insertSpaces: true,
            wordWrap: 'on',
            lineNumbers: 'on',
            scrollBeyondLastLine: false,
            padding: { top: 12 },
          }}
        />
      </div>

      <div className={`playground-actions ${compact ? 'sidebar-actions' : ''}`}>
        <button
          type="button"
          onClick={handleRun}
          disabled={isRunning || !code.trim()}
          className="button-primary"
        >
          {isRunning ? 'Running…' : 'Run Code'}
        </button>
        <button
          type="button"
          onClick={handleReset}
          className="button-secondary"
          disabled={isRunning}
        >
          Reset
        </button>
        {showExerciseActions && exercise?.id && (
          <button
            type="button"
            onClick={handleSubmit}
            disabled={isSubmitting || runStatus !== 'success' || !output}
            className="button-secondary"
          >
            {isSubmitting ? 'Submitting…' : 'Submit solution'}
          </button>
        )}
      </div>

      <div className={`console-panel ${compact ? 'sidebar-console' : ''}`}>
        <div className="console-header">
          <span>Terminal</span>
          {isRunning && <span className="console-spinner" aria-hidden="true" />}
        </div>
        <pre
          className={`console-output ${error ? 'error' : ''} ${runStatus === 'running' ? 'running' : ''}`}
          role="log"
          aria-live="polite"
        >
          {terminalContent}
        </pre>
      </div>

      {submitFeedback && (
        <div className="submit-feedback-panel">
          <h4>Submission feedback</h4>
          <p>{submitFeedback}</p>
        </div>
      )}
    </div>
  );
}
