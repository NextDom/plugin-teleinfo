#!/usr/bin/python
# -*- coding: utf-8 -*-

""" Teleinfo reader

License
=======

teleinfoftdiusb.py is Copyright:
- (C) 2010-2012 Samuel <Buffet samuel DOT buffet AT gmail DOT com>
- (C) 2012 Frédéric <fma38 AT gbiloba DOT org>

This software is governed by the CeCILL license under French law and
abiding by the rules of distribution of free software.  You can  use,
modify and/or redistribute the software under the terms of the CeCILL
license as circulated by CEA, CNRS and INRIA at the following URL
http://www.cecill.info.

As a counterpart to the access to the source code and  rights to copy,
modify and redistribute granted by the license, users are provided only
with a limited warranty  and the software's author,  the holder of the
economic rights,  and the successive licensors  have only  limited
liability.

In this respect, the user's attention is drawn to the risks associated
with loading,  using,  modifying and/or developing or reproducing the
software by the user in light of its specific status of free software,
that may mean  that it is complicated to manipulate,  and  that  also
therefore means  that it is reserved for developers  and  experienced
professionals having in-depth computer knowledge. Users are therefore
encouraged to load and test the software's suitability as regards their
requirements in conditions enabling the security of their systems and/or
data to be ensured and,  more generally, to use and operate it in the
same conditions as regards security.

The fact that you are presently reading this means that you have had
knowledge of the CeCILL license and that you accept its terms.
"""

import time
import optparse
import ftdi
import urllib2
import sys
import os
import traceback
import logging

# USB settings
USB_VENDOR = 0x0403
USB_PRODUCT = 0x6001
USB_PORT = [0x00, 0x11, 0x22]
BAUD_RATE = 1200
# Default log level
gLogLevel = logging.DEBUG

# TELEINFO settings
FRAME_LENGTH = 400  # Nb chars to read to ensure to get a least one complete raw frame

# Misc
STX = 0x02  # start of text
ETX = 0x03  # end of text
EOT = 0x04  # end of transmission

# Default output is stdout
gExternalIP = ''
gCleAPI = ''
gDebug = ''
gRealPath = ''

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
 
	def error(self, text):
		try:
			#global gMessageTemp
			text = text.replace("'", "")
			#gMessageTemp += str(text) + "**"
			#print text
			self._logger.error(text)
		except NameError:
			pass


class FtdiError(Exception):
    """ Ftdi related errors
    """


