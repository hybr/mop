-- ========================================
-- User Management Application
-- Supabase Database Setup Script
-- ========================================

-- Create users table
CREATE TABLE IF NOT EXISTS users (
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

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_is_active ON users(is_active);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at DESC);

-- Enable Row Level Security (RLS)
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if they exist
DROP POLICY IF EXISTS "Users can view their own data" ON users;
DROP POLICY IF EXISTS "Users can update their own data" ON users;
DROP POLICY IF EXISTS "Allow insert for authenticated users" ON users;
DROP POLICY IF EXISTS "Admin can view all users" ON users;
DROP POLICY IF EXISTS "Admin can update all users" ON users;
DROP POLICY IF EXISTS "Admin can delete users" ON users;

-- Create RLS policies

-- Allow users to view their own data
CREATE POLICY "Users can view their own data"
ON users FOR SELECT
USING (
    auth.uid()::text = id::text
    OR
    (SELECT role FROM users WHERE id = auth.uid()::uuid) = 'admin'
);

-- Allow users to update their own data
CREATE POLICY "Users can update their own data"
ON users FOR UPDATE
USING (
    auth.uid()::text = id::text
    OR
    (SELECT role FROM users WHERE id = auth.uid()::uuid) = 'admin'
)
WITH CHECK (
    auth.uid()::text = id::text
    OR
    (SELECT role FROM users WHERE id = auth.uid()::uuid) = 'admin'
);

-- Allow insert for authenticated users (for registration)
CREATE POLICY "Allow insert for authenticated users"
ON users FOR INSERT
WITH CHECK (true);

-- Admin can delete users
CREATE POLICY "Admin can delete users"
ON users FOR DELETE
USING (
    (SELECT role FROM users WHERE id = auth.uid()::uuid) = 'admin'
);

-- Create a function to automatically update the updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create trigger to call the function
DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Optional: Create a view for active users only
CREATE OR REPLACE VIEW active_users AS
SELECT * FROM users WHERE is_active = true;

-- Optional: Create function to count users by role
CREATE OR REPLACE FUNCTION count_users_by_role(user_role VARCHAR)
RETURNS INTEGER AS $$
DECLARE
    user_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO user_count FROM users WHERE role = user_role;
    RETURN user_count;
END;
$$ LANGUAGE plpgsql;

-- Optional: Create function to get user statistics
CREATE OR REPLACE FUNCTION get_user_statistics()
RETURNS TABLE (
    total_users BIGINT,
    active_users BIGINT,
    inactive_users BIGINT,
    admin_users BIGINT,
    regular_users BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        COUNT(*)::BIGINT as total_users,
        COUNT(*) FILTER (WHERE is_active = true)::BIGINT as active_users,
        COUNT(*) FILTER (WHERE is_active = false)::BIGINT as inactive_users,
        COUNT(*) FILTER (WHERE role = 'admin')::BIGINT as admin_users,
        COUNT(*) FILTER (WHERE role = 'user')::BIGINT as regular_users
    FROM users;
END;
$$ LANGUAGE plpgsql;

-- Grant permissions (adjust as needed)
GRANT ALL ON users TO authenticated;
GRANT SELECT ON users TO anon;

-- Insert sample data (optional - comment out if not needed)
-- INSERT INTO users (email, full_name, phone, role, is_active) VALUES
-- ('admin@example.com', 'Admin User', '+1234567890', 'admin', true),
-- ('user@example.com', 'Regular User', '+0987654321', 'user', true)
-- ON CONFLICT (email) DO NOTHING;

-- ========================================
-- Organizations Table
-- ========================================

-- Create organizations table
CREATE TABLE IF NOT EXISTS organizations (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    logo_url TEXT,
    is_active BOOLEAN DEFAULT true,
    created_by UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_by UUID REFERENCES users(id),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    deleted_by UUID REFERENCES users(id),
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Create indexes for organizations
CREATE INDEX IF NOT EXISTS idx_organizations_created_by ON organizations(created_by);
CREATE INDEX IF NOT EXISTS idx_organizations_deleted_at ON organizations(deleted_at);
CREATE INDEX IF NOT EXISTS idx_organizations_name ON organizations(name);
CREATE INDEX IF NOT EXISTS idx_organizations_created_at ON organizations(created_at DESC);

-- Enable Row Level Security for organizations
ALTER TABLE organizations ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if they exist
DROP POLICY IF EXISTS "Users can view their own organizations" ON organizations;
DROP POLICY IF EXISTS "Users can create organizations" ON organizations;
DROP POLICY IF EXISTS "Users can update their own organizations" ON organizations;
DROP POLICY IF EXISTS "Users can delete their own organizations" ON organizations;

-- RLS Policies for organizations
-- Users can only see their own organizations (non-deleted)
CREATE POLICY "Users can view their own organizations"
ON organizations FOR SELECT
USING (
    created_by = auth.uid()
);

-- Users can create organizations
CREATE POLICY "Users can create organizations"
ON organizations FOR INSERT
WITH CHECK (
    created_by = auth.uid()
);

-- Users can update their own organizations (non-deleted)
CREATE POLICY "Users can update their own organizations"
ON organizations FOR UPDATE
USING (
    created_by = auth.uid()
    AND deleted_at IS NULL
)
WITH CHECK (
    created_by = auth.uid()
);

-- Users can soft delete their own organizations
CREATE POLICY "Users can delete their own organizations"
ON organizations FOR UPDATE
USING (
    created_by = auth.uid()
);

-- Create trigger for updated_at timestamp
CREATE OR REPLACE FUNCTION update_organizations_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

DROP TRIGGER IF EXISTS update_organizations_updated_at ON organizations;
CREATE TRIGGER update_organizations_updated_at
    BEFORE UPDATE ON organizations
    FOR EACH ROW
    EXECUTE FUNCTION update_organizations_updated_at();

-- ========================================
-- Setup Complete
-- ========================================

-- Verify setup
SELECT 'Database setup completed successfully!' as status;
SELECT 'Total tables created: ' || COUNT(*)::TEXT as tables_count
FROM information_schema.tables
WHERE table_schema = 'public' AND table_name IN ('users', 'organizations');
