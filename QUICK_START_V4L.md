# V4L - Quick Start Guide

## 🚀 Get Started in 3 Steps

### 1. Start Development Server
```bash
php -S localhost:8000 -t public
```

### 2. First Time Setup
The SQLite database will auto-create on first use with the V4L schema.
No manual setup needed!

### 3. Register Your First User
1. Go to: http://localhost:8000/register.php
2. Create an account with:
   - Username (e.g., `johndoe`)
   - Full Name
   - Email
   - Phone (optional)
   - Password

### 4. Create Your First Organization
1. Login at: http://localhost:8000/auth/login
2. Navigate to Organizations
3. Click "New Organization"
4. Fill in:
   - Short Name: "Acme Corp"
   - Legal Structure: "LLC"
   - Subdomain: "acmecorp" (auto-generated, can edit)
   - Other details (optional)
5. Save!

Your organization will be at: **acmecorp.v4l.app** 🎉

---

## 📝 Key Features

### Organization Management
- **Unique Subdomain**: Each org gets subdomain.v4l.app
- **Full Legal Name**: Short Name + Legal Structure (e.g., "Acme Corp LLC")
- **Auto-Generation**: Subdomain auto-generated from short name
- **User-Scoped**: Users only see/manage their own organizations
- **Soft Delete**: Deleted orgs go to trash, can be restored

### User Management
- **Flexible Login**: Username, email, or phone
- **Unique Username**: 3-30 chars, lowercase, alphanumeric + underscores/hyphens
- **Password Recovery**: Via email or phone
- **Session Management**: Secure session-based authentication

---

## 🗂️ Project Structure

```
mop/
├── public/                    # Web root
│   ├── register.php          # User registration
│   ├── login.php             # User login
│   ├── dashboard.php         # User dashboard
│   ├── organizations.php     # Organization list
│   ├── organization-form.php # Create/Edit organization
│   └── css/style.css         # Mobile-first styles
├── src/
│   ├── classes/              # Domain entities & repositories
│   │   ├── User.php
│   │   ├── UserRepository.php
│   │   ├── Organization.php
│   │   ├── OrganizationRepository.php
│   │   └── Auth.php
│   └── config/
│       ├── Database.php      # Database abstraction (SQLite + Supabase)
│       └── Env.php          # Environment config
├── views/
│   ├── header.php           # Common header (V4L branding)
│   └── footer.php           # Common footer
├── database/
│   ├── app.db              # SQLite database (auto-created)
│   └── v4l_migration.sql   # Supabase migration script
└── .env                    # Environment configuration
```

---

## ⚙️ Configuration

### .env File
```env
# Database Driver: sqlite or supabase
DB_DRIVER=sqlite

# SQLite (Development)
SQLITE_DB_PATH=database/app.db

# Supabase (Production)
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
```

---

## 🔄 Switching to Supabase

### 1. Run Migration
```sql
-- In Supabase SQL Editor, run:
-- database/v4l_migration.sql
```

### 2. Update .env
```env
DB_DRIVER=supabase
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
```

### 3. Test
- Register new user
- Create organization
- Verify everything works

---

## 🧪 Testing

### Test Organization Creation
```
Short Name: Tech Innovators
Legal Structure: Private Limited
Subdomain: techinnovators
→ Full Name: "Tech Innovators Private Limited"
→ URL: https://techinnovators.v4l.app
```

### Test User Login
```
Username: johndoe
Email: john@example.com
Phone: +1-555-1234

✅ Can login with: johndoe
✅ Can login with: john@example.com
✅ Can login with: +1-555-1234
```

### Test Validations
- ❌ Duplicate subdomain → Error
- ❌ Duplicate username → Error
- ❌ Invalid subdomain (uppercase, special chars) → Error
- ❌ Invalid username (uppercase, special chars) → Error

---

## 🎯 Common Tasks

### Create New User
```php
$auth = new Auth();
$auth->register('username', 'email', 'password', 'Full Name', 'phone');
```

### Login User
```php
$auth = new Auth();
// Can use username, email, or phone
$auth->login('identifier', 'password');
```

### Create Organization
```php
$org = new Organization();
$org->setShortName('Acme Corp');
$org->setLegalStructure('LLC');
$org->setSubdomain('acmecorp');
$org->setDescription('A great company');

$orgRepo = new OrganizationRepository();
$orgRepo->create($org, $userId);
```

### Find Organization
```php
$orgRepo = new OrganizationRepository();

// By ID
$org = $orgRepo->findById($id, $userId);

// All user's organizations
$orgs = $orgRepo->findAllByUser($userId);

// Search
$orgs = $orgRepo->searchByUser('query', $userId);
```

---

## 🛡️ Security

### Authentication
- ✅ Password hashing (bcrypt)
- ✅ Session-based authentication
- ✅ CSRF protection (via form tokens)
- ✅ SQL injection protection (prepared statements)

### Authorization
- ✅ Row-level security (Supabase)
- ✅ User-scoped queries (SQLite)
- ✅ Ownership verification on all operations

### Input Validation
- ✅ Client-side (HTML5 patterns)
- ✅ Server-side (PHP validation)
- ✅ Database-level (UNIQUE constraints)

---

## 📚 API Reference

### Organization Entity
```php
$org->setShortName(string)          // Required
$org->setLegalStructure(string)     // Optional
$org->setSubdomain(string)          // Required, unique, 3-63 chars
$org->setDescription(string)        // Optional
$org->setEmail(string)              // Optional
$org->setPhone(string)              // Optional
$org->setAddress(string)            // Optional
$org->setWebsite(string)            // Optional
$org->setIsActive(bool)             // Default: true

$org->getFullName()                 // Returns "Short Name Legal Structure"
$org->getUrl()                      // Returns "https://subdomain.v4l.app"
```

### User Entity
```php
$user->setUsername(string)          // Required, unique, 3-30 chars
$user->setEmail(string)             // Required, unique
$user->setFullName(string)          // Required
$user->setPhone(string)             // Optional
$user->setRole(string)              // Default: 'user'
$user->setIsActive(bool)            // Default: true
```

---

## 🐛 Troubleshooting

### SQLite Database Issues
```bash
# Delete and recreate
rm database/app.db
# Restart server - will auto-create with new schema
```

### Subdomain Already Exists
- Check if another organization uses that subdomain
- Try a different subdomain
- Format: lowercase, alphanumeric, hyphens only

### Username Already Taken
- Choose a different username
- Format: lowercase, alphanumeric, underscores, hyphens

### Can't Login
- Make sure you're using the correct identifier (username/email/phone)
- Check password is correct
- Verify account exists and is active

---

## 💡 Tips

1. **Auto-Generation**: Subdomain auto-generates from short name. Type "Acme Corp" → gets "acme-corp"
2. **Manual Override**: Click subdomain field to manually edit - auto-generation stops
3. **Flexible Login**: Use whatever you remember - username, email, or phone all work
4. **Soft Delete**: Deleted organizations go to trash - can be restored or permanently deleted
5. **Legal Structures**: Choose from common options (LLC, Inc., Ltd., Private Limited, etc.)

---

## 🎉 You're Ready!

Start building amazing organizations with V4L - Vocal 4 Local!

For detailed implementation docs, see:
- `V4L_COMPLETED_IMPLEMENTATION.md` - Full implementation summary
- `V4L_REMAINING_UPDATES.md` - Code reference
- `database/v4l_migration.sql` - Supabase migration

---

**Happy Coding! 🚀**
