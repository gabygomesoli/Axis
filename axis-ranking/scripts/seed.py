# pip install mysql-connector-python
import mysql.connector as mysql
cfg = {"host":"127.0.0.1","user":"root","password":"","database":"axis_ranking"}
cn = mysql.connect(host=cfg["host"], user=cfg["user"], password=cfg["password"])
cur = cn.cursor()
cur.execute("CREATE DATABASE IF NOT EXISTS axis_ranking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
cur.execute("USE axis_ranking;")
schema = open("../db/schema.sql","r",encoding="utf-8").read()
for s in [x.strip() for x in schema.split(";") if x.strip()]: cur.execute(s)
seed = open("../db/seed.sql","r",encoding="utf-8").read()
for s in [x.strip() for x in seed.split(";") if x.strip()]: cur.execute(s)
cn.commit(); cur.close(); cn.close(); print("Base criada e populada com sucesso!")
