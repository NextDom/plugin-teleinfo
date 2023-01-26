# Changelog

Pour toutes demandes : 
 - [https://github.com/NextDom/plugin-teleinfo/issues](https://github.com/NextDom/plugin-teleinfo/issues) 
 - [https://community.jeedom.com/tag/plugin-teleinfo](https://community.jeedom.com/tag/plugin-teleinfo)

 - [Doc version stable](https://nextdom.github.io/plugin-teleinfo/fr_FR/)
 - [Doc version bêta](https://noyax.github.io/plugin-teleinfo/fr_FR/)

 - [Changelog version stable](https://nextdom.github.io/plugin-teleinfo/fr_FR/changelog.md)
 - [Changelog version bêta](https://noyax.github.io/plugin-teleinfo/fr_FR/changelog.md)

## 4.7.3a (24-01-2022) => version bêta
- correction sur la fonction "optimize" pour ne pas enregistrer la valeur max du jour actuel à 23h59mn59s alors que la journée n'est pas terminée

## 4.7.3 (24-01-2022)
- création d'index de 00 à 10 pour suivre tous les tarifs possibles en fonction de vos abonnements. Voir la doc https://noyax.github.io/plugin-teleinfo/fr_FR/index/
- mise à jour de l'optimisation des données. Maintenant cette fonctionnalité conserve le mini par heure et le maxi de la dernière heure de la journée lorsque le mode d elissage est sur "aucun", la moyenne par heure lorsque le lissage est sur "moyenne" et le max de la journée pour les "STAT_YESTERDAY_***"

## 4.7.2b (24-01-2022) => version stable
- pas de nouveauté, juste pour dire que le v 4.7.3 est parue en version béta

## 4.7.2a (14-01-2022)
- correction manque courbe HC sur graph annuel
- mise à jour de la documentation

## 4.7.2 (27-12-2022)
Modification du pannel:
- ajout de la courbe des températures dans tous les graphiques sauf jour courant
- durée de la courbe des consommations instantanées sur l'intégralité de la période sélectionnée et non plus sur seulement le jour en cours
- ajout de case à cocher pour faire correspondre son abonnement aux courbes à afficher
- ajout de la courbe de production si 1 seul linky est utilisé en conso avec des panneaux solaire
- modification d'une erreur dans les stats affichés en haut du panel qui ne prenaient pas en compte le dernier jour de la période
- prise en compte d'un diviseur si on affiche les stat en kwh par exemple directement dans le compteur
- reprise de la partie haute du panel
- ajout courbe pluri annuelle
- pas mal de corrections de détails

## 4.7.1 (04-02-2022)

Ajout des modifications pour la compatibilité Jeedom 4.2. Merci à Noyax37
passage en python3

modem cartelectronic version 2 compteurs =>
- si cavalier interne modem sur 1 compteur => ok => attirer l'attention sur la doc à ne pas cocher "modem caretelectronic 2 compteurs" dans configuration
- si cavalier interne modem sur 2 compteurs => aucune idée si ça fonctionne ou pas, normalement oui mais...

un peu de ménage dans les fichiers temporaires

ajout du fichier packages.json pour profiter de l'évolution de la gestion des dépendances liée à jeedom en v4.2 https://blog.jeedom.com/6170-introduction-jeedom-4-2-installation-de-dependance/

je ne sais plus...

## 4.6.5b (provisoire 10-01-2023)
Aucune modification apportée sauf la supression de "l'erreur 500". Il s'agit surtout d'un avis pour ceux qui sont encore avec ce plugin en version stable.

La version 4.7.1 n'apporte aucun changement ni dans le traitement des données ni dans le traitement des statistiques juste dans leur affichage.

Cette mise à jour et ce qui va suivre est expliqué là:
https://community.jeedom.com/t/plugin-teleinfo-annonce-importante/99198?u=noyax37


## 4.6.5b (provisoire 10-01-2023)
Aucune modification apportée mais un avis pour ceux qui sont encore avec ce plugin en version stable.

La version 4.7.1 n'apporte aucun changement ni dans le traitement des données ni dans le traitement des statistiques.

Cette mise à jour et ce qui va suivre est expliqué là:
https://community.jeedom.com/t/plugin-teleinfo-annonce-importante/99198

### 4.6.5 (28-10-2019)
* Correction calcul puissance dernière minute dans le cas de plusieurs compteurs

### 4.6.4 (28-10-2019)

* Correction affichage SINSTS / PAPP

### 4.6.3 (28-10-2019)

* Re-Correction SINSTI

### 4.6.2 (11-06-2019)

* Mise à jour panel

### 4.6.1 (16-05-2019)

* Correction SINSTI par SINST1

### 4.6.0 (08-05-2019)

* Ajout coût sur le panel

### 4.5.1 (01-05-2019)

* Correction de l'affichage partiel sur le panel
* BugFix sur le calcul des historique (Jeedom double la dernière valeur).

### 4.5.0 (16-04-2019)

* Refonte des statistiques
* Ajout d'une dépendance manquante

### 4.4.0 (04-04-2019)

* Détection automatique du type d'abonnement.
* Détection automatique du compteur depuis la configuration (non disponible pour le modem Cartelectronic 2 compteurs).
* Déclaration index production.
* Correction du diagnostique.
* Maintenance de l'historique des index.
* Nettoyage des statistiques.

### 4.3.2 (01-04-2019)

* Stat de production.
* Augmentation du délais du passage des logs normal au démarrage du démon.
* Changement pour le niveau de log, création du socket oublié pour la prod.
* Bug sur le calcul de la consommation moyenne.

### 4.3.1 (25-03-2019)

* Corrections pour le modem 2 compteurs.

### 4.3.0 (18-03-2019)

* Modifications pour deport du daemon - cf faq pour utilisation

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
