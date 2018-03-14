Configuration
===
Le plugin offre la possibilité de créer automatiquement les commandes réceptionnées. Pour cela démarrez le daemon, attendez que les premières trames arrivent puis rendez vous sur Plugin / Protocoles Domotiques / Téléinfo.
Vous devriez voir apparaitre un objet avec un ID.

>Il est possible de désactiver la création automatique des nouveaux compteurs.
>
>![teleinfo51](../images/teleinformation_blocage.png)

Cliquez sur l'objet et sélectionnez "Création automatique des commandes" en haut à droite, enfin sauvegardez. A partir de ce moment toutes les commandes reçu et non reconnu seront ajoutée automatiquement.

![teleinfo52](../images/teleinformation_objet.png)

![teleinfo52](../images/teleinformation_commandes_auto.png)

Vous pouvez également créer votre appareil manuellement :
-   Créez votre nouvel appareil en cliquant sur Ajouter
-   Ajoutez les commandes que vous souhaitez récupérer en cliquant sur le bouton vert "Commande"
-   Vous pouvez également afficher des statistiques de consommation (Aujourd'hui / Hier / Mois en cours / Mois dernier) en cliquant sur le bouton "Ajouter une statistique"
-   Renseignez le nom affiché dans Jeedom, le Sous-Type en fonction de l'information qui sera récupérée, la donnée à récupérer et enfin les paramètres associés.
-   Cliquez sur sauvegarder.
