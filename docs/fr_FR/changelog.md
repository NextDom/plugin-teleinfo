# Changelog

Pour toutes demandes : [https://github.com/NextDom/plugin-teleinfo/issues](https://github.com/NextDom/plugin-teleinfo/issues)

### 4.4.0 (04-04-2019)

* Détection automatique du type d'abonnement.
* Détection automatique du compteur depuis la configuration (non disponible pour le modem Cartelectronic 2 compteurs).
* Déclaration index production.
* Correction du diagnostique.
* Maintenance de l'historique des index.

### 4.3.2 (01-04-2019)

* Stat de production.
* Augmentation du délais du passage des logs normal au démarrage du démon.
* Changement pour le niveau de log, création du socket oublié pour la prod.
* Bug sur le calcul de la consommation moyenne.

### 4.3.1 (25-03-2019)

* Corrections pour le modem 2 compteurs.

### 4.3.0 (18-03-2019)

* Modifications pour deport du daemon (@jlayec)

### 4.2.4 (15-03-2019)

* Bug sur le type de commandes lors de la création automatique.

### 4.2.3 (12-03-2019)

* Globalisation des index
* Correction changement niveau log du démon
* Correction dépendances
* Coloration du type de commande
* Correction sur le panel
* Ajout de la configuration des index a utiliser
* Ajout librairie tagsinput

### 4.2.2 (08-03-2019)

* Correction du panel santé spécifique

### 4.2.1 (08-03-2019)

* Correction du bug de création du répertoire de conservation du pid.

### 4.2.0 (06-03-2019)

* Gestion des indicateurs linky standard
* Affichage de la production

### 4.1.3 (06-03-2019)

* Erreur lors de la création automatique d'un compteur.

### 4.1.2 (06-03-2019)

* Correction des dépendances
* Correction du socket modem production

### 4.1.1 (05-03-2019)

* Correction de la ré-envoi des données.

### 4.1.0 (05-03-2019)

* Ajout d'un affichage sur le panel

### 4.0.1 (03-03-2019)

* Bugfix de l'interval calcul des stats mois et année.
* Automation de la création de certaines stats pour le panel.

### 4.0.0  (28-02-2019)

* Refonte de la communication entre le démon et jeedom.
* Mise à niveau pour compatibilité
* Changement de l'API

### 3.0.2

* Droits sur le port

### 3.0.1

* Correction erreur horodatage

### 3.0.0

* Compatibilité Linky
* Fonction diagnostique

### 2.7.3

* Bugfix sur le démon afin de prévenir des crash dans certaines conditions.

### 2.7.2

* Bugfix sur les dépendances utilisées

### 2.7.1

* Problème lors de l'installation des dépendances

### 2.7.0

* Issue du démon 2 compteurs et librairie ftdi.
* Reprise du processus des dépendances.
* Erreurs 500 sur le panel.
* Suppresion de fonctions dépréciés

### 2.6.4

* Bugfix erreur lors de l'enregistrement

### 2.6.3

* Lancement du démon pour la partie production

### 2.6.2

* Etat du démon pour le mode 2 compteurs

### 2.6.1

* Revue de certains designs
* Ajout de la fonction compteur de production

### 2.6.0

* Modification du panel
* Ajout de statistiques
