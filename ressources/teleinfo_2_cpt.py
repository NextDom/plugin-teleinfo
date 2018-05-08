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
try:
    import ftdi
    ftdi_type = 0
except ImportError:
    import ftdi1 as ftdi
    ftdi_type = 1
    #raise ImportError('Erreur de librairie ftdi')

# USB settings
usb_vendor = 0x0403
usb_product = 0x6001
usb_port = [0x00, 0x11, 0x22]
baud_rate = 1200
# Default log level
global_log_level = logging.DEBUG

# TELEINFO settings
frame_length = 400  # Nb chars to read to ensure to get a least one complete raw frame

# Misc
stx = 0x02  # start of text
etx = 0x03  # end of text
eot = 0x04  # end of transmission

# Datas
global_external_ip = ''
global_cle_api = ''
global_debug = ''
global_real_path = ''

class MyLogger:
    """ Our own logger """
    def __init__(self):
        program_path = os.path.dirname(os.path.realpath(__file__))
        self._logger = logging.getLogger('teleinfo')
        hdlr = logging.FileHandler(program_path + '/../../../log/teleinfo_deamon')
        formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
        hdlr.setFormatter(formatter)
        self._logger.addHandler(hdlr)
        self._logger.setLevel(global_log_level)


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
        err = ftdi.ftdi_usb_open(self.__ftdic, usb_vendor, usb_product)
        if err < 0:
            self._log.error("Can't open usb (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))
            raise FtdiError("Can't open usb (%d, %s)" % (err, ftdi.ftdi_get_error_string(self.__ftdic)))

        err = ftdi.ftdi_set_baudrate(self.__ftdic, baud_rate)
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
        if ftdi_type == 0:
            self.context = ""
            super(Teleinfo, self).__init__()
            self.__ftdi = ftdi_
        else:
            self.context = ftdi.new()
            ret = ftdi.usb_open(self.context, 0x0403, 0x6001)
            ftdi.set_baudrate(self.context, 1200)
            ftdi.set_line_property(self.context, ftdi.BITS_8, ftdi.EVEN, ftdi.STOP_BIT_1)

    def __selectMeter(self, num):
        """ Select giver meter
        """
        if ftdi_type == 0:
            self.__ftdi.selectPort(usb_port[num])
        else:
            err = ftdi.set_bitmode(self.context, usb_port[num], ftdi.BITMODE_CBUS)
            if err < 0:
                self._log.error("Can't set bitmode (%d, %s)" % (err, ftdi.get_error_string(self.context)))
                raise FtdiError("Can't set bitmode (%d, %s)" % (err, ftdi.get_error_string(self.context)))
            time.sleep(0.1)

    def __readOne(self):
        """ read 1 char from usb
        """
        err, buf = ftdi.read_data(self.context, 0x1)
        if err < 0:
            self._log.error("Can't read data (%d, %s)" % (err, ftdi.get_error_string(self.context)))
            self.close()
            raise FtdiError("Can't read data (%d, %s)" % (err, ftdi.get_error_string(self.context)))
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
        if ftdi_type == 0:
            raw = self.__ftdi.read(frame_length)
        else:
            err = ftdi.usb_purge_buffers(self.context)
            if err < 0:
                self._log.error("Can't purge buffers (%d, %s)" % (err, ftdi.get_error_string(self.context)))
                raise FtdiError("Can't purge buffers (%d, %s)" % (err, ftdi.get_error_string(self.context)))
            raw = u""
            while len(raw) < frame_length:
                err, c = self.__readOne()
                if c is not None and c != '\x00':
                    raw += c
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

    def readMeter(self, device, externalip, cleapi, debug, realpath):
        """ Read raw frame for giver meter
        """
        self._device = device
        self._externalip = externalip
        self._cleAPI = cleapi
        self._debug = debug
        self._realpath = realpath

        num_compteur = 1
        cpt1_data = {}
        cpt2_data = {}
        cpt1_data_temp = {}
        cpt2_data_temp = {}
        raz_time = 600
        separateur = " "
        send_data = ""
        #for cle, valeur in Donnees.items():
        #            Donnees.pop(cle)
        #            _Donnees.pop(cle)
        while(1):
            if raz_time > 1:
                raz_time = raz_time - 1
            else:
                raz_time = 600
                for cle, valeur in cpt1_data.items():
                    cpt1_data.pop(cle)
                    cpt1_data_temp.pop(cle)
                for cle, valeur in cpt2_data.items():
                    cpt2_data.pop(cle)
                    cpt2_data_temp.pop(cle)
            send_data = ""

            self.__selectMeter(num_compteur)
            raw = self.__readRawFrame()
            self.__selectMeter(0)
            datas = self.extractDatas(raw)

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

            if self._externalip != "":
                self.cmd = self._externalip +'/plugins/teleinfo/core/php/jeeTeleinfo.php?api=' + self._cleAPI
                separateur = "&"
            else:
                self.cmd = 'nice -n 19 timeout 15 /usr/bin/php ' + self._realpath + '/../php/jeeTeleinfo.php api=' + self._cleAPI
                separateur = " "

            if num_compteur == 1:
                for cle, valeur in cpt1_data.items():
                    if cle in cpt1_data_temp:
                        if cpt1_data[cle] != cpt1_data_temp[cle]:
                            send_data += separateur + cle +'='+ valeur
                            cpt1_data_temp[cle] = valeur
                    else:
                        send_data += separateur + cle +'='+ valeur
                        cpt1_data_temp[cle] = valeur
            elif num_compteur == 2:
                for cle, valeur in cpt2_data.items():
                    if cle in cpt2_data_temp:
                        if cpt2_data[cle] != cpt2_data_temp[cle]:
                            send_data += separateur + cle +'='+ valeur
                            cpt2_data_temp[cle] = valeur
                    else:
                        send_data += separateur + cle +'='+ valeur
                        cpt2_data_temp[cle] = valeur

            if send_data != "":
                if num_compteur == 1:
                    if cpt1_data_temp.has_key("ADCO"):
                        send_data += separateur + "ADCO=" + cpt1_data_temp["ADCO"]
                elif(num_compteur == 2):
                    if cpt2_data_temp.has_key("ADCO"):
                        send_data += separateur + "ADCO=" + cpt2_data_temp["ADCO"]
                self.cmd += send_data
                if self._debug == '1':
                    #print self.cmd
                    self._log.debug(self.cmd)
                if self._externalip != "":
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
            if num_compteur == 1:
                num_compteur = 2
            else:
                num_compteur = 1
        self.terminate()

    def exit_handler(self, *args):
        self.terminate()
        self._log.info("[exit_handler]")

    def close(self):
        if ftdi_type == 0:
            self.__ftdi.shutdown()
        else:
            ftdi.close()

    def terminate(self):
        print "Terminating..."
        self.close()
        #sys.close(gOutput)
        sys.exit()

def main():
    usage = "%prog -r [options] -> read meters\n"
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
    gDeviceName = global_external_ip = global_debug = global_cle_api = global_real_path = ""
    if options.port:
        try:
            gDeviceName = options.port
        except:
            error = "Can not change port %s" % options.port
            raise TeleinfoError(error)
    if options.externalip:
        try:
            global_external_ip = options.externalip
        except:
            error = "Can not change ip %s" % options.externalip
            raise TeleinfoError(error)
    if options.debug:
        try:
            global_debug = options.debug
        except:
            error = "Can not set debug mode %s" % options.debug
            #raise TeleinfoError(error)
    if options.cleapi:
        try:
            global_cle_api = options.cleapi
        except:
            error = "Can not change ip %s" % options.cleapi
            raise TeleinfoError(error)
    if options.realpath:
        try:
            global_real_path = options.realpath
        except:
            error = "Can not get realpath %s" % options.realpath
            raise TeleinfoError(error)

    if ftdi_type == 0:
        ftdi_ = Ftdi()
        ftdi_.init()
        teleinfo = Teleinfo(ftdi_)
    else:
        teleinfo = Teleinfo("")
    pid = str(os.getpid())
    file("/tmp/teleinfo2cpt.pid", 'w').write("%s\n" % pid)
    signal.signal(signal.SIGTERM, teleinfo.exit_handler)
    teleinfo.readMeter(gDeviceName, global_external_ip, global_cle_api, global_debug, global_real_path)
    if ftdi_type == 0:
        ftdi_.shutdown()
    sys.exit()


if __name__ == "__main__":
    main()
