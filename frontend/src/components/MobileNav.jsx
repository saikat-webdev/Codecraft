import { useState } from 'react';
import { Link } from 'react-router-dom';
import ProfileMenu from './ProfileMenu';

const NAV_LINKS = [
  { to: '/modules', label: 'Roadmap' },
  { to: '/lessons', label: 'Lessons' },
  { to: '/playground', label: 'Playground' },
  { to: '/exams', label: 'Exams' },
  { to: '/dashboard', label: 'Dashboard' },
];

export default function MobileNav({ theme, onToggleTheme, ThemeIcon }) {
  const [open, setOpen] = useState(false);

  return (
    <>
      <button
        type="button"
        className="mobile-nav-toggle"
        aria-expanded={open}
        aria-label={open ? 'Close menu' : 'Open menu'}
        onClick={() => setOpen((v) => !v)}
      >
        <span className={`hamburger ${open ? 'open' : ''}`} aria-hidden="true" />
      </button>

      {open && (
        <div className="mobile-nav-overlay" onClick={() => setOpen(false)} aria-hidden="true" />
      )}

      <nav className={`mobile-nav-drawer ${open ? 'open' : ''}`} aria-hidden={!open}>
        {NAV_LINKS.map((link) => (
          <Link key={link.to} to={link.to} onClick={() => setOpen(false)}>
            {link.label}
          </Link>
        ))}
        <div className="mobile-nav-footer">
          <ProfileMenu />
          <button
            type="button"
            onClick={onToggleTheme}
            className="theme-toggle"
            aria-label={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
          >
            <ThemeIcon theme={theme} />
          </button>
        </div>
      </nav>
    </>
  );
}
