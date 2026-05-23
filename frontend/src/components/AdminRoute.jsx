import { useContext } from 'react';
import { Link, Navigate } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';

export default function AdminRoute({ children }) {
  const { user, loading } = useContext(AuthContext);

  if (loading) return <div className="p-8">Loading...</div>;
  if (!user) return <Navigate to="/login" replace />;

  if (!user.is_admin) {
    return (
      <div className="app-shell">
        <div className="page-card">
          <h2>Access denied</h2>
          <p>Admin access is required for this page.</p>
          <Link to="/dashboard" className="button-primary" style={{ marginTop: '1rem', display: 'inline-block' }}>
            Back to dashboard
          </Link>
        </div>
      </div>
    );
  }

  return children;
}
