import { useContext, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../services/api';
import { AuthContext } from '../context/AuthProvider';

export default function LoginPage() {
  const navigate = useNavigate();
  const { login } = useContext(AuthContext);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);

  const submit = async (e) => {
    e.preventDefault();
    try {
      const res = await api.post('/login', { email, password });
      const { access_token, user } = res.data.data;
      login(access_token, user);
      navigate('/dashboard');
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed');
    }
  };

  return (
    <div className="app-shell">
      <section className="page-card form-panel auth-grid">
        <div className="auth-intro">
          <span className="eyebrow">Back to the code lab</span>
          <h1>Sign in to your coding mentor</h1>
          <p>Jump into lessons, get warm guidance, and build your first code habits with bright, beginner-friendly pacing.</p>
          <div className="assistant-note">Your assistant is waiting to help you learn step-by-step, one comfortable lesson at a time.</div>
        </div>

        <div className="auth-form-panel">
          {error && <div className="alert-error">{error}</div>}

          <form onSubmit={submit} className="space-y-5">
            <label className="form-field">
              <span>Email</span>
              <input
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="you@example.com"
                className="form-input"
                autoComplete="email"
              />
            </label>

            <label className="form-field">
              <span>Password</span>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Enter your password"
                className="form-input"
                autoComplete="current-password"
              />
            </label>

            <button type="submit" className="form-button">Login</button>
          </form>

          <p className="form-note">
            New to CodeCraft? <Link to="/register" className="link-accent">Create an account</Link> and start coding.
          </p>
        </div>
      </section>
    </div>
  );
}
