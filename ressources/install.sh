#!/bin/bash
touch /tmp/teleinfo_in_progress
echo 0 > /tmp/teleinfo_in_progress
echo "Lancement de l'installation des dépendances Téléinfo"
echo 10 > /tmp/teleinfo_in_progress
sudo apt-get -y install python-ftdi
sudo apt-get -y install python-serial
echo 50 > /tmp/teleinfo_in_progress
echo 100 > /tmp/teleinfo_in_progress
echo "Everything is successfully installed!"
rm /tmp/teleinfo_in_progress
