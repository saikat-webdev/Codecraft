# CodeCraft on Supabase (PostgreSQL + pgvector)

Yes — you can run the **entire CodeCraft database** on [Supabase](https://supabase.com). Supabase is managed PostgreSQL with **pgvector built in**, so you avoid local pgAdmin/pgvector setup issues.

Your app (Laravel on XAMPP or Docker) connects to Supabase over the internet. You do **not** need local Postgres for the database.

---

## Architecture

```text
┌─────────────────┐     ┌──────────────────────┐
│  React (local)  │     │  Laravel (local)     │
│  localhost:5173 │────▶│  localhost:8000      │
└─────────────────┘     └──────────┬───────────┘
                                   │ SSL
                                   ▼
                        ┌──────────────────────┐
                        │  Supabase Postgres   │
                        │  + pgvector          │
                        │  (all tables + RAG)  │
                        └──────────────────────┘
```

n8n and Gemini stay wherever they are today; only the **database** moves to Supabase.

---

## Step 1 — Create a Supabase project

1. Go to [https://supabase.com/dashboard](https://supabase.com/dashboard) → **New project**.
2. Choose a region close to you, set a strong **database password** (save it).
3. Wait until the project is ready.

---

## Step 2 — Enable pgvector (usually already on)

In Supabase: **SQL Editor** → New query → run:

```sql
create extension if not exists vector;
```

If it succeeds, you are ready for `knowledge_chunks` and `php artisan knowledge:embed`.

---

## Step 3 — Get connection details

**Dashboard → Connect** (or Project Settings → Database).

### Important: Windows / XAMPP / many home networks (IPv4 only)

The hostname `db.YOUR_REF.supabase.co` often resolves to **IPv6 only**. If Laravel shows:

`could not translate host name "db....supabase.co" to address: Unknown host`

your PC cannot use that address. **Do not use the Direct host on Windows** unless you have IPv6 working end-to-end.

**Use the Session pooler (IPv4-compatible)** instead:

| Field | Example |
|-------|---------|
| Host | `aws-0-ap-southeast-1.pooler.supabase.com` *(copy yours from dashboard)* |
| Port | `5432` |
| Database | `postgres` |
| Username | `postgres.wuybppciiqjtqojpdkxw` *(postgres.**your-project-ref**)* |
| Password | your database password |
| SSL | `require` |

Copy the exact **Session mode** string from **Connect → ORM / URI** in the Supabase dashboard (not the Direct `db....supabase.co` line).

| Use case | Connection | Port |
|----------|------------|------|
| **Laravel on Windows (recommended)** | **Session pooler** | `5432` |
| **Direct** (IPv6 networks only) | `db.xxxxx.supabase.co` | `5432` |
| Transaction pooler (serverless) | pooler host | `6543` |

---

## Step 4 — Configure Laravel `backend/.env`

Replace local Postgres settings with Supabase.

**Windows / XAMPP — Session pooler (IPv4):**

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-YOUR_REGION.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.YOUR_PROJECT_REF
DB_PASSWORD=your_supabase_db_password
DB_SSLMODE=require
```

**Linux / IPv6-capable server — Direct (optional):**

```env
DB_HOST=db.YOUR_PROJECT_REF.supabase.co
DB_USERNAME=postgres
# ... same port, database, password, sslmode
```

Or use one URI from **Connect → Session mode**:

```env
DB_URL=postgresql://postgres.YOUR_PROJECT_REF:YOUR_PASSWORD@aws-0-YOUR_REGION.pooler.supabase.com:5432/postgres?sslmode=require
```

Also keep your AI keys:

```env
GEMINI_API_KEY=your_key
GEMINI_EMBEDDING_MODEL=gemini-embedding-001
GEMINI_EMBEDDING_DIMENSIONS=768
KNOWLEDGE_RAG_ENABLED=true
```

Clear config cache:

```powershell
cd backend
php artisan config:clear
```

Test connection:

```powershell
php artisan db:show
```

---

## Step 5 — Create all tables on Supabase

```powershell
cd backend
php artisan migrate
```

This creates users, modules, lessons, `ai_conversations`, **`knowledge_chunks`**, etc. on Supabase.

Seed course data:

```powershell
php artisan db:seed
```

---

## Step 6 — Build AI knowledge vectors

```powershell
php artisan knowledge:embed --fresh
```

Embeddings are stored in **`knowledge_chunks.embedding`** on Supabase. You can verify in Supabase **Table Editor** (row count) or SQL:

```sql
select count(*) from knowledge_chunks;
select chunk_key, title, left(content, 80) from knowledge_chunks limit 10;
```

---

## Step 7 — Run the app without local Postgres

### Option A — XAMPP (typical for you)

1. Do **not** start Docker Postgres (or remove `DB_HOST=postgres` from Docker `.env`).
2. Laravel + frontend run locally; DB is only on Supabase.
3. `php artisan serve` and `npm run dev` in `frontend`.

### Option B — Docker for app only

In `docker-compose.yml`, you can **stop using** the `postgres` service and point `backend` environment at Supabase via `backend/.env` (mounted or copied into the container).

Or use only:

```powershell
docker compose -f docker-compose.db-only.yml down
```

and use Supabase instead of local DB entirely.

---

## pgAdmin vs Supabase

| Tool | Role |
|------|------|
| **pgAdmin (local)** | Optional GUI; must connect to Supabase host with SSL |
| **Supabase Dashboard** | Table Editor, SQL Editor — easier for hosted DB |

To use pgAdmin with Supabase:

- Host: `db.xxx.supabase.co`
- Port: `5432`
- SSL mode: **require**
- Username / password from Supabase

If pgvector failed locally, it was likely your **local** Postgres install — Supabase already ships compatible Postgres.

---

## Connection pooler (production tip)

Supabase **Transaction pooler** (port `6543`) can break some Laravel migrations or long sessions. Use:

- **Direct (`5432`)** for: `migrate`, `db:seed`, `knowledge:embed`
- **Pooler (`6543`)** for: high-traffic production app (optional later)

---

## Re-index after content changes

Whenever you update modules/lessons:

```powershell
php artisan knowledge:embed --fresh
```

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `could not translate host name` / `Unknown host` for `db.*.supabase.co` | **IPv6-only DNS** — switch to **Session pooler** host (`aws-0-….pooler.supabase.com`) and user `postgres.your-ref` ([Supabase IPv4 guide](https://supabase.com/docs/guides/troubleshooting/supabase--your-network-ipv4-and-ipv6-compatibility-cHe3BP)) |
| `could not connect to server` | Check password, host, firewall; use Session pooler on Windows |
| SSL required | Set `DB_SSLMODE=require` |
| `extension "vector" does not exist` | Run `create extension vector;` in SQL Editor |
| `knowledge:embed` slow | Normal; many API calls; uses `KNOWLEDGE_EMBED_DELAY_MS` |
| Empty AI site answers | Run `knowledge:embed --fresh`; confirm rows in `knowledge_chunks` |
| IPv6 issues on Windows | In Supabase connect settings try **IPv4** host if offered |

---

## Security

- Never commit `.env` with Supabase password to Git.
- In Supabase: restrict **Database** password rotation if exposed.
- Use **Row Level Security** only if you add Supabase client from the browser; Laravel uses the service role connection via `.env` (server-side only).

---

## Summary

| Question | Answer |
|----------|--------|
| Can the whole DB live on Supabase? | **Yes** |
| Does pgvector / RAG work? | **Yes** |
| Do I still need local Postgres? | **No** (only if you want a local copy) |
| What changes in CodeCraft? | **`backend/.env`** points to Supabase; run `migrate`, `db:seed`, `knowledge:embed` |

See also: [KNOWLEDGE_RAG.md](KNOWLEDGE_RAG.md), [AI_INSTRUCTOR_SETUP.md](AI_INSTRUCTOR_SETUP.md).
