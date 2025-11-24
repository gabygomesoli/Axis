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

today = dt.date.today()
monday = today - dt.timedelta(days=today.weekday()) 
created_at = monday

for t in ('lessons_week','posts_week','questions_week'):
    cur.execute(f"DELETE FROM {t} WHERE created_at=%s", (created_at,))

lessons = [4,3,1,3,7,4,0]
posts   = [5,2,1,4,12,10,2]

for i, val in enumerate(lessons, start=1):
    cur.execute("INSERT INTO lessons_week (weekday, lessons, created_at) VALUES (%s,%s,%s)",
                (i, val, created_at))

for i, val in enumerate(posts, start=1):
    cur.execute("INSERT INTO posts_week (weekday, posts, created_at) VALUES (%s,%s,%s)",
                (i, val, created_at))

this_week = sum(random.randint(8,16) for _ in range(7))
last_week = this_week - random.randint(-15, 25)

cur.execute("INSERT INTO questions_week (this_week, last_week, created_at) VALUES (%s,%s,%s)",
            (this_week, last_week, created_at))

conn.commit()
cur.close(); conn.close()
print("Seed complete for week starting", created_at)