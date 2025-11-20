-- Criação do banco de dados (ajuste o charset conforme necessário)
CREATE DATABASE IF NOT EXISTS axis_simulados
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE axis_simulados;

-- Tabela de arquivos do ENEM
DROP TABLE IF EXISTS enem_arquivos;

CREATE TABLE enem_arquivos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ano INT NOT NULL,
  categoria ENUM('prova','gabarito') NOT NULL,
  label VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  ordem TINYINT UNSIGNED NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dados de exemplo (ajuste as URLs para apontar para seus PDFs reais)
INSERT INTO enem_arquivos (ano, categoria, label, url, ordem) VALUES
(2025, 'prova',    '1ª Prova – Linguagens e Humanas', '/downloads/enem/2025/prova1.pdf', 1),
(2025, 'prova',    '2ª Prova – Matemática e Natureza', '/downloads/enem/2025/prova2.pdf', 2),
(2025, 'gabarito', 'Gabarito 1ª Prova', '/downloads/enem/2025/gabarito1.pdf', 1),
(2025, 'gabarito', 'Gabarito 2ª Prova', '/downloads/enem/2025/gabarito2.pdf', 2),

(2024, 'prova',    '1ª Prova – Caderno Azul', '/downloads/enem/2024/prova1.pdf', 1),
(2024, 'prova',    '2ª Prova – Caderno Azul', '/downloads/enem/2024/prova2.pdf', 2),
(2024, 'gabarito', 'Gabarito 1ª Prova', '/downloads/enem/2024/gabarito1.pdf', 1),
(2024, 'gabarito', 'Gabarito 2ª Prova', '/downloads/enem/2024/gabarito2.pdf', 2),

(2023, 'prova',    '1ª Prova', '/downloads/enem/2023/prova1.pdf', 1),
(2023, 'prova',    '2ª Prova', '/downloads/enem/2023/prova2.pdf', 2),
(2023, 'gabarito', 'Gabarito 1ª Prova', '/downloads/enem/2023/gabarito1.pdf', 1),
(2023, 'gabarito', 'Gabarito 2ª Prova', '/downloads/enem/2023/gabarito2.pdf', 2),

(2022, 'prova',    '1ª Prova', '/downloads/enem/2022/prova1.pdf', 1),
(2022, 'prova',    '2ª Prova', '/downloads/enem/2022/prova2.pdf', 2),
(2022, 'gabarito', 'Gabarito 1ª Prova', '/downloads/enem/2022/gabarito1.pdf', 1),
(2022, 'gabarito', 'Gabarito 2ª Prova', '/downloads/enem/2022/gabarito2.pdf', 2),

(2021, 'prova',    '1ª Prova – Impresso/Digital', '/downloads/enem/2021/prova1.pdf', 1),
(2021, 'prova',    '2ª Prova – Impresso/Digital', '/downloads/enem/2021/prova2.pdf', 2),
(2021, 'gabarito', 'Gabarito 1ª Prova', '/downloads/enem/2021/gabarito1.pdf', 1),
(2021, 'gabarito', 'Gabarito 2ª Prova', '/downloads/enem/2021/gabarito2.pdf', 2);
