import { useContext, useEffect, useState } from 'react';
import { AuthContext } from '../context/AuthProvider';
import { profileApi } from '../services/profile';
import AvatarStyleSelector from '../components/AvatarStyleSelector';

export default function Profile() {
  const { user, refreshUser, updateUser } = useContext(AuthContext);
  const [form, setForm] = useState({
    name: '',
    bio: '',
    learning_goal: '',
    preferred_language: '',
    daily_learning_time: '',
    skill_level: '',
  });
  const [stats, setStats] = useState(null);
  const [achievements, setAchievements] = useState([]);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    if (!user) return;
    setForm({
      name: user.name || '',
      bio: user.bio || '',
      learning_goal: user.learning_goal || '',
      preferred_language: user.preferred_language || '',
      daily_learning_time: user.daily_learning_time ?? '',
      skill_level: user.skill_level || 'Beginner',
    });
  }, [user]);

  useEffect(() => {
    profileApi.getStats().then((res) => setStats(res.data.data)).catch(() => {});
    profileApi.getAchievements().then((res) => setAchievements(res.data.data || [])).catch(() => {});
  }, []);

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError('');
    setMessage('');
    try {
      const res = await profileApi.updateProfile({
        ...form,
        daily_learning_time: form.daily_learning_time
          ? Number(form.daily_learning_time)
          : null,
      });
      updateUser(res.data.data);
      setMessage('Profile saved.');
    } catch (err) {
      setError(err.response?.data?.message || 'Could not save profile.');
    } finally {
      setSaving(false);
    }
  };

  const handleAvatar = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setError('');
    try {
      const res = await profileApi.uploadAvatar(file);
      updateUser(res.data.data);
      await refreshUser();
      setMessage('Avatar updated.');
    } catch (err) {
      setError(err.response?.data?.message || 'Avatar upload failed.');
    }
  };

  if (!user) {
    return (
      <div className="app-shell">
        <div className="page-card">Please log in to view your profile.</div>
      </div>
    );
  }

  const earned = achievements.filter((a) => a.earned);

  return (
    <div className="app-shell profile-page">
      <section className="page-card profile-hero-card fade-up">
        <div className="profile-hero">
          <label className="profile-avatar-upload">
            {user.avatar_url ? (
              <img src={user.avatar_url} alt="" className="profile-hero-avatar" />
            ) : (
              <span className="profile-hero-avatar fallback">{user.name?.[0]}</span>
            )}
            <input type="file" accept="image/*" hidden onChange={handleAvatar} />
            <span className="avatar-upload-hint">Change photo</span>
          </label>
          <div>
            <h1>{user.name}</h1>
            <p className="profile-email">{user.email}</p>
            <div className="profile-xp-row">
              <span className="profile-level-pill">Level {user.level ?? 1}</span>
              <span>{user.xp ?? 0} XP</span>
              <span>🔥 {user.streak_count ?? 0} day streak</span>
            </div>
            <div className="progress-bar profile-xp-bar">
              <div
                className="progress-fill"
                style={{
                  width: `${Math.min(
                    100,
                    Math.max(
                      8,
                      100 - ((user.xp_to_next_level ?? 50) / ((user.xp ?? 0) + (user.xp_to_next_level ?? 50) || 1)) * 100
                    )
                  )}%`,
                }}
              />
            </div>
            <p className="profile-xp-hint">{user.xp_to_next_level ?? 0} XP to next level</p>
          </div>
        </div>
      </section>

      <div className="profile-grid">
        <section className="page-card fade-up delay-1">
          <h2>Edit profile</h2>
          {message && <div className="alert-success">{message}</div>}
          {error && <div className="alert-error">{error}</div>}
          <form onSubmit={handleSave} className="profile-form space-y-5">
            <label className="form-field">
              <span>Name</span>
              <input className="form-input" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
            </label>
            <label className="form-field">
              <span>Bio</span>
              <textarea className="form-input" rows={3} value={form.bio} onChange={(e) => setForm({ ...form, bio: e.target.value })} />
            </label>
            <label className="form-field">
              <span>Learning goal</span>
              <input className="form-input" value={form.learning_goal} onChange={(e) => setForm({ ...form, learning_goal: e.target.value })} />
            </label>
            <label className="form-field">
              <span>Preferred language</span>
              <input className="form-input" value={form.preferred_language} onChange={(e) => setForm({ ...form, preferred_language: e.target.value })} />
            </label>
            <label className="form-field">
              <span>Daily minutes</span>
              <input type="number" className="form-input" value={form.daily_learning_time} onChange={(e) => setForm({ ...form, daily_learning_time: e.target.value })} />
            </label>
            <label className="form-field">
              <span>Skill level</span>
              <select className="form-input language-select" value={form.skill_level} onChange={(e) => setForm({ ...form, skill_level: e.target.value })}>
                <option>Beginner</option>
                <option>Intermediate</option>
                <option>Advanced</option>
              </select>
            </label>
            <button type="submit" className="form-button" disabled={saving}>
              {saving ? 'Saving…' : 'Save changes'}
            </button>
          </form>
        </section>

        <section className="page-card fade-up delay-2">
          <h2>Coding stats</h2>
          {stats ? (
            <div className="profile-stats-grid">
              <article className="stat-card">
                <span className="stat-number">{stats.lessons_completed}</span>
                <p>Lessons done</p>
              </article>
              <article className="stat-card">
                <span className="stat-number">{stats.exercises_passed}</span>
                <p>Exercises passed</p>
              </article>
              <article className="stat-card">
                <span className="stat-number">{stats.sudden_tests_passed}</span>
                <p>Sudden tests passed</p>
              </article>
              <article className="stat-card">
                <span className="stat-number">{stats.longest_streak ?? user.longest_streak}</span>
                <p>Best streak</p>
              </article>
              {stats.sudden_test_stats && (
                <article className="stat-card wide">
                  <p>
                    Sudden tests: {stats.sudden_test_stats.passed}/{stats.sudden_test_stats.total_attempts} passed ·
                    avg {stats.sudden_test_stats.average_score}% · difficulty {stats.sudden_test_stats.current_difficulty}/5
                  </p>
                </article>
              )}
            </div>
          ) : (
            <p className="text-muted">Loading stats…</p>
          )}
        </section>
      </div>

      <section className="page-card fade-up delay-3">
        <h2>Avatar Style</h2>
        <AvatarStyleSelector
          userId={user.id}
          currentStyle={user.avatar_style || 'avataaars'}
          onSelect={() => refreshUser()}
        />
      </section>

      <section className="page-card fade-up delay-4">
        <h2>Achievement badges</h2>
        <p className="section-subtitle">{earned.length} of {achievements.length} unlocked</p>
        <div className="badges-grid">
          {achievements.map((badge) => (
            <article
              key={badge.slug}
              className={`badge-card ${badge.earned ? 'earned' : 'locked'}`}
              title={badge.description}
            >
              <span className="badge-icon">{badge.icon}</span>
              <strong>{badge.name}</strong>
              <p>{badge.description}</p>
              {badge.earned && <span className="status-pill pill-success">Unlocked</span>}
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
