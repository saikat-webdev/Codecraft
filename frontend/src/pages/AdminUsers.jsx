import { useContext, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';
import { adminApi } from '../services/admin';

export default function AdminUsers() {
  const { user } = useContext(AuthContext);
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('all');
  const [selectedUser, setSelectedUser] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    if (user?.is_admin) {
      loadUsers();
    }
  }, [user, filter]);

  const loadUsers = async () => {
    setLoading(true);
    try {
      const params = { search, status: filter };
      const res = await adminApi.getUsers(params);
      // Handle both paginated and non-paginated responses
      const data = res.data.data;
      if (Array.isArray(data)) {
        setUsers(data);
      } else if (data && Array.isArray(data.data)) {
        setUsers(data.data);
      } else {
        setUsers([]);
      }
    } catch (err) {
      console.error('Failed to load users:', err);
      setUsers([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const timer = setTimeout(() => loadUsers(), 300);
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

  const handleSuspend = async (userId) => {
    if (!confirm('Are you sure you want to suspend this user?')) return;
    try {
      await adminApi.suspendUser(userId);
      setMessage('User suspended successfully.');
      loadUsers();
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to suspend user.');
    }
  };

  const handleActivate = async (userId) => {
    try {
      await adminApi.activateUser(userId);
      setMessage('User activated successfully.');
      loadUsers();
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to activate user.');
    }
  };

  const handleAssignRole = async (userId, role) => {
    try {
      await adminApi.assignRole(userId, { role });
      setMessage(`User role updated to ${role}.`);
      loadUsers();
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to update role.');
    }
  };

  const handleViewUser = async (userId) => {
    try {
      const res = await adminApi.getUser(userId);
      setSelectedUser(res.data.data);
      setShowModal(true);
    } catch (err) {
      setError('Failed to load user details.');
    }
  };

  return (
    <div className="app-shell admin-users-page">
      <div className="page-header">
        <div>
          <span className="eyebrow">Admin</span>
          <h1>User Management</h1>
          <p>View, search, and manage all registered users.</p>
        </div>
        <Link to="/admin" className="button-secondary">
          Back to Dashboard
        </Link>
      </div>

      {message && <div className="alert-success">{message}</div>}
      {error && <div className="alert-error">{error}</div>}

      <section className="page-card fade-up">
        <div className="admin-filters">
          <div className="search-box">
            <input
              type="text"
              className="form-input"
              placeholder="Search by name or email..."
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
              className={`filter-btn ${filter === 'active' ? 'active' : ''}`}
              onClick={() => setFilter('active')}
            >
              Active
            </button>
            <button
              className={`filter-btn ${filter === 'suspended' ? 'active' : ''}`}
              onClick={() => setFilter('suspended')}
            >
              Suspended
            </button>
            <button
              className={`filter-btn ${filter === 'admin' ? 'active' : ''}`}
              onClick={() => setFilter('admin')}
            >
              Admins
            </button>
          </div>
        </div>

        {loading ? (
          <p className="text-muted">Loading users...</p>
        ) : (
          <div className="admin-table-wrapper">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Level</th>
                  <th>XP</th>
                  <th>Status</th>
                  <th>Role</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {users.map((u) => (
                  <tr key={u.id}>
                    <td>
                      <div className="user-cell">
                        {u.avatar_url ? (
                          <img src={u.avatar_url} alt="" className="user-avatar" />
                        ) : (
                          <span className="user-avatar fallback">{u.name?.[0]}</span>
                        )}
                        <div>
                          <strong>{u.name}</strong>
                          <button
                            className="view-details-btn"
                            onClick={() => handleViewUser(u.id)}
                          >
                            View
                          </button>
                        </div>
                      </div>
                    </td>
                    <td>{u.email}</td>
                    <td>
                      <span className="status-pill">Lv.{u.level || 1}</span>
                    </td>
                    <td>{u.xp?.toLocaleString() || 0}</td>
                    <td>
                      <span className={`status-pill ${u.is_suspended ? 'pill-warning' : 'pill-success'}`}>
                        {u.is_suspended ? 'Suspended' : 'Active'}
                      </span>
                    </td>
                    <td>
                      <span className={`status-pill ${u.is_admin ? 'pill-success' : 'pill-secondary'}`}>
                        {u.is_admin ? 'Admin' : 'User'}
                      </span>
                    </td>
                    <td>{new Date(u.created_at).toLocaleDateString()}</td>
                    <td>
                      <div className="action-buttons">
                        {u.is_suspended ? (
                          <button
                            className="button-secondary small-btn"
                            onClick={() => handleActivate(u.id)}
                          >
                            Activate
                          </button>
                        ) : (
                          <button
                            className="button-secondary small-btn danger-btn"
                            onClick={() => handleSuspend(u.id)}
                            disabled={u.is_admin}
                          >
                            Suspend
                          </button>
                        )}
                        {!u.is_admin && (
                          <button
                            className="button-secondary small-btn"
                            onClick={() => handleAssignRole(u.id, 'admin')}
                          >
                            Make Admin
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </section>

      {showModal && selectedUser && (
        <div className="modal-overlay" onClick={() => setShowModal(false)}>
          <div className="modal-content page-card" onClick={(e) => e.stopPropagation()}>
            <button className="modal-close" onClick={() => setShowModal(false)}>×</button>
            <h2>User Details</h2>
            <div className="user-detail-grid">
              <div className="user-detail-avatar">
                {selectedUser.avatar_url ? (
                  <img src={selectedUser.avatar_url} alt="" className="large-avatar" />
                ) : (
                  <span className="large-avatar fallback">{selectedUser.name?.[0]}</span>
                )}
                <h3>{selectedUser.name}</h3>
                <p>{selectedUser.email}</p>
              </div>
              <div className="user-detail-stats">
                <div className="detail-stat">
                  <strong>Level</strong>
                  <span>{selectedUser.level || 1}</span>
                </div>
                <div className="detail-stat">
                  <strong>XP</strong>
                  <span>{selectedUser.xp?.toLocaleString() || 0}</span>
                </div>
                <div className="detail-stat">
                  <strong>Streak</strong>
                  <span>🔥 {selectedUser.streak_count || 0} days</span>
                </div>
                <div className="detail-stat">
                  <strong>Best Streak</strong>
                  <span>{selectedUser.longest_streak || 0} days</span>
                </div>
                <div className="detail-stat">
                  <strong>Skill Level</strong>
                  <span>{selectedUser.skill_level || 'Beginner'}</span>
                </div>
                <div className="detail-stat">
                  <strong>Preferred Language</strong>
                  <span>{selectedUser.preferred_language || 'Not set'}</span>
                </div>
                <div className="detail-stat">
                  <strong>Daily Goal</strong>
                  <span>{selectedUser.daily_learning_time ? `${selectedUser.daily_learning_time} min` : 'Not set'}</span>
                </div>
                <div className="detail-stat">
                  <strong>Learning Goal</strong>
                  <span>{selectedUser.learning_goal || 'Not set'}</span>
                </div>
              </div>
              {selectedUser.bio && (
                <div className="user-detail-bio">
                  <strong>Bio</strong>
                  <p>{selectedUser.bio}</p>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}