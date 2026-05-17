/* 
 * Job Portal Database Schema
 * Full Project Requirements 
 */

CREATE DATABASE IF NOT EXISTS job_portal;
USE job_portal;

-- 1. Users Table (Core account information)
CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'seeker', 'employer', 'recruiter') NOT NULL,
    profile_pic VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Categories Table
CREATE TABLE categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

-- 3. Seeker Profiles
CREATE TABLE seeker_profiles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL UNIQUE,
    headline VARCHAR(150),
    summary TEXT,
    skills TEXT, -- comma-separated
    years_experience INT(3) DEFAULT 0,
    education_level VARCHAR(100),
    current_salary DECIMAL(10,2),
    expected_salary DECIMAL(10,2),
    preferred_location VARCHAR(100),
    resume_path VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Employer Profiles
CREATE TABLE employer_profiles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    industry VARCHAR(100),
    company_size ENUM('1-10', '11-50', '51-200', '201-500', '501-1000', '1000+') DEFAULT '1-10',
    description TEXT,
    website VARCHAR(255),
    address TEXT,
    logo_path VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Recruiter Profiles
CREATE TABLE recruiter_profiles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL UNIQUE,
    agency_name VARCHAR(150) NOT NULL,
    specialization VARCHAR(150),
    description TEXT,
    website VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Recruiter Clients
CREATE TABLE recruiter_clients (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT(11) NOT NULL,
    employer_id INT(11), -- Nullable if standalone company without an account
    company_name_override VARCHAR(150),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 7. Jobs Table
CREATE TABLE jobs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employer_id INT(11), -- Can be NULL if posted by recruiter as standalone
    recruiter_id INT(11), -- Can be NULL if posted by employer
    category_id INT(11) NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    benefits TEXT,
    salary_min DECIMAL(10,2),
    salary_max DECIMAL(10,2),
    location VARCHAR(100) NOT NULL,
    job_type ENUM('full-time', 'part-time', 'remote', 'contract') NOT NULL,
    experience_level ENUM('entry', 'mid', 'senior') NOT NULL DEFAULT 'entry',
    deadline DATE NOT NULL,
    status ENUM('active', 'closed', 'draft') DEFAULT 'active',
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Applications Table
CREATE TABLE applications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    seeker_id INT(11) NOT NULL,
    recruiter_id INT(11), -- If submitted by a recruiter on behalf of seeker
    cover_letter TEXT,
    resume_path VARCHAR(255),
    status ENUM('submitted', 'reviewed', 'shortlisted', 'interview', 'rejected', 'withdrawn') DEFAULT 'submitted',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 9. Saved Jobs
CREATE TABLE saved_jobs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    job_id INT(11) NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_save (user_id, job_id)
) ENGINE=InnoDB;

-- 10. Job Alerts
CREATE TABLE job_alerts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    seeker_id INT(11) NOT NULL,
    keyword VARCHAR(100),
    category_id INT(11),
    location VARCHAR(100),
    job_type ENUM('full-time', 'part-time', 'remote', 'contract'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 11. Recruiter Outreach
CREATE TABLE recruiter_outreach (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT(11) NOT NULL,
    seeker_id INT(11) NOT NULL,
    job_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('sent', 'read', 'responded') DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 12. Messages
CREATE TABLE messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    recipient_id INT(11) NOT NULL,
    application_id INT(11), -- Nullable, context for message
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 13. Complaints
CREATE TABLE complaints (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    submitter_id INT(11) NOT NULL,
    subject_id INT(11), -- Can be another user or job id depending on context
    description TEXT NOT NULL,
    status ENUM('open', 'resolved') DEFAULT 'open',
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submitter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default Admin Account & Categories
INSERT INTO categories (name, description) VALUES 
('Software Engineering', 'Software development and engineering roles.'),
('Data Science', 'Data analysis, machine learning, and AI.'),
('Digital Marketing', 'SEO, content creation, and digital advertising.'),
('Human Resources', 'Recruitment, HR, and talent acquisition.'),
('UI/UX Design', 'User interface and experience design.'),
('Finance', 'Accounting, financial planning, and banking.');

INSERT INTO users (name, email, password_hash, role, is_verified) 
VALUES ('System Admin', 'admin@portal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- password is 'password'

-- 14. Platform Settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value, description) VALUES 
    ('max_jobs_per_employer', '10', 'Maximum number of active jobs an employer can have.'),
    ('max_applications_per_seeker', '50', 'Maximum pending applications a seeker can have.'),
    ('resume_visibility', 'public', 'Default resume visibility (public/private).');

-- 15. Announcements
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    target_role ENUM('all', 'seeker', 'employer', 'recruiter') DEFAULT 'all',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;