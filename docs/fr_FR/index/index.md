<br><br>

# Nouveautés
<br><br><br><br><br><br><br>
# Après l'installation

<br>
une fois le plugin installé Jeedom vous propose d'aller dans le panneau de configuration:
<br>

<a href="../../images/teleinfo_config01.png">
<img src="../../images/teleinfo_config01.png" alt="configuration" style="width:400px;"/>
<a>
<br><br><br><br><br>


## configuration:
<br>

<a href="../../images/teleinfo_config02.png">
<img src="../../images/teleinfo_config02.png" alt="configuration" style="width:400px;"/>
</a>
<br><br><br><br><br><br><br>

## Détection automatique:
<br>

Ne fonctionne pas à l'heure actuelle. Affiche systématiquement la TIC du compteur en mode historique.
<br>

Cette option a été dévalidée.
<br><br><br><br><br><br><br>

## Bloquer la création automatique des compteurs
<br>

Décocher cette case permet au plugin de créer un nouveau compteur en fonction de la trame TIC reçue.
Cette option n'est à décocher que si c'est votre première utilisation du plugin ou si vous changez de compteur ou encore si vous installez un nouveau compteur.
<br><br><br><br><br><br><br>

# Le Panel:
<br><br>

<a href="../../images/teleinformation_panel0.png">
<img src="../../images/teleinformation_panel0.png" alt="configuration" style="width:400px;"/>
<a>
<br><br>

Cocher cette case pour permettre l'affichage du panel que vous pourrez afficher via le menu:
<br>

<a href="../../images/teleinformation_panel1.png">
<img src="../../images/teleinformation_panel1.png" alt="configuration" style="width:400px;"/>
<a>
<br><br>
Le panel en lui même sera vu plus loin dans cette doc

<br><br><br><br><br><br><br><br><br><br><br>

# Configuration avancée:
<br>


Cliquer sur le + permet d'afficher les paramètres de configuration du modem
<br>

<a href="../../images/teleinfo_config03.png">
<img src="../../images/teleinfo_config03.png" alt="configuration" style="width:400px;"/>
<a>
<br><br><br><br><br><br><br><br><br><br>

## Compteur type Linky:
<br>

Cette option permet de faire la différence entre le mode TIC historique ou standard. Le mode TIC peut être identifié pour un compteur Linky sur la cadran du compteur en appuyant plusieurs fois sur le + ou le -. Tous les autres compteurs sont en mode historique, seul le Linky peut avoir un mode standard.
Si vous avez un mode historique il ne faut pas cocher cette case
<br><br><br><br><br><br><br><br><br><br><br><br>

## Vitesse:
<br>

Si vous avez un mode historique la vitesse doit être fixée à 1200
Si vous avez un mode standard la vitesse doit être fixée à 9600
<br><br><br><br><br><br><br><br><br><br><br><br>

# Options:
<br>

<a href="../../images/teleinfo_options.png">
<img src="../../images/teleinfo_options.png" alt="configuration" style="width:400px;"/>
<a>
<br><br>
Ces options ne servent que pour l'affichage des statistiques dans le panel selon la même forme que dans les versions antérieures à la V 4.7.3
<br><br><br><br><br><br><br><br><br><br><br><br>

## Index conso globales (nouveau)
<br>
Permet d'indiquer au plugin quel est l'index qui sert à construire les statistiques de la consommation globale
<br><br><br><br><br><br><br><br><br><br><br><br>

## Index conso HP
<br>
Si vous avez un abonnement HP / HC
<br><br><br><br><br><br><br><br><br><br><br><br>

## Index conso HC
<br>
Si vous avez un abonnement HP / HC
<br><br><br><br><br><br><br><br><br><br><br><br>

## Index Production
<br>
Si vous avez un compteur qui sert aussi à comptabiliser la production que vous envoyez vers le réseau (option uniquement possible avec un linky en mode standard)
<br><br><br><br><br><br><br><br><br><br><br><br>

## Prix kWh
<br>
Sert à indiquer le tarif appliqué pour chaque index cité avant
<br><br><br><br><br><br><br><br><br><br><br><br>

# Le compteur
<br>
Si des trames TIC sont reçues par le plugin et que l'option de création automatique de nouveau compteur n'est pas bloquée alors celui ci va créer un nouveau compteur:
<br><br>
<a href="../../images/teleinfo_compteur02.png">
<img src="../../images/teleinfo_compteur02.png" alt="configuration" style="width:400px;"/>
<a>
<br><br>
En cliquant dessus vous rentrez dans l'équipement
<br><br><br><br><br><br><br><br><br><br>

# L'équipement:
<br>
<a href="../../images/teleinfo_compteur02.png">
<img src="../../images/teleinfo_compteur02.png" alt="configuration" style="width:400px;"/>
<a>
<br><br>

## Paramètres de base
<br>
rien de neuf
<br><br>

## Création des commandes
<br>
Autorise la création de nouvelles commandes reçues via le modem. Permet de créer automatiquement les commandes qui n'existaient pas avant ou qui auraient été effacées.
<br><br>

## Compteur en mode conso ET prod
<br>
Introduit avec la version 4.7.2, permet de suivre les statistiques d'un compteur servant à comptabiliser sa consommation et sa production. Production en autoconsommation par exemple. Cette possibilité n'existe que si vous avez un linky en mode standard.
<br><br>

## Abo HP / HC (ancienne méthode)
<br>
Si vous voulez continuer à suivre les statistiques dans le panel de votre compteur de la même façon qu'avant la V4.7.3 et que vous avez un abonnement qui incrémente les index HP et HC
<br><br>

## Utilisation des nouveaux index (nouveau)
<br><br>
Permet de suivre jusqu'à 10 index dans le panel paramètrables indépendamment les uns des autres + l'index 00 qui sert soit au suivi des conso pour un abonnement de base soit de totalisateur de l'ensembles des index de 01 à 10.
<br><br><br><br><br><br><br><br><br><br>









# [Retour à la documentation principale](/plugin-teleinfo/fr_FR/)
