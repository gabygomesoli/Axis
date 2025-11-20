-- Criação do banco de dados e tabela para o cronograma AXIS
CREATE DATABASE IF NOT EXISTS axis_cronograma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE axis_cronograma;

CREATE TABLE IF NOT EXISTS cronograma_estudos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semana INT NOT NULL,
    dia INT NOT NULL,
    numero INT NOT NULL,
    titulo VARCHAR(255) NOT NULL
);

DELETE FROM cronograma_estudos;

INSERT INTO cronograma_estudos (semana, dia, numero, titulo) VALUES
(1, 1, 1, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 1, 2, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 1, 3, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 2, 4, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 2, 5, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 2, 6, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 3, 7, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 3, 8, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 3, 9, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 4, 10, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 4, 11, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 4, 12, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 5, 13, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 5, 14, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(1, 5, 15, 'Interpretação de textos: compreender ideias explícitas e implícitas.'),
(2, 1, 1, 'Semana 2 - Aula 1'),
(2, 1, 2, 'Semana 2 - Aula 2'),
(2, 1, 3, 'Semana 2 - Aula 3'),
(2, 2, 4, 'Semana 2 - Aula 4'),
(2, 2, 5, 'Semana 2 - Aula 5'),
(2, 2, 6, 'Semana 2 - Aula 6'),
(2, 3, 7, 'Semana 2 - Aula 7'),
(2, 3, 8, 'Semana 2 - Aula 8'),
(2, 3, 9, 'Semana 2 - Aula 9'),
(2, 4, 10, 'Semana 2 - Aula 10'),
(2, 4, 11, 'Semana 2 - Aula 11'),
(2, 4, 12, 'Semana 2 - Aula 12'),
(2, 5, 13, 'Semana 2 - Aula 13'),
(2, 5, 14, 'Semana 2 - Aula 14'),
(2, 5, 15, 'Semana 2 - Aula 15'),
(3, 1, 1, 'Semana 3 - Conteúdo 1'),
(3, 1, 2, 'Semana 3 - Conteúdo 2'),
(3, 1, 3, 'Semana 3 - Conteúdo 3'),
(3, 2, 4, 'Semana 3 - Conteúdo 4'),
(3, 2, 5, 'Semana 3 - Conteúdo 5'),
(3, 2, 6, 'Semana 3 - Conteúdo 6'),
(3, 3, 7, 'Semana 3 - Conteúdo 7'),
(3, 3, 8, 'Semana 3 - Conteúdo 8'),
(3, 3, 9, 'Semana 3 - Conteúdo 9'),
(3, 4, 10, 'Semana 3 - Conteúdo 10'),
(3, 4, 11, 'Semana 3 - Conteúdo 11'),
(3, 4, 12, 'Semana 3 - Conteúdo 12'),
(3, 5, 13, 'Semana 3 - Conteúdo 13'),
(3, 5, 14, 'Semana 3 - Conteúdo 14'),
(3, 5, 15, 'Semana 3 - Conteúdo 15');