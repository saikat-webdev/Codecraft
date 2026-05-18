import { Link } from 'react-router-dom';

export default function BrandLogo({ compact }) {
  return (
    <Link to="/" className={`brand ${compact ? 'brand-compact' : ''}`}>
      <span className="brand-mark" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="2" y="2" width="24" height="24" rx="12" fill="#4338CA" opacity="0.12" />
          <path d="M10.5 9.5L7.5 14L10.5 18.5" stroke="#4338CA" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          <path d="M17.5 9.5L20.5 14L17.5 18.5" stroke="#4338CA" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          <circle cx="14" cy="14" r="2" fill="#4338CA" />
        </svg>
      </span>
      <span className="brand-text">CodeCraft</span>
    </Link>
  );
}
