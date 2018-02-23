#!/bin/bash
touch /tmp/teleinfo_in_progress
echo 0 > /tmp/teleinfo_in_progress
echo "Lancement de l'installation des dépendances Téléinfo"
echo 10 > /tmp/teleinfo_in_progress
sudo apt-get -y install python-ftdi
echo 20 > /tmp/teleinfo_in_progress
sudo apt-get -y install python-ftdi1
echo 30 > /tmp/teleinfo_in_progress
sudo apt-get -y install python-serial
echo 50 > /tmp/teleinfo_in_progress
if [ -e /dev/ttyAMA0 ];  then
    sudo sed -i 's/console=ttyAMA0,115200//; s/kgdboc=ttyAMA0,115200//' /boot/cmdline.txt
    if [ -e /etc/inittab ]; then
        sudo sed -i 's|[^:]*:[^:]*:respawn:/sbin/getty[^:]*ttyAMA0[^:]*||' /etc/inittab
    fi
    sudo systemctl stop serial-getty@ttyAMA0.service
    sudo systemctl disable serial-getty@ttyAMA0.service
fi
echo 100 > /tmp/teleinfo_in_progress
echo "Everything is successfully installed!"
rm /tmp/teleinfo_in_progress
