import { useState, useEffect } from 'react';
import Editor from '@monaco-editor/react';
import { codingApi } from '../services/coding';

export default function CodingPlayground({ exercise, onSubmissionComplete, compact = false }) {
    const [code, setCode] = useState(exercise?.starter_code || '');
    const [output, setOutput] = useState('');
    const [error, setError] = useState('');
    const [isRunning, setIsRunning] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [aiFeedback, setAiFeedback] = useState('');
    const [hints, setHints] = useState([]);
    const [showHints, setShowHints] = useState(false);
    const [hintIndex, setHintIndex] = useState(0);

    useEffect(() => {
        if (exercise?.starter_code) {
            setCode(exercise.starter_code);
        }
        setOutput('');
        setError('');
        setAiFeedback('');
        setHints([]);
        setShowHints(false);
        setHintIndex(0);
    }, [exercise]);

    const handleRun = async () => {
        setIsRunning(true);
        setOutput('');
        setError('');
        setAiFeedback('');

        try {
            const response = await codingApi.runCode(code);
            if (response.data.success) {
                setOutput(response.data.output);
            } else {
                setError(response.data.error);
            }
        } catch (err) {
            setError(err.response?.data?.error || 'Failed to run code. Please try again.');
        } finally {
            setIsRunning(false);
        }
    };

    const handleSubmit = async () => {
        if (!exercise) return;

        setIsSubmitting(true);
        try {
            const response = await codingApi.submitExercise(exercise.id, {
                code,
                output,
                error,
            });
            setAiFeedback(response.data.data.ai_feedback);
            onSubmissionComplete?.(response.data.data);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to submit. Please try again.');
        } finally {
            setIsSubmitting(false);
        }
    };

    const getHints = async () => {
        if (!exercise) return;

        try {
            const response = await codingApi.getHints(exercise.id);
            setHints(response.data.hints);
            setShowHints(true);
        } catch (err) {
            setHints([
                'Read the problem carefully and identify what you need to do.',
                'Break the problem into smaller, manageable steps.',
                'Try writing pseudocode first, then convert to Python.',
            ]);
            setShowHints(true);
        }
    };

    const resetCode = () => {
        setCode(exercise?.starter_code || '');
        setOutput('');
        setError('');
        setAiFeedback('');
    };

    if (compact) {
        return (
            <div className="coding-playground sidebar-playground">
                <div className="playground-editor sidebar-editor">
                    <Editor
                        height="200px"
                        language="python"
                        value={code}
                        onChange={(value) => setCode(value || '')}
                        theme="vs-dark"
                        options={{
                            fontSize: 12,
                            fontFamily: "'Fira Code', monospace",
                            minimap: { enabled: false },
                            automaticLayout: true,
                            tabSize: 4,
                            insertSpaces: true,
                            wordWrap: 'on',
                            lineNumbers: 'on',
                        }}
                    />
                </div>

                <div className="playground-actions sidebar-actions">
                    <button onClick={handleRun} disabled={isRunning} className="button-primary" style={{ padding: '0.4rem 0.8rem' }}>
                        {isRunning ? 'Running...' : 'Run'}
                    </button>
                    <button onClick={resetCode} className="button-secondary" disabled={isRunning} style={{ padding: '0.4rem 0.8rem' }}>
                        Reset
                    </button>
                    <button onClick={handleSubmit} disabled={isSubmitting || !output} className="button-secondary" style={{ padding: '0.4rem 0.8rem' }}>
                        {isSubmitting ? '...' : 'Submit'}
                    </button>
                </div>

                <div className="console-panel sidebar-console">
                    <div className="console-header"><span>Output</span></div>
                    <pre className={`console-output ${error ? 'error' : ''}`} style={{ padding: '0.5rem', fontSize: '0.75rem', minHeight: '40px' }}>
                        {error || output || 'Run code...'}
                    </pre>
                </div>

                {aiFeedback && (
                    <div className="ai-feedback-panel sidebar-feedback">
                        <p>{aiFeedback}</p>
                    </div>
                )}
            </div>
        );
    }

    return (
        <div className="coding-playground">
            <div className="playground-header">
                <h3>{exercise?.title || 'Coding Exercise'}</h3>
                <p className="exercise-description">{exercise?.description}</p>
            </div>

            <div className="playground-editor">
                <Editor
                    height="300px"
                    language="python"
                    value={code}
                    onChange={(value) => setCode(value || '')}
                    theme="vs-dark"
                    options={{
                        fontSize: 14,
                        fontFamily: "'Fira Code', monospace",
                        minimap: { enabled: false },
                        automaticLayout: true,
                        tabSize: 4,
                        insertSpaces: true,
                        wordWrap: 'on',
                        lineNumbers: 'on',
                    }}
                />
            </div>

            <div className="playground-actions">
                <button onClick={handleRun} disabled={isRunning} className="button-primary">
                    {isRunning ? 'Running...' : 'Run Code'}
                </button>
                <button onClick={resetCode} className="button-secondary" disabled={isRunning}>
                    Reset
                </button>
                <button onClick={handleSubmit} disabled={isSubmitting || !output} className="button-secondary">
                    {isSubmitting ? 'Submitting...' : 'Submit'}
                </button>
                <button onClick={getHints} className="button-secondary">
                    Get Hints
                </button>
            </div>

            {showHints && hints.length > 0 && (
                <div className="hints-panel">
                    <h4>Hint {hintIndex + 1} of {hints.length}</h4>
                    <p>{hints[hintIndex]}</p>
                    <div className="hint-navigation">
                        <button onClick={() => setHintIndex(Math.max(0, hintIndex - 1))} disabled={hintIndex === 0}>
                            Previous
                        </button>
                        <button onClick={() => setHintIndex(Math.min(hints.length - 1, hintIndex + 1))} disabled={hintIndex === hints.length - 1}>
                            Next
                        </button>
                    </div>
                </div>
            )}

            <div className="console-panel">
                <div className="console-header">
                    <span>Output</span>
                </div>
                <pre className={`console-output ${error ? 'error' : ''}`}>
                    {error || output || 'Click "Run Code" to see output here...'}
                </pre>
            </div>

            {aiFeedback && (
                <div className="ai-feedback-panel">
                    <h4>AI Feedback</h4>
                    <p>{aiFeedback}</p>
                </div>
            )}
        </div>
    );
}