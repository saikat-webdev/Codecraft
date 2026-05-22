import { useEffect, useState } from 'react';
import { profileApi } from '../services/profile';

const avatarStyles = [
  { id: 'avataaars', name: 'Classic Avatars', description: 'Classic avatar style' },
  { id: 'notionists', name: 'Notionists', description: 'Minimalist style' },
  { id: 'pixels', name: 'Pixels', description: 'Pixel art style' },
  { id: 'fun-emoji', name: 'Fun Emoji', description: 'Emoji-style avatars' },
  { id: 'bottts', name: 'Robots', description: 'Robot avatars' },
  { id: 'lorelei', name: 'Lorelei', description: 'Artistic style' },
  { id: 'pixel-art', name: 'Pixel Art', description: 'Retro pixel art' },
  { id: 'open-peeps', name: '🦸 Hero Squad', description: 'Hand-drawn hero characters' },
  { id: 'micah', name: '🥷 Ninja Warrior', description: 'Illustration style warriors' },
  { id: 'identicon', name: '⚡ Power Icons', description: 'Geometric power symbols' },
  { id: 'notionists-neon', name: '🌟 Neon Stars', description: 'Glowing neon avatars' },
  { id: 'avataaars-neon', name: '💀 Dark Knight', description: 'Dark neon hero style' },
  { id: 'big-ears', name: '🎭 Cartoon Crew', description: 'Fun cartoon characters' },
  { id: 'big-ears-neon', name: '🔥 Fire Squad', description: 'Neon cartoon heroes' },
  { id: 'croodles', name: '👾 Pixel Monsters', description: 'Cute monster avatars' },
  { id: 'croodles-neutral', name: '🐉 Dragon Clan', description: 'Neutral monster style' },
  { id: 'rings', name: '⭕ Magic Rings', description: 'Mystical ring avatars' },
];

export default function AvatarStyleSelector({ userId, currentStyle, onSelect }) {
  const [selectedStyle, setSelectedStyle] = useState(currentStyle || 'avataaars');
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

  const getAvatarUrl = (styleId) => {
    const seed = encodeURIComponent(`${userId || 'user'}-${Date.now()}`);
    const bgColor = encodeURIComponent('b6e3f4,c0aede,d1d4f9');
    return `https://api.dicebear.com/7.x/${styleId}/svg?seed=${seed}&backgroundColor=${bgColor}`;
  };

  return (
    <div className="avatar-style-selector">
      <h3 style={{ marginBottom: '1rem', fontSize: '1rem', fontWeight: '600' }}>
        Choose Avatar Style
      </h3>
      <div className="avatar-styles-grid">
        {avatarStyles.map((style) => (
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