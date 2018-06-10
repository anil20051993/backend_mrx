import requests
import json
import numpy as np
import smtplib

def find_bad_data_count():

	count=0
	for row in data:
		#print(float(row['HIGH']))
		if float(row['HIGH'])<float(row['LOW']) or float(row['HIGH'])<float(row['OPEN']) or float(row['HIGH'])<float(row['CLOSE']) or float(row['LOW'])>float(row['OPEN']) or float(row['LOW'])>float(row['CLOSE']) or float(row['VOLUME']) < 0:
			count=count+1
			#print(row)
	return count

def missing_data_count():
	count=0
	for row in data:
		if row['HIGH'] == '' or row['LOW'] == '' or row['OPEN'] == '' or row['CLOSE'] == '' or row['VOLUME'] == '' :
			count = count+1
	return count

def duplicate_count():
	count=0
	for index,row in enumerate(data):
		if index > 0:
			if lastRow == row:
				count=count+1
		lastRow = row
	return count
 
def main():

	data_len = len(data_all)
	count=0
	market_count=0
	global data
	data=[]
	text='\nData points available for each Market\n'
	market_entry=0
	available_data=0
	available_total=0;
	for item in data_all:
		count=count+1
		data.append(item)
		if count == data_len or item['MARKET'] != data_all[count]['MARKET'] :
			available_data =  (len(data)-find_bad_data_count()-missing_data_count()-duplicate_count())
			available_total=available_total+available_data
			text=text+" "+item['MARKET']+"\t"+str(available_data)+"/"+str(len(data))+"\n"
			market_count=market_count+1
			data=[]
		
	text=text+"\n\nNo. of Markets "+str(market_count)+"\nTotal data points "+str(len(data_all))+"\n%Available Data "+str((available_total/len(data_all))*100)+"%"
	
	## Sending Email
	server = smtplib.SMTP('smtp.gmail.com', 587)
	server.starttls()
	server.login("anil20051993@gmail.com", "********")
	server.sendmail("anil20051993@gmail.com", "edul@mudrex.com", text)
	server.quit()	

url = "http://eventskidunia.com/assignment/data_process/ticker_data_api.php"
response = requests.get(url)
data_all=json.loads(response.content)

###           MAIN           ###
if __name__ == "__main__": main()