# post-install script for Jeedom plugin teleinfo
#!/bin/bash
PROGRESS_FILE=/tmp/jeedom_install_in_progress_teleinfo
if [ ! -z $1 ]; then
    PROGRESS_FILE=$1
fi
date
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "**********************************"
echo "*  Installation des dépendances  *"
echo "**********************************"
BASEDIR=/var/www/html/plugins/teleinfo/ressources

echo 5 > ${PROGRESS_FILE}
sudo apt-get update
sudo apt-get upgrade
date
#echo 10 > ${PROGRESS_FILE}
#sudo apt remove -y python3-serial

echo 10 > ${PROGRESS_FILE}
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y libxml2-dev libxslt-dev

echo 15 > ${PROGRESS_FILE}
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y python3
date

echo 20 > ${PROGRESS_FILE}
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y python3-venv python3-pip python3-dev
date

echo 25 > ${PROGRESS_FILE}
sudo -u www-data python3 -m venv $BASEDIR/venv
date

sudo -u www-data $BASEDIR/venv/bin/python3 -m pip install --upgrade pip wheel

echo 30 > ${PROGRESS_FILE}
sudo -u www-data $BASEDIR/venv/bin/pip3 install --no-cache-dir pylibftdi
echo 35 > ${PROGRESS_FILE}
sudo -u www-data $BASEDIR/venv/bin/pip3 install --no-cache-dir six
echo 40 > ${PROGRESS_FILE}
sudo -u www-data $BASEDIR/venv/bin/pip3 install --no-cache-dir pyserial
echo 45 > ${PROGRESS_FILE}
sudo -u www-data $BASEDIR/venv/bin/pip3 install --no-cache-dir setuptools
echo 50 > ${PROGRESS_FILE}
sudo -u www-data $BASEDIR/venv/bin/pip3 install --no-cache-dir requests
echo 55 > ${PROGRESS_FILE}
sudo -u www-data $BASEDIR/venv/bin/pip3 install --no-cache-dir pyudev
echo 60 > ${PROGRESS_FILE}
sudo -u www-data $BASEDIR/venv/bin/pip3 install --no-cache-dir paho-mqtt==1.6.1

date
rm ${PROGRESS_FILE}
echo "*************************************"
echo "*  Installation des dépendances OK  *"
echo "*************************************"