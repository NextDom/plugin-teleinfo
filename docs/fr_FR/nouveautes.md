<br>

# Nouveautés
<br><br><br><br><br><br><br>
# Après l'installation

<br>
une fois le plugin installé Jeedom vous propose d'aller dans le panneau de configuration:
<br>

<a href="../../images/teleinfo_config01.png">
<img src="../../images/teleinfo_config01.png" alt="configuration" style="width:600px;"/>
<a>
<br><br><br><br><br>


# configuration:
<br>

<a href="../../images/teleinfo_config02.png">
<img src="../../images/teleinfo_config02.png" alt="configuration" style="width:600px;"/>
</a>
<br><br><br><br><br><br><br>

## (1) Détection automatique:
<br>

Ne fonctionne pas à l'heure actuelle. Affiche systématiquement la TIC du compteur en mode historique.
<br>

Cette option a été dévalidée.
<br><br><br><br><br><br><br>

## (2) Bloquer la création automatique des compteurs
<br>

Décocher cette case permet au plugin de créer un nouveau compteur en fonction de la trame TIC reçue.
Cette option n'est à décocher que si c'est votre première utilisation du plugin ou si vous changez de compteur ou encore si vous installez un nouveau compteur.
<br><br><br><br><br><br><br>

## (3) Configuration avancée:
<br>


Cliquer sur le + permet d'afficher les paramètres de configuration du modem
<br>

<a href="../../images/teleinfo_config03.png">
<img src="../../images/teleinfo_config03.png" alt="configuration" style="width:600px;"/>
<a>
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

# Le Panel:
<br><br>

<a href="../../images/teleinformation_panel0.png">
<img src="../../images/teleinformation_panel0.png" alt="configuration" style="width:600px;"/>
<a>
<br><br><br><br>

Cocher cette case pour permettre l'affichage du panel que vous pourrez afficher via le menu:
<br><br><br>

<a href="../../images/teleinformation_panel1.png">
<img src="../../images/teleinformation_panel1.png" alt="configuration" style="width:400px;"/>
<a>
<br><br>

Le panel en lui même sera vu plus loin dans cette doc

<br><br><br><br><br><br><br><br><br><br><br>

# Options:
<br>

<a href="../../images/teleinfo_options.png">
<img src="../../images/teleinfo_options.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

Ces options ne servent que pour l'affichage des statistiques dans le panel selon la même forme que dans les versions antérieures à la V 4.7.3
<br><br><br><br><br><br><br><br><br><br><br><br>

## (1) Index conso globales (nouveau)
<br>

Permet d'indiquer au plugin quel est l'index qui sert à construire les statistiques de la consommation globale
<br><br><br><br><br><br><br><br><br><br><br><br>

## (2) Index conso HP
<br>

Si vous avez un abonnement HP / HC
<br><br><br><br><br><br><br><br><br><br><br><br>

## (3) Index conso HC
<br>

Si vous avez un abonnement HP / HC
<br><br><br><br><br><br><br><br><br><br><br><br>

## (4) Index Production
<br>

Si vous avez un compteur qui sert aussi à comptabiliser la production que vous envoyez vers le réseau (option uniquement possible avec un linky en mode standard)
<br><br><br><br><br><br><br><br><br><br><br><br>

## (5) Prix kWh
<br>

Sert à indiquer le tarif appliqué pour chaque index cité avant
<br><br><br><br><br><br><br><br><br><br><br><br>

# Le compteur
<br>

Si des trames TIC sont reçues par le plugin et que l'option de création automatique de nouveau compteur n'est pas bloquée alors celui ci va créer un nouveau compteur:
<br><br>

<a href="../../images/teleinfo_compteur02.png">
<img src="../../images/teleinfo_compteur02.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

En cliquant dessus vous rentrez dans l'équipement
<br><br><br><br><br><br><br><br><br><br>

# L'équipement 1/2:
<br>
<a href="../../images/teleinfo_equipement01.png">
<img src="../../images/teleinfo_equipement01.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

## (1) Paramètres classiques
<br>

rien de neuf
<br><br>

## (2)Création des commandes
<br>

