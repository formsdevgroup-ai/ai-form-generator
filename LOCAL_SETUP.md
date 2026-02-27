# Local Setup Guide

How to clone and run the AI Forms Generator on your machine.

---

## Prerequisites

- **PHP 8.2+** with extensions: `curl`, `mbstring`, `openssl`, `pdo`, `sqlite`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- **Composer** – [getcomposer.org](https://getcomposer.org)
- **Node.js 18+** and **npm** – [nodejs.org](https://nodejs.org)
- **Git**

---

## 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/ai-forms-generator.git
cd ai-forms-generator
```

Replace `YOUR_USERNAME` with the actual GitHub username or organization.

---

## 2. Install Dependencies

### Option A: One-command setup (recommended)

```bash
# Create SQLite database file first (when using default SQLite)
# Linux/macOS:
touch database/database.sqlite
# Windows (PowerShell):
New-Item -ItemType File -Path database/database.sqlite -Force

composer setup
```

This runs: `composer install`, creates `.env`, generates `APP_KEY`, runs migrations, installs npm packages, and builds assets.

### Option B: Manual steps

```bash
# PHP dependencies
composer install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database (if using default)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Node dependencies and build
npm install
npm run build
```

---

## 3. Configure Environment

Edit `.env` and set:

| Variable | Description |
|----------|-------------|
| `APP_URL` | `http://localhost:8000` (for `php artisan serve`) |
| `GEMINI_API_KEY` | Your [Google AI Studio](https://aistudio.google.com) API key |

The app uses **SQLite** by default. To use MySQL or PostgreSQL, update the `DB_*` variables in `.env`.

---

## 4. Run the Application

### Development (with hot reload)

```bash
composer dev
```

This starts:

- Laravel server at `http://localhost:8000`
- Queue worker
- Log viewer (Pail)
- Vite dev server (hot reload)

### Simple run (no hot reload)

```bash
php artisan serve
```

Then open `http://localhost:8000` in your browser. Run `npm run dev` in another terminal if you want Vite hot reload.

---

## 5. Queue Worker (optional)

If you use the database queue and run jobs in the background, keep a queue worker running:

```bash
php artisan queue:listen --tries=1
```

`composer dev` already starts this for you.

---

## Quick Reference

| Command | Purpose |
|---------|---------|
| `composer setup` | Full first-time setup |
| `composer dev` | Start dev server + queue + Vite |
| `php artisan serve` | Start Laravel only |
| `npm run dev` | Start Vite dev server |
| `npm run build` | Build assets for production |
| `php artisan migrate` | Run database migrations |
| `php artisan migrate:fresh` | Reset database and re-run migrations |

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `Class "X" not found` | Run `composer dump-autoload` |
| Assets 404 | Run `npm run build` or `npm run dev` |
| Database error | Ensure `database/database.sqlite` exists (SQLite) or DB credentials are correct |
| Gemini API errors | Set `GEMINI_API_KEY` in `.env` |
