#!/bin/bash
PROGRESS_FILE=/tmp/jeedom/teleinfo/dependance
PROGRESS_FILE=$1
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}

function apt_install {
  sudo apt-get -y install "$@"
  if [ $? -ne 0 ]; then
    echo "could not install $1 - abort"
    rm ${PROGRESS_FILE}
    exit 1
  fi
}

function pip_install {
  sudo pip install "$@"
  if [ $? -ne 0 ]; then
    echo "could not install $p - abort"
    rm ${PROGRESS_FILE}
    exit 1
  fi
}
#echo "Prérequis python3"
echo 10 > ${PROGRESS_FILE}
#apt_install python3
#apt_install python3-pip
echo "Lancement de l'installation/mise à jour des dépendances Téléinfo"
echo "Raffraichissement du système"
sudo apt-get update
echo 20 > ${PROGRESS_FILE}
echo "Installation de la librairie ftdi pour modem 2 compteurs"
sudo apt-get -y install python-ftdi
sudo apt-get -y install python-ftdi1
#pip_install pyftdi
pip_install pylibftdi
echo 30 > ${PROGRESS_FILE}
#pip_install python-ftdi1
echo 40 > ${PROGRESS_FILE}
echo "Installation de la librairie serial"
#pip_install serial
sudo pip uninstall -y serial
#apt_install python-serial
pip_install pyserial
echo 50 > ${PROGRESS_FILE}
echo "Mise à jour de cmdline ou inittab suivant système"
if [ -e /dev/ttyAMA0 ];  then
    sed -i 's/console=ttyAMA0,115200//; s/kgdboc=ttyAMA0,115200//' /boot/cmdline.txt
    if [ -e /etc/inittab ]; then
        sed -i 's|[^:]*:[^:]*:respawn:/sbin/getty[^:]*ttyAMA0[^:]*||' /etc/inittab
    fi
    echo "Désactivation de la sortie serie vers la console"
    sudo systemctl stop serial-getty@ttyAMA0.service
    sudo systemctl disable serial-getty@ttyAMA0.service
fi
echo 100 > ${PROGRESS_FILE}
echo "Everything is successfully installed!"
rm ${PROGRESS_FILE}
