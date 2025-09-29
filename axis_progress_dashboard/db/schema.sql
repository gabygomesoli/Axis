-- db/schema.sql
-- Create database and tables
CREATE DATABASE IF NOT EXISTS axis_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE axis_dashboard;

-- Users table (minimal for demo)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL
) ENGINE=InnoDB;

-- Daily lessons watched (per weekday count)
CREATE TABLE IF NOT EXISTS lessons_week (
  id INT AUTO_INCREMENT PRIMARY KEY,
  weekday TINYINT NOT NULL, -- 1=Mon ... 7=Sun
  lessons INT NOT NULL DEFAULT 0,
  created_at DATE NOT NULL
) ENGINE=InnoDB;

-- Community posts per weekday
CREATE TABLE IF NOT EXISTS posts_week (
  id INT AUTO_INCREMENT PRIMARY KEY,
  weekday TINYINT NOT NULL, -- 1=Mon ... 7=Sun
  posts INT NOT NULL DEFAULT 0,
  created_at DATE NOT NULL
) ENGINE=InnoDB;

-- Questions answered this week and last week
CREATE TABLE IF NOT EXISTS questions_week (
  id INT AUTO_INCREMENT PRIMARY KEY,
  this_week INT NOT NULL DEFAULT 0,
  last_week INT NOT NULL DEFAULT 0,
  created_at DATE NOT NULL
) ENGINE=InnoDB;
