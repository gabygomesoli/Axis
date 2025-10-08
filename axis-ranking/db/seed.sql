USE axis_ranking;
INSERT INTO users (name, username, score, trend) VALUES
('Gaby G. de Oliveira', 'gaby', 980,  0),
('Bia Ferreira',         'bia',  860,  1),
('Iara Sophie Brasil',   'iara', 820,  1),
('Você',                 'voce', 790,  1),
('João Ribeiro',         'joao', 710, -1);
INSERT INTO goals (title, target_value, color) VALUES
('Aulas assistidas', 4, '#FF79B0'),
('Questões respondidas', 15, '#FFE32D'),
('Posts na comunidade', 10, '#FF79B0');
INSERT INTO goal_progress (user_id, goal_id, current_value) VALUES
(4, 1, 2),(4, 2, 10),(4, 3, 6);
INSERT INTO badges (title, icon, position) VALUES
('Explorador I','🟢',1),('Comentarista I','🟠',2),
('Contribuinte I','⚪',3),('Contribuinte II','⚪',4),
('Contribuinte III','⚪',5),('Contribuinte IV','⚪',6),
('Contribuinte V','⚪',7),('Contribuinte VI','⚪',8);
INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES
(4,1,NOW() - INTERVAL 7 DAY),(4,2,NOW() - INTERVAL 1 DAY);
