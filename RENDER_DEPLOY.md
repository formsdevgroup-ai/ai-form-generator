# Deploy AI Forms Generator to Render

Step-by-step guide to deploy this Laravel app on [Render](https://render.com) (free tier).

---

## Prerequisites

- [GitHub](https://github.com) account
- [Render](https://render.com) account (free)
- Code pushed to a GitHub repository

---

## Step 1: Push Your Code to GitHub

If not already done:

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/ai-forms-generator.git
git push -u origin main
```

---

## Step 2: Create a PostgreSQL Database on Render

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **New +** → **PostgreSQL**
3. Name it (e.g. `ai-forms-db`)
4. Choose **Free** plan
5. Select region (closest to you)
6. Click **Create Database**
7. Wait for it to spin up, then copy the **Internal Database URL** (starts with `postgresql://`)

---

## Step 3: Create the Web Service

1. Click **New +** → **Web Service**
2. Connect your GitHub account if needed
3. Select your `ai-forms-generator` repository
4. Configure:
   - **Name:** `ai-forms-generator` (or any name)
   - **Region:** Same as your database
   - **Branch:** `main`
   - **Runtime:** **Docker**
   - **Instance Type:** **Free**

---

## Step 4: Set Environment Variables

In the Web Service settings, go to **Environment** and add:

| Key | Value |
|-----|-------|
| `APP_KEY` | Run `php artisan key:generate --show` locally and paste the output |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://YOUR-SERVICE-NAME.onrender.com` (update after first deploy) |
| `DATABASE_URL` | Paste the **Internal Database URL** from Step 2 (Render auto-fills this if you link the DB) |
| `DB_CONNECTION` | `pgsql` |
| `GEMINI_API_KEY` | Your Google Gemini API key from [Google AI Studio](https://aistudio.google.com) |

**Optional** (for correct asset URLs):
| Key | Value |
|-----|-------|
| `ASSET_URL` | `https://YOUR-SERVICE-NAME.onrender.com` |

---

## Step 5: Deploy

1. Click **Create Web Service**
2. Render will build the Docker image and deploy
3. First deploy may take 5–10 minutes
4. When done, your app will be at `https://YOUR-SERVICE-NAME.onrender.com`

---

## Step 6: Update APP_URL (if needed)

After the first deploy, copy your live URL and set:

- `APP_URL` = your full Render URL
- `ASSET_URL` = same (optional, helps with Vite assets)

Redeploy or use **Manual Deploy** → **Deploy latest commit** after changing env vars.

---

## Queue Workers (Optional)

This app uses `database` queue. On the free tier you typically **don’t** run a worker. Jobs will run synchronously if you set:

```
QUEUE_CONNECTION=sync
```

If you need background jobs, add a **Background Worker** service on Render and run:

```
php artisan queue:work --tries=3
```

---

## Free Tier Notes

- **Web Service:** Spins down after ~15 min of inactivity; first request may take 30–60 seconds
- **PostgreSQL:** 1 GB storage, 90-day retention on free tier
- **Build minutes:** 400 free build minutes/month

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 502 Bad Gateway | Check logs; often DB connection or migration failure |
| Assets not loading | Set `ASSET_URL` to your Render URL |
| Mixed content (HTTP/HTTPS) | `AppServiceProvider` already forces HTTPS in production |
| Migration fails | Ensure `DATABASE_URL` and `DB_CONNECTION=pgsql` are set |

