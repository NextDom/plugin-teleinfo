#!/usr/bin/python
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4
 
""" Read one teleinfo frame and output the frame in CSV format on stdout
"""
 
import serial
import os
import time
import traceback
import logging
import sys
from optparse import OptionParser
from datetime import datetime
import subprocess
import urllib2
import threading
import signal 
 
# Default log level
gLogLevel = logging.DEBUG
 
# Device name
gDeviceName = '/dev/ttyUSB0'
# Default output is stdout
gOutput = sys.__stdout__
gExternalIP = ''
gCleAPI = ''
gDebug = ''
gRealPath = ''
gVitesse = ''
gMessageTemp = ''
gCanStart = 'true'
# ----------------------------------------------------------------------------
# LOGGING
# ----------------------------------------------------------------------------
class MyLogger:
	""" Our own logger """
 
	def __init__(self):
		program_path = os.path.dirname(os.path.realpath(__file__))
		self._logger = logging.getLogger('teleinfo')
		hdlr = logging.FileHandler(program_path + '/../../../log/teleinfo_deamon')
		formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
		hdlr.setFormatter(formatter)
		self._logger.addHandler(hdlr) 
		self._logger.setLevel(gLogLevel)


	def debug(self, text):
		try:
			self._logger.debug(text)
			#print text
		except NameError:
			pass
 
	def info(self, text):
		try:
			#global gMessageTemp
			text = text.replace("'", "")
			#gMessageTemp += str(text) + "**"
			#print text
			self._logger.info(text)
		except NameError:
			pass

	def warning(self, text):
		try:
			#global gMessageTemp
			text = text.replace("'", "")
			#gMessageTemp += str(text) + "**"
			#print text
			self._logger.warn(text)
		except NameError:
			pass
 
	def error(self, text):
		try:
			#global gMessageTemp
			text = text.replace("'", "")
			#gMessageTemp += str(text) + "**"
			#print text
			self._logger.error(text)
		except NameError:
			pass


# ----------------------------------------------------------------------------
# Exception
# ----------------------------------------------------------------------------
class TeleinfoException(Exception):
	"""
	Teleinfo exception
	"""
 
	def __init__(self, value):
		Exception.__init__(self)
		self.value = value
 
	def __str__(self):
		return repr(self.value)
 
 
