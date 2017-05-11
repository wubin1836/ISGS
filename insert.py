import MySQLdb,re

conn=MySQLdb.connect(host='localhost',user='root',passwd='thunlp4506',db='isgs',port=3306)
cur=conn.cursor()

index = 0
f = open('phone.csv')
while 1:
	line = f.readline().decode('utf8')
	if not line:
		break
	tmp = line.split(',')

	try:
		value = [int(tmp[0]),tmp[1],tmp[2],int(tmp[5]),0,100,tmp[7],int(tmp[3]),int(tmp[4])]
		tt = tmp[6].split('-')
		if len(tt) == 1:
			temp = re.findall(r'\d+\.?\d*', tt[0])
			value[4] = float(temp[0])
			value[5] = float(temp[0])
		else:
			temp = re.findall(r'\d+\.?\d*', tt[0])
			value[4] = float(temp[0])
			temp = re.findall(r'\d+\.?\d*', tt[1])
			value[5] = float(temp[0])
		sql = "insert into Product values(%d,'%s','%s',%d,%f,%f,'%s',%d,%d);"%(value[0],value[1],value[2],value[3],value[4],value[5],value[6],value[7],value[8])
		print sql
		cur.execute(sql)
		conn.commit()
	except:
		pass
	
cur.close()
conn.close()