Autorise la création de nouvelles commandes reçues via le modem. Permet de créer automatiquement les commandes qui n'existaient pas avant ou qui auraient été effacées.
<br><br>

Les commandes ne sont crées que si elles sont effectivement reçues par la trame TIC.
<br><br>

## (3) Compteur en mode conso ET prod
<br>

Introduit avec la version 4.7.2, permet de suivre les statistiques d'un compteur servant à comptabiliser sa consommation et sa production. Production en autoconsommation par exemple. Cette possibilité n'existe que si vous avez un linky en mode standard.
<br><br>

## (4) Abo HP / HC (ancienne méthode)
<br>

Si vous voulez continuer à suivre les statistiques dans le panel de votre compteur de la même façon qu'avant la V4.7.3 et que vous avez un abonnement qui incrémente les index HP et HC
<br><br>

## (5) Utilisation des nouveaux index (nouveau)
<br><br>

Permet de suivre jusqu'à 10 index dans le panel paramètrables indépendamment les uns des autres + l'index 00 qui sert soit au suivi des conso pour un abonnement de base soit de totalisateur de l'ensembles des index de 01 à 10.
<br><br><br><br><br><br><br><br><br><br>

## (6) Maintenance
<br><br>

L'écran sur lequel on arrive en cliquant sur ce bouton sert à montrer l'utilisation en base de données des différentes commandes et à en assurer la maintenance (lissage des données).
<br><br>

<a href="../../images/teleinfo_maintenance.png">
<img src="../../images/teleinfo_maintenance.png" alt="configuration" style="width:800px;"/>
<a>

### (1) Regénérer les stats mensuelles
<br><br>

Ancienne méthode, a une action sur les statistiques HP / HC et Prod. Ne plus employer si vous utilisez les nouveaux index
<br><br>

### (2) Nombre en base
<br><br>

Correspond au total d'enregistrement associés à la commande correspondante
<br><br>

### (3) A lisser
<br><br>

Correspond au total d'enregistrement dont les heures en base ne sont pas égales à **h 00mn 00s
<br><br>

### (4) Lissage
<br><br>

Affiche le mode de lissage utilisé pour la commande correspondante. Si un lissage est paramétré dans votre commande alors cela sera affiché ici: "AVG" (moyenne), "MAX" ou "MIN". Si aucun lissage n'est paramétré pour cette commande alors il sera affiché "aucun".
<br><br>

### (5) >_optimiser
<br><br>

Si le nombre affiché dans la colonne "A lisser" est supérieur à 1 000 alors ce bouton apparait.

L'action de ce bouton dépend de:
- la commande est une des "STAT_YESTERDAY-***" : prend la valeur max de la journée et la recopie sur cette même journée à 00h 00mn 00s
- la commande est une des "STAT_TODAY-***" : prend la valeur max de l'heure' et la recopie sur cette même heure à 00mn 00s
- le mode de lissage a été paramétré sur "AVG" (moyenne): prend la valeur moyenne sur chaque intervalle horaire puis la replace à chaque heure à 00mn et 00s
- le mode de lissage n'est pas paramétré: prend le min de chaque heure et le remplace à chaque heure correspondante à 00mn 00s ET prend le max de la journée et le replace sur cette journée à 23h 59mn 59s
<br><br>


**IMPORTANT:** cette opération est destructrice, pensez à faire une sauvegarde de votre base au cas où un problème survient pendant cette manipulation.
<br><br>

**IMPORTANT bis:** Il est préférable de na pas demander à JEEDOM de lisser automatiquement les index utilisés pour construire les statistiques. En effet JEEDOM ne prend pas la valeur max de la journée lors su lissage ce qui peut ammener à ne pas tenir compte de la dernère heure de la journée (ou de la première si le lissage est sur la valeur MAX). L'inconvénient est qu'il faut penser régulièrement à faire la maintenance des données. Dans une version future je pense proposer le lissage type TELEINFO à intervalle régulier en option.

<br><br><br><br><br><br><br><br><br><br>

# L'équipement 2/2:
<br>

<a href="../../images/teleinfo_equipement02.png">
<img src="../../images/teleinfo_equipement02.png" alt="configuration" style="width:800px;"/>
<a>

<br><br>

