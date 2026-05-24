import { useEffect, useState } from 'react';
import { profileApi } from '../services/profile';

const fallbackStyles = [
  { id: 'avataaars', name: 'Legendary Hero', description: 'Epic comic-style portrait', inspirations: ['legendary-team', 'comic-epic'] },
  { id: 'adventurer', name: 'Classic Champion', description: 'Heroic cartoon portrait', inspirations: ['team-leader', 'classic-hero'] },
  { id: 'big-ears', name: 'Masked Defender', description: 'Friendly vigilante with bold style', inspirations: ['dark-knight-inspired', 'masked-vigilante'] },
  { id: 'bottts', name: 'Tech Guardian', description: 'Futuristic robot hero look', inspirations: ['cyber-warrior', 'tech-guardian'] },
  { id: 'croodles', name: 'Alien Protector', description: 'Colorful fantasy character style', inspirations: ['cartoon-squad', 'playful-crew'] },
  { id: 'pixel-art', name: 'Retro Avenger', description: 'Pixel-powered action avatar', inspirations: ['retro-arcade', 'pixel-vigilante'] },
  { id: 'open-peeps', name: 'Team Hero', description: 'Hand-drawn squad member look', inspirations: ['ensemble', 'group-heroes'] },
];

export default function AvatarStyleSelector({ userId, currentStyle, onSelect }) {
  const [selectedStyle, setSelectedStyle] = useState(currentStyle || 'avataaars');
  const [styles, setStyles] = useState(fallbackStyles);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');

  const handleSelect = async (styleId) => {
    setLoading(true);
    setMessage('');
    setSelectedStyle(styleId);

    try {
      await profileApi.setAvatarStyle(styleId);
      setMessage('Avatar style updated!');
      if (onSelect) onSelect(styleId);
    } catch (err) {
      setMessage('Failed to update avatar style.');
    } finally {
      setLoading(false);
      setTimeout(() => setMessage(''), 3000);
    }
  };

  useEffect(() => {
    if (!currentStyle) {
      return;
    }

    setSelectedStyle(currentStyle);
  }, [currentStyle]);

  useEffect(() => {
    let canceled = false;

    const loadStyles = async () => {
      try {
        const response = await profileApi.getAvatarStyles();
        if (!canceled && response?.data?.data?.styles) {
          setStyles(response.data.data.styles);
        }
      } catch (error) {
        // Keep fallback styles when API call fails.
      }
    };

    loadStyles();

    return () => {
      canceled = true;
    };
  }, []);

  const getAvatarUrl = (styleId) => {
    const seed = encodeURIComponent(`${userId || 'user'}-${styleId}`);
    const bgColor = encodeURIComponent('b6e3f4,c0aede,d1d4f9');
    return `https://api.dicebear.com/7.x/${styleId}/svg?seed=${seed}&backgroundColor=${bgColor}`;
  };

  return (
    <div className="avatar-style-selector">
      <h3 style={{ marginBottom: '1rem', fontSize: '1rem', fontWeight: '600' }}>
        Choose Avatar Style
      </h3>
      <div className="avatar-styles-grid">
        {styles.map((style) => (
          <button
            key={style.id}
            className={`avatar-style-option ${selectedStyle === style.id ? 'selected' : ''}`}
            onClick={() => handleSelect(style.id)}
            disabled={loading}
            type="button"
          >
            <div className="avatar-style-preview">
              <img
                src={getAvatarUrl(style.id)}
                alt={style.name}
                className="avatar-preview-img"
                loading="lazy"
              />
            </div>
            <div className="avatar-style-info">
              <strong>{style.name}</strong>
              <span className="avatar-style-desc">{style.description}</span>
              {style.inspirations && style.inspirations.length > 0 && (
                <small style={{ display: 'block', marginTop: '0.35rem', color: 'var(--muted)' }}>
                  {style.inspirations.map((s, i) => (
                    <span key={s}>{s}{i < style.inspirations.length - 1 ? ', ' : ''}</span>
                  ))}
                </small>
              )}
            </div>
            {selectedStyle === style.id && (
              <span className="avatar-selected-badge">✓</span>
            )}
          </button>
        ))}
      </div>
      {loading && <p className="text-muted" style={{ marginTop: '0.5rem' }}>Updating...</p>}
      {message && (
        <p className={`text-${message.includes('Failed') ? 'error' : 'success'}`} style={{ marginTop: '0.5rem', fontSize: '0.85rem' }}>
          {message}
        </p>
      )}
    </div>
  );
}