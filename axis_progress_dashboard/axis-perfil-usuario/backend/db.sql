-- backend/db.sql
CREATE DATABASE IF NOT EXISTS axis_perfil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE axis_perfil;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  avatar_path VARCHAR(255) NULL,
  role ENUM('aluno','professor','admin') DEFAULT 'aluno',
  points INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_profile (
  user_id INT PRIMARY KEY,
  cep VARCHAR(12),
  street VARCHAR(160),
  number VARCHAR(20),
  city VARCHAR(120),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lessons_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  weekday TINYINT NOT NULL, -- 1=seg ... 7=dom
  lessons INT DEFAULT 0,
  exercises INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- seed user
INSERT INTO users (name, username, email, password_hash, role, points)
VALUES ('Beatryz Ferreira de Lima', 'beatryz_ferreirah1', 'beatryz@example.com', 
        '$2y$10$2CQQd2m3gQ9b4q4QqP2sUe8b2mBzYwT2eH9s2v3mQGk7iO8m1n6Uu', 'aluno', 147)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- senha do seed acima = 'axis123' (já hasheada)

INSERT INTO user_profile (user_id, cep, street, number, city)
VALUES (1, '06535-135', 'Rua General Julio Miranda', '75', 'Santana de Parnaíba')
ON DUPLICATE KEY UPDATE cep=VALUES(cep);

-- amostra de métricas semanais (1=seg...7=dom)
DELETE FROM lessons_log WHERE user_id=1;
INSERT INTO lessons_log (user_id, weekday, lessons, exercises) VALUES 
 (1,1,8,6),(1,2,6,3),(1,3,2,7),(1,4,7,5),(1,5,11,8),(1,6,9,7),(1,7,7,5);
