import os
import datetime as dt
import random
import mysql.connector as mysql

DB_HOST = os.getenv('DB_HOST','localhost')
DB_USER = os.getenv('DB_USER','root')
DB_PASS = os.getenv('DB_PASS','')
DB_NAME = os.getenv('DB_NAME','axis_dashboard')

conn = mysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME)
cur = conn.cursor()

# Pega a data de hoje
today = dt.date.today()
# Calcula a segunda-feira da semana atual
monday = today - dt.timedelta(days=today.weekday()) 
# Define essa segunda-feira como data de referência
created_at = monday

# Remove registros antigos da mesma semana em todas as tabelas
for t in ('lessons_week','posts_week','questions_week'):
    cur.execute(f"DELETE FROM {t} WHERE created_at=%s", (created_at,))

# Valores fixos de lições e posts da semana
lessons = [4,3,1,3,7,4,0]
posts   = [5,2,1,4,12,10,2]

# Insere registros de aulas da semana na tabela lessons_week
for i, val in enumerate(lessons, start=1):
    cur.execute("INSERT INTO lessons_week (weekday, lessons, created_at) VALUES (%s,%s,%s)",
                (i, val, created_at))

# Insere registros de posts da semana na tabela posts_week
for i, val in enumerate(posts, start=1):
    cur.execute("INSERT INTO posts_week (weekday, posts, created_at) VALUES (%s,%s,%s)",
                (i, val, created_at))

# Gera número aleatório de perguntas da semana atual (somatório de 7 dias)
this_week = sum(random.randint(8,16) for _ in range(7))
# Calcula perguntas da semana anterior com diferença aleatória (+/-)
last_week = this_week - random.randint(-15, 25)

# Insere registro de perguntas na tabela questions_week
cur.execute("INSERT INTO questions_week (this_week, last_week, created_at) VALUES (%s,%s,%s)",
            (this_week, last_week, created_at))

# Confirma todas as alterações no banco
conn.commit()
cur.close(); conn.close()
print("Seed complete for week starting", created_at)