# ----------------------------------------------------------------------------
# Teleinfo core
# ----------------------------------------------------------------------------
class Teleinfo:
	""" Fetch teleinformation datas and call user callback
	each time all data are collected
	"""
 
	def __init__(self, device, externalip, cleapi, debug, realpath, vitesse):
		""" @param device : teleinfo modem device path
		@param log : log instance
		@param callback : method to call each time all data are collected
		The datas will be passed using a dictionnary
		"""
		self._log = MyLogger()
		self._device = device
		self._externalip = externalip
		self._cleAPI = cleapi
		self._debug = debug
		self._realpath = realpath
		self._vitesse = vitesse
		self._ser = None
		#self._stop = Event()
 
	def open(self):
		""" open teleinfo modem device
		"""
		try:
			self._log.info("Try to open Teleinfo modem '%s' with speed '%s'" % (self._device, self._vitesse))
			# if(self.vitesse == '9600'):
			self._ser = serial.Serial(self._device, self._vitesse, bytesize=7, parity = 'E', stopbits=1)
			# else:
				# self._ser = serial.Serial(self._device, 1200, bytesize=7, parity = 'E', stopbits=1)
			self._log.info("Teleinfo modem successfully opened")
		except:
			#error = "Error opening Teleinfo modem '%s' : %s" % (self._device, traceback.format_exc())
			self._log.error("Error opening Teleinfo modem '%s' : %s" % (self._device, traceback.format_exc()))
			raise TeleinfoException(error)
 
	def close(self):
		""" close telinfo modem
		"""
		self._log.info("Try to close Teleinfo modem")
		#self._stop.set()
		if self._ser != None  and self._ser.isOpen():
			self._ser.close()
			self._log.info("Teleinfo modem successfully closed")
 
	def terminate(self):
		print "Terminating..."
		self.close()
		#sys.close(gOutput)
		sys.exit()
 
	def read(self):
		""" Fetch one full frame for serial port
		If some part of the frame is corrupted,
		it waits until th enext one, so if you have corruption issue,
		this method can take time but it enures that the frame returned is valid
		@return frame : list of dict {name, value, checksum}
		"""
		#Get the begin of the frame, markde by \x02
		resp = self._ser.readline()
		is_ok = False
		#frame = []
		#frameCsv = []
		Content = {}
		while not is_ok:
			try:
				while '\x02' not in resp:
					resp = self._ser.readline()
				#\x02 is in the last line of a frame, so go until the next one
				#print "* Begin frame"
				resp = self._ser.readline()
				#A new frame starts
				#\x03 is the end of the frame
				while '\x03' not in resp:
					#Don't use strip() here because the checksum can be ' '
					if len(resp.replace('\r','').replace('\n','').split()) == 2:
						#The checksum char is ' '
						name, value = resp.replace('\r','').replace('\n','').split()
						checksum = ' '
					else:
						name, value, checksum = resp.replace('\r','').replace('\n','').split()
						#print "name : %s, value : %s, checksum : %s" % (name, value, checksum)
					if self._is_valid(resp, checksum):
						#frame.append({"name" : name, "value" : value, "checksum" : checksum})
						#frameCsv.append(value)
						Content[name] = value;
					else:
						self._log.error("** FRAME CORRUPTED !")
						#This frame is corrupted, we need to wait until the next one
						#frame = []
						#frameCsv = []
						while '\x02' not in resp:
							resp = self._ser.readline()
						self._log.error("* New frame after corrupted")
					resp = self._ser.readline()
				#\x03 has been detected, that's the last line of the frame
				if len(resp.replace('\r','').replace('\n','').split()) == 2:
					#print "* End frame"
					#The checksum char is ' '
					name, value = resp.replace('\r','').replace('\n','').replace('\x02','').replace('\x03','').split()
					checksum = ' '
				else:
					name, value, checksum = resp.replace('\r','').replace('\n','').replace('\x02','').replace('\x03','').split()
				if self._is_valid(resp, checksum):
					#frame.append({"name" : name, "value" : value, "checksum" : checksum})
					#frameCsv.append(value)
					#print "* End frame, is valid : %s" % frame
					is_ok = True
				else:
					self._log.error("** Last frame invalid")
					resp = self._ser.readline()
			except ValueError:
				#Badly formatted frame
				#This frame is corrupted, we need to wait until the next one
				#frame = []
				#frameCsv = []
				while '\x02' not in resp:
					resp = self._ser.readline()
		#self._log.info(Content)
		return Content
 
	def _is_valid(self, frame, checksum):
		""" Check if a frame is valid
		@param frame : the full frame
		@param checksum : the frame checksum
		"""
		#print "Check checksum : f = %s, chk = %s" % (frame, checksum)
		datas = ' '.join(frame.split()[0:2])
		my_sum = 0
		for cks in datas:
			my_sum = my_sum + ord(cks)
		computed_checksum = ( my_sum & int("111111", 2) ) + 0x20
		#print "computed_checksum = %s" % chr(computed_checksum)
		return chr(computed_checksum) == checksum
 
	def run(self):
		""" Main function
		"""
		Donnees = {}
		_Donnees = {}
		_RAZ = datetime.now()
		_RazCalcul = 0
		_Separateur = " "
		_SendData = ""
		#global gMessageTemp
		
		def target():
			self.process = None
			#logger.debug("Thread started, timeout = " + str(timeout)+", command : "+str(self.cmd))
			self.process = subprocess.Popen(self.cmd + _SendData, shell=True)
			#print self.cmd
			self.process.communicate()
			#logger.debug("Return code: " + str(self.process.returncode))
			#logger.debug("Thread finished")
			self.timer.cancel()
		
		def timer_callback():
			#logger.debug("Thread timeout, terminate it")
			if self.process.poll() is None:
				try:
					self.process.kill()
				except OSError as error:
					#logger.error("Error: %s " % error)
					self._log.error("Error: %s " % error)
				self._log.warning("Thread terminated")
			else:
				self._log.warning("Thread not alive")
				
		# Open Teleinfo modem
		try:
			self.open()
		except TeleinfoException as err:
			self._log.error(err.value)
			#print err.value
			self.terminate()
			return
		# Read a frame
		while(1):
			_RazCalcul = datetime.now() - _RAZ
			if(_RazCalcul.seconds > 3600):
				_RAZ = datetime.now()
				for cle, valeur in Donnees.items():
					Donnees.pop(cle)
					_Donnees.pop(cle)
			_SendData = ""
			frameCsv = self.read()
			for cle, valeur in frameCsv.items():
				if(cle == 'PTEC'):
					valeur = valeur.replace(".","")
					valeur = valeur.replace(")","")
					Donnees[cle] = valeur
				elif(cle == 'OPTARIF'):
					valeur = valeur.replace(".","")
					valeur = valeur.replace(")","")
					Donnees[cle] = valeur
				else:
					Donnees[cle] = valeur
			if(self._externalip != ""):
				self.cmd = "curl -L -s -G --max-time 15 " + self._externalip +"/plugins/teleinfo/core/php/jeeTeleinfo.php -d 'api=" + self._cleAPI
				#self.cmd = "curl -L -s " + "192.168.1.150" +'/plugins/teleinfo/core/php/jeeTeleinfo.php?api=' + self._cleAPI
				_Separateur = "&"
			else:
				#self.cmd = "curl -L -s -G " + self._externalip +"/plugins/teleinfo/core/php/jeeTeleinfo.php -d 'api=" + self._cleAPI
				self.cmd = 'nice -n 19 timeout 15 /usr/bin/php ' + self._realpath + '/../php/jeeTeleinfo.php api=' + self._cleAPI
				_Separateur = " "
			
			for cle, valeur in Donnees.items():
				if(cle in _Donnees):
					if (Donnees[cle] != _Donnees[cle]):
						_SendData += _Separateur + cle +'='+ valeur
						_Donnees[cle] = valeur
				else:
					_SendData += _Separateur + cle +'='+ valeur
					_Donnees[cle] = valeur
			
			#response = urllib2.urlopen(self.cmd)
			if (_SendData != ""):
				_SendData += _Separateur + "ADCO=" + Donnees["ADCO"]
				#self.cmd += _SendData
				if (self._debug == '1'):
					print self.cmd
					#print ""
					self._log.debug(self.cmd + _SendData)
				if(self._externalip != ""):
					try:
						_SendData += "'"
						thread = threading.Thread(target=target)
						self.timer = threading.Timer(int(5), timer_callback)
						self.timer.start()
						thread.start()
						#response = urllib2.urlopen(self.cmd)
					except Exception, e:
						errorCom = "Connection error '%s'" % e
				else:
					try:
						thread = threading.Thread(target=target)
						self.timer = threading.Timer(int(5), timer_callback)
						self.timer.start()
						thread.start()
						#self.process = subprocess.Popen(self.cmd, shell=True)
						#self.process.communicate()
					except Exception, e:
						errorCom = "Connection error '%s'" % e
		# This is the End!
		self.terminate()
	def exit_handler(self, *args):
		self.terminate()
		self._log.info("[exit_handler]")

