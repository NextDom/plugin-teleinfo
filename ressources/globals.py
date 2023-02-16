# coding: utf-8
JEEDOM_COM = ''
TELEINFO_SERIAL = ''
TELEINFO_FTDI = ''
TELEINFO_FTDI_CONTEXT = ''
log_level = "info"
pidfile = '/tmp/jeedom/teleinfo/teleinfo'
apikey = ''
callback = ''
cycle = 0.3
cycle_sommeil = 0.5
type = 'local'
socketport = 55062
sockethost = '127.0.0.1'
ftdi_context = ''
# TELEINFO settings
frame_length = 3000  # Nb chars to read to ensure to get a least one complete raw frame

# Device name
port = '/dev/ttyUSB0'
mode = 'historique'
vitesse = '1200'

# MQTT
modem = ''
mqtt = ''
mqtt_broker = ''
mqtt_port = ''
mqtt_topic = '#'
mqtt_keepalive = 45
mqtt_username = ''
mqtt_password = ''
