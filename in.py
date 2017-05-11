import MySQLdb

conn=MySQLdb.connect(host='localhost',user='root',passwd='thunlp4506',db='isgs',port=3306)
cur=conn.cursor()

index = 0
f = open('q.sql')
while 1:
	line = f.readline().strip()
	if not line:
		break
	print line
	cur.execute(line)
	conn.commit()