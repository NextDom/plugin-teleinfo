#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4

""" Read one teleinfo MQTT frame and output the frame
"""

import _thread
import argparse
import json
import sys
import traceback
import globals
import paho.mqtt.client as mqtt_client
from threading import Thread, Lock

try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    #sys.exit(1)
from datetime import datetime

class error(Exception):
    def __init__(self, value):
        self.value = value
    def __str__(self):
        return repr(self.value)


# ----------------------------------------------------------------------------
# Teleinfo core
# ----------------------------------------------------------------------------
def handler(signum=None, frame=None):
    logging.debug("MQTT------Signal %i caught, exiting..." % int(signum))
    shutdown()

def mqtt_on_log( client, userdata, level, buf ):
    logging.info( "MQTT------log: " + buf)

def mqtt_on_connect( client, userdata, flags, rc ):
    logging.info( "MQTT------Connexion: code retour = %d" % rc )
    logging.info( "MQTT------Connexion: Statut = %s" % ("OK" if rc==0 else "échec") )

def mqtt_on_disconnect(client, userdata, rc):
    logging.info("MQTT------disconnecting reason  "  +str(rc))
    shutdown()

def mqtt_on_message(client, userdata, message):
    """lecture des trames MQTT"""
    #logging.info("GLOBAL------Debut reception MQTT...")
    data = {}
    _SendData = {}
    y = str(message.payload.decode("utf-8"))
    logging.debug( "MQTT------Topic : %s" % message.topic )
    logging.debug( "MQTT------Data  : " + y )
    trouveTIC = False
    try:
        x = json.loads(str(y))
        for key in x:
                for keys in x[key]:
                    if keys == "ADCO" or keys == "ADSC" or trouveTIC:
                        if trouveTIC == False:
                            logging.debug( "------------------------------------") 
                            device = keys
                        trouveTIC = True
                        valeur = str(x[key][keys])
                        if keys == 'PTEC':
                            valeur = valeur.replace(".", "")
                            valeur = valeur.replace(")", "")
                            data[keys] = valeur
                        elif keys == 'OPTARIF':
                            valeur = valeur.replace(".", "")
                            valeur = valeur.replace(")", "")
                            data[keys] = valeur
                        else:
                            # valeur = valeur.replace(" ", "%20")
                            data[keys] = valeur
                        logging.debug( "MQTT------ " + str(keys) + " : " +  valeur)
    except:
        logging.debug("MQTT------message autre")
    if trouveTIC:
            for cle, valeur in data.items():
                _SendData[cle] = valeur
            try:
                _SendData["device"] = data[device]
                globals.JEEDOM_COM.add_changes('device::' + data[device], _SendData)
            except Exception:
                error_com = "Connection error"
                logging.error("MQTT------" + error_com)
    
def read_socket(cycle):
    while True:
        try:
            global JEEDOM_SOCKET_MESSAGE
            if not JEEDOM_SOCKET_MESSAGE.empty():
                logging.debug("SOCKET-READ------Message received in socket JEEDOM_SOCKET_MESSAGE")
                message = json.loads(JEEDOM_SOCKET_MESSAGE.get())
                logging.debug("SOCKET-READ------Message received in socket JEEDOM_SOCKET_MESSAGE " + message['cmd'])
                if message['apikey'] != globals.apikey:
                    logging.error("SOCKET-READ------Invalid apikey from socket : " + str(message))
                    return
                logging.debug('SOCKET-READ------Received command from jeedom : ' + str(message['cmd']))
                if message['cmd'] == 'action':
                    logging.debug('SOCKET-READ------Attempt an action on a device')
                    _thread.start_new_thread(action_handler, (message,))
                    logging.debug('SOCKET-READ------Action Thread Launched')
                elif message['cmd'] == 'changelog':
                    log = logging.getLogger()
                    for hdlr in log.handlers[:]:
                        log.removeHandler(hdlr)
                    jeedom_utils.set_log_level('info')
                    logging.info('SOCKET-READ------Passage des log du demon en mode ' + message['level'])
                    for hdlr in log.handlers[:]:
                        log.removeHandler(hdlr)
                    jeedom_utils.set_log_level(message['level'])
        except Exception as e:
            logging.error("SOCKET-READ------Exception on socket : %s" % str(e))
            logging.debug("MQTT------" + traceback.format_exc())
        time.sleep(cycle)

def listen():
    jeedom_socket.open()
    try:
        _thread.start_new_thread(read_socket, (globals.cycle,))
        _thread.start_new_thread(listen_mqtt())
        logging.info('MQTT------Tout roule')
        while 1:
            time.sleep(0.5)
    except KeyboardInterrupt:
        shutdown()


