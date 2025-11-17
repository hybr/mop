# Database Setup Guide

## Overview

This application supports **two database systems**:

| Database | Use Case | Setup Time | Features |
|----------|----------|------------|----------|
| **SQLite** | Development | 0 seconds (automatic) | Local file, no config needed |
| **Supabase** | Production | 3 minutes | Cloud PostgreSQL, scalable, auth |

---

## SQLite Setup (Development)

### ✅ Zero Configuration Required

The application is pre-configured for SQLite development. Just start the server:

```bash
cd public
php -S localhost:8000
```

**That's it!** The database will be created automatically.

### What Happens Automatically

1. ✅ Creates `database/` directory
2. ✅ Creates `database/app.db` SQLite file
3. ✅ Creates `users` table with all columns
4. ✅ Creates `auth_sessions` table
5. ✅ Creates indexes for performance
6. ✅ Ready to use!

### Database Location

**File**: `C:\Users\Faber\mop\database\app.db`
**Size**: ~20 KB (empty) → grows as you add data
**Format**: SQLite 3

### SQLite Schema

```sql
-- Users table
CREATE TABLE users (
    id TEXT PRIMARY KEY,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT,
    full_name TEXT NOT NULL,
    phone TEXT,
    avatar_url TEXT,
    role TEXT DEFAULT 'user',
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- Auth sessions table
CREATE TABLE auth_sessions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL,
    email TEXT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expires_at TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_is_active ON users(is_active);
```

### Authentication in SQLite

- Passwords are hashed using PHP's `password_hash()` with bcrypt
- Tokens are generated using `random_bytes(32)`
- Sessions stored in `auth_sessions` table
- No external API calls needed

---

## Supabase Setup (Production)

### Prerequisites

1. Supabase account (free tier available)
2. A Supabase project created

### Step-by-Step Setup

#### 1. Create Supabase Project (2 minutes)

1. Go to https://supabase.com/dashboard
2. Click "New Project"
3. Fill in:
   - **Name**: Your app name
   - **Database Password**: Save this securely
   - **Region**: Choose closest to your users
4. Click "Create new project"
5. Wait for setup to complete (~2 minutes)

#### 2. Run Database Setup SQL (1 minute)

1. In your Supabase project, click "SQL Editor" in the left sidebar
2. Click "New Query"
3. Copy the entire contents of `database_setup.sql`
4. Paste into the SQL Editor
5. Click "Run" or press Ctrl+Enter
6. Verify "Success" message

The SQL will create:
- ✅ `users` table
- ✅ Indexes for performance
- ✅ Row Level Security (RLS) policies
- ✅ Triggers for automatic timestamps
- ✅ Helper functions for statistics

#### 3. Get API Credentials (30 seconds)

1. Click "Settings" in the left sidebar
2. Click "API"
3. Copy these values:
   - **Project URL** (e.g., `https://xxx.supabase.co`)
   - **anon public** key (under "Project API keys")

#### 4. Update .env Configuration (30 seconds)

Edit `.env` file:

```env
# Change database driver
DB_DRIVER=supabase

# Add your Supabase credentials
SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=your-anon-public-key-here
```

#### 5. Test the Connection

1. Restart your application
2. Try to register a new user
3. Check the Supabase dashboard → Authentication → Users
4. You should see your new user!

### Supabase Features

**Authentication:**
- Uses Supabase Auth API
- Email/password authentication
- Email verification available
- OAuth providers (Google, GitHub, etc.)
- JWT tokens

**Security:**
- Row Level Security (RLS) policies
- Users can only see/edit their own data
- Admins can manage all users
- API keys for access control

**Scalability:**
- Cloud PostgreSQL database
- Automatic backups
- Global CDN
- Scales to millions of users

---

## Configuration File (.env)

### SQLite Configuration

```env
# Environment
APP_ENV=development
DB_DRIVER=sqlite

# SQLite Settings
SQLITE_DB_PATH=database/app.db

# App Settings
APP_NAME=MyApp
APP_DEBUG=true
BASE_URL=http://localhost:8000
```

### Supabase Configuration

```env
# Environment
APP_ENV=production
DB_DRIVER=supabase

# Supabase Settings
SUPABASE_URL=https://famnnqgqobqthfeygjzx.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# App Settings
APP_NAME=MyApp
APP_DEBUG=false
BASE_URL=https://yourdomain.com
```

---

## Switching Databases

### Development → Production

1. Set up Supabase (see above)
2. Update `.env`:
   ```env
   DB_DRIVER=supabase
   ```
3. No code changes needed!

### Production → Development

1. Update `.env`:
   ```env
   DB_DRIVER=sqlite
   ```
2. SQLite database will be created automatically

### Data Migration

To move data from SQLite to Supabase:

**Option 1: Manual Entry** (for small datasets)
- Re-register users in production

**Option 2: SQL Export/Import**
```bash
# Export from SQLite
sqlite3 database/app.db .dump > export.sql

# Modify SQL for PostgreSQL
# Import to Supabase via SQL Editor
```

