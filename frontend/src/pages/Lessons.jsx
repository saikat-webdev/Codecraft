import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';

export default function Lessons() {
  const [lessons, setLessons] = useState([]);

  useEffect(() => {
    api.get('/lessons').then((res) => {
      setLessons(res.data.data || []);
    }).catch(() => {});
  }, []);

  return (
    <div className="app-shell">
      <section className="page-card space-y-8 lesson-library-card">
        <div className="page-header">
          <div>
            <span className="eyebrow">Course Library</span>
            <h1>Explore beginner lessons</h1>
            <p>Find easy-to-follow coding lessons crafted like bite-sized lab experiments.</p>
          </div>
        </div>

        {lessons.length === 0 ? (
          <div className="lesson-card">No lessons are available at the moment. Please check back later.</div>
        ) : (
          <div className="lesson-list">
            {lessons.map((l) => (
              <article key={l.id} className="lesson-card lesson-card-grid">
                <div>
                  <h3>{l.title}</h3>
                  <p>{l.description}</p>
                </div>
                <Link to={`/lessons/${l.slug}`} className="button-secondary">Open lesson</Link>
              </article>
            ))}
          </div>
        )}
      </section>
    </div>
  );
}
