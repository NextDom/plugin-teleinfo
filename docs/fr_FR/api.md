API
===
Le plugin téléinfo met à disposition une api afin de mettre à jour les données depuis d'autres systèmes.
Il s'agit d'une URL d'appel de type GET.
Pour y accéder ces informations sont obligatoires :

URL = http://#IP_JEEDOM#:#PORT_JEEDOM#/jeedom/plugins/teleinfo/core/php/jeeTeleinfo.php?api=#VOTRE_API_JEEDOM#&ADCO=#IDENTIFIANT_DU_COMPTEUR#

 #IP_JEEDOM# correspond à l'adresse ip de votre Jeedom
 #PORT_JEEDOM# correspond au port d'accès à votre Jeedom
 #VOTRE_API_JEEDOM# est la clé API disponible depuis le menu Général / Administration / Configuration

 ![teleinfo8](../images/teleinformation_api_menu1.png)

 #IDENTIFIANT_DU_COMPTEUR# correspond à l'ADCO de celui-ci. Cette information est obligatoirement transmise par le compteur lorsque vous recevez une trame.

Attention le /jeedom peux être différent si vous êtes sur une installation DIY ou OEM. En règle générale il faut l'ajouter.

A cette url d'appel vous pouvez ajouter les différentes valeurs suivant le format : &#NOM_DE_LA_VALEUR#=#VALEUR#

Par exemple pour l'index d'un abonnement BASE EDF équivalent à 123456789 :

*&BASE=123456789*

L'URL sera donc surchargée de toutes information utile.

Par exemple :

    IP_JEEDOM : 192.168.1.15
    PORT_JEEDOM : 80
    VOTRE_API_JEEDOM : zertyuiolkjhgfdsxc
    IDENTIFIANT_DU_COMPTEUR : 0095123657
    Index BASE : 123456789

Donnera l'URL : http://192.168.1.15:80/jeedom/plugins/teleinfo/core/php/jeeTeleinfo.php?api=zertyuiolkjhgfdsxc&ADCO=0095123657*&BASE=123456789*

Il est possible d'envoyer toutes les informations transmises par les compteurs suivant les étiquettes définie par la norme.
Quelques unes (Les plus connues) :

	* BASE
	* HPHP
	* HPHC
	* PTEC
	* PAPP
	* IINST
	* ADPS
