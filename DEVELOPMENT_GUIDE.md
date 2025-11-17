# Development Guide

## Database Configuration

This application supports **two database drivers**:
- **SQLite** - For development (default)
- **Supabase** - For production

### Quick Start for Development

The application is pre-configured to use SQLite for local development. Just start the server:

```bash
cd public
php -S localhost:8000
```

The SQLite database will be automatically created at `database/app.db` on first run.

---

## Switching Between Databases

### Using SQLite (Development)

1. Open `.env` file
2. Set `DB_DRIVER=sqlite`
3. That's it! The database will be created automatically

```env
# .env
DB_DRIVER=sqlite
SQLITE_DB_PATH=database/app.db
```

### Using Supabase (Production)

1. Set up your Supabase project at https://supabase.com
2. Run the SQL from `database_setup.sql` in Supabase SQL Editor
3. Update `.env`:

```env
# .env
DB_DRIVER=supabase
SUPABASE_URL=your-project-url
SUPABASE_ANON_KEY=your-anon-key
```

---

## Environment Configuration

### .env File

Copy `.env.example` to `.env` and configure:

```env
# Application Environment
APP_ENV=development          # development | production

# Database Driver
DB_DRIVER=sqlite             # sqlite | supabase

# SQLite Configuration (for development)
SQLITE_DB_PATH=database/app.db

# Supabase Configuration (for production)
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-api-key

# Application Settings
APP_NAME=MyApp
APP_DEBUG=true              # Set to false in production
BASE_URL=http://localhost:8000
```

---

## Database Differences

### SQLite (Development)

**Advantages:**
- ✅ Zero configuration
- ✅ No internet required
- ✅ Fast for development
- ✅ Single file database
- ✅ Automatic schema creation

**Features:**
- Local authentication (password hashing with PHP)
- All CRUD operations
- Full-text search
- Sessions stored in database

**Database Location:**
- File: `database/app.db`
- Auto-created on first run

### Supabase (Production)

**Advantages:**
- ✅ Cloud-hosted PostgreSQL
- ✅ Built-in authentication
- ✅ Row Level Security (RLS)
- ✅ Real-time capabilities
- ✅ Automatic backups
- ✅ Scalable

**Features:**
- Supabase Auth API
- REST API access
- Advanced PostgreSQL features
- Email verification
- OAuth providers

---

## Development Workflow

### 1. Local Development (SQLite)

```bash
# Start development server
cd public
php -S localhost:8000

# Database is created automatically
# Located at: database/app.db
```

### 2. Testing

```bash
# Register test user
# Login and test features
# All data stays in local SQLite file
```

### 3. Deploy to Production (Supabase)

```bash
# 1. Set up Supabase project
# 2. Run database_setup.sql in Supabase SQL Editor
# 3. Update .env with Supabase credentials
# 4. Set DB_DRIVER=supabase
# 5. Deploy your application
```

---

## Code Examples

### The Code is Database-Agnostic

You write the same code regardless of database:

```php
// This works with both SQLite and Supabase!
$userRepo = new UserRepository();

// Create user
$user = new User();
$user->setEmail('user@example.com');
$user->setFullName('John Doe');
$userRepo->create($user);

// Find user
$user = $userRepo->findByEmail('user@example.com');

// Update user
$user->setFullName('Jane Doe');
$userRepo->update($user);

// Delete user
$userRepo->delete($user->getId());
```

The application automatically uses the correct database driver based on `.env` configuration.

---

## Database Schema

### Tables Created Automatically

**users table:**
```sql
CREATE TABLE users (
    id TEXT PRIMARY KEY,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT,           -- Only used in SQLite
    full_name TEXT NOT NULL,
    phone TEXT,
    avatar_url TEXT,
    role TEXT DEFAULT 'user',
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);
```

**auth_sessions table** (SQLite only):
```sql
CREATE TABLE auth_sessions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL,
    email TEXT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expires_at TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
```

---

## Authentication

### SQLite Authentication

- Passwords hashed with `password_hash()`
- Sessions stored in `auth_sessions` table
- Tokens generated with `random_bytes()`

