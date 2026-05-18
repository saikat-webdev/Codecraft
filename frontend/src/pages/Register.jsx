import { useState, useContext } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../services/api';
import { AuthContext } from '../context/AuthProvider';

export default function RegisterPage() {
  const navigate = useNavigate();
  const { login } = useContext(AuthContext);
  const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
  const [error, setError] = useState(null);

  const submit = async (e) => {
    e.preventDefault();
    try {
      const res = await api.post('/register', form);
      const { access_token, user } = res.data.data;
      login(access_token, user);
      navigate('/dashboard');
    } catch (err) {
      setError(err.response?.data?.message || 'Registration failed');
    }
  };

  return (
    <div className="app-shell">
      <section className="page-card form-panel auth-grid">
        <div className="auth-intro">
          <span className="eyebrow">Kickstart your coding journey</span>
          <h1>Register for CodeCraft</h1>
          <p>No experience required. We guide beginners with encouraging lessons, friendly prompts, and real code practice.</p>
          <div className="assistant-note">Your instructor-ready assistant is here to cheer you on while you learn and build.</div>
        </div>

        <div className="auth-form-panel">
          {error && <div className="alert-error">{error}</div>}

          <form onSubmit={submit} className="space-y-5">
            <label className="form-field">
              <span>Name</span>
              <input
                value={form.name}
                onChange={(e) => setForm({ ...form, name: e.target.value })}
                placeholder="Your name"
                className="form-input"
                autoComplete="name"
              />
            </label>

            <label className="form-field">
              <span>Email</span>
              <input
                value={form.email}
                onChange={(e) => setForm({ ...form, email: e.target.value })}
                placeholder="you@example.com"
                className="form-input"
                autoComplete="email"
              />
            </label>

            <label className="form-field">
              <span>Password</span>
              <input
                type="password"
                value={form.password}
                onChange={(e) => setForm({ ...form, password: e.target.value })}
                placeholder="Create a password"
                className="form-input"
                autoComplete="new-password"
              />
            </label>

            <label className="form-field">
              <span>Confirm Password</span>
              <input
                type="password"
                value={form.password_confirmation}
                onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                placeholder="Repeat your password"
                className="form-input"
                autoComplete="new-password"
              />
            </label>

            <button type="submit" className="form-button">Register</button>
          </form>

          <p className="form-note">
            Already registered? <Link to="/login" className="link-accent">Login here</Link>.
          </p>
        </div>
      </section>
    </div>
  );
}