## (1) Libellés
<br>

Les libellés sont libres de choix sauf pour l'index00 qui correspond à la consommation globale et l'index de production.
<br>

Ce sont ces libellés qui vont servir de nom pour l'affichage des statistiques et des courbes dans le panel
<br>

**Important:** s'il n'y a pas de libellé => il n'y a pas de suivi de cet index
<br><br>

## (2) Les champs de téléinformation
<br>

<a href="../../images/teleinfo_equipement03.png">
<img src="../../images/teleinfo_equipement03.png" alt="configuration" style="width:200px;"/>
<a>
<br><br>

Liste de choix proposant de choisir en fonction de votre mode du TIC:
1. **historique**: "HCHC" ou "HCHP" ou ... 
2. **standard**: "EASF01" ou "EASF02" ou ...

Ces choix devront être fait en fonction de votre abonnement. Pour vous aider vous pouvez vous reporter à la documentation Enedis [Enedis-NOI-CPT_54E.pdf](../../images/Enedis-NOI-CPT_54E.pdf) ou vous appuyer sur le tableau ci dessous:

<br>
<a href="../../images/teleinformation_liste_index.png">
<img src="../../images/teleinformation_liste_index.png" alt="configuration" style="width:400px;"/>
<a>
<br><br>

Ces champs peuvent être mis sur n'importe quel index, l'important pour vous c'est que le libellé correspondent aux données que vous souhaitez suivre voire des archives que vous souhaitez afficher.
<br>

Vous pouvez aussi profiter de ces 10 index disponibles pour afficher les évolutions de vos abonnements. Par exemple, vous étiez en abonnement HP/HC et vous venez de passer en Tempo, vous pouvez très bien suivre vos index comme cela:
<br>

<a href="../../images/teleinfo_equipement04.png">
<img src="../../images/teleinfo_equipement04.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

**Important:** 
1. l'enregistrement des index commence dès la sauvegarde de ces infos. Tant qu'il n'y a pas de sauvegarde la configuration précédente est toujours utilisée
2. s'il y a un libellé inscrit mais qu'il n'y a pas de champs sélectionné => l'affichage des statistiques déjà enregistrées continue mais il n'y a plus de nouveaux enregistrements. Cette possibilité permet de pouvoir continuer à afficher les courbes et les statistiques d'un champs qui soit n'existe plus soit a changé d'utilisation à cause d'un changement d'abonnement. Par exemple le champs EASF02 sert à totaliser les HP si vous avez un abonnement HP/HC mais peut aussi servir à totaliser les HP Jour Bleu d'un abonnement Tempo. Donc si vous passez de l'un à l'autre la signification change.

## (3) Les tarifs
<br>

Il y a besoin d'une explication? ;)
<br><br>

## (4) Période servant pour la (ré)génération des index
<br>

Si vous avez déjà un historique du plugin teleinfo ou si vous avez oublié de changer la configuration des champs lors d'un changement d'abonnement ces dates servent à borner la (ré)rénération des index. Voir explication au paragraphe suivant.
<br><br>

## (5) Copie des anciennes données vers Index
<br>

Une fois que vous aurez fini de configurer les 3 premiers points ci-dessus vous souhaiterez sans doute récupérer vos archives de téléinfo afin d'alimenter les statistiques qui seront présentées sur le panel. C'est ici que cela va se passer.

