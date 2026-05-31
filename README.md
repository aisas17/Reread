# Reread

## Deploy to Vercel

This is a plain PHP project. Vercel does not run PHP as a default first-party runtime, so this project uses the community `vercel-php` runtime configured in `vercel.json`.

### 1. Prepare the database

Vercel cannot use your local `localhost` MySQL database. Create a hosted MySQL database first, then import your local `reread` database into it.

Set these Vercel environment variables:

```env
DB_HOST=your_mysql_host
DB_PORT=3306
DB_USER=your_mysql_user
DB_PASS=your_mysql_password
DB_NAME=reread
GROQ_API_KEY=your_groq_api_key_here
GROQ_MODEL=llama-3.1-8b-instant
```

### 2. Deploy

Install and log in to the Vercel CLI:

```bash
npm i -g vercel
vercel login
```

Deploy from this project folder:

```bash
vercel
```

For production:

```bash
vercel --prod
```

### Important upload note

Files written to `uploads/` during runtime will not be reliable on Vercel because serverless function storage is temporary. Existing files committed in `uploads/` can be served, but new user uploads should be moved to persistent object storage such as Vercel Blob, S3, Cloudinary, or another external file store.
