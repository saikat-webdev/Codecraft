import CodingPlayground from '../components/CodingPlayground';

/**
 * Standalone playground page — replaces the former AI instructor section.
 * Public route so learners can experiment without opening a lesson first.
 */
export default function Playground() {
  return (
    <div className="app-shell playground-page">
      <section className="page-card playground-page-card fade-up">
        <div className="page-header playground-page-header">
          <div>
            <span className="eyebrow">Code lab</span>
            <h1>Coding Playground</h1>
            <p>
              Write code in Python, JavaScript, Java, C, C++, or React-style JS.
              Runs through our free execution service (public Judge0 CE with optional local fallback).
            </p>
          </div>
        </div>

        <CodingPlayground showExerciseActions={false} />
      </section>
    </div>
  );
}
