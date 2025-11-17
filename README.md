# User Management Application

A modern, mobile-first user management application built with Core PHP. Supports **SQLite for development** and **Supabase for production**.

## Features

- **Dual Database Support**: SQLite for development, Supabase for production
- **Complete User Management**: Registration, login, profile management, and password management
- **Mobile-First Design**: Fully responsive design that works perfectly on all devices
- **Secure Authentication**: Password hashing (SQLite) or Supabase Auth (production)
- **Object-Oriented Architecture**: Clean, maintainable code with proper separation of concerns
- **Database-Agnostic Code**: Same code works with both SQLite and Supabase

## Project Structure

```
mop/
├── public/                 # Public-facing files (web root)
│   ├── css/
│   │   └── style.css      # Mobile-first CSS framework
│   ├── js/
│   │   └── main.js        # Client-side JavaScript
│   ├── assets/            # Images and other static assets
│   ├── index.php          # Homepage
│   ├── register.php       # User registration
│   ├── login.php          # User login
│   ├── dashboard.php      # User dashboard
│   ├── profile.php        # Profile editor
│   ├── change-password.php # Password management
│   ├── logout.php         # Logout handler
│   └── .htaccess          # Apache configuration
├── src/
│   ├── classes/           # Core PHP classes
│   │   ├── User.php       # User entity class
│   │   ├── UserRepository.php  # User data access
│   │   └── Auth.php       # Authentication class
│   ├── config/
│   │   └── Database.php   # Database configuration
│   └── includes/
│       └── autoload.php   # Autoloader
└── views/
    ├── header.php         # Header template
    └── footer.php         # Footer template
```

## Requirements

### Development (SQLite)
- PHP 7.4 or higher with PDO SQLite extension
- Any web server (Apache, Nginx, or PHP built-in server)

### Production (Supabase)
- PHP 7.4 or higher with cURL extension
- Apache web server with mod_rewrite enabled (recommended)
- Supabase account

## Quick Start (Development with SQLite)

The application is pre-configured to use SQLite for local development. No database setup needed!

```bash
# Navigate to project
cd C:\Users\Faber\mop

# Start PHP built-in server
cd public
php -S localhost:8000

# Open browser to http://localhost:8000
# SQLite database will be created automatically at database/app.db
```

That's it! The application is ready to use with SQLite.

---

## Installation

### Option 1: Development Setup (SQLite - Recommended)

The `.env` file is already configured for SQLite. Just start the server:

```bash
cd public
php -S localhost:8000
```

The database will be automatically created on first run.

### Option 2: Production Setup (Supabase)

#### Step 1: Set up Supabase Database

1. Go to your Supabase project dashboard: https://supabase.com/dashboard
2. Navigate to the SQL Editor
3. Copy and paste the contents of `database_setup.sql`
4. Click "Run" to execute the SQL

#### Step 2: Update Environment Configuration

Edit the `.env` file and change the database driver:

```env
# Change from sqlite to supabase
DB_DRIVER=supabase

# Update Supabase credentials if different
SUPABASE_URL=https://famnnqgqobqthfeygjzx.supabase.co
SUPABASE_ANON_KEY=your-api-key
```

#### Step 3: Configure Your Web Server

#### Apache Configuration

Make sure your Apache server is configured to serve files from the `public` directory.

**Option A: Using XAMPP/WAMP**

1. Copy the project to your htdocs folder
2. Access via `http://localhost/mop/public/`

**Option B: Configure Virtual Host**

Add to your Apache configuration:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/Users/Faber/mop/public"
    ServerName myapp.local

    <Directory "C:/Users/Faber/mop/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to your hosts file (`C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1 myapp.local
