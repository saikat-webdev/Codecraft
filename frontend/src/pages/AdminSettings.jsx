import { useContext, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { adminApi } from '../services/admin';

export default function AdminSettings() {
  const { user } = useContext(AuthContext);
  const [settings, setSettings] = useState(null);
  const [judge0Settings, setJudge0Settings] = useState({
    base_url: '',
    api_key: '',
  });
  const [branding, setBranding] = useState({
    site_name: '',
    tagline: '',
    logo_url: '',
    favicon_url: '',
  });
  const [features, setFeatures] = useState({
    maintenance_mode: false,
    registration_enabled: true,
    sudden_tests_enabled: true,
    leaderboard_enabled: true,
    achievements_enabled: true,
  });
  const [avatarMode, setAvatarMode] = useState('default');
  const [avatarOptions, setAvatarOptions] = useState(['default', 'superb']);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (user?.is_admin) {
      loadSettings();
    }
  }, [user]);

  const loadSettings = async () => {
    setLoading(true);
    try {
      const res = await adminApi.getSettings();
      const data = res.data.data;
      setSettings(data);
      if (data.judge0) {
        setJudge0Settings(data.judge0);
      }
      if (data.branding) {
        setBranding(data.branding);
      }
      if (data.features) {
        setFeatures(data.features);
      }
      if (data.avatars) {
        setAvatarMode(data.avatars.mode || 'default');
        setAvatarOptions(data.avatars.available_modes || ['default', 'superb']);
      }
    } catch (err) {
      console.error('Failed to load settings:', err);
    } finally {
      setLoading(false);
    }
  };

  if (!user?.is_admin) {
    return (
      <div className="app-shell">
        <div className="page-card">
          <h2>Access Denied</h2>
          <p>Admin access required.</p>
          <Link to="/" className="button-primary" style={{ marginTop: '1rem', display: 'inline-block' }}>
            Go Home
          </Link>
        </div>
      </div>
    );
  }

  const handleSaveSettings = async () => {
    setSaving(true);
    setError('');
    setMessage('');
    try {
      await adminApi.updateSettings({
        branding,
        features,
        avatars: {
          mode: avatarMode,
        },
      });
      setMessage('Settings saved successfully.');
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to save settings.');
    } finally {
      setSaving(false);
    }
  };

  const handleSaveJudge0 = async () => {
    setSaving(true);
    setError('');
    setMessage('');
    try {
      await adminApi.updateJudge0Settings(judge0Settings);
      setMessage('Judge0 settings saved successfully.');
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to save Judge0 settings.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="app-shell">
        <div className="page-card">
          <p>Loading settings...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="app-shell admin-settings-page">
      <div className="page-header">
        <div>
          <span className="eyebrow">Admin</span>
          <h1>Platform Settings</h1>
          <p>Configure your learning platform settings.</p>
        </div>
        <Link to="/admin" className="button-secondary">
          Back to Dashboard
        </Link>
      </div>

      {message && <div className="alert-success">{message}</div>}
      {error && <div className="alert-error">{error}</div>}

      <div className="admin-settings-grid-page">
        <section className="page-card fade-up">
          <h2>Branding</h2>
          <div className="form-fields-vertical">
            <label className="form-field">
              <span>Site Name</span>
              <input
                type="text"
                className="form-input"
                value={branding.site_name}
                onChange={(e) => setBranding({ ...branding, site_name: e.target.value })}
              />
            </label>
            <label className="form-field">
              <span>Tagline</span>
              <input
                type="text"
                className="form-input"
                value={branding.tagline}
                onChange={(e) => setBranding({ ...branding, tagline: e.target.value })}
              />
            </label>
            <label className="form-field">
              <span>Logo URL</span>
              <input
                type="text"
                className="form-input"
                value={branding.logo_url}
                onChange={(e) => setBranding({ ...branding, logo_url: e.target.value })}
                placeholder="https://..."
              />
            </label>
            <label className="form-field">
              <span>Favicon URL</span>
              <input
                type="text"
                className="form-input"
                value={branding.favicon_url}
                onChange={(e) => setBranding({ ...branding, favicon_url: e.target.value })}
                placeholder="https://..."
              />
            </label>
          </div>
        </section>

        <section className="page-card fade-up delay-1">
          <h2>Feature Toggles</h2>
          <div className="feature-toggles">
            <div className="toggle-row">
              <div>
                <strong>Maintenance Mode</strong>
                <p className="text-muted">Disable access for non-admin users</p>
              </div>
              <button
                type="button"
                className={`toggle-switch ${features.maintenance_mode ? 'active' : ''}`}
                onClick={() => setFeatures({ ...features, maintenance_mode: !features.maintenance_mode })}
              >
                <span className="toggle-knob" />
              </button>
            </div>
            <div className="toggle-row">
              <div>
                <strong>Registration Enabled</strong>
                <p className="text-muted">Allow new user registrations</p>
              </div>
              <button
                type="button"
                className={`toggle-switch ${features.registration_enabled ? 'active' : ''}`}
                onClick={() => setFeatures({ ...features, registration_enabled: !features.registration_enabled })}
              >
                <span className="toggle-knob" />
              </button>
            </div>
            <div className="toggle-row">
              <div>
                <strong>Sudden Tests</strong>
                <p className="text-muted">Enable pop-up quizzes during lessons</p>
              </div>
              <button
                type="button"
                className={`toggle-switch ${features.sudden_tests_enabled ? 'active' : ''}`}
                onClick={() => setFeatures({ ...features, sudden_tests_enabled: !features.sudden_tests_enabled })}
              >
                <span className="toggle-knob" />
              </button>
            </div>
            <div className="toggle-row">
              <div>
                <strong>Leaderboard</strong>
                <p className="text-muted">Show user leaderboard</p>
              </div>
              <button
                type="button"
                className={`toggle-switch ${features.leaderboard_enabled ? 'active' : ''}`}
                onClick={() => setFeatures({ ...features, leaderboard_enabled: !features.leaderboard_enabled })}
              >
                <span className="toggle-knob" />
              </button>
            </div>
            <div className="toggle-row">
              <div>
                <strong>Achievements</strong>
                <p className="text-muted">Enable achievement badges</p>
              </div>
              <button
                type="button"
                className={`toggle-switch ${features.achievements_enabled ? 'active' : ''}`}
                onClick={() => setFeatures({ ...features, achievements_enabled: !features.achievements_enabled })}
              >
                <span className="toggle-knob" />
              </button>
            </div>
          </div>
        </section>

        <section className="page-card fade-up delay-2">
          <h2>Avatar Style Mode</h2>
          <p className="text-muted" style={{ marginBottom: '1.5rem' }}>
            Choose which avatar style set users may select from on their profile.
          </p>
          <label className="form-field">
            <span>Active avatar mode</span>
            <select
              className="form-input"
              value={avatarMode}
              onChange={(e) => setAvatarMode(e.target.value)}
            >
              {avatarOptions.map((mode) => (
                <option key={mode} value={mode}>
                  {mode === 'superb' ? 'Superb avatars' : 'Default avatars'}
                </option>
              ))}
            </select>
          </label>
        </section>

        <section className="page-card fade-up delay-3">
          <h2>Judge0 Configuration</h2>
          <p className="text-muted" style={{ marginBottom: '1.5rem' }}>
            Configure the code execution engine settings.
          </p>
          <div className="form-fields-vertical">
            <label className="form-field">
              <span>Judge0 Base URL</span>
              <input
                type="text"
                className="form-input"
                value={judge0Settings.base_url}
                onChange={(e) => setJudge0Settings({ ...judge0Settings, base_url: e.target.value })}
                placeholder="https://ce.judge0.com"
              />
            </label>
            <label className="form-field">
              <span>API Key (optional)</span>
              <input
                type="password"
                className="form-input"
                value={judge0Settings.api_key}
                onChange={(e) => setJudge0Settings({ ...judge0Settings, api_key: e.target.value })}
                placeholder="Leave empty for public API"
              />
            </label>
          </div>
          <button
            type="button"
            className="button-primary"
            onClick={handleSaveJudge0}
            disabled={saving}
          >
            {saving ? 'Saving...' : 'Save Judge0 Settings'}
          </button>
        </section>

        <div className="save-bar">
          <button
            type="button"
            className="button-primary"
            onClick={handleSaveSettings}
            disabled={saving}
          >
            {saving ? 'Saving...' : 'Save All Settings'}
          </button>
        </div>
      </div>
    </div>
  );
}