### Supabase Authentication

- Uses Supabase Auth API
- Email verification available
- OAuth providers supported
- JWT tokens

---

## Troubleshooting

### SQLite Issues

**Problem**: Database file not created
- **Solution**: Check write permissions on `database/` directory

**Problem**: "database locked" error
- **Solution**: Close any SQLite browser tools

**Problem**: Can't find database file
- **Solution**: Check `SQLITE_DB_PATH` in `.env`

### Supabase Issues

**Problem**: Connection error
- **Solution**: Check `SUPABASE_URL` and `SUPABASE_ANON_KEY`

**Problem**: RLS policy errors
- **Solution**: Run `database_setup.sql` to create policies

**Problem**: User already exists
- **Solution**: Check Supabase Auth dashboard

---

## Migration Between Databases

### Export from SQLite

```php
// Example export script
$pdo = new PDO('sqlite:database/app.db');
$users = $pdo->query('SELECT * FROM users')->fetchAll();

// Save to JSON or CSV for import to Supabase
```

### Import to Supabase

1. Export users from SQLite
2. Use Supabase SQL Editor or API to import
3. Update `.env` to use Supabase
4. Test the application

---

## Environment-Specific Configuration

### Development (.env)
```env
APP_ENV=development
DB_DRIVER=sqlite
APP_DEBUG=true
```

### Production (.env.production)
```env
APP_ENV=production
DB_DRIVER=supabase
APP_DEBUG=false
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-production-key
```

---

## Best Practices

### Development

1. ✅ Use SQLite for local development
2. ✅ Keep `.env` in `.gitignore`
3. ✅ Test all features locally first
4. ✅ Use version control

### Production

1. ✅ Use Supabase for production
2. ✅ Set `APP_DEBUG=false`
3. ✅ Enable HTTPS
4. ✅ Set up Supabase RLS policies
5. ✅ Enable email verification
6. ✅ Regular backups

---

## Database Tools

### SQLite

**Recommended Tools:**
- [DB Browser for SQLite](https://sqlitebrowser.org/) - GUI tool
- [SQLite CLI](https://sqlite.org/cli.html) - Command line
- VSCode Extensions: SQLite Viewer

**View Database:**
```bash
sqlite3 database/app.db
.tables
SELECT * FROM users;
```

### Supabase

**Dashboard:**
- https://supabase.com/dashboard
- Table Editor
- SQL Editor
- Auth Users
- API Logs

---

## Performance Tips

### SQLite

- ✅ Use indexes (already created)
- ✅ Enable WAL mode for better concurrency
- ✅ Regular VACUUM for optimization

### Supabase

- ✅ Use indexes (created in setup SQL)
- ✅ Optimize queries with EXPLAIN
- ✅ Use connection pooling
- ✅ Monitor query performance in dashboard

---

## Security

### SQLite

- ✅ Password hashing with `password_hash()`
- ✅ Prepared statements (prevents SQL injection)
- ✅ File permissions (600 for database file)
- ✅ Session tokens

### Supabase

- ✅ Row Level Security (RLS)
- ✅ API key security
- ✅ JWT tokens
- ✅ Built-in rate limiting
- ✅ SSL/TLS encryption

---

## FAQs

**Q: Can I switch databases later?**
A: Yes! The code is database-agnostic. Just update `.env`.

**Q: Which database should I use?**
A: SQLite for development, Supabase for production.

**Q: Is SQLite suitable for production?**
A: For small apps with single server, yes. For scalable apps, use Supabase.

**Q: How do I backup SQLite?**
A: Copy the `database/app.db` file.

**Q: How do I backup Supabase?**
A: Supabase provides automatic backups in the dashboard.

**Q: Can I use another database?**
A: Yes! Extend the `Database` class to support MySQL, PostgreSQL, etc.

---

## Next Steps

1. ✅ Start with SQLite for development
2. ✅ Build and test your features
3. ✅ When ready for production, set up Supabase
4. ✅ Update `.env` configuration
5. ✅ Deploy!

---

**Happy Coding!** 🚀