def listen_mqtt():
    logging.info("MQTT------Start listening...")
    logging.info("MQTT------Preparing Teleinfo...")
    logging.info('MQTT------Read Socket Thread Launched')
    logging.info("MQTT------Start listening MQTT...")
    client = mqtt_client.Client( client_id="", clean_session=True)

    # Assignation des fonctions de rappel
    client.on_message = mqtt_on_message
    client.on_connect = mqtt_on_connect
    client.on_disconnect = mqtt_on_disconnect

    # Connexion broker
    client.username_pw_set( username=globals.mqtt_username, password=globals.mqtt_username )
    client.connect( host=globals.mqtt_broker, port=int(globals.mqtt_port), keepalive=int(globals.mqtt_keepalive))
    client.subscribe(globals.mqtt_topic)
    #client.subscribe("tasmota/teleinfo_full_linky/SENSOR")
    #client.subscribe("tasmota/teleinfo_full/SENSOR")
    # Envoi des messages
    client.loop_forever()  

def shutdown():
    logging.debug("MQTT------Shutdown")
    logging.debug("MQTT------Removing PID file " + str(globals.pidfile))
    try:
        os.remove(globals.pidfile)
    except:
        pass
    try:
        jeedom_socket.close()
    except:
        pass
    logging.debug("MQTT------Exit 0")
    sys.stdout.flush()
    os._exit(0)


# ------------------------------------------------------------------------------
# MAIN
# ------------------------------------------------------------------------------

parser = argparse.ArgumentParser(description='Teleinfo Daemon MQTT for Jeedom plugin')
parser.add_argument("--apikey", help="Value to write", type=str)
parser.add_argument("--loglevel", help="log level", type=str)
parser.add_argument("--callback", help="Value to write", type=str)
parser.add_argument("--pidfile", help="pidfile", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
parser.add_argument("--socketport", help="Socket Port", type=str)
parser.add_argument("--sockethost", help="Socket Host", type=str)
parser.add_argument("--modem", help="presence ou non d un modem", type=str)
parser.add_argument("--mqtt", help="decodage mqtt demande", type=str)
parser.add_argument("--mqtt_broker", help="nom du broker mqtt", type=str)
parser.add_argument("--mqtt_port", help="port utilise par mqtt", type=str)
parser.add_argument("--mqtt_keepalive", help="keep alive utilise par mqtt", type=str)
parser.add_argument("--mqtt_topic", help="topic mqtt a ecouter", type=str)
parser.add_argument("--mqtt_username", help="utilisateur declare sur le broker mqtt", type=str)
parser.add_argument("--mqtt_password", help="mot de passe pour le broker mqtt", type=str)
args = parser.parse_args()
if args.pidfile:
    globals.pidfile = args.pidfile
if args.socketport:
    globals.socketport = args.socketport
if args.sockethost:
    globals.sockethost = args.sockethost
if args.loglevel:
    globals.log_level = args.loglevel
if args.callback:
    globals.callback = args.callback
if args.cycle:
    globals.cycle = args.cycle
if args.apikey:
    globals.apikey = args.apikey
if args.modem:
    globals.modem = args.modem
if args.mqtt:
    globals.mqtt = args.mqtt
if args.mqtt_broker:
    globals.mqtt_broker = args.mqtt_broker
if args.mqtt_port:
    globals.mqtt_port = int(args.mqtt_port)
if args.mqtt_keepalive:
    globals.mqtt_keepalive = int(args.mqtt_keepalive)
if args.mqtt_topic:
    globals.mqtt_topic = args.mqtt_topic
if args.mqtt_username:
    globals.mqtt_username = args.mqtt_username
if args.mqtt_password:
    globals.mqtt_password = args.mqtt_password


globals.socketport = int(globals.socketport)
globals.cycle = float(globals.cycle)
jeedom_utils.set_log_level(globals.log_level)
globals.pidfile = globals.pidfile + "_Mqtt.pid"
logging.info('MQTT------Start teleinfo')
signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)
logging.info('MQTT------Socket port : ' + str(globals.socketport))
logging.info('MQTT------Broker : ' + str(globals.mqtt_broker))
logging.info('MQTT------Broker port : ' + str(globals.mqtt_port))
logging.info('MQTT------User : ' + str(globals.mqtt_username))
logging.info('MQTT------pass : ' + '*******')
logging.info('MQTT------Topic : ' + str(globals.mqtt_topic))
logging.info('MQTT------Log level : ' + str(globals.log_level))

try:
    jeedom_utils.write_pid(str(globals.pidfile))
    globals.JEEDOM_COM = jeedom_com(apikey = globals.apikey,url = globals.callback,cycle=globals.cycle)
    if not globals.JEEDOM_COM.test():
        logging.error('MQTT------Probleme de connexion reseau. Verifier votre configuration.')
        shutdown()
    jeedom_socket = jeedom_socket(port=globals.socketport,address=globals.sockethost)
    listen()
except Exception as e:
    logging.error('MQTT------Erreur fatale : '+str(e))
    shutdown()





logging.info('MQTT------fin')

sys.exit()
