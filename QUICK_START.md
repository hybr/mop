# Quick Start Guide

Get your User Management Application up and running in **2 minutes** with SQLite!

## Prerequisites Checklist

- [ ] PHP 7.4+ installed
- [ ] PDO SQLite extension enabled (usually enabled by default)

That's it for development!

---

## Development Setup (SQLite - 2 Minutes)

### Step 1: Start the Server (1 minute)

```bash
cd C:\Users\Faber\mop\public
php -S localhost:8000
```

### Step 2: Open Your Browser (30 seconds)

Navigate to: `http://localhost:8000`

### Step 3: Create an Account (30 seconds)

1. Click "Sign Up"
2. Fill in your details
3. Register and login

**Done! 🎉**

The SQLite database was automatically created at `C:\Users\Faber\mop\database/app.db`

---

## What Just Happened?

✅ PHP started a web server on port 8000
✅ SQLite database was automatically created
✅ Database tables were automatically created
✅ You're ready to develop!

---

## Test the Application

- [ ] Register a new account at `/register`
- [ ] Login with your credentials at `/login`
- [ ] View your dashboard
- [ ] Edit your profile
- [ ] Change your password
- [ ] Logout

---

## Database Information

**Type**: SQLite
**Location**: `database/app.db`
**Size**: ~20 KB (empty database)
**Tables**: `users`, `auth_sessions`

### View Your Database

**Option 1: DB Browser for SQLite**
1. Download from https://sqlitebrowser.org/
2. Open `database/app.db`
3. Browse your data

**Option 2: Command Line**
```bash
cd C:\Users\Faber\mop
sqlite3 database/app.db
.tables
SELECT * FROM users;
.quit
```

**Option 3: VSCode Extension**
1. Install "SQLite" extension
2. Right-click `database/app.db`
3. Choose "Open Database"

---

## Configuration

The application uses `.env` for configuration:

```env
# Current configuration
DB_DRIVER=sqlite
SQLITE_DB_PATH=database/app.db
APP_ENV=development
APP_DEBUG=true
```

No changes needed for development!

---

## Production Setup (Supabase)

When you're ready to deploy to production:

### Step 1: Set Up Supabase (3 minutes)

1. Go to https://supabase.com/dashboard
2. Create a new project
3. Go to SQL Editor
4. Copy and paste contents from `database_setup.sql`
5. Click "Run"

### Step 2: Update Configuration (1 minute)

Edit `.env`:

```env
# Change these lines
DB_DRIVER=supabase
SUPABASE_URL=your-project-url
SUPABASE_ANON_KEY=your-api-key
```

### Step 3: Deploy

Deploy your application to your production server.

---

## Switching Between Databases

### Use SQLite (Development)
```env
DB_DRIVER=sqlite
```

### Use Supabase (Production)
```env
DB_DRIVER=supabase
```

**The code stays the same!** No code changes needed.

---

## Available Pages

After starting the server, you can access:

| Page | URL | Description |
|------|-----|-------------|
| Home | `/` | Landing page |
| Register | `/register` | Create account |
| Login | `/login` | User login |
| Dashboard | `/dashboard` | User dashboard (requires login) |
| Profile | `/profile` | Edit profile (requires login) |
| Change Password | `/change-password` | Update password (requires login) |
| Forgot Password | `/forgot-password` | Reset password |
| Logout | `/logout` | Logout user |

---

## Troubleshooting

### Server won't start

**Problem**: Port 8000 already in use
**Solution**: Use a different port
```bash
php -S localhost:8080
```

**Problem**: `php` command not found
**Solution**: Add PHP to your PATH or use full path
```bash
C:\php\php.exe -S localhost:8000
```

### Database errors

**Problem**: Can't create database file
**Solution**: Check write permissions on `database/` directory
```bash
mkdir database
chmod 755 database
```

**Problem**: SQLite extension not enabled
**Solution**: Check `php.ini` and enable:
```ini
extension=pdo_sqlite
```

### Clean URLs not working

**For Apache**:
1. Enable mod_rewrite
2. Check `.htaccess` is in `public/` folder
3. Restart Apache

**For PHP Built-in Server**:
URLs work automatically (e.g., `/register` instead of `/auth/register`)

---

## Next Steps

### Customize the Application

1. **Change Branding**
   - Edit `views/header.php` to change "MyApp"
   - Update colors in `public/css/style.css`

2. **Add Features**
   - Create new entity classes in `src/classes/`
   - Add new pages in `public/`
   - See `PROJECT_OVERVIEW.md` for examples

3. **Deploy to Production**
   - Set up Supabase
   - Update `.env` with production credentials
   - Deploy to your hosting provider

---

## Documentation

| Document | Purpose |
|----------|---------|
| `README.md` | Complete documentation |
| `QUICK_START.md` | This file - quick setup |
| `DEVELOPMENT_GUIDE.md` | Detailed development guide |
| `PROJECT_OVERVIEW.md` | Technical overview & examples |

---

## Support

### Common Issues

**Q: Where is my data stored?**
A: In `database/app.db` (SQLite) or Supabase (production)

**Q: Can I switch databases later?**
A: Yes! Just update `DB_DRIVER` in `.env`

**Q: Is SQLite suitable for production?**
A: For small apps on single server, yes. For scalable apps, use Supabase.

**Q: How do I backup my data?**
A: SQLite: Copy `database/app.db` file. Supabase: Use dashboard backups.

**Q: Can I use this with Docker?**
A: Yes! Mount the database directory as a volume.

---

## Development Tips

### Hot Reload

The PHP built-in server doesn't have hot reload. Refresh your browser manually after code changes.

### Debug Mode

Debug is enabled by default in `.env`:
```env
APP_DEBUG=true
```

Errors will be displayed on screen.

### Reset Database

To start fresh:

**SQLite**:
```bash
rm database/app.db
# Restart server - database will be recreated
```

**Supabase**:
Run SQL: `TRUNCATE users CASCADE;`

---

## Performance Notes

### SQLite
- ⚡ Fast for development
- ⚡ No network latency
- ⚡ Single file database
- 💡 Good for up to ~100K rows
- 💡 Single server only

### Supabase
- ☁️ Cloud-hosted PostgreSQL
- ☁️ Scales automatically
- ☁️ Global CDN
- ☁️ Handles millions of rows
- ☁️ Multiple servers

---

**You're all set! Happy coding! 🚀**

For detailed information, see:
- `DEVELOPMENT_GUIDE.md` - Database switching guide
- `README.md` - Full documentation
- `PROJECT_OVERVIEW.md` - Code examples
