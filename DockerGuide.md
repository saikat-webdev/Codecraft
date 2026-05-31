# CodeCraft — Docker Setup Guide

This guide runs the full CodeCraft stack (Laravel API, React frontend, and **PostgreSQL**) using Docker Compose. You only need **Docker Desktop** on your machine — you do **not** need to install PHP, Node, Composer, or PostgreSQL locally for this workflow.

---

## Windows: PC freezes or hangs when using Docker

**Common cause:** mounting `backend/` and `frontend/` from Windows into Linux containers (`./backend:/var/www/html`). Docker Desktop must sync every file change through WSL2. Running `composer install`, Vite, and Laravel on that mount can peg CPU and RAM and **freeze the whole PC**.

**This project’s `docker-compose.yml` avoids that** by baking code into images (no bind mounts). After you change code, rebuild:

```powershell
docker compose up --build
```

**If Docker is already stuck** (commands never finish):

1. Run the recovery script (PowerShell, from project root):

```powershell
.\scripts\docker-recover.ps1
```

2. Or manually: Task Manager → end **Docker Desktop** → open PowerShell → `wsl --shutdown` → start Docker Desktop again.

**Lightest option on Windows** (recommended if freezes continue): run **only Postgres** in Docker and PHP/Node on the host (you already have XAMPP):

```powershell
docker compose -f docker-compose.db-only.yml up -d
```

Then point `backend/.env` at `127.0.0.1` port `5433` (see `docker-compose.db-only.yml` comments).

**Limit WSL2 memory** (Docker Desktop → Settings → Resources, or create `%UserProfile%\.wslconfig`):

```ini
[wsl2]
memory=4GB
processors=2
swap=1GB
```

Then run `wsl --shutdown` and restart Docker Desktop.

---

## Do I need to install PostgreSQL on my PC?

**No.** PostgreSQL runs inside a Docker container defined in `docker-compose.yml`. Docker downloads the official Postgres image, creates the database, and keeps your data in a Docker volume (`postgres_data`).

| Scenario | Install PostgreSQL on Windows? |
|----------|--------------------------------|
| Run project with **Docker Compose** (this guide) | **No** |
| Run backend with **XAMPP / `php artisan serve`** on the host | **Yes** (or use another DB you configure in `backend/.env`) |

You only need a local PostgreSQL install if you develop **without** Docker and point `backend/.env` at `127.0.0.1`.

---

## Prerequisites

1. **Docker Desktop** for Windows  
   - Download: https://www.docker.com/products/docker-desktop/  
   - Enable WSL 2 backend if prompted (recommended on Windows 10/11).
2. **Git** (to clone the repo, if you have not already).
3. At least **4 GB RAM** free for Docker.

Verify installation:

```powershell
docker --version
docker compose version
```

---

## Project layout (Docker-related files)

| File | Purpose |
|------|---------|
| `docker-compose.yml` | Defines `postgres`, `backend`, and `frontend` services |
| `backend/Dockerfile` | PHP 8.2 image with PostgreSQL extensions |
| `backend/docker-entrypoint.sh` | Waits for DB, installs Composer deps, migrates, starts API |
| `backend/env.docker.example` | Template `.env` for Docker (copied on first run) |
| `frontend/Dockerfile` | Node 20 image for Vite dev server |

---

## Step-by-step: run CodeCraft in Docker

### Step 1 — Open the project folder

```powershell
cd c:\xampp\htdocs\Codecraft
```

(Use your actual clone path if different.)

### Step 2 — (Optional) Avoid port conflicts

Default ports:

| Service    | Port on your PC |
|-----------|-----------------|
| Frontend  | 5173            |
| Backend   | 8000            |
| PostgreSQL| 5432            |

If something else already uses **5432** (e.g. local PostgreSQL), either stop that service or change the mapping in `docker-compose.yml`:

```yaml
ports:
  - "5433:5432"   # host:container
```

The backend still uses `DB_PORT=5432` **inside** the Docker network; only the host mapping changes.

### Step 3 — Build and start all services

First time (downloads images and builds containers — may take several minutes):

```powershell
docker compose up --build
```

Later runs:

```powershell
docker compose up
```

Run in the background:

```powershell
docker compose up -d --build
```

### Step 4 — Wait until services are healthy

Watch the logs until you see:

