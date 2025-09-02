-- Criação do banco e tabelas básicas
CREATE DATABASE IF NOT EXISTS forum_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forum_db;

-- Usuários
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Postagens
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comentários
CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (post_id)
) ENGINE=InnoDB;

-- Curtidas (única por usuário por post)
CREATE TABLE IF NOT EXISTS likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (post_id)
) ENGINE=InnoDB;


-- Perfis: avatar e bio
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS avatar_url VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL;

-- Notificações (menções)
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,              -- quem recebe
  actor_user_id INT NOT NULL,        -- quem mencionou
  type ENUM('mention_post','mention_comment') NOT NULL,
  source_post_id INT NOT NULL,
  source_comment_id INT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (source_post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (source_comment_id) REFERENCES comments(id) ON DELETE SET NULL,
  INDEX (user_id, is_read, created_at)
) ENGINE=InnoDB;
