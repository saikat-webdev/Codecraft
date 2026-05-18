import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../services/api';

export default function LessonPage() {
  const { slug } = useParams();
  const [lesson, setLesson] = useState(null);

  useEffect(() => {
    api.get(`/lessons/${slug}`).then((res) => setLesson(res.data.data)).catch(() => {});
  }, [slug]);

  if (!lesson) return <div className="app-shell">Loading...</div>;

  return (
    <div className="app-shell">
      <article className="page-card prose max-w-none lesson-detail-card">
        <div className="page-header">
          <div>
            <span className="eyebrow">Lesson Detail</span>
            <h1>{lesson.title}</h1>
            <p className="lesson-description">Learn with clear code examples, friendly instructor guidance, and instant next steps.</p>
          </div>
        </div>
        <div className="lesson-content" dangerouslySetInnerHTML={{ __html: lesson.content }} />
      </article>
    </div>
  );
}