class Ftdi(object):
    """ Class for handling ftdi communication
    """
    def __init__(self):
        """
        """
        self._log = MyLogger()
        self._log.info("Try to open Teleinfo modem")
        super(Ftdi, self).__init__()
        self.__ftdic = None

    def init(self):
        """ Init ftdi com.
        """

        # Create ftdi context
        self._log.info("Try to Create ftdi context")
        self.__ftdic = ftdi.ftdi_context()
        if self.__ftdic is None:
            self._log.error("Can't create ftdi context")
            raise FtdiError("Can't create ftdi context")

        # Init ftdi context
        err = ftdi.ftdi_init(self.__ftdic)
        if err < 0:
            self._log.error("Can't init ftdi context (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't init ftdi context (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        # Open port
        self._log.info("Try to open ftdi port")
        err = ftdi.ftdi_usb_open(self.__ftdic, USB_VENDOR, USB_PRODUCT)
        if err < 0:
            self._log.error("Can't open usb (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't open usb (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        err = ftdi.ftdi_set_baudrate(self.__ftdic, BAUD_RATE)
        if err < 0:
            self._log.error("Can't set baudrate (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't set baudrate (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        # Because of the usb interface, must use 8 bits transmission data, instead of 7 bits
        err = ftdi.ftdi_set_line_property(self.__ftdic, ftdi.BITS_8, ftdi.EVEN, ftdi.STOP_BIT_1)
        if err < 0:
            self._log.error("Can't set line property (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't set line property (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

    def shutdown(self):
        """ Shutdown ftdi com.
        """
        self._log.info("Try to close ftdi port")
        err = ftdi.ftdi_usb_close(self.__ftdic)
        if err < 0:
            self._log.error("Can't close ftdi com. (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't close ftdi com. (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        ftdi.ftdi_deinit(self.__ftdic)

    def selectPort(self, port):
        """ Select the giver port
        """
        err = ftdi.ftdi_set_bitmode(self.__ftdic, port, ftdi.BITMODE_CBUS)
        if err < 0:
            self._log.error("Can't set bitmode (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't set bitmode (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
        time.sleep(0.1)

    def purgeBuffers(self):
        """ Purge ftdi buffers
        """
        err = ftdi.ftdi_usb_purge_buffers(self.__ftdic)
        if err < 0:
            self._log.error("Can't purge buffers (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't purge buffers (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

    def readOne(self):
        """ read 1 char from usb
        """
        buf = ' '
        err = ftdi.ftdi_read_data(self.__ftdic, buf, 1)
        if err < 0:
            self._log.error("Can't read data (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            self.shutdown()
            raise FtdiError("Can't read data (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
        if err:
            c = unichr(ord(buf) % 0x80)  # Clear bit 7
            return c
        else:
            return None

    def read(self, size):
        """ read several chars
        """

        # Purge buffers
        self.purgeBuffers()

        raw = u""
        while len(raw) < FRAME_LENGTH:
            c = self.readOne()
            if c is not None and c != '\x00':
                raw += c

        return raw


class TeleinfoError(Exception):
    """ Teleinfo related errors
    """


class Teleinfo(object):
    """ Class for handling teleinfo stuff
    """
    def __init__(self, ftdi_):
        """
        """
        self._log = MyLogger()
        self._log.info("Initialisation de la teleinfo")
        super(Teleinfo, self).__init__()

        self.__ftdi = ftdi_

    def __selectMeter(self, num):
        """ Select giver meter
        """
        self.__ftdi.selectPort(USB_PORT[num])

    def __readRawFrame(self):
        """ Read raw frame
        """

        # As the data are sent asynchronously by the USB interface, we probably don't start
        # to read at the start of a frame. So, we read enough chars to retreive a complete frame
        raw = self.__ftdi.read(FRAME_LENGTH)

        return raw

    def __frameToDatas(self, frame):
        """ Split frame in datas
        """
        #essai indent
        Content = {}
        lines = frame.split('\r')
        for line in lines:
	    #print line
	    try:
                checksum = line[-1]
                header, value = line[:-2].split()
                data = {'header': header.encode(), 'value': value.encode(), 'checksum': checksum}
                self.__checkData(data)
                Content[header.encode()] = value.encode()
            except:
                pass
            #datas.append(data)
        return Content

    def __checkData(self, data):
        """ Check if data is ok (checksum)
        """

        # Check entry
        sum = 0x20  # Space between header and value
        for s in (data['header'], data['value']):
            for c in s:
                sum += ord(c)
        sum %= 0x40  # Checksum on 6 bits
        sum += 0x20  # Ensure printable char

        if sum != ord(data['checksum']):
            data = null
	    #raise TeleinfoError("Corrupted data found (%s)" % data)


    def extractDatas(self, raw):
        """ Extract datas from raw frame
        """
        end = raw.rfind(chr(ETX)) + 1
        start = raw[:end].rfind(chr(ETX)+chr(STX))
        frame = raw[start+2:end-2]

        # Check if there is a EOT, cancel frame
        if frame.find(chr(EOT)) != -1:
            return {'Message':'EOT'}
            #raise TeleinfoError("EOT found")

        # Convert frame back to ERDF standard
        #frame = frame.replace('\n', '')     # Remove new line

        # Extract data
        datas = self.__frameToDatas(frame)

        return datas

    def readMeter(self, device, externalip, cleapi, debug, realpath):
        """ Read raw frame for giver meter
        """
        self._device = device
        self._externalip = externalip
        self._cleAPI = cleapi
        self._debug = debug
        self._realpath = realpath

        _CompteurNum = 1
        Donnees_cpt1 = {}
        Donnees_cpt2 = {}
        _Donnees_cpt1 = {}
        _Donnees_cpt2 = {}
        _RAZ = 3600
        _Separateur = " "
        _SendData = ""
        #for cle, valeur in Donnees.items():
        #            Donnees.pop(cle)
        #            _Donnees.pop(cle)
        while(1):
            if(_RAZ > 1):
                _RAZ = _RAZ - 1
            else:
                _RAZ = 3600
                for cle, valeur in Donnees_cpt1.items():
                    Donnees_cpt1.pop(cle)
                    _Donnees_cpt1.pop(cle)
                for cle, valeur in Donnees_cpt2.items():
                    Donnees_cpt2.pop(cle)
                    _Donnees_cpt2.pop(cle)
            _SendData = ""
            
            self.__selectMeter(_CompteurNum)
            raw = self.__readRawFrame()
            self.__selectMeter(0)
            datas = self.extractDatas(raw)
            
            if(_CompteurNum == 1):
                for cle, valeur in datas.items():
                    if(cle == 'PTEC'):
                        valeur = valeur.replace(".","")
                        valeur = valeur.replace(")","")
                        Donnees_cpt1[cle] = valeur
                    else:
                        Donnees_cpt1[cle] = valeur
            elif(_CompteurNum == 2):
                for cle, valeur in datas.items():
                    if(cle == 'PTEC'):
                        valeur = valeur.replace(".","")
                        valeur = valeur.replace(")","")
                        Donnees_cpt2[cle] = valeur
                    else:
                        Donnees_cpt2[cle] = valeur
                
            if(self._externalip != ""):
                self.cmd = self._externalip +'/plugins/teleinfo/core/php/jeeTeleinfo.php?api=' + self._cleAPI
                _Separateur = "&"
            else:
                self.cmd = 'nice -n 19 /usr/bin/php ' + self._realpath + '/../php/jeeTeleinfo.php api=' + self._cleAPI
                _Separateur = " "
            #_SendData += _Separateur + 'ADCO='+ ADCO
            
            
            if(_CompteurNum == 1):
                for cle, valeur in Donnees_cpt1.items():
                    if(cle in _Donnees_cpt1):
                        if (Donnees_cpt1[cle] != _Donnees_cpt1[cle]):
                            _SendData += _Separateur + cle +'='+ valeur
                            _Donnees_cpt1[cle] = valeur
                    else:
                        _SendData += _Separateur + cle +'='+ valeur
                        _Donnees_cpt1[cle] = valeur
            elif(_CompteurNum == 2):
                for cle, valeur in Donnees_cpt2.items():
                    if(cle in _Donnees_cpt2):
                        if (Donnees_cpt2[cle] != _Donnees_cpt2[cle]):
                            _SendData += _Separateur + cle +'='+ valeur
                            _Donnees_cpt2[cle] = valeur
                    else:
                        _SendData += _Separateur + cle +'='+ valeur
                        _Donnees_cpt2[cle] = valeur
            
            #response = urllib2.urlopen(self.cmd)
            if (_SendData != ""):
                if(_CompteurNum == 1):
                    if (_Donnees_cpt1.has_key("ADCO")):
                        _SendData += _Separateur + "ADCO=" + _Donnees_cpt1["ADCO"]
                elif(_CompteurNum == 2):
                    if (_Donnees_cpt2.has_key("ADCO")):
                        _SendData += _Separateur + "ADCO=" + _Donnees_cpt2["ADCO"]
                self.cmd += _SendData
                if (self._debug == '1'):
                    #print self.cmd
                    self._log.debug(self.cmd)
                if(self._externalip != ""):
                    try:
                        response = urllib2.urlopen(self.cmd)
                    except Exception, e:
                        errorCom = "Connection error '%s'" % e
                else:
                    try:
                        self.process = subprocess.Popen(self.cmd, shell=True)
                        self.process.communicate()
                    except Exception, e:
                        errorCom = "Connection error '%s'" % e
            if (_CompteurNum == 1):
                _CompteurNum = 2
            else:
                _CompteurNum = 1


def main():
    usage  = "%prog -r [options] -> read meters\n"
    # Common options
    parser = optparse.OptionParser(usage)
    parser.add_option("-o", "--output", dest="filename", help="append result in FILENAME")
    parser.add_option("-p", "--port", dest="port", help="port du modem")
    parser.add_option("-e", "--externalip", dest="externalip", help="ip de jeedom")
    parser.add_option("-c", "--cleapi", dest="cleapi", help="cle api de jeedom")
    parser.add_option("-d", "--debug", dest="debug", help="mode debug")
    parser.add_option("-r", "--realpath", dest="realpath", help="path usr")
    parser.add_option("-v", "--vitesse", dest="vitesse", help="vitesse")
	parser.add_option("-f", "--force", dest="force", help="forcer le lancement")
    (options, args) = parser.parse_args()
    # Create MySQL table options
    #groupeCreate = optparse.OptionGroup(parser, "Create")
    #groupeCreate.add_option("-c", "--create", action="store_true", dest="create", default=False,
    #                      help=optparse.SUPPRESS_HELP)
    #parser.add_option_group(groupeCreate)
    # Execute command
    #teleinfoSQL = TeleinfoSQL()
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
    
    ftdi_ = Ftdi()
    ftdi_.init()
    teleinfo = Teleinfo(ftdi_)
    pid = str(os.getpid())
    file("/tmp/teleinfo.pid", 'w').write("%s\n" % pid)
    
    teleinfo.readMeter(gDeviceName, gExternalIP, gCleAPI, gDebug, gRealPath)
    ftdi_.shutdown()


if __name__ == "__main__":
    main()
