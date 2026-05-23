import { useEffect, useRef } from 'react';

function normalizeCodeText(text) {
  if (!text) return '';
  return text
    .replace(/\\n/g, '\n')
    .replace(/\\t/g, '\t')
    .replace(/\\r/g, '\r');
}

export default function LessonContent({ html }) {
  const containerRef = useRef(null);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    container.querySelectorAll('pre').forEach((pre, index) => {
      if (pre.dataset.enhanced === 'true') return;

      const codeEl = pre.querySelector('code') ?? pre;
      const normalized = normalizeCodeText(codeEl.textContent || '');
      codeEl.textContent = normalized;

      const wrapper = document.createElement('div');
      wrapper.className = 'code-block-wrapper';
      pre.parentNode?.insertBefore(wrapper, pre);
      wrapper.appendChild(pre);

      const toolbar = document.createElement('div');
      toolbar.className = 'code-block-toolbar';

      const label = document.createElement('span');
      label.className = 'code-block-label';
      label.textContent = 'Example';

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'code-copy-btn';
      btn.textContent = 'Copy';
      btn.addEventListener('click', async () => {
        try {
          await navigator.clipboard.writeText(normalized);
          btn.textContent = 'Copied!';
          setTimeout(() => {
            btn.textContent = 'Copy';
          }, 2000);
        } catch {
          btn.textContent = 'Failed';
        }
      });

      toolbar.appendChild(label);
      toolbar.appendChild(btn);
      wrapper.insertBefore(toolbar, pre);

      pre.dataset.enhanced = 'true';
      pre.dataset.blockIndex = String(index);
    });
  }, [html]);

  if (!html) return null;

  return (
    <div
      ref={containerRef}
      className="lesson-content"
      dangerouslySetInnerHTML={{ __html: html }}
    />
  );
}
