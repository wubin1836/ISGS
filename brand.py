#coding=utf8
f = open('phone.csv')
fw = open('brand.txt','w')
ddd = []

def is_number(uchar):
	if uchar >= '0' and uchar<='9':
		return True
	else:
		return False
 
def is_alphabet(uchar):
	if (uchar >= 'a' and uchar<='z') or (uchar >= 'A' and uchar<='Z'):
		return True
	else:
		return False

while 1:
	line = f.readline().decode('utf8')
	if not line:
		break
	tmp = line.split(',')
	brand_tmp = tmp[2].split(u'（')

	if len(brand_tmp) == 1:
		if brand_tmp[0].upper() not in ddd:
			ddd.append(brand_tmp[0].upper())
	else:
		for item in brand_tmp:
			item = item.replace(u'）','')
			if item.upper() not in ddd:
				ddd.append(item.upper())


for item in ddd:
	fw.write(item.encode('utf8').strip()+'\n')