- `codecraft-postgres` — ready / healthy  
- `codecraft-backend` — `Server running on [http://0.0.0.0:8000]`  
- `codecraft-frontend` — Vite ready on port 5173  

On first start, the backend will:

1. Copy `backend/env.docker.example` → `backend/.env` (if `.env` does not exist)  
2. Run `composer install`  
3. Generate `APP_KEY`  
4. Run `php artisan migrate`  
5. Create the storage symlink  

### Step 5 — Seed the database (first time only)

Open a **new** terminal in the project root:

```powershell
docker compose exec backend php artisan db:seed
```

This loads courses, lessons, admin user, and sample data.

### Step 6 — Open the app in your browser

| What | URL |
|------|-----|
| **Frontend (use this)** | http://localhost:5173 |
| API health check | http://localhost:8000/up |
| API base | http://localhost:8000/api |

### Step 7 — Log in with test accounts

After seeding:

| Role  | Email               | Password  |
|-------|---------------------|-----------|
| Admin | admin@codecraft.com | admin123  |
| User  | user@codecraft.com  | user123   |

---

## Common Docker commands

```powershell
# Stop containers
docker compose down

# Stop and remove database volume (full reset — deletes all DB data)
docker compose down -v

# View logs
docker compose logs -f

# Logs for one service
docker compose logs -f backend

# Run Artisan inside the backend container
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
docker compose exec backend php artisan config:clear

# Open a shell in the backend container
docker compose exec backend sh

# Rebuild after Dockerfile changes
docker compose up --build
```

---

## Environment variables

Docker Compose sets database and app URLs for containers. Key values:

| Variable | Value in Docker |
|----------|-----------------|
| `DB_HOST` | `postgres` (service name, not `127.0.0.1`) |
| `DB_DATABASE` | `codecraft_db` |
| `DB_USERNAME` | `codecraft` |
| `DB_PASSWORD` | `codecraft_secret` |
| `VITE_API_URL` | `http://localhost:8000/api` (browser calls your machine’s port 8000) |

To change database credentials, edit **both**:

1. `postgres` service `environment` in `docker-compose.yml`  
2. `backend` service `environment` (and `backend/env.docker.example` if you use the file template)

Then reset the database volume:

```powershell
docker compose down -v
docker compose up --build
docker compose exec backend php artisan db:seed
```

---

## Troubleshooting

### “Port is already allocated”

Another app is using 5173, 8000, or 5432. Stop that app or change the `ports:` mapping in `docker-compose.yml`.

### Backend keeps restarting / “connection refused” to database

Wait for Postgres healthcheck to pass, then:

```powershell
docker compose restart backend
```

### Frontend shows API errors / CORS

Ensure you open **http://localhost:5173** (not `127.0.0.1` unless you also add it to `FRONTEND_URL` and Sanctum domains).

Check backend env:

```powershell
docker compose exec backend php artisan config:clear
```

### `vendor` or `node_modules` issues on Windows

Named volumes (`backend_vendor`, `frontend_node_modules`) avoid slow or broken bind-mount installs. If dependencies look wrong:

```powershell
docker compose down
docker volume rm codecraft_backend_vendor codecraft_frontend_node_modules
docker compose up --build
```

### Reset everything and start fresh

```powershell
docker compose down -v
docker compose up --build
docker compose exec backend php artisan db:seed
```

### Code playground (Judge0)

Code execution uses the public **Judge0 CE** API (`https://ce.judge0.com`) over the internet. No extra Docker service is required. If Judge0 is down, some features may fall back to local runners only when those language runtimes exist in the backend image (not installed by default in Docker).

---

## Docker vs local XAMPP development

| | Docker | XAMPP / local |
|---|--------|----------------|
| PHP / Composer | In container | On your PC |
| Node / npm | In container | On your PC |
| PostgreSQL | In container | Install on PC |
| Database host in `.env` | `postgres` | `127.0.0.1` |

You can keep using XAMPP for other projects; CodeCraft in Docker does not conflict as long as ports **5173**, **8000**, and **5432** are free.

---

## Quick reference (copy-paste)

```powershell
cd c:\xampp\htdocs\Codecraft
docker compose up --build
# New terminal after services are up:
docker compose exec backend php artisan db:seed
```

Then open **http://localhost:5173**.
