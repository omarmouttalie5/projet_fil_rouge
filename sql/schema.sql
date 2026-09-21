-- Database Initialization Script
CREATE DATABASE IF NOT EXISTS crowdfunding_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crowdfunding_db;

-- 1. Table: project_category
CREATE TABLE IF NOT EXISTS project_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: project
CREATE TABLE IF NOT EXISTS project (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    goal_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    raised_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_project_category 
        FOREIGN KEY (category_id) REFERENCES project_category(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: contributeur
CREATE TABLE IF NOT EXISTS contributeur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    project_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contributeur_project 
        FOREIGN KEY (project_id) REFERENCES project(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Seed Data
INSERT INTO project_category (name, description) VALUES 
('Technology', 'Hardware, software, and innovative tech innovations.'),
('Environment', 'Green initiative, ecology, and sustainability projects.'),
('Creative Arts', 'Film, music, design, and digital artwork');

INSERT INTO project (title, description, goal_amount, raised_amount, category_id) VALUES 
('EcoSolar Charger', 'Portable solar panels for off-grid power solution.', 5000.00, 1250.00, 2),
('Open Web IDE', 'A high-performance browser code editor.', 10000.00, 3200.00, 1);

INSERT INTO contributeur (full_name, email, amount, project_id) VALUES 
('Youssef El Alami', 'youssef@example.com', 250.00, 1),
('Amina Benali', 'amina@example.com', 1000.00, 1),
('Karim Mansouri', 'karim@example.com', 3200.00, 2);