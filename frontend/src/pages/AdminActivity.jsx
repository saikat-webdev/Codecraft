import { useContext, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { adminApi } from '../services/admin';

export default function AdminActivity() {
  const { user } = useContext(AuthContext);
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');

  useEffect(() => {
    if (user?.is_admin) {
      loadLogs();
    }
  }, [user, filter]);

  const loadLogs = async () => {
    setLoading(true);
    try {
      const params = { type: filter, search };
      const res = await adminApi.getActivityLogs(params);
      setLogs(res.data.data || []);
    } catch (err) {
      console.error('Failed to load activity logs:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const timer = setTimeout(() => loadLogs(), 300);
    return () => clearTimeout(timer);
  }, [search]);

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

  const getActivityIcon = (type) => {
    const icons = {
      user_login: '🔑',
      user_register: '👤',
      code_execute: '💻',
      lesson_complete: '📖',
      exercise_submit: '📝',
      achievement_earned: '🏆',
      sudden_test: '⚡',
      admin_action: '⚙️',
      user_suspend: '🚫',
      user_activate: '✅',
    };
    return icons[type] || '•';
  };

  const getActivityColor = (type) => {
    const colors = {
      user_login: 'info',
      user_register: 'success',
      code_execute: 'primary',
      lesson_complete: 'success',
      exercise_submit: 'info',
      achievement_earned: 'warning',
      sudden_test: 'warning',
      admin_action: 'secondary',
      user_suspend: 'danger',
      user_activate: 'success',
    };
    return colors[type] || 'secondary';
  };

  return (
    <div className="app-shell admin-activity-page">
      <div className="page-header">
        <div>
          <span className="eyebrow">Admin</span>
          <h1>Activity Logs</h1>
          <p>Monitor system activity and user actions.</p>
        </div>
        <Link to="/admin" className="button-secondary">
          Back to Dashboard
        </Link>
      </div>

      <section className="page-card fade-up">
        <div className="admin-filters">
          <div className="search-box">
            <input
              type="text"
              className="form-input"
              placeholder="Search activities..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <div className="filter-buttons">
            <button
              className={`filter-btn ${filter === 'all' ? 'active' : ''}`}
              onClick={() => setFilter('all')}
            >
              All
            </button>
            <button
              className={`filter-btn ${filter === 'user' ? 'active' : ''}`}
              onClick={() => setFilter('user')}
            >
              User Actions
            </button>
            <button
              className={`filter-btn ${filter === 'code' ? 'active' : ''}`}
              onClick={() => setFilter('code')}
            >
              Code Execution
            </button>
            <button
              className={`filter-btn ${filter === 'system' ? 'active' : ''}`}
              onClick={() => setFilter('system')}
            >
              System
            </button>
          </div>
        </div>

        {loading ? (
          <p className="text-muted">Loading activity logs...</p>
        ) : (
          <div className="activity-log-list">
            {logs.length === 0 ? (
              <p className="text-muted">No activity found.</p>
            ) : (
              logs.map((log) => (
                <div key={log.id} className="activity-log-item">
                  <span className={`activity-log-icon ${getActivityColor(log.type)}`}>
                    {getActivityIcon(log.type)}
                  </span>
                  <div className="activity-log-content">
                    <div className="activity-log-header">
                      <strong>{log.description}</strong>
                      <span className="activity-log-time">{log.time_ago}</span>
                    </div>
                    <div className="activity-log-meta">
                      {log.user && (
                        <span className="activity-log-user">
                          {log.user.avatar_url ? (
                            <img src={log.user.avatar_url} alt="" className="tiny-avatar" />
                          ) : (
                            <span className="tiny-avatar fallback">{log.user.name?.[0]}</span>
                          )}
                          {log.user.name}
                        </span>
                      )}
                      <span className="activity-log-ip">IP: {log.ip_address || 'N/A'}</span>
                      <span className="activity-log-type">{log.type}</span>
                    </div>
                    {log.details && (
                      <div className="activity-log-details">
                        <pre>{JSON.stringify(log.details, null, 2)}</pre>
                      </div>
                    )}
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </section>
    </div>
  );
}