#------------------------------------------------------------------------------
# MAIN
#------------------------------------------------------------------------------
if __name__ == "__main__":
	usage = "usage: %prog [options]"
	parser = OptionParser(usage)
	parser.add_option("-o", "--output", dest="filename", help="append result in FILENAME")
	parser.add_option("-p", "--port", dest="port", help="port du modem")
	parser.add_option("-e", "--externalip", dest="externalip", help="ip de jeedom")
	parser.add_option("-c", "--cleapi", dest="cleapi", help="cle api de jeedom")
	parser.add_option("-d", "--debug", dest="debug", help="mode debug")
	parser.add_option("-r", "--realpath", dest="realpath", help="path usr")
	parser.add_option("-v", "--vitesse", dest="vitesse", help="vitesse du modem")
	parser.add_option("-f", "--force", dest="force", help="forcer le lancement")
	(options, args) = parser.parse_args()
	#print "opt: %s, arglen: %s" % (options, len(args))
	if options.port:
			try:
				gDeviceName = options.port
			except:
				error = "Can not change port %s" % options.port
				raise TeleinfoException(error)
	if options.externalip:
			try:
				gExternalIP = options.externalip
			except:
				error = "Can not change ip %s" % options.externalip
				raise TeleinfoException(error)
	if options.debug:
			try:
				gDebug = options.debug
			except:
				error = "Can not set debug mode %s" % options.debug
				#raise TeleinfoException(error)
	if options.cleapi:
			try:
				gCleAPI = options.cleapi
			except:
				error = "Can not change ip %s" % options.cleapi
				raise TeleinfoException(error)
	if options.realpath:
			try:
				gRealPath = options.realpath
			except:
				error = "Can not get realpath %s" % options.realpath
				raise TeleinfoException(error)
	if options.vitesse:
			try:
				gVitesse = options.vitesse
			except:
				error = "Can not get vitesse %s" % options.vitesse
				raise TeleinfoException(error)
	if options.force:
			try:
				if options.force == '0':
					if os.path.isfile("/tmp/teleinfo.pid"):
						filetmp = open("/tmp/teleinfo.pid", 'r')
						filepid = filetmp.readline()
						filetmp.close()
						if filepid != "":
							_log = MyLogger()
							_log.warning('Deamon deja lance')
							gCanStart = 'false'
			except:
				error = "Can not get file PID"
				raise TeleinfoException(error)
	if gCanStart == 'true':
		pid = str(os.getpid())
		file("/tmp/teleinfo.pid", 'w').write("%s\n" % pid)
		teleinfo = Teleinfo(gDeviceName, gExternalIP, gCleAPI, gDebug, gRealPath, gVitesse)
		signal.signal(signal.SIGTERM, teleinfo.exit_handler)
		teleinfo.run()
	sys.exit()