# Modifier GLPI

Le but est de pouvoir adapter GLPI à souhait sans modifier sont code source 
initial de manière manuelle pour faciliter la mise à jour vers de futures 
versions.

Le contenu de GLPI peut être modifié de manière plus ou moins orthodoxe. Un
certain degré de modifications est permise par GLPI, un autre est permis en
rusant et un autre que je vous invite à prendre connaissance à la fin de ce
document (*méthode honteuse*)

## Par les crochets 'hooks'

GLPI met à [dispoosition des '*hooks*' (crochets)
](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/hooks.html),
venant se greffer au logiciel et permettent d'appeler des fonctions 
(définies dans `hook.php`) pour modifier certaines parties du fonctionnement 
comme à l'ajout d'un item (objet GLPI), la modification du menu, l'affichage 
de contenu sur la page de connexion...

Cela peut suffire pour une partie des réalisations demandées par JC, mais il 
va falloir ruser un peu plus pour le reste. 

## JS & CSS

Toujours cette fois-ci en utilisant les hooks on peut rajouter des fichiers 
JavaScript et CSS qui seront ajoutés à toutes les pages (*sauf celle de 
login me semble-t-il*).

Le thème des donc régit par `plugin.css`, l'affichage du logo, des couleurs...

Et certaines modifications sont opérées en JS en modifiant le contenu coté 
client après la réception de la page HTML par le client (pour les parties 
non modifiables avec des crochets). C'est le cas de la notice de copyright 
en bas du site, du favicon, du `<title>` de l'onglet (`js/plugin.js`) et des 
pages de faq (`js/plugin.js` et aussi la config VHost apache).

##  shell_exec()

Ma dernière trouvaille (dont je ne suis pas forcément fier), l'utilisation 
de la fonction PHP `shell_exec()` qui permet d'exécuter un script bash 
depuis le PHP. Je l'ai utilisé dans `setup.php:72` pour modifier le code 
source de GLPI à l'initialisation du plugin pour rajouter une entrés dans le 
menu (en écrivant une ligne contenant un objet tableau).

Cette méthode fonctionne certes, mais n'est pas portable en dehors de Linux, 
et augmente considérablement la complexité du code donc réduit sa 
maintenabilité.