**Important:** Ce qu'il faut savoir avant de commencer:
1. Si un index n'a pas de libellé => n'est pas bloquant
2. Si un index n'a pas de champs sélectionné => pas de copie de données ni d'écrasement des anciennes données de l'index
3. Si un index a un champ sélectionné mais qu'il n'y a pas d'enregistrement sur ce champ => écrasement des données existantes de l'index (peut être utilisé pour remettre à 0 des statistiques d'un index)
4. Si un index n'a pas de tarif indiqué => écrasement des coûts enregistrés pour cet index
5. pour lancer des copies il n'est pas nécessaire de sauvegarder donc les index continuent à être alimentés sans être perturbés.
6. Aucune action de copie ne touche les champs de téléinfo archivés. Cela ne joue que sur l'archivage des index. Il n'y a donc aucun risque et toute mauvaise manipulation peut se rattraper.
<br><br>

**Passons aux cas concrets:**
1. Cas simple, vous n'avez jamais changé ni d'abonnement ni de mode TIC depuis le début de l'utilisation du plugin:
- Vous paramétrez comme indiqué les 3 premiers points ci_dessus
- Vous indiquez la date de début de vos premiers enregistrement dans le plugin
- Vous laissez aujourd'hui comme datre de fin
- Vous cliquez sur le bouton "copie" et c'est tout
<br><br><br>

2. Cas un peu plus complexe, vous êtes passé d'un abonnement de base à un abonnement HP/HC le 23/11/2021:
- Vous indiquez le tafif de base appliqué sur la période de votre abonnement
- Vous indiquez la date de début de vos premiers enregistrement dans le plugin
- Vous indiquez la date de fin de cet abonnement: 23/11/2021
- Vous cliquez sur "copie"
- Une fois la copie terminée (affichage d'un popup vert à droite de l'écran) vous paramétrez les index comme vous le souhaitez: libellé "HP" + champs "HCHP" (ou "EASF02") sur index01 et "HC" + "HCHC" (ou "EASF01") sur index02 par exemple et les tarifs qui correspondent
- Il faut mettre à 0 le tarif de base précédemment indiqué.
- Vous indiquez la date de début de cet abonnement: 23/11/2021
- Vous indiquez en date de fin la date d'aujourd'hui
- Vous cliquez sur "copie"
- Attendre l'affichage d'un popup vert à droite de l'écran indiquant la fin du traitement des données
<br><br><br>

**Conseils:**
- commencez par lister vos périodes de changements soit d'abonnement soit de mode TIC soit de ... en partant du plus ancien pour arriver à aujourd'hui
- sur chaque période lister les données qui serviront à alimenter vos futurs index : champs, tarifs.
- paramétrez d'abord tous les libellés que vous voulez voir apparaitre et les données de configuration actuelle de votre abonnement puis auvegardez
- **SANS SAUVEGARDER à chaque fois**; procéder ensuite par période listée en partant de la plus ancienne à la plus récente
- bien attendre la fin du traitement avant d'en lacer un autre
- si possible allez voir dans le panel si les données sont conformes à ce que vous attendez.
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

# Le panel
<br><br>

<a href="../../images/teleinfo_panel01.png">
<img src="../../images/teleinfo_panel01.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

## (1) Les options
<br><br>

**Les dates:** permettent de sélectionner la plage de temps concernant les différents graphique. Ne joue par sur le tableau des données.
<br><br>

**Le bouton €:** ne garde l'affichage que pour les données relatives aux coûts aussi bien sur le graphique que dans le tableau
<br><br>

**le bouton kWh:** ne garde l'affichage que pour les données relatives aux consommations aussi bien sur le graphique que dans le tableau
<br><br>

**le bouton "tout":** remet l'affichage de toutes les données de conso et de coûts
<br><br>

## (2) Les rubriques
<br><br>

Il y en a au moins deux qui seront affichées, les données relatives à la production ne seront affichées que si cette option est choisie dans les paramètres du compteur
<br><br>

## (3) les index:
<br><br>

Correspondent aux libellés qui auront été saisies dans les paramètres du compteur
<br><br>

# Les courbes
<br><br>

Voici les courbes que vous pourrez avoir:

**Instantanées:**
<br><br>

<a href="../../images/teleinfo_panel02.png">
<img src="../../images/teleinfo_panel02.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

**Journalières:**
<br><br>

<a href="../../images/teleinfo_panel03.png">
<img src="../../images/teleinfo_panel03.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

**Mensuelles:**
<br><br>

<a href="../../images/teleinfo_panel04.png">
<img src="../../images/teleinfo_panel04.png" alt="configuration" style="width:600px;"/>
<a>
<br><br>

**Annuelles:**
<br><br>

<a href="../../images/teleinfo_panel05.png">
<img src="../../images/teleinfo_panel05.png" alt="configuration" style="width:600px;"/>
<a>
<br><br><br><br><br><br><br><br><br><br><br><br>


# [Retour à la documentation principale](/plugin-teleinfo/fr_FR/)
