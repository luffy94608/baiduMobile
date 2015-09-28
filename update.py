import os,sys
import re

paramErr = 'error !!!\nneed params : release= -r; debug= -d; loc= -l;'
argCount = len(sys.argv);
if(argCount == 1):
	print paramErr
else:
	param = sys.argv[1]
	typeMap = {'-r':'0','-d':'1','-l':'2'}
	type = typeMap.get(str(param))
	if(type == None):
		print paramErr
	else:
		filename = "./hollo/config/server_type.ini"
		data2=''
		if(os.path.exists(filename)):
			file = open(filename,'r+')
			data = file.read()
			data2 = re.sub(r"type=.*", 'type='+type, data)
			print data2
			file.flush() 
			file.close()
		else:
			data2 = '[production]\r\ntype='+type+'\r\n;release=0; debug=1; loc=2;'
		
		open(filename,'w').write(data2)
			
		os.system('git pull')