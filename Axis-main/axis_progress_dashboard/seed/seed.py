import os
import datetime as dt
import random
import mysql.connector as mysql

# -------------------------------------------------------
# CONFIGURAÇÃO DO BANCO
# -------------------------------------------------------
DB_HOST = os.getenv('DB_HOST', 'localhost')
DB_USER = os.getenv('DB_USER', 'root')
DB_PASS = os.getenv('DB_PASS', 'root')
DB_NAME = os.getenv('DB_NAME', 'AXISBD')

# Conexão MySQL
conn = mysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME)
cur = conn.cursor()

# -------------------------------------------------------
# DATA DE REFERÊNCIA (segunda-feira da semana atual)
# -------------------------------------------------------
today = dt.date.today()
monday = today - dt.timedelta(days=today.weekday())
created_at = monday

# -------------------------------------------------------
# REMOVE DADOS ANTIGOS DA MESMA SEMANA
# -------------------------------------------------------
for t in ('dashboard_lessons_week', 'dashboard_posts_week', 'dashboard_questions_week'):
    cur.execute(f"DELETE FROM {t} WHERE created_at = %s", (created_at,))

# -------------------------------------------------------
# DADOS FIXOS DE LIÇÕES E POSTS
# -------------------------------------------------------
lessons = [4, 3, 1, 3, 7, 4, 0]  # Segunda a domingo
posts   = [5, 2, 1, 4, 12, 10, 2]

# Insere registros de lições da semana
for i, val in enumerate(lessons, start=1):
    cur.execute("""
        INSERT INTO dashboard_lessons_week (weekday, lessons, created_at)
        VALUES (%s, %s, %s)
    """, (i, val, created_at))

# Insere registros de posts da semana
for i, val in enumerate(posts, start=1):
    cur.execute("""
        INSERT INTO dashboard_posts_week (weekday, posts, created_at)
        VALUES (%s, %s, %s)
    """, (i, val, created_at))

# -------------------------------------------------------
# GERA DADOS DE PERGUNTAS DA SEMANA
# -------------------------------------------------------
this_week = sum(random.randint(8, 16) for _ in range(7))
last_week = this_week - random.randint(-15, 25)

cur.execute("""
    INSERT INTO dashboard_questions_week (this_week, last_week, created_at)
    VALUES (%s, %s, %s)
""", (this_week, last_week, created_at))

# -------------------------------------------------------
# FINALIZAÇÃO
# -------------------------------------------------------
conn.commit()
cur.close()
conn.close()

print(f"✅ Seed concluído com sucesso para a semana iniciada em {created_at}")
