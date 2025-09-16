CREATE DATABASE IF NOT EXISTS forum_redacoes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forum_redacoes;
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  avatar_url VARCHAR(255) NULL,
  bio TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS essays (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(160) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS essay_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  essay_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (essay_id) REFERENCES essays(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (essay_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS essay_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  essay_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like (essay_id, user_id),
  FOREIGN KEY (essay_id) REFERENCES essays(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (essay_id)
) ENGINE=InnoDB;

-- Perfis/Moderação e recursos adicionais
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS role ENUM('user','moderator','admin') DEFAULT 'user';

ALTER TABLE essays
  ADD COLUMN IF NOT EXISTS status ENUM('em_correcao','corrigida','publicada') DEFAULT 'publicada',
  ADD COLUMN IF NOT EXISTS scan_path VARCHAR(255) DEFAULT NULL;

-- Rubrica de competências ENEM (auto e manual)
CREATE TABLE IF NOT EXISTS essay_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  essay_id INT NOT NULL UNIQUE,
  comp1 INT DEFAULT NULL,
  comp2 INT DEFAULT NULL,
  comp3 INT DEFAULT NULL,
  comp4 INT DEFAULT NULL,
  comp5 INT DEFAULT NULL,
  auto_comp1 INT DEFAULT NULL,
  auto_comp2 INT DEFAULT NULL,
  auto_comp3 INT DEFAULT NULL,
  auto_comp4 INT DEFAULT NULL,
  auto_comp5 INT DEFAULT NULL,
  total INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (essay_id) REFERENCES essays(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Banco de modelos de redação (proposta + coletânea)
CREATE TABLE IF NOT EXISTS essay_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  prompt TEXT NOT NULL,            -- proposta/tema
  collection_text MEDIUMTEXT NULL, -- coletânea (textos base)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Alguns templates iniciais (exemplo)
INSERT INTO essay_templates (title, prompt, collection_text) VALUES
('Caminhos para combater a epidemia da distração no Brasil', 'Disserte sobre as causas e consequências da distração na sociedade brasileira e proponha intervenções.', 'Trechos de Bauman, Arendt e dados da OMS sobre atenção e saúde mental.'),
('Educação financeira na juventude', 'Argumente sobre a importância da educação financeira no ensino médio e proponha medidas.', 'Dados do IBGE, Banco Central e exemplos de iniciativas públicas.');
