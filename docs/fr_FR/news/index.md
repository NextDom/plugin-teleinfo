<br><br>

# Nouveautés de la version 4.8.0
<br><br><br><br><br><br><br>



# Après l'installation

# configuration (nouvelle présentation avec la V 4.8.0):

<br>

<a href="../images/teleinfo_config02.png">
<img src="../images/teleinfo_config02.png" alt="configuration" style="width:600px;"/>
</a>
<br><br><br><br><br><br><br>

## (1) Configuration générale du plugin

<a href="../images/teleinfo_config04.png">
<img src="../images/teleinfo_config04.png" alt="configuration" style="width:600px;"/>
</a>
<br><br><br>
Un clic sur chaque bouton fait apparaitre un sous menu associé
<br><br><br>

## (1) Bloquer la création automatique des compteurs
<br>

Décocher cette case permet au plugin de créer un nouveau compteur en fonction de la trame TIC reçue.
Cette option n'est à décocher que si c'est votre première utilisation du plugin ou si vous changez de compteur ou encore si vous installez un nouveau compteur.
<br><br><br><br><br><br><br>

## (2) Utilisation d'un modem téléinformation
<br>

Cocher cette case permet au plugin d' utiliser un modem de téléinformation type carteélectronique pour récupérer les données.
Si cette case est cochée, il faut en plus aller configurer la partie "modem"
<br><br><br><br><br><br><br>


## (3) Activer le MQTT (nouveauté v4.8.0)

<br>

Cocher cette case permet au plugin d' utiliser Broker MQTT pour récupérer les données. Le Broker peut être local ou distant.
Si cette case est cochée, il faut en plus aller configurer la partie "MQTT"
<br><br><br><br><br><br><br>


## (4) Partie "sensible" de la configuration du plugin
<br>

Si vous ne savez pas à quoi cela sert, n'y touchez pas.
<br><br><br><br><br><br><br>


<a href="../images/teleinfo_config05.png">
<img src="../images/teleinfo_config05.png" alt="configuration" style="width:600px;"/>
</a>

<br><br><br><br>

## (1) Configuration des ports du modem:
<br>

Sélectionner celui qui correspond à votre modem.

<br><br><br><br><br><br><br>


## (2) Détection automatique:
<br>

Ne fonctionne pas à l'heure actuelle. Affiche systématiquement la TIC du compteur en mode historique.
<br>

Cette option a été dévalidée.
<br><br><br><br><br><br><br>


## (3) Modem 2 compteurs: 

<br>


Permet de faire savoir au plugin que le modem installé est un type 2 compteurs de cartelectronic
<br><br><br><br><br><br><br>


## (4) Configuration avancée:
<br>


Cliquer sur le + permet d'afficher les paramètres de configuration du modem
<br>

<a href="../images/teleinfo_config03.png">
<img src="../images/teleinfo_config03.png" alt="configuration" style="width:600px;"/>
</a>
<br><br><br><br>

### (1) Compteur type Linky:
<br>

Cette option permet de faire la différence entre le mode TIC historique ou standard. Le mode TIC peut être identifié pour un compteur Linky sur la cadran du compteur en appuyant plusieurs fois sur le + ou le -. Tous les autres compteurs sont en mode historique, seul le Linky peut avoir un mode standard.
Si vous avez un mode historique il ne faut pas cocher cette case
<br><br><br><br><br><br><br>

### (2) Vitesse:
<br>

Si vous avez un mode historique la vitesse doit être fixée à 1200
Si vous avez un mode standard la vitesse doit être fixée à 9600
<br><br><br><br><br><br><br><br><br><br><br><br>


<a href="../images/teleinfo_config06.png">
<img src="../images/teleinfo_config06.png" alt="configuration" style="width:600px;"/>
</a>

<br><br><br><br>

## (1) Configuration du Broker MQTT (nouveauté 4.8.0):
<br>

compléter avec les informations nécessaire à la prise en compte du Broker

<br><br><br><br><br><br><br>

## (2) Topic MQTT (nouveauté 4.8.0):
<br>

Si vous ne savez pas quoi saisir, laisser vide. Il est possible aussi de saisir le caractère générique "#".

La meilleure solution est de mettre le topic le plus proche des données que vous voulez récupérer, cela permet d'éviter des temps de traitement inutiles au plugin.

Par exemple, vous n'avez qu'un seul compteur accessible via MQTT alors il est préférable de saisir le topic complet du style "tasmota/compteur_linky/SENSOR".

Par contre si vous avez 2 compteurs sur ce Broker, un sur le topic "tasmota/compteur_linky/SENSOR" et l'autre sur le topic "tasmota/linky/SENSOR" alors le topic à saisir le plus adapté serait "tasmota/#".

Si vous avez des difficultés vous pouvez essayer l'excellent logiciel MQTT Explorer.

<br><br><br><br><br><br><br>


<br>

# L'équipement 2/2:

<br>

<a href="../images/teleinfo_equipement02.png">
<img src="../images/teleinfo_equipement02.png" alt="configuration" style="width:800px;"/>
<a>

<br><br>

## (4) RAZ Couleurs (Nouveauté 4.8.0)
<br>

Permet de remettre les couleurs par défaut des lignes (voir ci dessous)
<br><br>

## (5) Les couleurs de ligne (Nouveauté 4.8.0)
<br>

Permet de sélectionner les couleurs qui vous plaisent pour le traçage des courbes dans le panel
<br><br>
Si les couleurs n'apparaissent pas la 1ère fois (carrés noirs partout) il faut faire une RAZ des couleurs en premier lieu puis sauvegarder
<br><br>
