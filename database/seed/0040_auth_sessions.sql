-- ===========================
-- Auth Sessions Table Schema
-- ===========================
CREATE TABLE IF NOT EXISTS auth_sessions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL,
    email TEXT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expires_at TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===========================
-- Auth Sessions Seed Data
-- ===========================
-- Note: Auth sessions are typically not seeded as they are created at runtime during authentication
-- This table will be populated when users log in