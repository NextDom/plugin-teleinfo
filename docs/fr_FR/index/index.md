

Les Index
===

## Le plugin


### Après l'installation

une fois le plugin installé Jeedom vous propose d'aller dans le panneau de configuration:

[Configuration](../../images/teleinfo_config01.png)


#### "configuration" (1):

[Configuration 1](../../images/teleinfo_config02.png)

##### Détection automatique:

Ne fonctionne pas à l'heure actuelle. Affiche systématiquement la TIC du compteur en mode historique.

Cette option a été dévalidée.

##### Bloquer la création automatique des compteurs

Décocher cette case permet au plugin de créer un nouveau compteur en fonction de la trame TIC reçue.
Cette option n'est à décocher que si c'est votre première utilisation du plugin ou si vous changez de compteur ou encore si vous installez un nouveau compteur.

##### Configuration avancée:


Cliquer sur le + permet d'afficher les paramètres de configuration du modem

[Configuration modem](../../images/teleinfo_config03.png)

###### Compteur type Linky:

Cette option permet de faire la différence entre le mode TIC historique ou standard. Le mode TIC peut être identifié pour un compteur Linky sur la cadran du compteur en appuyant plusieurs fois sur le + ou le -. Tous les autres compteurs sont en mode historique, seul le Linky peut avoir un mode standard.
Si vous avez un mode historique il ne faut pas cocher cette case

###### Vitesse:

Si vous avez un mode historique la vitesse doit être fixée à 1200
Si vous avez un mode standard la vitesse doit être fixée à 9600





  

[Retour à la documentation principale](/plugin-teleinfo/fr_FR/)
===

