#!/usr/bin/python
# -*- coding: utf-8 -*-

""" Teleinfo reader

License
=======

teleinfo_2_cpt.py is Copyright:
- (C) 2010-2012 Samuel <samuel DOT buffet AT gmail DOT com>
- (C) 2012-2017 Frédéric <fma38 AT gbiloba DOT org>
- (C) 2017 Samuel <samuel DOT buffet AT gmail DOT com>
- (C) 2015-2018 Cédric Guiné <cedric DOT guine AT gmail DOT com>

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
import urllib2
import sys
import os
import traceback
import logging
import signal
import globals
import argparse
import thread
import json
try:
    import ftdi
    ftdi_type = 0
except ImportError:
    import ftdi1 as ftdi
    ftdi_type = 1
    #raise ImportError('Erreur de librairie ftdi')
try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)
# USB settings
usb_vendor = 0x0403
usb_product = 0x6001
usb_port = [0x00, 0x11, 0x22]
baud_rate = 1200

# TELEINFO settings
frame_length = 3000  # Nb chars to read to ensure to get a least one complete raw frame

# Misc
stx = 0x02  # start of text
etx = 0x03  # end of text
eot = 0x04  # end of transmission

# Datas
global_external_ip = ''
global_cle_api = ''
global_debug = ''
global_real_path = ''

class FtdiError(Exception):
    """ Ftdi related errors
    """


class Ftdi(object):
    """ Class for handling ftdi communication
    """
    def __init__(self):
        """
        """
        logging.info("Try to open Teleinfo modem")
        super(Ftdi, self).__init__()
        self.__ftdic = None

    def init(self):
        """ Init ftdi com.
        """
        # Create ftdi context
        logging.info("Try to Create ftdi context")
        self.__ftdic = ftdi.ftdi_context()
        if self.__ftdic is None:
            logging.error("Can't create ftdi context")
            raise FtdiError("Can't create ftdi context")

        # Init ftdi context
        err = ftdi.ftdi_init(self.__ftdic)
        if err < 0:
            logging.error("Can't init ftdi context (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't init ftdi context (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        # Open port
        logging.info("Try to open ftdi port")
        err = ftdi.ftdi_usb_open(self.__ftdic, usb_vendor, usb_product)
        if err < 0:
            logging.error("Can't open usb (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't open usb (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        err = ftdi.ftdi_set_baudrate(self.__ftdic, baud_rate)
        if err < 0:
            logging.error("Can't set baudrate (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't set baudrate (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        # Because of the usb interface, must use 8 bits transmission data, instead of 7 bits
        err = ftdi.ftdi_set_line_property(self.__ftdic, ftdi.BITS_8, ftdi.EVEN, ftdi.STOP_BIT_1)
        if err < 0:
            logging.error("Can't set line property (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't set line property (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

    def shutdown(self):
        """ Shutdown ftdi com.
        """
        logging.info("Try to close ftdi port")
        err = ftdi.ftdi_usb_close(self.__ftdic)
        if err < 0:
            logging.error("Can't close ftdi com. (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't close ftdi com. (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        ftdi.ftdi_deinit(self.__ftdic)

    def selectPort(self, port):
        """ Select the giver port
        """
        err = ftdi.ftdi_set_bitmode(self.__ftdic, port, ftdi.BITMODE_CBUS)
        if err < 0:
            logging.error("Can't set bitmode (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't set bitmode (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
        time.sleep(0.1)

    def purgeBuffers(self):
        """ Purge ftdi buffers
        """
        err = ftdi.ftdi_usb_purge_buffers(self.__ftdic)
        if err < 0:
            logging.error("Can't purge buffers (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't purge buffers (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

    def readOne(self):
        """ read 1 char from usb
        """
        buf = ' '
        err = ftdi.ftdi_read_data(self.__ftdic, buf, 1)
        if err < 0:
            logging.error("Can't read data (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            self.shutdown()
            raise FtdiError("Can't read data (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
        if err:
            c_error = unichr(ord(buf) % 0x80)  # Clear bit 7
            return c_error
        else:
            return None

    def read(self, size):
        """ read several chars
        """

        # Purge buffers
        self.purgeBuffers()

        raw = u""
        while len(raw) < frame_length:
            c = self.readOne()
            if c is not None and c != '\x00':
                raw += c

        return raw

class Teleinfo(object):
    import globals
    """ Class for handling teleinfo stuff
    """
    def __init__(self, ftdi_):
        """
        """
        logging.info("TELEINFO------Initialisation de la teleinfo")
        logging.info("TELEINFO------FTDI TYPE : " + str(ftdi_type))
        if ftdi_type == 0:
            globals.ftdi_context = ""
            super(Teleinfo, self).__init__()
            self.__ftdi = ftdi_
        else:
            globals.ftdi_context = ftdi.new()
            ret = ftdi.usb_open(globals.ftdi_context, 0x0403, 0x6001)
            if ret < 0:
                logging.error("Can't open usb (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            #ftdi.set_baudrate(globals.ftdi_context, 1200)
            #ftdi.set_line_property(globals.ftdi_context, ftdi.BITS_8, ftdi.EVEN, ftdi.STOP_BIT_1)

    def __selectMeter(self, num):
        """ Select giver meter
        """
        if ftdi_type == 0:
            self.__ftdi.selectPort(usb_port[num])
        else:
            err = ftdi.set_bitmode(globals.ftdi_context, usb_port[num], ftdi.BITMODE_CBUS)
            if err < 0:
                logging.error("Can't set bitmode (%d, %s)" % (err, ftdi.get_error_string(globals.ftdi_context)))
                raise FtdiError("Can't set bitmode (%d, %s)" % (err, ftdi.get_error_string(globals.ftdi_context)))
            time.sleep(0.1)

    def __readOne(self):
        """ read 1 char from usb
        """
        err, buf = ftdi.read_data(globals.ftdi_context, 0x1)
        if err < 0:
            logging.error("Can't read data (%d, %s)" % (err, ftdi.get_error_string(globals.ftdi_context)))
            self.close()
            raise FtdiError("Can't read data (%d, %s)" % (err, ftdi.get_error_string(globals.ftdi_context)))
        if err:
            #c = unichr(ord(buf) % 0x80)  # Clear bit 7
            c = chr(ord(buf) & 0x07f)
            return err, c
        else:
            return err, None

    def __readRawFrame(self):
        """ Read raw frame
        """
        # As the data are sent asynchronously by the USB interface, we probably don't start
        # to read at the start of a frame. So, we read enough chars to retreive a complete frame
        logging.debug("Lecture des donnees")
        if ftdi_type == 0:
            raw = self.__ftdi.read(frame_length)
        else:
            err = ftdi.usb_purge_buffers(globals.ftdi_context)
            if err < 0:
                logging.error("Can't purge buffers (%d, %s)" % (err, ftdi.get_error_string(globals.ftdi_context)))
                raise FtdiError("Can't purge buffers (%d, %s)" % (err, ftdi.get_error_string(globals.ftdi_context)))
            raw = u""
            while len(raw) < frame_length:
                err, c = self.__readOne()
                if c is not None and c != '\x00':
                    raw += c
                    #logging.debug(c)
        return raw

    def __frameToDatas(self, frame):
        """ Split frame in datas
        """
        #essai indent
        Content = {}
        lines = frame.split('\r')
        for line in lines:
            try:
                checksum = line[-1]
                header, value = line[:-2].split()
                #data = {'header': header.encode(), 'value': value.encode(), 'checksum': checksum}
                logging.debug('TELEINFO------name : ' + header.encode() + ' value : ' + value.encode() + ' checksum : ' + checksum)
                if self.__checkData(line):
                    Content[header.encode()] = value.encode()
            except:
                pass
                #datas.append(data)
        return Content

    def __checkData(self, data):
        """ Check if data is ok (checksum)
        """
        if globals.mode == "standard":
            #Gestion des champs horodates
            if len(data.split('\x09')) == 4:
                datas = '\x09'.join(data.split('\x09')[0:3])
            else:
                datas = '\x09'.join(data.split('\x09')[0:2])
            my_sum = 0
            for cks in datas:
                my_sum = my_sum + ord(cks)
            computed_checksum = ((my_sum - 0x01) & int("111111", 2)) + 0x20
        else:
            #print "Check checksum : f = %s, chk = %s" % (frame, checksum)
            datas = ' '.join(data.split()[0:2])
            my_sum = 0
            for cks in datas:
                my_sum = my_sum + ord(cks)
            computed_checksum = (my_sum & int("111111", 2)) + 0x20
            #print "computed_checksum = %s" % chr(computed_checksum)
        return chr(computed_checksum) == data[-1]

    def extractDatas(self, raw):
        """ Extract datas from raw frame
        """
        end = raw.rfind(chr(etx)) + 1
        start = raw[:end].rfind(chr(etx)+chr(stx))
        frame = raw[start+2:end-2]

        # Check if there is a eot, cancel frame
        if frame.find(chr(eot)) != -1:
            return {'Message':'eot'}
            #raise TeleinfoError("eot found")

        # Convert frame back to ERDF standard
        #frame = frame.replace('\n', '')     # Remove new line

        # Extract data
        datas = self.__frameToDatas(frame)

        return datas

    def readMeter(self):
        """ Read raw frame for giver meter
        """
        num_compteur = 1
        cpt1_data = {}
        cpt2_data = {}
        cpt1_data_temp = {}
        cpt2_data_temp = {}
        raz_time = 600
        separateur = " "
        send_data = {}
        #for cle, valeur in Donnees.items():
        #            Donnees.pop(cle)
        #            _Donnees.pop(cle)
        while(1):
            if raz_time > 1:
                raz_time = raz_time - 1
            else:
                logging.info("TELEINFO------HEARTBEAT")
                raz_time = 600
                for cle, valeur in cpt1_data.items():
                    cpt1_data.pop(cle)
                    cpt1_data_temp.pop(cle)
                for cle, valeur in cpt2_data.items():
                    cpt2_data.pop(cle)
                    cpt2_data_temp.pop(cle)
            send_data = {}
            self.__selectMeter(num_compteur)
            raw = self.__readRawFrame()
            self.__selectMeter(0)
            datas = self.extractDatas(raw)
            logging.debug(datas)

            if num_compteur == 1:
                for cle, valeur in datas.items():
                    if cle == 'PTEC':
                        valeur = valeur.replace(".", "")
                        valeur = valeur.replace(")", "")
                        cpt1_data[cle] = valeur
                    else:
                        cpt1_data[cle] = valeur
            elif num_compteur == 2:
                for cle, valeur in datas.items():
                    if cle == 'PTEC':
                        valeur = valeur.replace(".", "")
                        valeur = valeur.replace(")", "")
                        cpt2_data[cle] = valeur
                    else:
                        cpt2_data[cle] = valeur
            PENDING_CHANGES = False
            if num_compteur == 1:
                for cle, valeur in cpt1_data.items():
                    if cle in cpt1_data_temp:
                        if cpt1_data[cle] != cpt1_data_temp[cle]:
                            send_data[cle] = valeur
                            cpt1_data_temp[cle] = valeur
                            PENDING_CHANGES = True
                    else:
                        send_data[cle] = valeur
                        cpt1_data_temp[cle] = valeur
                        PENDING_CHANGES = True
            elif num_compteur == 2:
                for cle, valeur in cpt2_data.items():
                    if cle in cpt2_data_temp:
                        if cpt2_data[cle] != cpt2_data_temp[cle]:
                            send_data[cle] = valeur
                            cpt2_data_temp[cle] = valeur
                            PENDING_CHANGES = True
                    else:
                        send_data[cle] = valeur
                        cpt2_data_temp[cle] = valeur
                        PENDING_CHANGES = True
            try:
                if PENDING_CHANGES :
                    if num_compteur == 1:
                        if globals.mode == "standard": # Zone linky standard
                            if not cpt1_data_temp["ADSC"] == '':
                                send_data["device"] = cpt1_data_temp["ADSC"]
                                globals.JEEDOM_COM.add_changes('device::'+cpt1_data_temp["ADSC"],send_data)
                        else:
                            send_data["device"] = cpt1_data_temp["ADCO"]
                            globals.JEEDOM_COM.add_changes('device::'+cpt1_data_temp["ADCO"],send_data)
                    elif num_compteur == 2:
                        if globals.mode == "standard": # Zone linky standard
                            if not cpt2_data_temp["ADSC"] == '':
                                send_data["device"] = cpt2_data_temp["ADSC"]
                                globals.JEEDOM_COM.add_changes('device::'+cpt2_data_temp["ADSC"],send_data)
                        else:
                            send_data["device"] = cpt2_data_temp["ADCO"]
                            globals.JEEDOM_COM.add_changes('device::'+cpt2_data_temp["ADCO"],send_data)
            except Exception:
                errorCom = "Connection error"
                logging.error(errorCom)
            #logging.debug("TELEINFO------START SLEEPING " + str(globals.cycle_sommeil) + " seconds")
            time.sleep(globals.cycle_sommeil)
            #logging.debug("TELEINFO------WAITING : " + str(globals.TELEINFO_SERIAL.inWaiting()) + " octets dans la file apres sleep ")
            #if globals.TELEINFO_SERIAL.inWaiting() > 1500:
            #    globals.TELEINFO_SERIAL.flushInput()
            #    logging.info("TELEINFO------BUFFER OVERFLOW => FLUSH")
            #    logging.debug(str(globals.TELEINFO_SERIAL.inWaiting()) + "octets dans la file apres flush ")
            if num_compteur == 1:
                num_compteur = 2
            else:
                num_compteur = 1
        self.terminate()

    def exit_handler(self, *args):
        self.terminate()
        logging.info("[exit_handler]")

    def close(self):
        if ftdi_type == 0:
            self.__ftdi.shutdown()
        else:
            ftdi.close()

    def terminate(self):
        print "Terminating..."
        self.close()
        #sys.close(gOutput)
        #sys.exit()

def read_socket(cycle):
    while True :
        try:
            global JEEDOM_SOCKET_MESSAGE
            if not JEEDOM_SOCKET_MESSAGE.empty():
                logging.debug("SOCKET-READ------Message received in socket JEEDOM_SOCKET_MESSAGE")
                message = json.loads(JEEDOM_SOCKET_MESSAGE.get())
                if message['apikey'] != globals.apikey:
                    logging.error("SOCKET-READ------Invalid apikey from socket : " + str(message))
                    return
                logging.debug('SOCKET-READ------Received command from jeedom : '+str(message['cmd']))
                if message['cmd'] == 'action':
                    logging.debug('SOCKET-READ------Attempt an action on a device')
                    thread.start_new_thread( action_handler, (message,))
                    logging.debug('SOCKET-READ------Action Thread Launched')
                elif message['cmd'] == 'logdebug':
                    logging.info('SOCKET-READ------Passage du demon en mode debug force')
                    log = logging.getLogger()
                    for hdlr in log.handlers[:]:
                        log.removeHandler(hdlr)
                    jeedom_utils.set_log_level('debug')
                    logging.debug('SOCKET-READ------<----- La preuve ;)')
                elif message['cmd'] == 'lognormal':
                    logging.info('SOCKET-READ------Passage du demon en mode de log normal')
                    log = logging.getLogger()
                    for hdlr in log.handlers[:]:
                        log.removeHandler(hdlr)
                    jeedom_utils.set_log_level('error')
        except Exception as e:
            logging.error("SOCKET-READ------Exception on socket : %s" % str(e))
            logging.debug(traceback.format_exc())
        time.sleep(cycle)

def listen():
    globals.PENDING_ACTION=False
    jeedom_socket.open()
    logging.info("GLOBAL------Start listening...")
    #globals.TELEINFO = Teleinfo()
    logging.info("GLOBAL------Preparing Teleinfo...")
    thread.start_new_thread( read_socket, (globals.cycle,))
    logging.debug('GLOBAL------Read Socket Thread Launched')
    while 1:
        try:
            if ftdi_type == 0:
                ftdi_ = Ftdi()
                ftdi_.init()
                globals.TELEINFO = Teleinfo(ftdi_)
            else:
                globals.TELEINFO = Teleinfo("")
            thread.start_new_thread( log_starting,(globals.cycle,))
            globals.TELEINFO.readMeter()
        except Exception as e:
            print("Error:")
            print(e)
            shutdown()

def log_starting(cycle):
	time.sleep(30)
	logging.info('GLOBAL------Passage des logs en normal')
	log = logging.getLogger()
	for hdlr in log.handlers[:]:
		log.removeHandler(hdlr)
	jeedom_utils.set_log_level('error')

def handler(signum=None, frame=None):
    logging.debug("Signal %i caught, exiting..." % int(signum))
    shutdown()

def shutdown():
    logging.debug("GLOBAL------Shutdown")
    #if ftdi_type == 0:
        #ftdi_.shutdown()
    #globals.TELEINFO.close()
    logging.debug("Shutdown")
    logging.debug("Removing PID file " + str(globals.pidfile))
    try:
        os.remove(globals.pidfile)
    except:
        pass
    try:
        jeedom_socket.close()
    except:
        pass
    logging.debug("Exit 0")
    sys.stdout.flush()
    os._exit(0)
#------------------------------------------------------------------------------
# MAIN
#------------------------------------------------------------------------------

globals.log_level = "info"
globals.socketport = 55062
globals.sockethost = '127.0.0.1'
globals.apikey = ''
globals.callback = ''
globals.cycle = 1;

parser = argparse.ArgumentParser(description='Teleinfo Daemon for Jeedom plugin')
parser.add_argument("--apikey", help="Value to write", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--callback", help="Value to write", type=str)
parser.add_argument("--socketport", help="Socket Port", type=str)
parser.add_argument("--sockethost", help="Socket Host", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
parser.add_argument("--port", help="Port du modem", type=str)
parser.add_argument("--vitesse", help="Vitesse du modem", type=str)
parser.add_argument("--mode", help="Model mode", type=str)
parser.add_argument("--cyclesommeil", help="Wait time between 2 readline", type=str)
args = parser.parse_args()

if args.apikey:
    globals.apikey = args.apikey
if args.loglevel:
    globals.log_level = args.loglevel
if args.callback:
    globals.callback = args.callback
if args.socketport:
    globals.socketport = args.socketport
if args.sockethost:
    globals.sockethost = args.sockethost
if args.cycle:
    globals.cycle = float(args.cycle)
if args.port:
    globals.port = args.port
if args.vitesse:
    globals.vitesse = args.vitesse
if args.mode:
    globals.mode = args.mode
if args.cyclesommeil:
    globals.cycle_sommeil = float(args.cyclesommeil)

globals.socketport = int(globals.socketport)
globals.cycle = float(globals.cycle)

jeedom_utils.set_log_level(globals.log_level)
logging.info('GLOBAL------Start teleinfod')
logging.info('GLOBAL------Cycle Sommeil : '+str(globals.cycle_sommeil))
logging.info('GLOBAL------Socket port : '+str(globals.socketport))
logging.info('GLOBAL------Socket host : '+str(globals.sockethost))
logging.info('GLOBAL------Log level : '+str(globals.log_level))
logging.info('GLOBAL------Callback : '+str(globals.callback))
logging.info('GLOBAL------Vitesse : '+str(globals.vitesse))
logging.info('GLOBAL------Apikey : '+str(globals.apikey))
logging.info('GLOBAL------Cycle : '+str(globals.cycle))
logging.info('GLOBAL------Port : '+str(globals.port))
logging.info('GLOBAL------Mode : '+str(globals.mode))

signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)
globals.pidfile = "/tmp/jeedom/teleinfo/teleinfo2cpt.pid"
jeedom_utils.write_pid(str(globals.pidfile))
globals.JEEDOM_COM = jeedom_com(apikey = globals.apikey,url = globals.callback,cycle=globals.cycle)
if not globals.JEEDOM_COM.test():
    logging.error('GLOBAL------Network communication issues. Please fix your Jeedom network configuration.')
    shutdown()
jeedom_socket = jeedom_socket(port=globals.socketport,address=globals.sockethost)
listen()
sys.exit()