**Option 3: PHP Script**
```php
// Example migration script
$sqliteRepo = new UserRepository(); // with SQLite
// Switch to Supabase in .env
$supabaseRepo = new UserRepository(); // with Supabase

foreach ($sqliteRepo->findAll() as $user) {
    $supabaseRepo->create($user);
}
```

---

## Database Comparison

| Feature | SQLite | Supabase |
|---------|--------|----------|
| **Setup Time** | 0 seconds | 3 minutes |
| **Configuration** | Zero | Minimal |
| **Internet Required** | No | Yes |
| **Cost** | Free | Free tier available |
| **Scalability** | Single server | Cloud-scale |
| **Backups** | Manual file copy | Automatic |
| **Authentication** | Built-in PHP | Supabase Auth API |
| **Real-time** | No | Yes (optional) |
| **Max Users** | ~100K | Millions |
| **Concurrent Connections** | Limited | Unlimited |
| **Data Location** | Local file | Cloud |

---

## Viewing Your Data

### SQLite Data Viewer

**Option 1: DB Browser for SQLite** (Recommended)
- Download: https://sqlitebrowser.org/
- Open `database/app.db`
- Browse tables, run queries, edit data

**Option 2: Command Line**
```bash
sqlite3 database/app.db
.schema users
SELECT * FROM users;
.quit
```

**Option 3: VSCode Extension**
- Install "SQLite" or "SQLite Viewer" extension
- Right-click `database/app.db` → "Open Database"

### Supabase Data Viewer

1. Go to Supabase Dashboard
2. Click "Table Editor"
3. Select "users" table
4. View, edit, search data in web interface

---

## Backup & Restore

### SQLite Backup

**Backup:**
```bash
# Copy the database file
cp database/app.db database/backup-2024-01-15.db

# Or create a SQL dump
sqlite3 database/app.db .dump > backup.sql
```

**Restore:**
```bash
# Restore from file
cp database/backup-2024-01-15.db database/app.db

# Or restore from SQL dump
sqlite3 database/app.db < backup.sql
```

### Supabase Backup

**Automatic Backups:**
- Supabase automatically backs up your database
- Access in Dashboard → Settings → Backups

**Manual Backup:**
1. Go to SQL Editor
2. Run: `SELECT * FROM users;`
3. Export results as CSV

**Point-in-Time Recovery:**
- Available on paid plans
- Restore to any point in time

---

## Troubleshooting

### SQLite Issues

**Problem**: `database is locked`
**Solution**:
- Close any SQLite browser tools
- Check file permissions
- Use WAL mode: `PRAGMA journal_mode=WAL;`

**Problem**: Database file not created
**Solution**:
```bash
mkdir -p database
chmod 755 database
```

**Problem**: `no such table: users`
**Solution**:
- Delete `database/app.db`
- Restart server (tables will be recreated)

### Supabase Issues

**Problem**: Connection refused
**Solution**:
- Check `SUPABASE_URL` and `SUPABASE_ANON_KEY`
- Verify project is active in Supabase dashboard
- Check internet connection

**Problem**: RLS policy errors
**Solution**:
- Re-run `database_setup.sql`
- Check policies in Dashboard → Authentication → Policies

**Problem**: User not appearing in Auth
**Solution**:
- Supabase Auth and database users are separate
- In SQLite mode, users are only in database
- In Supabase mode, users are in both Auth and database

---

## Security Best Practices

### SQLite Security

1. ✅ Set proper file permissions (600 or 640)
   ```bash
   chmod 600 database/app.db
   ```

2. ✅ Keep database outside web root
   - ✅ Already done: `database/` is outside `public/`

3. ✅ Regular backups
   - Set up automated backup script

4. ✅ Encrypt sensitive data if needed

### Supabase Security

1. ✅ Keep API keys secure
   - Never commit to git
   - Use environment variables

2. ✅ Enable RLS policies
   - Already configured in `database_setup.sql`

3. ✅ Use HTTPS in production
   - Supabase URLs are always HTTPS

4. ✅ Enable email verification
   - Configure in Supabase Dashboard

---

## Performance Optimization

### SQLite Performance

```sql
-- Enable WAL mode for better concurrency
PRAGMA journal_mode=WAL;

-- Optimize database
VACUUM;

-- Analyze for better query planning
ANALYZE;
```

### Supabase Performance

1. Use indexes (already created)
2. Monitor slow queries in Dashboard
3. Use connection pooling
4. Enable caching for read-heavy operations

---

## Need Help?

- **SQLite Documentation**: https://sqlite.org/docs.html
- **Supabase Documentation**: https://supabase.com/docs
- **PHP PDO Documentation**: https://www.php.net/manual/en/book.pdo.php

---

**Quick Reference:**

- SQLite for development ✅
- Supabase for production ✅
- Switch with one line in `.env` ✅
- Same code for both ✅
