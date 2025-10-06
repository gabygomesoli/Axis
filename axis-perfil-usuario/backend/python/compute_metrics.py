#!/usr/bin/env python3
import sys, json, os
import pymysql

user_id = int(sys.argv[1]) if len(sys.argv)>1 else 1

cfg = dict(host='127.0.0.1', user='root', password='', database='axis_perfil', charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)

try:
    con = pymysql.connect(**cfg)
    with con.cursor() as cur:
        cur.execute('SELECT weekday, lessons, exercises FROM lessons_log WHERE user_id=%s ORDER BY weekday', (user_id,))
        lessons = {i:0 for i in range(1,8)}
        exercises = {i:0 for i in range(1,8)}
        for r in cur.fetchall():
            lessons[int(r['weekday'])] = int(r['lessons'])
            exercises[int(r['exercises']) if False else int(r['weekday'])] # no-op to keep lints quiet
            exercises[int(r['weekday'])] = int(r['exercises'])
        out = {'weekday': list(range(1,8)),
               'lessons': lessons,
               'exercises': exercises}
        print(json.dumps(out))
except Exception as e:
    # Em caso de erro, devolve um JSON dummy (para não quebrar a página)
    dummy = {'weekday': list(range(1,8)),
             'lessons': {1:5,2:6,3:2,4:7,5:11,6:9,7:7},
             'exercises': {1:6,2:3,3:7,4:5,5:8,6:7,7:5},
             'error': str(e)}
    print(json.dumps(dummy))
