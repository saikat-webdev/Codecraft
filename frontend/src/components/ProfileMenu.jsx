import { useContext, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { AuthContext } from '../context/AuthProvider';

function getInitials(name) {
  if (!name) return '?';
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
}

export default function ProfileMenu() {
  const { user, logout, loading } = useContext(AuthContext);
  const [open, setOpen] = useState(false);
  const menuRef = useRef(null);
  const navigate = useNavigate();

  useEffect(() => {
    const handleClick = (e) => {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  if (loading) return null;

  if (!user) {
    return (
      <>
        <Link to="/login">Login</Link>
        <Link to="/register" className="button-secondary nav-register-btn">Register</Link>
      </>
    );
  }

  const handleLogout = async () => {
    setOpen(false);
    await logout();
    navigate('/');
  };

  return (
    <div className="profile-menu-wrap" ref={menuRef}>
      <button
        type="button"
        className="profile-menu-trigger"
        onClick={() => setOpen((v) => !v)}
        aria-expanded={open}
        aria-haspopup="true"
      >
        {user.avatar_url ? (
          <img src={user.avatar_url} alt="" className="profile-avatar-img" />
        ) : (
          <span className="profile-avatar-fallback">{getInitials(user.name)}</span>
        )}
        <span className="profile-menu-name">{user.name}</span>
        <span className="profile-menu-chevron" aria-hidden="true">
          {open ? '▲' : '▼'}
        </span>
      </button>

      {open && (
        <div className="profile-dropdown" role="menu">
          <div className="profile-dropdown-header">
            <strong>{user.name}</strong>
            <span className="profile-dropdown-meta">
              Lv.{user.level ?? 1} · {user.xp ?? 0} XP · 🔥 {user.streak_count ?? 0}
            </span>
          </div>
          <Link to="/profile" className="profile-dropdown-item" onClick={() => setOpen(false)}>
            My profile
          </Link>
          <Link to="/dashboard" className="profile-dropdown-item" onClick={() => setOpen(false)}>
            Dashboard
          </Link>
          <Link to="/exams" className="profile-dropdown-item" onClick={() => setOpen(false)}>
            Practice exams
          </Link>
          {user.is_admin && (
            <>
              <Link
                to="/admin"
                className="profile-dropdown-item"
                onClick={() => setOpen(false)}
              >
                Admin Dashboard
              </Link>
              <Link
                to="/admin/sudden-tests"
                className="profile-dropdown-item"
                onClick={() => setOpen(false)}
              >
                Sudden Tests
              </Link>
            </>
          )}
          <button type="button" className="profile-dropdown-item danger" onClick={handleLogout}>
            Log out
          </button>
        </div>
      )}
    </div>
  );
}
