import { useContext, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { adminApi } from '../services/admin';

export default function AdminDashboard() {
  const { user } = useContext(AuthContext);
  const [stats, setStats] = useState(null);
  const [activity, setActivity] = useState([]);
  const [leaderboard, setLeaderboard] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user?.is_admin) {
      loadDashboard();
    }
  }, [user]);

  const loadDashboard = async () => {
    setLoading(true);
    try {
      const [statsRes, activityRes, leaderboardRes] = await Promise.all([
        adminApi.getDashboardStats(),
        adminApi.getRecentActivity(),
        adminApi.getLeaderboard(),
      ]);
      setStats(statsRes.data.data);
      setActivity(activityRes.data.data || []);
      setLeaderboard(leaderboardRes.data.data || []);
    } catch (err) {
      console.error('Failed to load dashboard:', err);
    } finally {
      setLoading(false);
    }
  };

  if (!user?.is_admin) {
    return (
      <div className="app-shell">
        <div className="page-card">
          <h2>Access Denied</h2>
          <p>Admin access required to view this page.</p>
          <Link to="/" className="button-primary" style={{ marginTop: '1rem', display: 'inline-block' }}>
            Go Home
          </Link>
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="app-shell">
        <div className="page-card">
          <p>Loading dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="app-shell admin-dashboard">
      <div className="page-header">
        <div>
          <span className="eyebrow">Admin Panel</span>
          <h1>Dashboard</h1>
          <p>Overview of your learning platform performance.</p>
        </div>
      </div>

      {stats && (
        <>
          <div className="admin-stats-grid">
            <article className="stat-card metric-card">
              <h3>Total Users</h3>
              <p>{stats.total_users?.toLocaleString() || 0}</p>
              <span className="status-pill">+{stats.new_users_this_week || 0} this week</span>
            </article>
            <article className="stat-card metric-card">
              <h3>Active Users</h3>
              <p>{stats.active_users?.toLocaleString() || 0}</p>
              <span className="status-pill pill-success">{stats.active_percentage || 0}% active</span>
            </article>
            <article className="stat-card metric-card">
              <h3>Total Executions</h3>
              <p>{stats.total_executions?.toLocaleString() || 0}</p>
              <span className="status-pill">Code runs</span>
            </article>
            <article className="stat-card metric-card">
              <h3>Courses</h3>
              <p>{stats.total_courses || 0}</p>
              <span className="status-pill pill-secondary">{stats.published_courses || 0} published</span>
            </article>
            <article className="stat-card metric-card">
              <h3>Challenges</h3>
              <p>{stats.total_challenges || 0}</p>
              <span className="status-pill">Total exercises</span>
            </article>
            <article className="stat-card metric-card">
              <h3>Sudden Tests</h3>
              <p>{stats.sudden_test_attempts || 0}</p>
              <span className="status-pill pill-warning">This week</span>
            </article>
          </div>

          <div className="admin-content-grid">
            <section className="page-card fade-up">
              <h2>Recent Activity</h2>
              <div className="activity-list">
                {activity.length === 0 ? (
                  <p className="text-muted">No recent activity</p>
                ) : (
                  activity.slice(0, 8).map((item, idx) => (
                    <div key={idx} className="activity-item">
                      <span className="activity-icon">{item.icon || '•'}</span>
                      <div className="activity-content">
                        <p>{item.message}</p>
                        <span className="activity-time">{item.time_ago}</span>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </section>

            <section className="page-card fade-up delay-1">
              <h2>Top Learners</h2>
              <div className="leaderboard-list">
                {leaderboard.length === 0 ? (
                  <p className="text-muted">No leaderboard data</p>
                ) : (
                  leaderboard.slice(0, 10).map((learner, idx) => (
                    <div key={learner.id} className="leaderboard-item">
                      <span className="leaderboard-rank">#{idx + 1}</span>
                      <div className="leaderboard-user">
                        {learner.avatar_url ? (
                          <img src={learner.avatar_url} alt="" className="leaderboard-avatar" />
                        ) : (
                          <span className="leaderboard-avatar fallback">{learner.name?.[0]}</span>
                        )}
                        <span>{learner.name}</span>
                      </div>
                      <span className="leaderboard-xp">{learner.xp?.toLocaleString() || 0} XP</span>
                      <span className="status-pill">Lv.{learner.level || 1}</span>
                    </div>
                  ))
                )}
              </div>
            </section>
          </div>

          <div className="admin-quick-actions">
            <h2>Quick Actions</h2>
            <div className="quick-actions-grid">
              <Link to="/admin/users" className="quick-action-card">
                <span className="quick-action-icon">👥</span>
                <strong>Manage Users</strong>
                <p>View and manage all users</p>
              </Link>
              <Link to="/admin/sudden-tests" className="quick-action-card">
                <span className="quick-action-icon">⚡</span>
                <strong>Sudden Tests</strong>
                <p>Configure pop-up quizzes</p>
              </Link>
              <Link to="/admin/settings" className="quick-action-card">
                <span className="quick-action-icon">⚙️</span>
                <strong>Settings</strong>
                <p>Platform configuration</p>
              </Link>
              <Link to="/admin/activity" className="quick-action-card">
                <span className="quick-action-icon">📊</span>
                <strong>Activity Logs</strong>
                <p>View system activity</p>
              </Link>
            </div>
          </div>
        </>
      )}
    </div>
  );
}