```

### 3. Set Up Supabase Database

#### Create Users Table

Go to your Supabase project and run this SQL:

```sql
-- Create users table
CREATE TABLE users (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar_url TEXT,
    role VARCHAR(50) DEFAULT 'user',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create index on email for faster lookups
CREATE INDEX idx_users_email ON users(email);

-- Create index on role
CREATE INDEX idx_users_role ON users(role);

-- Enable Row Level Security (RLS)
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Create policies (adjust based on your needs)
-- Allow users to read their own data
CREATE POLICY "Users can view their own data"
ON users FOR SELECT
USING (auth.uid()::text = id::text);

-- Allow users to update their own data
CREATE POLICY "Users can update their own data"
ON users FOR UPDATE
USING (auth.uid()::text = id::text);

-- Allow insert for authenticated users
CREATE POLICY "Allow insert for authenticated users"
ON users FOR INSERT
WITH CHECK (true);
```

### 4. Database Configuration

The database configuration is already set in `src/config/Database.php`:

- **Supabase URL**: `https://famnnqgqobqthfeygjzx.supabase.co`
- **API Key**: Already configured in the Database class

### 5. Enable PHP Extensions

Make sure these extensions are enabled in your `php.ini`:

```ini
extension=curl
extension=openssl
extension=mbstring
```

### 6. Test the Application

1. Start your web server
2. Navigate to `http://myapp.local/` or `http://localhost/mop/public/`
3. Register a new account
4. Check your email for verification (if configured in Supabase)
5. Log in and test the features

## Usage

### User Registration

1. Navigate to `/register` or click "Sign Up"
2. Fill in the registration form
3. Submit to create your account
4. Check email for verification (if enabled)

### User Login

1. Navigate to `/login`
2. Enter your email and password
3. Access your dashboard

### Profile Management

1. Log in to your account
2. Navigate to `/profile`
3. Update your information
4. Save changes

### Change Password

1. Log in to your account
2. Navigate to `/change-password`
3. Enter your new password
4. Confirm and save

## Security Features

- **Supabase Authentication**: Enterprise-grade auth system
- **Session Management**: Secure session handling
- **HTTPS Ready**: Easy HTTPS configuration in .htaccess
- **Security Headers**: XSS protection, clickjacking prevention
- **Input Validation**: Server-side validation on all forms
- **Password Requirements**: Minimum 6 characters
- **Email Validation**: Proper email format checking

## Core PHP Classes

### User Class (`src/classes/User.php`)

Entity class representing a user with:
- Properties: id, email, fullName, phone, avatarUrl, role, isActive
- Getters and setters with validation
- Array hydration and conversion
- Built-in validation methods

### UserRepository Class (`src/classes/UserRepository.php`)

Data access layer with methods:
- `create(User $user)` - Create new user
- `findById($id)` - Find user by ID
- `findByEmail($email)` - Find user by email
- `findAll()` - Get all users
- `update(User $user)` - Update user
- `delete($id)` - Delete user
- `search($query)` - Search users
- `findByRole($role)` - Get users by role
- `count()` - Count total users

### Auth Class (`src/classes/Auth.php`)

Authentication and authorization with:
- `register()` - Register new user
- `login()` - Login user
- `logout()` - Logout user
- `isLoggedIn()` - Check login status
- `getCurrentUser()` - Get current user
- `requireAuth()` - Require authentication
- `hasRole()` - Check user role
- `updatePassword()` - Change password

### Database Class (`src/config/Database.php`)

Supabase integration with:
- Singleton pattern
- REST API requests
- Auth API requests
- Error handling

## Customization

### Styling

Edit `public/css/style.css` to customize:
- Colors (CSS variables in `:root`)
- Typography
- Layout and spacing
- Mobile breakpoints

### Branding

1. Update logo in `views/header.php`
2. Change app name from "MyApp" to your brand
3. Update colors in CSS variables
4. Add your favicon to `public/assets/`

### Adding New Features

1. Create entity class in `src/classes/`
2. Create repository class for database operations
3. Create view files in `public/`
4. Update navigation in `views/header.php`

## API Documentation

### Supabase REST API

All database operations use the Supabase REST API:

```php
// Example: Get users
$db = Database::getInstance();
$response = $db->request('GET', 'users');

// Example: Create user
$response = $db->request('POST', 'users', [
    'email' => 'user@example.com',
    'full_name' => 'John Doe'
]);
```

## Troubleshooting

### .htaccess not working

1. Make sure `mod_rewrite` is enabled in Apache
2. Check `AllowOverride All` in Apache config
3. Verify the RewriteBase path

### cURL errors

1. Enable `php_curl` extension
2. Check firewall settings
3. Verify Supabase URL is accessible

### Session issues

1. Check PHP session settings
2. Verify session directory has write permissions
3. Clear browser cookies

### Database connection errors

1. Verify Supabase URL and API key
2. Check network connectivity
3. Confirm Supabase project is active

## Production Deployment

1. **Enable HTTPS**: Uncomment HTTPS redirect in `.htaccess`
2. **Update Database Config**: Move credentials to environment variables
3. **Error Handling**: Disable error display in `php.ini`
4. **Session Security**: Set secure session cookie flags
5. **File Permissions**: Set appropriate permissions on files
6. **Backup**: Implement regular database backups

## License

This project is open-source and available for modification and distribution.

## Support

For issues and questions, please create an issue in the project repository.

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

---

Built with Core PHP and Supabase
