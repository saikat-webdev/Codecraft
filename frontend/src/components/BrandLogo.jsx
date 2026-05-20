import { Link } from 'react-router-dom';

export default function BrandLogo({ compact }) {
  return (
    <Link to="/" className={`brand ${compact ? 'brand-compact' : ''}`}>
      <span className="brand-mark" aria-hidden="true">
        <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="logoGradient" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stopColor="#38BDF8" />
              <stop offset="100%" stopColor="#8B5CF6" />
            </linearGradient>
          </defs>
          <rect x="2" y="2" width="30" height="30" rx="14" fill="url(#logoGradient)" />
          <path d="M12 11h5.5a5 5 0 0 1 0 10H12" stroke="#fff" strokeWidth="2.8" strokeLinecap="round" />
          <path d="M20.5 11.5L16 17.5L20.5 23.5" stroke="#fff" strokeWidth="2.8" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </span>
      <span className="brand-text">CodeCraft</span>
    </Link>
  );
}
