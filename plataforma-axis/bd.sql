-- ===================================================
-- BANCO DE DADOS UNIFICADO FINAL - AXISBD
-- ===================================================
CREATE DATABASE IF NOT EXISTS AXISBD
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE AXISBD;

-- ===================================================
-- TABELA DE USUÁRIOS
-- ===================================================
CREATE TABLE IF NOT EXISTS users (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(120) NOT NULL,
  nome_completo   VARCHAR(150) NULL,
  email           VARCHAR(255) UNIQUE NULL,
  username        VARCHAR(60) UNIQUE NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  tipo            ENUM('aluno','professor','admin') DEFAULT 'aluno',
  role            ENUM('user','moderator','admin') DEFAULT 'user',
  ocupacao        VARCHAR(100) NULL,
  idade           INT NULL,
  cep             VARCHAR(15) NULL,
  street          VARCHAR(160) NULL,
  number          VARCHAR(20) NULL,
  city            VARCHAR(120) NULL,
  foto_perfil     VARCHAR(255) NULL,
  avatar_url      VARCHAR(255) DEFAULT NULL,
  bio             TEXT DEFAULT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- ÁREAS DE CONHECIMENTO
-- ===================================================
CREATE TABLE IF NOT EXISTS areas_conhecimento (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo     VARCHAR(20) UNIQUE NOT NULL,
  nome       VARCHAR(100) NOT NULL,
  cor        VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- CONTEÚDOS
-- ===================================================
CREATE TABLE IF NOT EXISTS conteudos (
  id                       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  area_conhecimento_id     INT UNSIGNED NOT NULL,
  codigo                   VARCHAR(20) UNIQUE NOT NULL,
  titulo                   VARCHAR(255) NOT NULL,
  tipo                     ENUM('video','pdf','quiz','outro') NOT NULL,
  pontos                   INT DEFAULT 0,
  duracao_estimada_minutos INT DEFAULT 0,
  created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_conteudos_area FOREIGN KEY (area_conhecimento_id)
    REFERENCES areas_conhecimento(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- PROGRESSO DOS USUÁRIOS
-- ===================================================
CREATE TABLE IF NOT EXISTS usuario_progresso (
  id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  users_id             INT UNSIGNED NOT NULL,
  conteudo_id          INT UNSIGNED NOT NULL,
  concluido            BOOLEAN DEFAULT FALSE,
  pontos_obtidos       INT DEFAULT 0,
  data_inicio          DATETIME,
  data_conclusao       DATETIME,
  tempo_gasto_minutos  INT DEFAULT 0,
  percentual_conclusao DECIMAL(5,2) DEFAULT 0.00,
  tentativas           INT DEFAULT 0,
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_users_conteudo (users_id, conteudo_id),
  CONSTRAINT fk_up_users FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_up_conteudo FOREIGN KEY (conteudo_id) REFERENCES conteudos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- REDAÇÕES E RELACIONADOS
-- ===================================================
CREATE TABLE IF NOT EXISTS redacoes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  users_id    INT UNSIGNED NOT NULL,
  titulo      VARCHAR(255) NOT NULL,
  texto       MEDIUMTEXT,
  imagem_url  VARCHAR(255),
  status      ENUM('em_correcao','corrigida','publicada') DEFAULT 'publicada',
  scan_path   VARCHAR(255) DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_redacoes_users FOREIGN KEY (users_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comentarios_redacoes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  redacao_id  INT UNSIGNED NOT NULL,
  users_id    INT UNSIGNED NOT NULL,
  comentario  TEXT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_coment_redacao FOREIGN KEY (redacao_id) REFERENCES redacoes(id) ON DELETE CASCADE,
  CONSTRAINT fk_coment_users FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS likes_redacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  redacao_id INT UNSIGNED NOT NULL,
  users_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like_redacao (redacao_id, users_id),
  FOREIGN KEY (redacao_id) REFERENCES redacoes(id) ON DELETE CASCADE,
  FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (redacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS redacao_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  redacao_id INT UNSIGNED NOT NULL UNIQUE,
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
  FOREIGN KEY (redacao_id) REFERENCES redacoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS redacao_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  prompt TEXT NOT NULL,
  collection_text MEDIUMTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- POSTS, COMENTÁRIOS, LIKES, NOTIFICAÇÕES
-- ===================================================
CREATE TABLE IF NOT EXISTS posts (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  content    TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id    INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  content    TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS likes (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id    INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like_post (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           INT UNSIGNED NOT NULL,
  actor_user_id     INT UNSIGNED NOT NULL,
  type              ENUM('mention_post','mention_comment') NOT NULL,
  source_post_id    INT UNSIGNED NOT NULL,
  source_comment_id INT UNSIGNED NULL,
  is_read           TINYINT NOT NULL DEFAULT 0,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (source_post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (source_comment_id) REFERENCES comments(id) ON DELETE SET NULL,
  INDEX (user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- RANKING E ATIVIDADES
-- ===================================================
CREATE TABLE IF NOT EXISTS ranking (
  users_id     INT UNSIGNED PRIMARY KEY,
  pontos_total INT DEFAULT 0,
  horas_estudo INT DEFAULT 0,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ranking_users FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_atividades_semanais (
  id INT AUTO_INCREMENT PRIMARY KEY,
  users_id INT UNSIGNED NOT NULL,
  weekday TINYINT NOT NULL,
  lessons INT DEFAULT 0,
  exercises INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- DASHBOARD / ESTATÍSTICAS
-- ===================================================
CREATE TABLE IF NOT EXISTS dashboard_lessons_week (
  id INT AUTO_INCREMENT PRIMARY KEY,
  weekday TINYINT NOT NULL,
  lessons INT NOT NULL DEFAULT 0,
  created_at DATE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dashboard_posts_week (
  id INT AUTO_INCREMENT PRIMARY KEY,
  weekday TINYINT NOT NULL,
  posts INT NOT NULL DEFAULT 0,
  created_at DATE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dashboard_questions_week (
  id INT AUTO_INCREMENT PRIMARY KEY,
  this_week INT NOT NULL DEFAULT 0,
  last_week INT NOT NULL DEFAULT 0,
  created_at DATE NOT NULL
) ENGINE=InnoDB;

-- ===================================================
-- TABELA DE PDFS (necessária para os INSERTs abaixo)
-- ===================================================
CREATE TABLE IF NOT EXISTS pdfs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  caminho_arquivo VARCHAR(255) NOT NULL,
  categoria VARCHAR(100) NOT NULL,
  subcategoria VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- INSERTS DE CONTEÚDOS PDF (materiais didáticos)
-- ===================================================
-- Filosofia Antiga
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilafilosofiaantiga.pdf', 'filosofia', 'filosofia antiga'),
('Questão', 'arquivos/questoes-filosofia.pdf', 'filosofia', 'filosofia antiga');


-- Filosofia Medieval -- certo
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/filosofiamedieval.pdf', 'filosofia', 'filosofia medieval'),
('Questão', 'arquivos/questoes-medieval.pdf', 'filosofia', 'filosofia medieval');

-- Filosofia Contemporânea -- certo
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/ebookfiloconte.pdf', 'filosofia', 'filosofia contemporânea'),
('Questão', 'arquivos/exerciciosconte.pdf', 'filosofia', 'filosofia contemporânea');

-- Filosofia moderna
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilafilosofiamoderna.pdf', 'filosofia', 'filosofia moderna'),
('Questão', 'arquivos/questoesfilosofiamoderna.pdf', 'filosofia', 'filosofia moderna');


-- Brasil Colônia -- sim
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilabrasilcolonia.pdf', 'historia', 'brasil colonia'),
('Questão', 'arquivos/questoesbrasilcolonia.pdf', 'historia', 'brasil colonia');

-- Brasil República -- 
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

-- geografia falta 

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


-- Karl Marx -- 
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/marx.pdf', 'sociologia', 'karl marx'),
('Questão', 'arquivos/Exerciciosmarx.pdf', 'sociologia', 'karl marx');

-- Émile  -- 
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/sociologia_Durkheim.pdf', 'sociologia', 'durkheim'),
('Questão', 'arquivos/exerciciosdurk.pdf', 'sociologia', 'durkheim');

-- Max Weber -- apostila nao foi
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilamaxweber.pdf', 'sociologia', 'max weber'),
('Questão', 'arquivos/exerciciosmax.pdf', 'sociologia', 'max weber');



-- Ecologia
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaoecologia.pdf', 'biologia', 'ecologia'),
('Questão', 'arquivos/exercicios-de-ecologia.pdf', 'biologia', 'ecologia');

-- Biotecnologia
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/introducao-a-biotecnologia.pdf', 'biologia', 'biotecnologia'),
('Questão', 'arquivos/questôesbiotecnologia.pdf', 'biologia', 'biotecnologia');

-- genetica
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/geneticaapostila.pdf', 'biologia', 'genetica'),
('Questão', 'arquivos/lista_biologia_genetica.pdf', 'biologia', 'genetica');

-- Citologia
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/citologia.pdf', 'biologia', 'citologia'),
('Questão', 'arquivos/questoescito.pdf', 'biologia', 'citologia');

-- Evolução
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaoevolucao.pdf', 'biologia', 'evolução'),
('Questão', 'arquivos/questoesevolucao.pdf', 'biologia', 'evolução');

-- Doenças
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaodoencas.pdf', 'biologia', 'doenças'),
('Questão', 'arquivos/questoesdoencas.pdf', 'biologia', 'doenças');


-- Calorimetria
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/calorimetriaapostila.pdf', 'fisica', 'calorimetria'),
('Questão', 'arquivos/calorimetriaquestoes.pdf', 'fisica', 'calorimetria');

-- Ondulatória
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/explicacaoondulatoria.pdf', 'fisica', 'ondulatoria'),
('Questão', 'arquivos/questaoondulatoria.pdf', 'fisica', 'ondulatoria');

-- cinematica
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilacinematica.pdf', 'fisica', 'cinematica'),
('Questão', 'arquivos/questoescinematica.pdf', 'fisica', 'cinematica');

-- dinamica
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/questoesdinamica.pdf', 'fisica', 'dinamica'),
('Questão', 'arquivos/apostiladinamica.pdf', 'fisica', 'dinamica');

-- eletrodinamica
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaeletrodinamica.pdf', 'fisica', 'eletrodinamica'),
('Questão', 'arquivos/eletrodinamicaexercicios.pdf', 'fisica', 'eletrodinamica');

-- eletromagnetismo
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaeletromagnetismo.pdf', 'fisica', 'eletromagnetismo'),
('Questão', 'arquivos/questoeseletromagnetismo.pdf', 'fisica', 'eletromagnetismo');






-- quimica organica
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaquimicaorganica.pdf', 'quimica', 'quimica organica'),
('Questão', 'arquivos/questoesquimicaorganica.pdf', 'quimica', 'quimica organica');

-- quimica ambiental
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaquimicaambiental.pdf', 'quimica', 'quimica ambiental'),
('Questão', 'arquivos/questoesquimicaambiental.pdf', 'quimica', 'quimica ambiental');

-- equilibrio quimico
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaequilibrioquimico.pdf', 'quimica', 'equilibrio quimico'),
('Questão', 'arquivos/questoesequilibrioquimico.pdf', 'quimica', 'equilibrio quimico');

-- solucoes
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilasolucoes.pdf', 'quimica', 'solucoes'),
('Questão', 'arquivos/questoessolucao.pdf', 'quimica', 'solucoes');







-- Porcentagem
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'porcentagem'),
('Questão', 'arquivos/questoesporcentagem.pdf', 'matematica', 'porcentagem');

-- Estatística
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'estatistica'),
('Questão', 'arquivos/questaoestatisticamat.pdf', 'matematica', 'estatistica');

-- Probabilidade
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaprobabilidade.pdf', 'matematica', 'probabilidade'),
('Questão', 'arquivos/questoesprobabilidade.pdf', 'matematica', 'probabilidade');

-- Geometria Plana e espacial
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'geometria plana e espacial'),
('Questão', 'arquivos/questoesgeometriaplana.pdf', 'matematica', 'geometria plana e espacial'),
('Questão', 'arquivos/questoesgeometria_espacial.pdf', 'matematica', 'geometria plana e espacial');

-- Regra de Três
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaregradetres.pdf', 'matematica', 'regra de tres'),
('Questão', 'arquivos/questoesregradetres.pdf', 'matematica', 'regra de tres');

-- Interpretação Gráfica
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilap_e_gp_ga_g.pdf', 'matematica', 'interpretação grafica'),
('Questão', 'arquivos/questaointerpretacaografico.pdf', 'matematica', 'interpretação grafica');


-- redacao 
INSERT INTO pdfs (titulo, caminho_arquivo, categoria, subcategoria) VALUES
('Apostila', 'arquivos/apostilaredacao.pdf', 'linguagens', 'linguagens'),
('Conectivos', 'arquivos/conectivosenem.pdf', 'linguagens', 'linguagens'),
('Repertórios', 'arquivos/repertoriosenem.pdf', 'linguagens', 'linguagens');









