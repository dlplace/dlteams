# Machine

## Infos générale

* IP : 54.36.101.196
  
* IPv6 : 2001:41d0:304:200::9e80 

* Hébergeur : OVH ([pannel](https://www.ovh.com/manager/dedicated/#/vps/vps-81694db7.vps.ovh.net/dashboard))

* Sauvegardes :
  * Automatiques par OVH (tous les jours à 20h25 et conservées 1 
    semaine).
  * Dumps de la base de données sur la machine elle-même pour 
    permettre la restauration rapide en cas de mauvaise manipulation (à 8h, 
    10h, 12h, 14h, 16h, et 18h conservées 24h ainsi que à 20h conservés 1 
    semaine [infos](https://github.com/dlplace/dlplacedpo/issues/105))
  
* Dépôt git distant : `ssh://root@54.36.101.196:/var/repos/dlplacedpo.git`

* Site de développement : accessible sur dev.dlplace.eu et dossier racine 
  `/var/www/dev_dlplace_eu/`

* Site de production : accessible sur app.dlplace.eu et dossier racine 
  `/var/www/app_dlplace_eu/`

* Accès base de données par PhpMyAdmin : phpmyadmin.dlplace.eu (aussi 
  accessible par le port local 3306 après tunnel SSH)

* Accès SSH : port 22, authentification par clés uniquement

## Taches effectuées

* Sécurisation accès : connexion par clés SSH uniquement

* Installation et configuration du serveur web (Apache 2) (dépôt de la 
  configuration disponible ici
  [dlplace/apache2-conf](https://github.com/dlplace/apache2-conf))

  * Configuration des VHosts (dev, prod(app), phpmyadmin)
   
  * Installation phpMyAdmin
   
  * Activation du SSL (Let’s Encrypt avec certbot)

* Installation de base de données (MariaDB)

* Mise en place du déploiement/intégration (git) avec le script de post 
  réception placé dans `/var/repos/dlplacedpo.git/hooks/post-receive`

## Bon à savoir

Il est possible d'accéder à la machine en SFTP (FTP sur SSH).

Les logs de GLPI se trouvent dans le dossier racine du site puis `files/_log/`.

Scripts de sauvegardes DB dans `/root/backup_db.sh` et 
`/root/backup_db_daily.sh`.

[Mémo pour JC reprenant quelques de ces infos](https://bellaspsy.sharepoint.com/:w:/r/sites/DLPlace/_layouts/15/Doc.aspx?sourcedoc=%7BF5FC2738-1722-4501-A8F9-9CC012284303%7D&file=M%C3%A9mo.docx&action=default&mobileredirect=true&cid=00bceb04-6562-4225-8669-b434de3ab921)

[Autre mémo reprenant aussi les infos](https://bellaspsy.sharepoint.com/:w:/r/sites/DLPlace/_layouts/15/Doc.aspx?sourcedoc=%7B62CADBCA-0E3C-4785-9611-BDDECC6099F0%7D&file=GitHub%20synchronisation.docx&action=default&mobileredirect=true)