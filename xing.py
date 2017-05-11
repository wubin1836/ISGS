f = open('phone.csv')
fw = open('xing.txt','w')
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
	name_tmp = tmp[1].split()

	try:
		if is_alphabet(name_tmp[1][0]) or is_number(name_tmp[1][0]):
			if "GSM" not in name_tmp[1] and "3G" not in name_tmp[1] and "4G" not in name_tmp[1] and "CDMA" not in name_tmp[1] and "Android" not in name_tmp[1]:
				if name_tmp[1] not in ddd:
					ddd.append(name_tmp[1])
	except:
		pass

	try:
		if is_alphabet(name_tmp[2][0]) or is_number(name_tmp[2][0]):
			if "GSM" not in name_tmp[2] and "3G" not in name_tmp[2] and "4G" not in name_tmp[2] and "CDMA" not in name_tmp[2] and "Android" not in name_tmp[2]:
				if name_tmp[2] not in ddd:
					ddd.append(name_tmp[2])
	except:
		pass
for item in ddd:
	fw.write(item.upper().strip().encode('utf8')+'\n')
