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
global_device_name = '/dev/ttyUSB0'
# Default output is stdout
global_output = sys.__stdout__
global_external_ip = ''
global_api = ''
global_debug = ''
global_real_path = ''
global_vitesse = ''
global_message_temp = ''
global_can_start = 'true'
global_mode = ''
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
        except NameError:
            pass

    def info(self, text):
        try:
            text = text.replace("'", "")
            self._logger.info(text)
        except NameError:
            pass

    def warning(self, text):
        try:
            text = text.replace("'", "")
            self._logger.warn(text)
        except NameError:
            pass

    def error(self, text):
        try:
            text = text.replace("'", "")
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

    def __init__(self, device, externalip, cleapi, debug, realpath, vitesse, mode):
        """ @param device : teleinfo modem device path
        @param log : log instance
        @param callback : method to call each time all data are collected
        The datas will be passed using a dictionnary
        """
        self._log = MyLogger()
        self._device = device
        self._externalip = externalip
        self._cleApi = cleapi
        self._debug = debug
        self._realpath = realpath
        self._vitesse = vitesse
        self._ser = None
        self._mode = mode

    def open(self):
        """ open teleinfo modem device
        """
        try:
            self._log.info("Try to open Teleinfo modem '%s' with speed '%s'" % (self._device, self._vitesse))
            self._ser = serial.Serial(self._device, self._vitesse, bytesize=7, parity='E', stopbits=1)
            self._log.info("Teleinfo modem successfully opened")
        except:
            error = "Error opening Teleinfo modem '%s' : %s" % (self._device, traceback.format_exc())
            self._log.error(error)
            raise TeleinfoException(error)

    def close(self):
        """ close telinfo modem
        """
        self._log.info("Try to close Teleinfo modem")
        if self._ser != None  and self._ser.isOpen():
            self._ser.close()
            self._log.info("Teleinfo modem successfully closed")

    def terminate(self):
        print "Terminating..."
        self.close()
        sys.exit()

    def read(self):
        """ Fetch one full frame for serial port
        If some part of the frame is corrupted,
        it waits until th enext one, so if you have corruption issue,
        this method can take time but it enures that the frame returned is valid
        @return frame : list of dict {name, value, checksum}
        """
        if self._mode == "standard": # Zone linky standard
            resp = self._ser.readline()
            is_ok = False
            content = {}
            while not is_ok:
                try:
                    while 'ADSC' not in resp:
                        resp = self._ser.readline()
                        if len(resp.replace('\r', '').replace('\n', '').split('\x09')) == 4:
                            name, horodate, value, checksum = resp.replace('\r', '').replace('\n', '').split('\x09')
                            checksum = ' '
                            content[name] = value;
                            if self._debug == '1':
                                self._log.debug('name : ' + name + ' value : ' + value + ' checksum : ' + checksum + ' Horodate : ' + horodate)
                        else:
                            name, value, checksum = resp.replace('\r', '').replace('\n', '').split('\x09')
                            content[name] = value;
                            if self._debug == '1':
                                self._log.debug('name : ' + name + ' value : ' + value + ' checksum : ' + checksum)
                    is_ok = True
                    if len(resp.replace('\r', '').replace('\n', '').split('\x09')) == 4:
                        name, horodate, value, checksum = resp.replace('\r', '').replace('\n', '').split('\x09')
                        checksum = ' '
                        content[name] = value;
                        if self._debug == '1':
                            self._log.debug('name : ' + name + ' value : ' + value + ' checksum : ' + checksum + ' Horodate : ' + horodate)
                    else:
                        name, value, checksum = resp.replace('\r', '').replace('\n', '').split('\x09')
                        content[name] = value;
                        if self._debug == '1':
                            self._log.debug('name : ' + name + ' value : ' + value + ' checksum : ' + checksum)
                except ValueError:
                    checksum = ' '
        else: # Zone historique
            #Get the begin of the frame, markde by \x02
            resp = self._ser.readline()
            is_ok = False
            content = {}
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
                        if len(resp.replace('\r', '').replace('\n', '').split()) == 2:
                            #The checksum char is ' '
                            name, value = resp.replace('\r', '').replace('\n', '').split()
                            checksum = ' '
                            if self._debug == '1':
                                self._log.debug('name : ' + name + ' value : ' + value)
                        else:
                            name, value, checksum = resp.replace('\r', '').replace('\n', '').split()
                            if self._debug == '1':
                                self._log.debug('name : ' + name + ' value : ' + value + ' checksum : ' + checksum)
                        if self._is_valid(resp, checksum):
                            content[name] = value;
                        else:
                            self._log.error("** FRAME CORRUPTED !")
                            self._log.debug('** FRAME : ' + resp + '**')
                            #This frame is corrupted, we need to wait until the next one
                            while '\x02' not in resp:
                                resp = self._ser.readline()
                            self._log.error("* New frame after corrupted")
                        resp = self._ser.readline()
                    #\x03 has been detected, that's the last line of the frame
                    if len(resp.replace('\r', '').replace('\n', '').split()) == 2:
                        name, value = resp.replace('\r', '').replace('\n', '').replace('\x02', '').replace('\x03', '').split()
                        checksum = ' '
                        if self._debug == '1':
                            self._log.debug('name : ' + name + ' value : ' + value)
                    else:
                        name, value, checksum = resp.replace('\r', '').replace('\n', '').replace('\x02', '').replace('\x03', '').split()
                        if self._debug == '1':
                            self._log.debug('name : ' + name + ' value : ' + value + ' checksum : ' + checksum)
                    if self._is_valid(resp, checksum):
                        is_ok = True
                    else:
                        self._log.error("** Last frame invalid")
                        resp = self._ser.readline()
                except ValueError:
                    #Badly formatted frame
                    #This frame is corrupted, we need to wait until the next one
                    while '\x02' not in resp:
                        resp = self._ser.readline()
        return content

    def _is_valid(self, frame, checksum):
        """ Check if a frame is valid
        @param frame : the full frame
        @param checksum : the frame checksum
        """
        if self._mode == "standard":
            #Gestion des champs horodates
            if len(frame.split('\x09')) == 4:
                datas = '\x09'.join(frame.split('\x09')[0:3])
            else:
                datas = '\x09'.join(frame.split('\x09')[0:2])
            my_sum = 0
            for cks in datas:
                my_sum = my_sum + ord(cks)
            computed_checksum = ((my_sum + 0x09) & int("111111", 2)) + 0x20
        else:
            #print "Check checksum : f = %s, chk = %s" % (frame, checksum)
            datas = ' '.join(frame.split()[0:2])
            my_sum = 0
            for cks in datas:
                my_sum = my_sum + ord(cks)
            computed_checksum = (my_sum & int("111111", 2)) + 0x20
            #print "computed_checksum = %s" % chr(computed_checksum)
        return chr(computed_checksum) == checksum

    def run(self):
        """ Main function
        """
        data = {}
        data_temp = {}
        raz_time = datetime.now()
        raz_calcul = 0
        separateur = " "
        send_data = ""

        def target():
            self.process = None
            self.process = subprocess.Popen(self.cmd + send_data_bak, shell=True)
            self.process.communicate()
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
            self.terminate()
            return
        # Read a frame
        while(1):
            raz_calcul = datetime.now() - raz_time
            if raz_calcul.seconds > 60:
                raz_time = datetime.now()
                for cle, valeur in data.items():
                    data.pop(cle)
                    data_temp.pop(cle)
            send_data = ""
            frameCsv = self.read()
            for cle, valeur in frameCsv.items():
                if cle == 'PTEC':
                    valeur = valeur.replace(".", "")
                    valeur = valeur.replace(")", "")
                    data[cle] = valeur
                elif cle == 'OPTARIF':
                    valeur = valeur.replace(".", "")
                    valeur = valeur.replace(")", "")
                    data[cle] = valeur
                else:
                    valeur = valeur.replace(" ", "%20")
                    data[cle] = valeur
            if self._externalip != "":
                self.cmd = "curl -L -s -G --max-time 8 " + self._externalip +"/plugins/teleinfo/core/php/jeeTeleinfo.php -d 'api=" + self._cleApi
                separateur = "&"
            else:
                self.cmd = 'nice -n 19 timeout 8 /usr/bin/php ' + self._realpath + '/../php/jeeTeleinfo.php api=' + self._cleApi
                separateur = " "

            for cle, valeur in data.items():
                if cle in data_temp:
                    if data[cle] != data_temp[cle]:
                        send_data += separateur + cle +'='+ valeur
                        data_temp[cle] = valeur
                else:
                    send_data += separateur + cle +'='+ valeur
                    data_temp[cle] = valeur
            try:
                if send_data != "":
                    if self._mode == "standard":
                        send_data += separateur + "ADCO=" + data["ADSC"]
                    else:
                        send_data += separateur + "ADCO=" + data["ADCO"]
                    if self._externalip != "":
                        try:
                            send_data += "'"
                            if self._debug == '1':
                                print self.cmd + send_data
                                self._log.debug(self.cmd + send_data)
                            send_data_bak = send_data
                            thread = threading.Thread(target=target)
                            self.timer = threading.Timer(int(10), timer_callback)
                            self.timer.start()
                            thread.start()
                        except Exception, e:
                            errorCom = "Connection error '%s'" % e
                    else:
                        try:
                            if self._debug == '1':
                                print self.cmd + send_data
                                self._log.debug(self.cmd + send_data)
                            send_data_bak = send_data
                            thread = threading.Thread(target=target)
                            self.timer = threading.Timer(int(10), timer_callback)
                            self.timer.start()
                            thread.start()
                        except Exception, e:
                            errorCom = "Connection error '%s'" % e
            except Exception:
                erreur = ""
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
    parser.add_option("-t", "--type", dest="type", help="type du deamon")
    parser.add_option("-m", "--mode", dest="mode", help="TIC standard ou historique")

    (options, args) = parser.parse_args()
    if options.port:
        try:
            global_device_name = options.port
        except:
            error = "Can not change port %s" % options.port
            raise TeleinfoException(error)
    if options.externalip:
        try:
            global_external_ip = options.externalip
        except:
            error = "Can not change ip %s" % options.externalip
            raise TeleinfoException(error)
    if options.debug:
        try:
            global_debug = options.debug
        except:
            error = "Can not set debug mode %s" % options.debug
            #raise TeleinfoException(error)
    if options.cleapi:
        try:
            global_api = options.cleapi
        except:
            error = "Can not change ip %s" % options.cleapi
            raise TeleinfoException(error)
    if options.realpath:
        try:
            global_real_path = options.realpath
        except:
            error = "Can not get realpath %s" % options.realpath
            raise TeleinfoException(error)
    if options.vitesse:
        try:
            global_vitesse = options.vitesse
        except:
            error = "Can not get vitesse %s" % options.vitesse
            raise TeleinfoException(error)
    if options.mode:
        try:
            global_mode = options.mode
        except:
            error = "Can not get mode %s" % options.mode
            #raise TeleinfoException(error)
    if options.force:
        try:
            if options.force == '0':
                if os.path.isfile("/tmp/teleinfo_" + options.type + ".pid"):
                    filetmp = open("/tmp/teleinfo_" + options.type + ".pid", 'r')
                    filepid = filetmp.readline()
                    filetmp.close()
                    if filepid != "":
                        _log = MyLogger()
                        _log.warning('Deamon deja lance')
                        global_can_start = 'false'
        except:
            error = "Can not get file PID"
            raise TeleinfoException(error)
    if global_can_start == 'true':
        pid = str(os.getpid())
        file("/tmp/teleinfo_" + options.type + ".pid", 'w').write("%s\n" % pid)
        teleinfo = Teleinfo(global_device_name, global_external_ip, global_api, global_debug, global_real_path, global_vitesse, global_mode)
        signal.signal(signal.SIGTERM, teleinfo.exit_handler)
        teleinfo.run()
    sys.exit()
