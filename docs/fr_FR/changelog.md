# Changelog

**[Fil d'actualité du plugin](https://community.jeedom.com/t/plugin-teleinfo-actualites/100688?u=noyax37)**

Pour toutes demandes : 
 - [https://github.com/NextDom/plugin-teleinfo/issues](https://github.com/NextDom/plugin-teleinfo/issues) 
 - [https://community.jeedom.com/tag/plugin-teleinfo](https://community.jeedom.com/tag/plugin-teleinfo)

 - [Doc version stable](https://nextdom.github.io/plugin-teleinfo/fr_FR/)
 - [Doc version bêta](https://noyax.github.io/plugin-teleinfo/fr_FR/)

 - [Changelog version stable](https://nextdom.github.io/plugin-teleinfo/fr_FR/changelog.md)
 - [Changelog version bêta](https://noyax.github.io/plugin-teleinfo/fr_FR/changelog.md)

## 4.8.2c (09-09-2023) => version stable ET bêta
- Pour les deux versions: résolution d'un problème de redémarrage intempestif du démon 

## 4.8.2b (09-09-2023) => version stable ET bêta
- Pour la version béta: pas de changement, cette montée en version n'est là que pour vous prévenir que la version stable rejoint la version béta donc si vous ne voulez pas rester en béta vous pouvez rebasculer en stable
- pour la version stable: toutes les évolutions depuis la dernières version stable qui était la 4.7.3h

## 4.8.2a (10-08-2023) => version bêta
- correction afin de faire fonctionner 2 modems en même temps sur les ports 1 et 2 de teleinfo
- amélioration de la gestion des démons
- modification du traitement des trames issues du compteur pour être certain de traiter l'ensemble des données envoyées par la TIC
- Déplacement du paramètre de configuration du plugin "cycle de sommeil" depuis la partie générale vers la partie modem car cela ne concerne que lui
- ajout d'un contrôle si la valeur d'un des index "EA*" (EASF.., EAST, EAIT) relevée est inférieure à la valeur relevée précédente, un index ne peut qu'augmenter
- log info un peu moins verbeux, passage de certains logs de info vers debug. Pour montrer que le log teleinfo_daemon_conso tourne => affichage toutes les 30 minutes de la dernière trame reçue de la TIC et les infos envoyées vers Jeedom (le démon n'envoie pas vers Jeedom des infos qui n'ont pas changées)

## 4.8.1a (16-06-2023) => version bêta
- Affichage du signe € à la place de $ dans les options du plugin
- Ajout d'un bouton pour supprimer la température extérieure dans les options du plugin
- Correction d'une erreur dans la recopie des index lorsque la productions sur une journée = 0 (merci @Bison)
- correction lorsque la valeur d'un index *HIER* était à 0 cette valeur n'était pas enregistrée et donc on continuait à voir la dernière valeur enregistrée différente de 0 (merci @stouph19)
- modification du libellé "Compteur 1 Type Linky" qui prétait à confusion

## 4.8.0c (23-03-2023) => version bêta
- MQTT: correction de problèmes sur inscription à un topic
- Panel: ajout de la possibilité de faire des pauses dans l'interrogation du serveur ce qui permet d'éviter les erreurs "Too many requests" sur certaines configuration (pour l'instant la pause se paramètre "en dur" dans un des fichiers mais sera implémentée prochainement dans les paramètres du plugin) => si vous avez cette erreur => me demander la procédure
- Equipement: ajout de l'affichage de la valeur des commandes

## 4.8.0b (16-03-2023) => version bêta
- correction de problèmes user / mot de passe
- ajout de la compatibilité avec [teleinfo2mqtt](https://fmartinou.github.io/teleinfo2mqtt/#/introduction/)

## 4.8.0a (14-03-2023)
- apparition de la possibilité d'utiliser le MQTT pour rapatrier des données de téléinfo déportées => ATTENTION: après l'installation de cette version il est fortement conseillé d'aller dans la page configuration et vérifier que tout est bien configuré
- personalisation possible des couleurs des courbes affichées dans le panel
- disparition du mode "débug forcé temporaire", la modification du mode de log dans la configuration du plugin s'applique maintenant à tous les fichiers de log
- correction qq pb mineurs: [celui-ci](https://community.jeedom.com/t/plugin-teleinfo-actualites/100688/9?u=noyax37) et [celui-là aussi](https://community.jeedom.com/t/plugin-teleinfo-actualites/100688/10?u=noyax37)


## 4.7.3h (16-06-2023) => stable
- correction lorsque la valeur d'un index *HIER* était à 0 cette valeur n'était pas enregistrée et donc on continuait à voir la dernière valeur enregistrée différente de 0 (merci @stouph19)

## 4.7.3g (14-03-2023) => stable
- correction d'erreurs mineures

## 4.7.3f (09-02-2022)
- correction de la multiplication par 2 du coût en global pour les compteurs en mode historique avec un abonnement autre que BASE
- modification du css du panel pour qu'il ne s'applique qu'au panel
- correction d'un pb d'affichage des index du panel lorsque plusieurs compteurs avec des index différents

## 4.7.3e (08-02-2023)
- affichage de 2 fois le coût en global sur J-1
- n'affiche les valeurs = 0 dans les graphiques du panel => necessite une copie des index
- préparation pour MQTT (téléchargement du module paho-mqtt)
- correction sur graphique des puissances instantanées qui apparaissaient en kWh au lieu de VA
- les coûts de l'affichage classique n'étaient pas affichés pour les HP et HC

## 4.7.3d (31-01-2023)
- modif pour tenir compte des index à 0 sur la journée pour les abonnements autres que BASE
- modif pour générer les stats globales (somme des index) pour les compteur dont la TIC fonctionne en mode historique.
- correction d'une erreur d'affichage concernant la partie PROD du tableau du panel quand elle ne doit pas être affichée
Vous serez sans doute obligé de relancer une copie des index

## 4.7.3c (29-01-2023)
- correction suite à une erreur de saisie qui faisait planter le plugin.

## 4.7.3b (28-01-2023)
- correction sur la fonction qui calcule le coût de la journée qui comptait en double uniquement pour un abonnement de base.

## 4.7.3a (27-01-2023)
- correction sur la fonction "optimize" pour ne pas enregistrer la valeur max du jour actuel à 23h59mn59s alors que la journée n'est pas terminée

## 4.7.3 (26-01-2023)
- création d'index de 00 à 10 pour suivre tous les tarifs possibles en fonction de vos abonnements. Voir la doc https://noyax.github.io/plugin-teleinfo/fr_FR/index/
- mise à jour de l'optimisation des données. Maintenant cette fonctionnalité conserve le mini par heure et le maxi de la dernière heure de la journée lorsque le mode d elissage est sur "aucun", la moyenne par heure lorsque le lissage est sur "moyenne" et le max de la journée pour les "STAT_YESTERDAY_***"

## 4.7.2b (24-01-2023) => version stable
- pas de nouveauté, juste pour dire que le v 4.7.3 est parue en version béta

## 4.7.2a (14-01-2023)
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
