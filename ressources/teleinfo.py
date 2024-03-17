#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4

""" Read one teleinfo frame and output the frame
"""

import _thread
import argparse
import json
import sys
import traceback
import globals

try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)

import serial
from datetime import date, datetime

class error(Exception):
    def __init__(self, value):
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
    def __init__(self):
        logging.debug("MODEM------INIT CONNECTION")

    @staticmethod
    def close():
        """ close telinfo modem
        """
        logging.info("MODEM------CLOSE CONNECTION")
        if globals.TELEINFO_SERIAL is not None and globals.TELEINFO_SERIAL.isOpen():
            globals.TELEINFO_SERIAL.close()
            logging.info("MODEM------CONNECTION CLOSED")

    def terminate(self):
        print("Terminating...")
        self.close()
        os.remove("/tmp/teleinfo_" + globals.type + ".pid")
        sys.exit()

    def read(self):
        """ Fetch one full frame for serial port
        If some part of the frame is corrupted, it waits until the next one, so if you have corruption issue,
        this method can take time, but it enures that the frame returned is valid.
        @return frame : list of dict {name, value, checksum}
        """
        if globals.mode == "standard":  # Process Linky "Standard" Mode
            resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
            is_ok = False
            premtrame = True
            content = {}
            while not is_ok:
                try:
                    while 'VTIC' not in resp:
                        resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
                    while 'ADSC' not in resp:
                        if premtrame:
                            premtrame = False
                        else:
                            resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
                        if len(resp.replace('\r', '').replace('\n', '').split('\x09')) == 4:
                            name, horodate, value, checksum = resp.replace('\r', '').replace('\n', '').split('\x09')
                            # checksum = ' '
                            # content[name] = value
                            if self._is_valid(resp, checksum):
                                content[name] = value
                            else:
                                logging.debug("MODEM------ ** DONNEES HS ! ** sur trame : " + resp)
                            logging.debug('MODEM----name : ' + name + ' value : ' + value + ' Horodate : ' + horodate + ' checksum : ' + checksum)
                        else:
                            name, value, checksum = resp.replace('\r', '').replace('\n', '').split('\x09')
                            # logging.debug('Nombre de champs : ' + champs)
                            # content[name] = value;
                            if self._is_valid(resp, checksum):
                                logging.debug('MODEM------ .......... DECODAGE Checksum de la ligne ci dessous OK')
                                content[name] = value
                                if name == 'STGE':
                                    stgebin = bin(int(value, 16))
                                    stgebin = stgebin[2::]
                                    logging.debug('MODEM------ stgebin ' + str(stgebin))
                                    bits = [stgebin]
                                    longueur = len(stgebin)
                                    logging.debug('MODEM------ Length stgebin ' + str(longueur))
                                    # print ('bits 1 ', bits)
                                    for i in range(32):
                                        if i > longueur - 1:
                                            bits += '0'
                                        else:
                                            bits += [stgebin[longueur - 1 - i:longueur - i]]

                                    name = 'STGE01'
                                    label = 'STGE01 - Contact sec'                                   
                                    message = switch_mot01(int(bits[1]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE02'
                                    label = 'STGE02 - Organe de coupure'
                                    message = switch_mot02(int(bits[4]+bits[3]+bits[2],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE03'
                                    label = 'STGE03 - Etat du cache-bornes distributeur'                                    
                                    message = switch_mot03(int(bits[5]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE04'
                                    label = 'STGE04 - Non utilise - Toujours a 0'                                    
                                    message = switch_mot04(int(bits[6]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE05'
                                    label = 'STGE05 - Surtension sur une des phases'                                    
                                    message = switch_mot05(int(bits[7]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE06'
                                    label = 'STGE06 - Depassement de la puissance de reference'                                    
                                    message = switch_mot06(int(bits[8]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE07'
                                    label = 'STGE07 - Fonctionnement producteur/consommateur'                                    
                                    message = switch_mot07(int(bits[9]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE08'
                                    label = 'STGE08 - Sens de l’energie active'                                    
                                    message = switch_mot08(int(bits[10]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE09'
                                    label = 'STGE09 - Tarif en cours sur le contrat fourniture'                                    
                                    message = switch_mot09(int(bits[14]+bits[13]+bits[12]+bits[11],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE10'
                                    label = 'STGE10 - Tarif en cours sur le contrat distributeur'                                    
                                    message = switch_mot10(int(bits[16]+bits[15],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE11'
                                    label = 'STGE11 - Mode degrade de l’horloge'                                    
                                    message = switch_mot11(int(bits[17]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE12'
                                    label = 'STGE12 - Etat de la sortie tele-information'                                    
                                    message = switch_mot12(int(bits[18]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE13'
                                    label = 'STGE13 - Non utilise - Non utilise'                                    
                                    message = switch_mot13(int(bits[19]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE14'
                                    label = 'STGE14 - Etat de la sortie communication Euridis'                                    
                                    message = switch_mot14(int(bits[21]+bits[20],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE15'
                                    label = 'STGE15 - Statut du CPL'                                    
                                    message = switch_mot15(int(bits[23]+bits[22],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE16'
                                    label = 'STGE16 - Synchronisation CPL'                                   
                                    message = switch_mot16(int(bits[24]))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE17'
                                    label = 'STGE17 - Couleur du jour pour le contrat historique Tempo'                                    
                                    message = switch_mot17(int(bits[26]+bits[25],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE18'
                                    label = 'STGE18 - Couleur du lendemain pour le contrat historique Tempo'                                    
                                    message = switch_mot18(int(bits[28]+bits[27],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)

                                    name = 'STGE19'
                                    label = 'STGE19 - Preavis pointes mobiles'                                    
                                    message = switch_mot19(int(bits[30]+bits[29],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'STGE20'
                                    label = 'STGE20 - Pointe mobile (PM)'                                    
                                    message = switch_mot20(int(bits[32]+bits[31],2))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (STGE Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'STGE'

                                # RELAIS Info Translation
                                if name == 'RELAIS':
                                    relaisbin = bin(int(value))
                                    relaisbin = relaisbin[2::]
                                    logging.debug('MODEM------ relaisbin ' + str(relaisbin))
                                    bitsrelais = [relaisbin]
                                    longueur = len(relaisbin)
                                    logging.debug('MODEM------ Length relaisbin ' + str(longueur))
                                    # print ('bits 1 ', bits)
                                    for i in range(8):
                                        if i > longueur - 1:
                                            bitsrelais += '0'
                                        else:
                                            bitsrelais += [relaisbin[longueur - 1 - i:longueur - i]]

                                    name = 'RELAIS01'
                                    label = 'RELAIS01 - Relais1'                                    
                                    message = switch_relais(int(str(bitsrelais[1])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'RELAIS02'
                                    label = 'RELAIS02 - Relais2'                                    
                                    message = switch_relais(int(str(bitsrelais[2])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'RELAIS03'
                                    label = 'RELAIS03 - Relais3'                                    
                                    message = switch_relais(int(str(bitsrelais[3])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'RELAIS04'
                                    label = 'RELAIS04 - Relais4'                                    
                                    message = switch_relais(int(str(bitsrelais[4])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'RELAIS05'
                                    label = 'RELAIS05 - Relais5'                                    
                                    message = switch_relais(int(str(bitsrelais[5])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'RELAIS06'
                                    label = 'RELAIS06 - Relais6'                                   
                                    message = switch_relais(int(str(bitsrelais[6])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'RELAIS07'
                                    label = 'RELAIS07 - Relais7'                                    
                                    message = switch_relais(int(str(bitsrelais[7])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    name = 'RELAIS08'
                                    label = 'RELAIS08 - Relais8'                                    
                                    message = switch_relais(int(str(bitsrelais[8])))
                                    content[name] = message
                                    logging.debug('MODEM------ name : ' + name + ' (RELAIS Translation) ' + 'label : ' + label + ' value : ' + message)
                                    
                                    
                                    name = 'RELAIS'
                                    
                            else:
                                logging.debug('MODEM------ ** DONNEES HS ! ** sur trame : ' + resp + ' checksum : ' + checksum)
                            
                            logging.debug('MODEM------ name : ' + name + ' value : ' + value + ' Horodate : ' + " " + ' checksum : ' + checksum)
                    logging.debug('MODEM------ Content : ' + str(content))
                    is_ok = True
                except ValueError:
                    checksum = ' '
        else:  # Process "Historique" Mode
            # Get the beginningning of the frame, markde by \x02
            resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
            is_ok = False
            content = {}
            while not is_ok:
                try:
                    while '\x02' not in resp:
                        resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
                    # \x02 is in the last line of a frame, so go until the next one
                    # print "* Begin frame"
                    resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
                    # A new frame starts
                    # \x03 is the end of the frame
                    while '\x03' not in resp:
                        # Don't use strip() here because the checksum can be ' '
                        if len(resp.replace('\r', '').replace('\n', '').split()) == 2:
                            # The checksum char is ' '
                            name, value = resp.replace('\r', '').replace('\n', '').split()
                            checksum = ' '
                            logging.debug('MODEM------ name : ' + name + ' value : ' + value)
                        else:
                            name, value, checksum = resp.replace('\r', '').replace('\n', '').split()
                            logging.debug('MODEM------ name : ' + name + ' value : ' + value + ' checksum : ' + checksum)
                        if self._is_valid(resp, checksum):
                            content[name] = value
                        else:
                            logging.error("MODEM------ ** FRAME CORRUPTED ! **")
                            logging.error('** FRAME : ' + resp + '**')
                            # This frame is corrupted, we need to wait until the next one
                            while '\x02' not in resp:
                                resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
                            logging.error("* New frame after corrupted")
                        resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
                    # \x03 has been detected, that's the last line of the frame
                    if len(resp.replace('\r', '').replace('\n', '').split()) == 2:
                        name, value = resp.replace('\r', '').replace('\n', '').replace('\x02', '').replace('\x03', '').split()
                        checksum = ' '
                        logging.debug('MODEM------ name : ' + name + ' value : ' + value)
                    else:
                        name, value, checksum = resp.replace('\r', '').replace('\n', '').replace('\x02', '').replace('\x03', '').split()
                        logging.debug('MODEM------ name : ' + name + ' value : ' + value + ' checksum : ' + checksum)
                    if self._is_valid(resp, checksum):
                        is_ok = True
                    else:
                        logging.error("** Last frame invalid")
                        resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
                except ValueError:
                    # Badly formatted frame
                    # This frame is corrupted, we need to wait until the next one
                    while '\x02' not in resp:
                        resp = (globals.TELEINFO_SERIAL.readline().decode("UTF-8"))
        return content

    @staticmethod
    def _is_valid(frame, checksum):
        """ Check if a frame is valid
        @param frame : the full frame
        @param checksum : the frame checksum
        """
        if globals.mode == "standard":
            # Gestion des champs horodates
            if len(frame.split('\x09')) == 4:
                datas = '\x09'.join(frame.split('\x09')[0:3])
            else:
                datas = '\x09'.join(frame.split('\x09')[0:2])
            my_sum = 0
            for cks in datas:
                my_sum = my_sum + ord(cks)
            computed_checksum = ((my_sum + 0x09) & int("111111", 2)) + 0x20
            if chr(computed_checksum) != checksum[0:1]:
                logging.debug('MODEM------ checksum non concordant. Checksum reçu : ' + checksum[0:1] + ' Checksum calcul : ' + chr(computed_checksum))
            else:
                logging.debug('MODEM------ checksum concordant. Checksum reçu : ' + checksum[0:1] + ' Checksum calcul : ' + chr(computed_checksum))
        else:
            # print "Check checksum : f = %s, chk = %s" % (frame, checksum)
            datas = ' '.join(frame.split()[0:2])
            my_sum = 0
            for cks in datas:
                my_sum = my_sum + ord(cks)
            computed_checksum = (my_sum & int("111111", 2)) + 0x20
            if chr(computed_checksum) != checksum[0:1]:
                logging.debug('MODEM------ checksum non concordant. Checksum reçu : ' + checksum[0:1] + ' Checksum calcul : ' + chr(computed_checksum))
            else:
                logging.debug('MODEM------ checksum concordant. Checksum reçu : ' + checksum[0:1] + ' Checksum calcul : ' + chr(computed_checksum))
            # print "computed_checksum = %s" % chr(computed_checksum)
        return chr(computed_checksum) == checksum[0:1]

    # noinspection PyBroadException
    def run(self):
        """ Main function
        """
        data = {}
        data_temp = {}
        raz_day = 0
        raz_time = 0
        info_heure_calcul = 0

        # Read a frame + RAZ au changement de date + evite le heartbeat du demon
        raz_time = datetime.now()
        raz_day = date.today()
        info_heure = datetime.now()
        while 1:
            if raz_day != date.today():
                raz_day = date.today()
                time.sleep(10)
                logging.info("MODEM------ HEARTBEAT raz le " + str(raz_day))
                for cle, valeur in list(data.items()):
                    data.pop(cle)
                    data_temp.pop(cle)
            frame_csv = self.read()
            for cle, valeur in frame_csv.items():
                if cle == 'PTEC':
                    valeur = valeur.replace(".", "")
                    valeur = valeur.replace(")", "")
                    data[cle] = valeur
                elif cle == 'OPTARIF':
                    valeur = valeur.replace(".", "")
                    valeur = valeur.replace(")", "")
                    data[cle] = valeur
                else:
                    # valeur = valeur.replace(" ", "%20")
                    data[cle] = valeur
            _SendData = {}
            pending_changes = False
            raz_calcul = datetime.now() - raz_time
            for cle, valeur in data.items():
                if cle in data_temp:
                    if ((data[cle] != data_temp[cle]) or (raz_calcul.seconds > 55)):
                        if cle[:3] == 'EAS' or cle[:3] == 'EAI':                     # test si on a affaire à un index commençant par EAI ou EAS (EAIT, EASF??, ...)
                            if (int(data[cle]) > int(data_temp[cle])) and (int(data[cle]) < (int(data_temp[cle]) + 10000)):    #s i la valeur relevée est plus grande que celle en mémoire et qu'elle n'est pas 10 kwh au dessus c'est ok
                                _SendData[cle] = valeur
                                data_temp[cle] = valeur
                                pending_changes = True
                            else:                               # sinon on ne la prend pas en compte
                                logging.error('Valeur incohérente pour l index ' + str(cle) + ' à ' + str(data[cle]))
                                data_temp[cle] = valeur         # là c'est au cas où la valeur relevée était incohérente mais plus grande que celle en mémoire alors on ne prendrait plus jamais celle relevée
                        else:
                            _SendData[cle] = valeur
                            data_temp[cle] = valeur
                            pending_changes = True
                else:
                    _SendData[cle] = valeur
                    data_temp[cle] = valeur
                    pending_changes = True
            try:
                if pending_changes:
                    raz_time = datetime.now()
                    if globals.mode == "standard":
                        _SendData["device"] = data["ADSC"]
                        globals.JEEDOM_COM.add_changes('device::' + data["ADSC"], _SendData)
                    else:
                        _SendData["device"] = data["ADCO"]
                        globals.JEEDOM_COM.add_changes('device::' + data["ADCO"], _SendData)
            except KeyError as ex:
                logging.debug('exception ' + type(ex).__name__ + ' ' + str(ex))
                logging.info(str(ex) + " not received yet, waiting next frame")
            except Exception as ex:
                logging.debug('exception ' + type(ex).__name__ + ' ' + str(ex))
                logging.error('Connection error')
            info_heure_calcul = datetime.now() - info_heure
            if info_heure_calcul.seconds > 1800:
                logging.info('MODEM------ Dernières datas reçues de la TIC : ' + str(data))
                logging.info('MODEM------ Dernières datas envoyées vers Jeedom : ' + str(_SendData))
                info_heure = datetime.now()
            logging.debug("MODEM------ START SLEEPING " + str(globals.cycle_sommeil) + " seconds")
            time.sleep(globals.cycle_sommeil)
            logging.debug("MODEM------ WAITING : " + str(
                globals.TELEINFO_SERIAL.inWaiting()) + " octets dans la file apres sleep ")
            if globals.TELEINFO_SERIAL.inWaiting() > 1500:
                globals.TELEINFO_SERIAL.flushInput()
                logging.debug("MODEM------ BUFFER OVERFLOW => FLUSH")
                logging.debug(str(globals.TELEINFO_SERIAL.inWaiting()) + " octets dans la file apres flush ")
        self.terminate()

    @staticmethod
    def exit_handler(*args):
        logging.info("[exit_handler]")


# noinspection PyBroadException
def open():
    """ open teleinfo modem device
    """
    try:
        logging.info("MODEM------ OPEN CONNECTION")
        globals.TELEINFO_SERIAL = serial.Serial(globals.port, globals.vitesse, bytesize=7, parity='E', stopbits=1)
        logging.info("MODEM------ CONNECTION OPENED")
    except serial.SerialException:
        logging.error("MODEM------ Error opening Teleinfo modem '%s' : %s" % (globals.port, traceback.format_exc()))


def read_socket(cycle):
    while True:
        try:
            global JEEDOM_SOCKET_MESSAGE
            if not JEEDOM_SOCKET_MESSAGE.empty():
                logging.debug("SOCKET-READ------ Message received in socket JEEDOM_SOCKET_MESSAGE")
                message = json.loads(JEEDOM_SOCKET_MESSAGE.get())
                logging.debug("SOCKET-READ------ Message received in socket JEEDOM_SOCKET_MESSAGE " + message['cmd'])
                if message['apikey'] != globals.apikey:
                    logging.error("SOCKET-READ------ Invalid apikey from socket : " + str(message))
                    return
                logging.debug('SOCKET-READ------ Received command from jeedom : ' + str(message['cmd']))
                if message['cmd'] == 'action':
                    logging.debug('SOCKET-READ------ Attempt an action on a device')
                    _thread.start_new_thread(action_handler, (message,))
                    logging.debug('SOCKET-READ------ Action Thread Launched')
                elif message['cmd'] == 'changelog':
                    log = logging.getLogger()
                    for hdlr in log.handlers[:]:
                        log.removeHandler(hdlr)
                    jeedom_utils.set_log_level('info')
                    logging.info('SOCKET-READ------ Passage des log du demon en mode ' + message['level'])
                    for hdlr in log.handlers[:]:
                        log.removeHandler(hdlr)
                    jeedom_utils.set_log_level(message['level'])
        except Exception as e:
            logging.error("SOCKET-READ------ Exception on socket : %s" % str(e))
            logging.debug(traceback.format_exc())
        time.sleep(cycle)

def listen():
    globals.PENDING_ACTION = False
    jeedom_socket.open()
    logging.info("MODEM------ Start listening...")
    globals.TELEINFO = Teleinfo()
    logging.info("MODEM------ Preparing Teleinfo...")
    _thread.start_new_thread(read_socket, (globals.cycle,))
    logging.debug('MODEM------ Read Socket Thread Launched')
    while 1:
        try:
            try:
                logging.info("MODEM------ RUN")
                open()
            except error as err:
                logging.error(err.value)
                globals.TELEINFO.terminate()
                return
            globals.TELEINFO.run()
        except Exception as e:
            print("Error:")
            print(e)
            shutdown()


def handler(signum=None, frame=None):
    logging.debug("Signal %i caught, exiting..." % int(signum))
    shutdown()


def shutdown():
    log = logging.getLogger()
    for hdlr in log.handlers[:]:
        log.removeHandler(hdlr)
    jeedom_utils.set_log_level('debug')
    logging.info("MODEM------ Shutdown")
    logging.info("Removing PID file " + str(globals.pidfile))
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


# ------------------------------------------------------------------------------
# STGE Info Translation Fields
# ------------------------------------------------------------------------------

def switch_mot01(argument):
    switcher = {
        0: "Ferme",
        1: "Ouvert",
    }
    return switcher.get(argument, "Invalide")

def switch_mot02(argument):
    switcher = {
        0: "Ferme",
        1: "Ouvert sur surpuissance",
        2: "ouvert sur surtension",
        3: "Ouvert sur delestage",
        4: "Ouvert sur ordre CPL ou Euridis",
        5: "Ouvert sur surchauffe avec I > Imax",
        6: "Ouvert sur surchauffe avec I < Imax",
    }
    return switcher.get(argument, "Invalide")

def switch_mot03(argument):
    switcher = {
        0: "Ferme",
        1: "Ouvert",
    }
    return switcher.get(argument, "Invalide")

def switch_mot04(argument):
    switcher = {
        0: "Toujours a 0",
        1: "Anormal",
    }
    return switcher.get(argument, "Invalide")

def switch_mot05(argument):
    switcher = {
        0: "Pas de surtension",
        1: "Surtension",
    }
    return switcher.get(argument, "Invalide")

def switch_mot06(argument):
    switcher = {
        0: "Pas de depassement",
        1: "Depassement en cours",
    }
    return switcher.get(argument, "Invalide")

def switch_mot07(argument):
    switcher = {
        0: "Consommateur",
        1: "Producteur",
    }
    return switcher.get(argument, "Invalide")

def switch_mot08(argument):
    switcher = {
        0: "Energie active positive",
        1: "Energie active negative",
    }
    return switcher.get(argument, "Invalide")

def switch_mot09(argument):
    switcher = {
        0: "Energie ventilee sur Index 1",
        1: "Energie ventilee sur Index 2",
        2: "Energie ventilee sur Index 3",
        3: "Energie ventilee sur Index 4",
        4: "Energie ventilee sur Index 5",
        5: "Energie ventilee sur Index 6",
        6: "Energie ventilee sur Index 7",
        7: "Energie ventilee sur Index 8",
        8: "Energie ventilee sur Index 9",
        9: "Energie ventilee sur Index 10",
    }
    return switcher.get(argument, "Invalide")

def switch_mot10(argument):
    switcher = {
        0: "Energie ventilee sur Index 1",
        1: "Energie ventilee sur Index 2",
        2: "Energie ventilee sur Index 3",
        3: "Energie ventilee sur Index 4",
    }
    return switcher.get(argument, "Invalide")

def switch_mot11(argument):
    switcher = {
        0: "Horloge correcte",
        1: "Horloge en mode degrade",
    }
    return switcher.get(argument, "Invalide")

def switch_mot12(argument):
    switcher = {
        0: "Mode Historique",
        1: "Mode Standard",
    }
    return switcher.get(argument, "Invalide")

def switch_mot13(argument):
    switcher = {
        0: "Non utilise",
        1: "Non utilise",
    }
    return switcher.get(argument, "Invalide")

def switch_mot14(argument):
    switcher = {
        0: "Desactivee",
        1: "Activee sans securite",
        2: "Invalide",
        3: "Activee avec securite",
    }
    return switcher.get(argument, "Invalide")

def switch_mot15(argument):
    switcher = {
        0: "New/Unlock",
        1: "New/Lock",
        2: "Registered",
        3: "Invalide",
    }
    return switcher.get(argument, "Invalide")

def switch_mot16(argument):
    switcher = {
        0: "Compteur non synchronise",
        1: "Compteur synchronise",
    }
    return switcher.get(argument, "Invalide")

def switch_mot17(argument):
    switcher = {
        0: "Pas d‘annonce",
        1: "Bleu",
        2: "Blanc",
        3: "Rouge",
    }
    return switcher.get(argument, "Invalide")

def switch_mot18(argument):
    switcher = {
        0: "Pas d‘annonce",
        1: "Bleu",
        2: "Blanc",
        3: "Rouge",
    }
    return switcher.get(argument, "Invalide")

def switch_mot19(argument):
    switcher = {
        0: "Pas de preavis en cours",
        1: "Preavis PM1 en cours",
        2: "Preavis PM2 en cours",
        3: "Preavis PM3 en cours",
    }
    return switcher.get(argument, "Invalide")

def switch_mot20(argument):
    switcher = {
        0: "Pas de pointe mobile",
        1: "PM1 en cours",
        2: "PM2 en cours",
        3: "PM3 en cours",
    }
    return switcher.get(argument, "Invalide")

# ------------------------------------------------------------------------------
# RELAIS Info Translation Fields
# ------------------------------------------------------------------------------

def switch_relais(argument):
    switcher = {
        0: "Ouvert",
        1: "Ferme",
    }
    return switcher.get(argument, "Invalide")


# ------------------------------------------------------------------------------
# MAIN
# ------------------------------------------------------------------------------

parser = argparse.ArgumentParser(description='Teleinfo Daemon for Jeedom plugin')
parser.add_argument("--apikey", help="Value to write", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--callback", help="Value to write", type=str)
parser.add_argument("--socketport", help="Socket Port", type=str)
parser.add_argument("--sockethost", help="Socket Host", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
parser.add_argument("--port", help="Port du modem", type=str)
parser.add_argument("--vitesse", help="Vitesse du modem", type=str)
parser.add_argument("--type", help="Compteur type", type=str)
parser.add_argument("--mode", help="Model mode", type=str)
parser.add_argument("--cyclesommeil", help="Wait time between 2 readline", type=str)
parser.add_argument("--pidfile", help="pidfile", type=str)
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
    globals.cycle = args.cycle
if args.port:
    globals.port = args.port
if args.vitesse:
    globals.vitesse = args.vitesse
if args.type:
    globals.type = args.type
if args.mode:
    globals.mode = args.mode
if args.cyclesommeil:
    globals.cycle_sommeil = args.cyclesommeil
if args.pidfile:
    globals.pidfile = args.pidfile


globals.socketport = int(globals.socketport)
globals.cycle = float(globals.cycle)
globals.cycle_sommeil = float(globals.cycle_sommeil)

jeedom_utils.set_log_level(globals.log_level)

globals.JEEDOM_COM = jeedom_com(apikey=globals.apikey, url=globals.callback, cycle=globals.cycle)
globals.pidfile = globals.pidfile + "_" + globals.type + ".pid"
logging.info('MODEM------Start teleinfod')
jeedom_utils.write_pid(str(globals.pidfile))

logging.info('MODEM------ Cycle Sommeil : ' + str(globals.cycle_sommeil))
logging.info('MODEM------ Socket port : ' + str(globals.socketport))
logging.info('MODEM------ Socket host : ' + str(globals.sockethost))
logging.info('MODEM------ Log level : ' + str(globals.log_level))
logging.info('MODEM------ Callback : ' + str(globals.callback))
logging.info('MODEM------ Vitesse : ' + str(globals.vitesse))
logging.info('MODEM------ Apikey : ' + str(globals.apikey))
logging.info('MODEM------ Cycle : ' + str(globals.cycle))
logging.info('MODEM------ Port : ' + str(globals.port))
logging.info('MODEM------ Type : ' + str(globals.type))
logging.info('MODEM------ Mode : ' + str(globals.mode))
logging.info('MODEM------ Pid File : ' + str(globals.pidfile))
signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)
if not globals.JEEDOM_COM.test():
    logging.error('MODEM------ Network communication issues. Please fix your Jeedom network configuration.')
    shutdown()
jeedom_socket = jeedom_socket(port=globals.socketport, address=globals.sockethost)
listen()
sys.exit()
