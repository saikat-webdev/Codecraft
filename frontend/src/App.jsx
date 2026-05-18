import { BrowserRouter, Routes, Route, Link } from 'react-router-dom';
import { AuthProvider } from './context/AuthProvider';
import ProtectedRoute from './components/ProtectedRoute';
import BrandLogo from './components/BrandLogo';
import LoginPage from './pages/Login';
import RegisterPage from './pages/Register';
import Dashboard from './pages/Dashboard';
import Lessons from './pages/Lessons';
import LessonPage from './pages/Lesson';
import './index.css';

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <div className="app-frame">
          <header className="site-header">
            <div className="container">
              <BrandLogo />
              <nav>
                <Link to="/lessons">Lessons</Link>
                <Link to="/dashboard">Dashboard</Link>
                <Link to="/login">Login</Link>
              </nav>
            </div>
          </header>

          <main className="app-shell">
            <Routes>
              <Route
                path="/"
                element={
                  <section className="hero-panel hero-grid">
                    <div className="hero-content">
                      <span className="eyebrow">Your coding mentor</span>
                      <h1>Learn code with a friendly instructor by your side.</h1>
                      <p>CodeCraft turns every lesson into a playful learning lab with instant coaching, bright feedback, and beginner-first projects.</p>
                      <div className="hero-actions">
                        <Link to="/register" className="button-primary">Join the lab</Link>
                        <Link to="/lessons" className="button-secondary">See lessons</Link>
                      </div>

                      <div className="hero-features">
                        <div className="feature-chip">Live-style guidance</div>
                        <div className="feature-chip">Project-based learning</div>
                        <div className="feature-chip">Built for beginners</div>
                      </div>
                    </div>

                    <div className="hero-console-wrap">
                      <div className="hero-console">
                        <div className="console-header">
                          <span className="console-dot red" />
                          <span className="console-dot yellow" />
                          <span className="console-dot green" />
                          <span className="console-title">CodeCraft console</span>
                        </div>
                        <pre className="console-code">
                          <code>
                            <span className="console-line"><span className="console-keyword">const</span> guide = &#123; name: <span className="console-string">'CodeCraft'</span>, style: <span className="console-string">'friendly'</span> &#125;;</span>
                            <span className="console-line"><span className="console-keyword">const</span> practice = [<span className="console-string">'learn'</span>, <span className="console-string">'build'</span>, <span className="console-string">'repeat'</span>];</span>
                            <span className="console-line console-comment">// your assistant guides every step</span>
                            <span className="console-line console-highlight"><span className="console-keyword">console</span>.log(<span className="console-string">'Welcome to the code lab!'</span>);</span>
                          </code>
                        </pre>
                      </div>

                      <div className="assistant-card">
                        <strong>Assistant:</strong> Start with a short lesson, then build a real mini project in the next step.
                      </div>
                    </div>
                  </section>
                }
              />
              <Route path="/login" element={<LoginPage />} />
              <Route path="/register" element={<RegisterPage />} />
              <Route path="/lessons" element={<Lessons />} />
              <Route path="/lessons/:slug" element={<LessonPage />} />
              <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
            </Routes>
          </main>
        </div>
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
