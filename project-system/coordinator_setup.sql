-- ============================================================
-- COORDINATOR TABLE SETUP
-- Run this SQL once in your project_management database
-- ============================================================

-- 1. Create the coordinators table
CREATE TABLE IF NOT EXISTS coordinators (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    department    VARCHAR(200) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Insert one coordinator per department (password = Coord@123 for all)
--    The hash below corresponds to "Coord@123" via password_hash(..., PASSWORD_BCRYPT)
--    IMPORTANT: Ask each coordinator to change their password after first login.

INSERT INTO coordinators (username, password_hash, department) VALUES
('coord_ce',   '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'Computer Engineering'),
('coord_it',   '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'Information Technology'),
('coord_extc', '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'Electronics & Telecommunication'),
('coord_me',   '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'Mechanical Engineering'),
('coord_civil','$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'Civil Engineering'),
('coord_ee',   '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'Electrical Engineering'),
('coord_aids', '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'AIDS / Data Science'),
('coord_chem', '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym', 'Chemical Engineering');

-- 3. To generate a new password hash in PHP:
--    echo password_hash('YourNewPassword', PASSWORD_BCRYPT);
--
-- 4. To verify the hash matches a password (run in PHP):
--    var_dump(password_verify('Coord@123', '$2y$12$...'));

-- ============================================================
-- DEFAULT CREDENTIALS SUMMARY
-- ============================================================
-- Username          Department                        Password
-- ─────────────────────────────────────────────────────────
-- coord_ce          Computer Engineering              Coord@123
-- coord_it          Information Technology            Coord@123
-- coord_extc        Electronics & Telecommunication   Coord@123
-- coord_me          Mechanical Engineering            Coord@123
-- coord_civil       Civil Engineering                 Coord@123
-- coord_ee          Electrical Engineering            Coord@123
-- coord_aids        AI & Data Science                 Coord@123
-- coord_chem        Chemical Engineering              Coord@123
-- ============================================================
