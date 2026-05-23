# CodeCraft — Development Instructions & Changelog

This document summarizes work completed on the CodeCraft platform (Laravel API + React frontend). Use it for local setup, testing, and review before pushing to GitHub.

---

## Quick start

### Prerequisites

- PHP 8.2+, Composer
- Node.js 18+, npm
- PostgreSQL (configured in `backend/.env`)
- Optional for local code fallback: Python, Node.js, Java JDK, GCC

### Run locally

**Terminal 1 — API**

```bash
cd backend
php artisan serve
```

**Terminal 2 — Frontend**

```bash
cd frontend
npm run dev
```

- Frontend: http://localhost:5173  
- API: http://localhost:8000/api  

### Database setup (fresh or after pull)

```bash
cd backend
php artisan migrate
php artisan db:seed
```

Or run individual seeders:

```bash
php artisan db:seed --class=FullCourseModulesSeeder
php artisan db:seed --class=FixLessonContentSeeder
php artisan db:seed --class=TrackCodingExerciseSeeder
php artisan db:seed --class=ExamSeeder
php artisan db:seed --class=AdminUserSeeder
```

### Test accounts

| Role  | Email                   | Password   |
|-------|-------------------------|------------|
| Admin | admin@codecraft.com     | admin123   |
| User  | user@codecraft.com      | user123    |

---

## What was built & fixed

### 1. Full course modules (all tracks)

| Track        | Modules | Focus |
|-------------|---------|--------|
| **Python**  | 9 modules (original roadmap) | Full beginner path — variables, loops, OOP, projects |
| **JavaScript** | 5 modules | Basics, functions, control flow, arrays, DOM concept, mini project |
| **Java**    | 3 modules | Hello World, control flow, methods |
| **C**       | 3 modules | Foundations, control flow, functions |
| **React**   | 2 modules | Components, JSX, props, state, lists |

**Seeders involved**

- `FullCourseModulesSeeder` — creates/updates modules and lessons per track  
- `FixLessonContentSeeder` — rewrites lesson HTML with proper multi-line code  
- `TrackCodingExerciseSeeder` — adds playground exercises for JS, Java, C, React lessons  
- `DatabaseSeeder` — Python roadmap + calls the seeders above  

**Roadmap UI:** `/modules` — filter by track (Python, JavaScript, Java, C, React).

---

### 2. Lesson code formatting fix

**Problem:** Code in lessons showed literal `\n` on one line instead of line breaks.

**Fixes (layered):**

1. **Frontend** — `LessonContent.jsx` normalizes `\n` / `\t` in `<pre><code>` and adds a **Copy** button per block.  
2. **Backend** — `Lesson` model accessor cleans legacy HTML when API returns content.  
3. **Seeders** — `LessonHtml` helper builds HTML with real newlines via `htmlspecialchars`.

---

### 3. Copy button on lesson code examples

Each code block in a lesson shows a toolbar with **Example** and **Copy**. Clicking Copy uses the clipboard API and briefly shows “Copied!”.

---

### 4. Lesson page coding playground

- Fixed **380px** sticky sidebar on desktop (same layout as the original design).  
- On tablet/mobile (≤1024px), playground stacks **below** the lesson (full width).

---

### 5. Mobile responsive design

- **Mobile nav** — hamburger drawer (`MobileNav.jsx`) with Roadmap, Lessons, Playground, Exams, Dashboard, profile, theme toggle.  
- **Lesson page** — single column on small screens; meta panel and actions wrap.  
- **Track filters, exams, dashboard metrics** — single-column grids on narrow screens.  
- **Header** — desktop links hidden below 1024px; mobile menu shown.

---

### 6. Practice exams module

- Routes: `/exams`, `/exams/:slug` (auth required to submit)  
- API: `GET /api/exams`, `GET /api/exams/{slug}`, `POST /api/exams/{slug}/submit`  
- Seeder: `ExamSeeder` — one exam per track  

---

### 7. Code playground (free execution)

- **Primary:** Public [Judge0 CE](https://ce.judge0.com) (no API key)  
- **Fallback:** `LocalCodeRunnerService` — Python, Node, Java, C if installed on the server  
- Languages in UI: Python, JavaScript, Java, C, C++, React (runs as JS)  

Config: `backend/config/services.php` — `JUDGE0_ENABLED`, `CODE_RUNNER_FALLBACK`.

---

### 8. Other bug fixes (from earlier pass)

- Roadmap no longer breaks when logged out (`fetchProgress` only if authenticated).  
- Admin Activity logs use `GET /api/admin/activity-logs`.  
- Unauthenticated API calls return **401 JSON** (not 500 redirect to missing login route).  
- Suspended users cannot log in.  
- Admin routes wrapped with `AdminRoute`.  
- Leaderboard returns `avatar_url`.  

---

## Key files added or changed

### Frontend

| File | Purpose |
|------|---------|
| `src/components/LessonContent.jsx` | Lesson HTML + code fix + copy |
| `src/components/ResizablePlayground.jsx` | Resizable lesson sidebar playground |
| `src/components/MobileNav.jsx` | Mobile navigation drawer |
| `src/components/AdminRoute.jsx` | Admin-only routes |
| `src/pages/Exams.jsx`, `ExamTake.jsx` | Exam list & take flow |
| `src/constants/playgroundLanguages.js` | Languages + track map |
| `src/index.css` | Code blocks, resize, mobile nav, responsive lesson layout |

### Backend

| File | Purpose |
|------|---------|
| `database/seeders/FullCourseModulesSeeder.php` | Full track modules |
| `database/seeders/FixLessonContentSeeder.php` | Proper lesson HTML |
| `database/seeders/TrackCodingExerciseSeeder.php` | Per-track exercises |
| `database/seeders/Support/LessonHtml.php` | HTML builder for seeders |
| `app/Models/Exam.php`, `ExamQuestion.php`, `ExamAttempt.php` | Exam domain |
| `app/Http/Controllers/Api/ExamController.php` | Exam API |
| `app/Services/LocalCodeRunnerService.php` | Local code execution fallback |
| `bootstrap/app.php` | API 401 JSON for guests |

---

## Testing checklist

Use this before you push to GitHub:

- [ ] `cd frontend && npm run build` — succeeds  
- [ ] Login as user and admin  
- [ ] Roadmap — switch all 5 tracks; modules load  
- [ ] Open **C → Hello World** — code block shows multiple lines (not `\n` text)  
- [ ] **Copy** on code block works  
- [ ] Resize playground on desktop  
- [ ] Narrow browser / phone — lesson stacks, hamburger menu works  
- [ ] Playground — run Python, JavaScript, Java, C  
- [ ] Exams — list, take, submit, see score  
- [ ] Admin — dashboard, activity logs, sudden tests  

---

## Environment notes

- `FRONTEND_URL` in `backend/.env` should match Vite origin (e.g. `http://localhost:5173`) for CORS.  
- `APP_URL` affects uploaded avatar URLs.  
- Vite proxies `/api` to port 8000 in `frontend/vite.config.js`.  

---

## Git

No commits or pushes were made by the agent. Review changes locally, then commit when ready:

```bash
git status
git diff
```

---

## Optional next steps

- Expand Python lesson HTML through `FixLessonContentSeeder` (same pattern as other tracks).  
- More exam questions per module.  
- Certificates / badges on exam pass.  
- Pyodide in-browser runner for offline-friendly JS/Python on mobile.  

---

*Last updated: May 2026 — CodeCraft local development pass.*
