import { BrowserRouter, Routes, Route, Link, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthProvider';
import ProtectedRoute from './components/ProtectedRoute';
import BrandLogo from './components/BrandLogo';
import LoginPage from './pages/Login';
import RegisterPage from './pages/Register';
import Dashboard from './pages/Dashboard';
import Roadmap from './pages/Roadmap';
import ModulePage from './pages/ModulePage';
import Lessons from './pages/Lessons';
import LessonPage from './pages/Lesson';
import Playground from './pages/Playground';
import { useTheme } from './context/ThemeProvider';
import './index.css';

function ThemeIcon({ theme }) {
  if (theme === 'dark') {
    return (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
        <circle cx="12" cy="12" r="4" />
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
      </svg>
    );
  }
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
    </svg>
  );
}

function App() {
  const { theme, toggleTheme } = useTheme();

  return (
    <AuthProvider>
      <BrowserRouter>
        <div className="app-frame">
          <div className="ambient-bg" aria-hidden="true">
            <div className="orb orb-1" />
            <div className="orb orb-2" />
            <div className="orb orb-3" />
            <div className="grid-overlay" />
          </div>

          <header className="site-header">
            <div className="container">
              <BrandLogo />
              <nav>
                <Link to="/modules">Roadmap</Link>
                <Link to="/lessons">Lessons</Link>
                <Link to="/playground">Playground</Link>
                <Link to="/dashboard">Dashboard</Link>
                <Link to="/login">Login</Link>
                <button
                  type="button"
                  onClick={toggleTheme}
                  className="theme-toggle"
                  aria-label={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
                >
                  <ThemeIcon theme={theme} />
                </button>
              </nav>
            </div>
          </header>

          <main className="app-shell">
            <Routes>
              <Route
                path="/"
                element={
                  <div className="home-page">
                    <section className="hero-panel hero-grid fade-up">
                      <div className="hero-content">
                        <span className="eyebrow">Your coding lab</span>
                        <h1>Learn Python with a live coding playground.</h1>
                        <p>CodeCraft turns every lesson into hands-on practice — run real code in the browser, track progress, and build skills project by project.</p>
                        <div className="hero-actions">
                          <Link to="/register" className="button-primary">Join the lab</Link>
                          <Link to="/playground" className="button-secondary">Open playground</Link>
                        </div>

                        <div className="hero-features">
                          <div className="feature-chip">Python Promise course</div>
                          <div className="feature-chip">Fast, daily routines</div>
                          <div className="feature-chip">Project-powered learning</div>
                        </div>
                      </div>

                      <div className="hero-console-wrap">
                        <div className="hero-console code-panel">
                          <div className="console-header">
                            <span className="console-dot red" />
                            <span className="console-dot yellow" />
                            <span className="console-dot green" />
                            <span className="console-title">Live learning terminal</span>
                          </div>
                          <pre className="console-code">
                            <code>
                              <span className="console-line animate-line delay-0"><span className="console-keyword">def</span> greet(name):</span>
                              <span className="console-line animate-line delay-1">  message = f"Hello, {name}!"</span>
                              <span className="console-line animate-line delay-2">  return message</span>
                              <span className="console-line animate-line delay-3 console-comment"># Run the lesson code and watch your skills grow</span>
                              <span className="console-line animate-line delay-4 console-highlight">print(greet('Learner'))</span>
                            </code>
                          </pre>
                        </div>

                        <div className="assistant-card glow-panel">
                          <strong>Studio tip:</strong> Practice every day and the code will start to feel like second nature.
                        </div>
                      </div>
                    </section>

                    <section className="page-card insight-panel fade-up delay-1">
                      <div className="section-title">
                        <h2>Why CodeCraft works</h2>
                        <p className="section-subtitle">Every part of the experience is designed for beginners who want fast wins and steady momentum.</p>
                      </div>
                      <div className="feature-grid">
                        <article className="feature-card">
                          <strong>Step-by-step lessons</strong>
                          <p>Each lesson builds on the last with clear examples and no jargon.</p>
                        </article>
                        <article className="feature-card">
                          <strong>Practice-first projects</strong>
                          <p>Apply new skills immediately with mini projects that feel like real code.</p>
                        </article>
                        <article className="feature-card">
                          <strong>Real progress tracking</strong>
                          <p>See your completed lessons, streaks, and next step right in the dashboard.</p>
                        </article>
                      </div>
                    </section>

                    <section className="page-card code-stage fade-up delay-2">
                      <div className="section-title">
                        <h2>Live coding flow</h2>
                        <p className="section-subtitle">Watch the learning path animate with color, motion, and a studio-style code preview.</p>
                      </div>
                      <div className="code-grid">
                        <div className="code-visual">
                          <div className="code-window">
                            <div className="code-toolbar">
                              <span className="toolbar-dot red" />
                              <span className="toolbar-dot yellow" />
                              <span className="toolbar-dot green" />
                            </div>
                            <div className="code-body">
                              <div className="code-line shimmer">from codecraft import beginner_path</div>
                              <div className="code-line shimmer delay-1">lesson = beginner_path.start()</div>
                              <div className="code-line shimmer delay-2">lesson.explain('variables')</div>
                              <div className="code-line shimmer delay-3">lesson.practice('write your first function')</div>
                              <div className="code-line shimmer delay-4">lesson.complete()</div>
                            </div>
                          </div>
                        </div>
                        <div className="code-copy">
                          <h3>Learn by doing</h3>
                          <p>Follow a guided learning flow with friendly explanations, handy feedback, and small wins on every step.</p>
                          <div className="stat-row">
                            <span>20+ lessons</span>
                            <span>3 guided projects</span>
                            <span>60m daily challenge</span>
                          </div>
                        </div>
                      </div>
                    </section>

                    <section className="page-card powered-panel fade-up delay-3">
                      <div className="section-title">
                        <h2>Build confidence in every session</h2>
                        <p className="section-subtitle">Short sprints, bite-sized practice, and progress that feels visible from the first day.</p>
                      </div>
                      <div className="stats-grid">
                        <article className="stat-card">
                          <span className="stat-number">92%</span>
                          <p>Beginner-friendly clarity</p>
                        </article>
                        <article className="stat-card">
                          <span className="stat-number">4.7/5</span>
                          <p>Student satisfaction rating</p>
                        </article>
                        <article className="stat-card">
                          <span className="stat-number">Next</span>
                          <p>Take your next lesson and keep momentum going.</p>
                        </article>
                      </div>
                    </section>

                    <section className="page-card journey-panel fade-up delay-4">
                      <div className="section-title">
                        <h2>From first script to actual project</h2>
                        <p className="section-subtitle">Follow a supportive roadmap with clear next steps, progress milestones, and build-ready exercises.</p>
                      </div>
                      <div className="journey-grid">
                        <article className="journey-card">
                          <strong>Start strong</strong>
                          <p>Easy first lessons that teach Python fundamentals with friendly explanations.</p>
                        </article>
                        <article className="journey-card">
                          <strong>Build fast</strong>
                          <p>Practice functions, loops, and data with mini apps that feel rewarding.</p>
                        </article>
                        <article className="journey-card">
                          <strong>Ship your work</strong>
                          <p>Finish a simple capstone project and see your progress reflected in the dashboard.</p>
                        </article>
                      </div>
                    </section>
                  </div>
                }
              />
              <Route path="/login" element={<LoginPage />} />
              <Route path="/register" element={<RegisterPage />} />
              <Route path="/modules" element={<Roadmap />} />
              <Route path="/modules/:slug" element={<ModulePage />} />
              <Route path="/lessons" element={<Lessons />} />
              <Route path="/lessons/:slug" element={<LessonPage />} />
              <Route path="/playground" element={<Playground />} />
              <Route path="/ai" element={<Navigate to="/playground" replace />} />
              <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
            </Routes>
          </main>
        </div>
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
