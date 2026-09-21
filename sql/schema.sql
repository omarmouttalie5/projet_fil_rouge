CREATE DATABASE IF NOT EXISTS crowdfunding_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crowdfunding_db;

-- Table 1: Project Categories
CREATE TABLE IF NOT EXISTS project_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table 2: Projects
CREATE TABLE IF NOT EXISTS project (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    goal_amount DECIMAL(10, 2) NOT NULL,
    raised_amount DECIMAL(10, 2) DEFAULT 0.00,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES project_category(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table 3: Contributors
CREATE TABLE IF NOT EXISTS contributeur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    project_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE
) ENGINE=InnoDB;