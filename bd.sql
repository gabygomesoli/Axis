CREATE DATABASE IF NOT EXISTS plataforma;
USE plataforma;

-- =========================
-- USUÁRIOS
-- =========================
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome_completo VARCHAR(150) NOT NULL,
  nome_usuario VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('aluno','professor') NOT NULL,
  cep CHAR(8) NOT NULL,
  foto_perfil VARCHAR(255) DEFAULT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE usuarios
  ADD COLUMN auth_provider ENUM('local', 'google') NOT NULL DEFAULT 'local',
  ADD COLUMN google_sub VARCHAR(255) DEFAULT NULL,
  ADD UNIQUE KEY uniq_google_sub (google_sub);

CREATE TABLE IF NOT EXISTS user_profile (
  user_id INT PRIMARY KEY,
  street VARCHAR(150),
  number VARCHAR(20),
  city   VARCHAR(150),
  CONSTRAINT fk_profile_usuario
    FOREIGN KEY (user_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- COMUNIDADE: POSTS / COMMENTS / LIKES / NOTIFICAÇÕES
-- =========================

-- Postagens
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários
CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Curtidas (1 por usuário por post)
CREATE TABLE IF NOT EXISTS likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notificações (menções em post ou comentário)
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,              -- quem recebe
  actor_user_id INT NOT NULL,        -- quem mencionou
  type ENUM('mention_post','mention_comment') NOT NULL,
  source_post_id INT NOT NULL,
  source_comment_id INT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (source_post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (source_comment_id) REFERENCES comments(id) ON DELETE SET NULL,
  INDEX (user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- REDAÇÕES + IA
-- =========================
CREATE TABLE IF NOT EXISTS redacoes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,  -- SEM UNSIGNED, igual a usuarios.id
  titulo      VARCHAR(255) NOT NULL,
  texto       MEDIUMTEXT NOT NULL,
  imagem_url  VARCHAR(255),
  status      ENUM('em_correcao','corrigida','publicada') DEFAULT 'publicada',
  scan_path   VARCHAR(255) DEFAULT NULL,

  -- NOVOS CAMPOS PARA IA
  nota_geral_ia INT DEFAULT NULL,
  competencias_ia JSON DEFAULT NULL,   -- { "C1": {...}, "C2": {...} }
  sugestoes_ia JSON DEFAULT NULL,      -- ["Sugestão 1", "Sugestão 2", ...]

  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_redacoes_usuarios
    FOREIGN KEY (user_id)
    REFERENCES usuarios(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários em redações
CREATE TABLE IF NOT EXISTS comentarios_redacoes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  redacao_id  INT NOT NULL,
  user_id     INT NOT NULL,
  comentario  TEXT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_coment_redacao
    FOREIGN KEY (redacao_id)
    REFERENCES redacoes(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_coment_usuarios
    FOREIGN KEY (user_id)
    REFERENCES usuarios(id)
    ON DELETE CASCADE,
  INDEX (redacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Curtidas de redações
CREATE TABLE IF NOT EXISTS likes_redacoes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  redacao_id  INT NOT NULL,
  user_id     INT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like_redacao (redacao_id, user_id),
  CONSTRAINT fk_like_redacoes
    FOREIGN KEY (redacao_id)
    REFERENCES redacoes(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_like_usuarios
    FOREIGN KEY (user_id)
    REFERENCES usuarios(id)
    ON DELETE CASCADE,
  INDEX (redacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notas ENEM das redações (humano + IA)
CREATE TABLE IF NOT EXISTS redacao_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  redacao_id INT NOT NULL UNIQUE,

  -- Notas atribuídas por corretor humano
  comp1 INT DEFAULT NULL,
  comp2 INT DEFAULT NULL,
  comp3 INT DEFAULT NULL,
  comp4 INT DEFAULT NULL,
  comp5 INT DEFAULT NULL,

  -- Notas atribuídas pela IA (auto)
  auto_comp1 INT DEFAULT NULL,
  auto_comp2 INT DEFAULT NULL,
  auto_comp3 INT DEFAULT NULL,
  auto_comp4 INT DEFAULT NULL,
  auto_comp5 INT DEFAULT NULL,

  -- Total humano
  total INT DEFAULT NULL,

  -- NOVO: total calculado pela IA
  auto_total INT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_scores_redacao
    FOREIGN KEY (redacao_id)
    REFERENCES redacoes(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modelos de proposta de redação
CREATE TABLE IF NOT EXISTS redacao_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  prompt TEXT NOT NULL,
  collection_text MEDIUMTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- LOG DE AULAS / PROGRESSO
-- =========================

CREATE TABLE IF NOT EXISTS lessons_log (
  user_id INT NOT NULL,
  weekday TINYINT NOT NULL,      -- 1 a 7 (seg a dom)
  lessons INT NOT NULL DEFAULT 0,
  exercises INT NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, weekday),
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajuste do lessons_log para ter ID e created_at e índice
ALTER TABLE lessons_log
  DROP PRIMARY KEY,
  ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
  ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER exercises,
  ADD INDEX idx_lessons_user_created (user_id, created_at);

-- =========================
-- TABELA DE PDFs (CONTEÚDOS)
-- =========================
CREATE TABLE IF NOT EXISTS pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia VARCHAR(50),
    tipo ENUM('apostila', 'questao'),
    titulo VARCHAR(150),
    caminho_arquivo VARCHAR(255),
    categoria VARCHAR(100) NOT NULL,
    subcategoria VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Filosofia Antiga
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilafilosofiaantiga.pdf', 'filosofia', 'filosofia antiga'),
('Questão', 'arquivos/questoes-filosofia.pdf', 'filosofia', 'filosofia antiga');

-- Filosofia Medieval
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/filosofiamedieval.pdf', 'filosofia', 'filosofia medieval'),
('Questão', 'arquivos/questoes-medieval.pdf', 'filosofia', 'filosofia medieval');

-- Filosofia Contemporânea
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/ebookfiloconte.pdf', 'filosofia', 'filosofia contemporânea'),
('Questão', 'arquivos/exerciciosconte.pdf', 'filosofia', 'filosofia contemporânea');

-- Filosofia moderna
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilafilosofiamoderna.pdf', 'filosofia', 'filosofia moderna'),
('Questão', 'arquivos/questoesfilosofiamoderna.pdf', 'filosofia', 'filosofia moderna');

-- Brasil Colônia
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilabrasilcolonia.pdf', 'historia', 'brasil colonia'),
('Questão', 'arquivos/questoesbrasilcolonia.pdf', 'historia', 'brasil colonia');

-- Brasil República
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilabrasilrepublica.pdf', 'historia', 'republica'),
('Questão', 'arquivos/questoesrepublica.pdf', 'historia', 'republica');

-- Idade Moderna
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaidademoderna.pdf', 'historia', 'idade moderna'),
('Questão', 'arquivos/questoesidademoderna.pdf', 'historia', 'idade moderna');

-- História Geral
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilahistoriageral.pdf', 'historia', 'história-geral'),
('Questão', 'arquivos/questoeshistoriageral.pdf', 'historia', 'história-geral');

-- Geografia
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilageopolitica.pdf', 'geografia', 'geopolitica'),
('Questão', 'arquivos/questoesgeopolitica.pdf', 'geografia', 'geopolitica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaurbanizacao.pdf', 'geografia', 'urbanizacao'),
('Questão', 'arquivos/questoesurbanizacao.pdf', 'geografia', 'urbanizacao');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilameioambiente.pdf', 'geografia', 'meio ambiente'),
('Questão', 'arquivos/questoesmeioambiente.pdf', 'geografia', 'meio ambiente');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilageografiafisica.pdf', 'geografia', 'geografia fisica'),
('Questão', 'arquivos/questoesgeografiafisica.pdf', 'geografia', 'geografia fisica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilacartografia.pdf', 'geografia', 'cartografia'),
('Questão', 'arquivos/questoescartografia.pdf', 'geografia', 'cartografia');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaleituramapa.pdf', 'geografia', 'leitura de mapa'),
('Questão', 'arquivos/questoesleituramapa.pdf', 'geografia', 'leitura de mapa');

-- Sociologia
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/marx.pdf', 'sociologia', 'karl marx'),
('Questão', 'arquivos/Exerciciosmarx.pdf', 'sociologia', 'karl marx');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/sociologia_Durkheim.pdf', 'sociologia', 'durkheim'),
('Questão', 'arquivos/exerciciosdurk.pdf', 'sociologia', 'durkheim');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilamaxweber.pdf', 'sociologia', 'max weber'),
('Questão', 'arquivos/exerciciosmax.pdf', 'sociologia', 'max weber');

-- Biologia
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaoecologia.pdf', 'biologia', 'ecologia'),
('Questão', 'arquivos/exercicios-de-ecologia.pdf', 'biologia', 'ecologia');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/introducao-a-biotecnologia.pdf', 'biologia', 'biotecnologia'),
('Questão', 'arquivos/questôesbiotecnologia.pdf', 'biologia', 'biotecnologia');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/geneticaapostila.pdf', 'biologia', 'genetica'),
('Questão', 'arquivos/lista_biologia_genetica.pdf', 'biologia', 'genetica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/citologia.pdf', 'biologia', 'citologia'),
('Questão', 'arquivos/questoescito.pdf', 'biologia', 'citologia');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaoevolucao.pdf', 'biologia', 'evolução'),
('Questão', 'arquivos/questoesevolucao.pdf', 'biologia', 'evolução');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaodoencas.pdf', 'biologia', 'doenças'),
('Questão', 'arquivos/questoesdoencas.pdf', 'biologia', 'doenças');

-- Física
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/calorimetriaapostila.pdf', 'fisica', 'calorimetria'),
('Questão', 'arquivos/calorimetriaquestoes.pdf', 'fisica', 'calorimetria');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaoondulatoria.pdf', 'fisica', 'ondulatoria'),
('Questão', 'arquivos/questaoondulatoria.pdf', 'fisica', 'ondulatoria');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilacinematica.pdf', 'fisica', 'cinematica'),
('Questão', 'arquivos/questoescinematica.pdf', 'fisica', 'cinematica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/questoesdinamica.pdf', 'fisica', 'dinamica'),
('Questão', 'arquivos/apostiladinamica.pdf', 'fisica', 'dinamica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaeletrodinamica.pdf', 'fisica', 'eletrodinamica'),
('Questão', 'arquivos/eletrodinamicaexercicios.pdf', 'fisica', 'eletrodinamica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaeletromagnetismo.pdf', 'fisica', 'eletromagnetismo'),
('Questão', 'arquivos/questoeseletromagnetismo.pdf', 'fisica', 'eletromagnetismo');

-- Química
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaquimicaorganica.pdf', 'quimica', 'quimica organica'),
('Questão', 'arquivos/questoesquimicaorganica.pdf', 'quimica', 'quimica organica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaquimicaambiental.pdf', 'quimica', 'quimica ambiental'),
('Questão', 'arquivos/questoesquimicaambiental.pdf', 'quimica', 'quimica ambiental');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaequilibrioquimico.pdf', 'quimica', 'equilibrio quimico'),
('Questão', 'arquivos/questoesequilibrioquimico.pdf', 'quimica', 'equilibrio quimico');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilasolucoes.pdf', 'quimica', 'solucoes'),
('Questão', 'arquivos/questoessolucao.pdf', 'quimica', 'solucoes');

-- Matemática
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'porcentagem'),
('Questão', 'arquivos/questoesporcentagem.pdf', 'matematica', 'porcentagem');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'estatistica'),
('Questão', 'arquivos/questaoestatisticamat.pdf', 'matematica', 'estatistica');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaprobabilidade.pdf', 'matematica', 'probabilidade'),
('Questão', 'arquivos/questoesprobabilidade.pdf', 'matematica', 'probabilidade');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'geometria plana e espacial'),
('Questão', 'arquivos/questoesgeometriaplana.pdf', 'matematica', 'geometria plana e espacial'),
('Questão', 'arquivos/questoesgeometria_espacial.pdf', 'matematica', 'geometria plana e espacial');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaregradetres.pdf', 'matematica', 'regra de tres'),
('Questão', 'arquivos/questoesregradetres.pdf', 'matematica', 'regra de tres');

INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'interpretação grafica'),
('Questão', 'arquivos/questaointerpretacaografico.pdf', 'matematica', 'interpretação grafica');

-- Redação / Linguagens
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaredacao.pdf', 'linguagens', 'linguagens'),
('Conectivos', 'arquivos/conectivosenem.pdf', 'linguagens', 'linguagens'),
('Repertórios', 'arquivos/repertoriosenem.pdf', 'linguagens', 'linguagens');

-- =========================
-- PROGRESSO DE AULAS / QUESTÕES
-- =========================

-- Cada aula marcada como "concluída"
CREATE TABLE IF NOT EXISTS lesson_progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  categoria   VARCHAR(50)  NOT NULL,   -- ex: 'matematica'
  subcategoria VARCHAR(100) NOT NULL,  -- ex: 'estatistica'
  lesson_key  VARCHAR(50)  NOT NULL,   -- ex: 'estatistica_aula1'
  completed   TINYINT(1) NOT NULL DEFAULT 0,
  completed_at DATE DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lesson_progress_user
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_lesson_user (user_id, lesson_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Questões concluídas por tema
CREATE TABLE IF NOT EXISTS question_progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  categoria   VARCHAR(50)  NOT NULL,
  subcategoria VARCHAR(100) NOT NULL,
  completed   TINYINT(1) NOT NULL DEFAULT 0,
  questions_count INT NOT NULL DEFAULT 0,
  completed_at DATE DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_question_progress_user
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_question_user (user_id, categoria, subcategoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cronograma_estudos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semana INT NOT NULL,
    dia INT NOT NULL,
    numero INT NOT NULL,
    titulo VARCHAR(255) NOT NULL
);

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

