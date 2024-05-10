# Mise en place d'un environment local de développement

Voici quelques possibilités pour mettre en place un environment GLPI (il n'y a 
pas d'image de container, car je n'ai ni eu les temps ou les connaissances, 
mais ce pourrait être intéressant à mettre en place)

## Linux 

Il faut un ordinateur sous linux (ou une machine virtuelle, ou un WSL, bref 
une instance Linux quelque part), c'est ce que j'utilise, il faut installer 
un stack LAMP sur votre machine, c’est-à-dire les [logiciels
suivants](https://glpi-install.readthedocs.io/fr/latest/prerequisites.html) :
* Apache 2
* MariaDB (ou MySQL)
* PHP (version 7.4 ou 8 testées en local, 7.4.9 version du serveur)

Ensuite GLPI est installé depuis une archive ([guide plus
complet](https://glpi-install.readthedocs.io/fr/latest/install/index.html)) 
ou depuis un copie présente sur la machine (dans `/var/www/` puis un dump 
présent dans `/root/backup_db/`).

***Note :*** *La fonctionnalité d'ajout du "menu GRC" devrait en théorie ne 
marcher que sous Linux (ou WSL) car il utilise des commandes Linux.*

## Windows

Il existe plusieurs moyens de développer sous widows : 

* WSL : Sous système Linux pour windows, permet de recréer à l'indentique un 
  système Linux (comme présent sur la machine, donc compatibilité optimale)
  
* XAMPP : utilisé par JC, mais ne marhce pas super bien

* Laragon : déjà testé, fonctionne très bien

* WAMP : fonctionne probablement, mais jamais testé

Ensuite GLPI est installé depuis une archive ([guide plus
complet](https://glpi-install.readthedocs.io/fr/latest/install/index.html))
ou depuis un copie présente sur la machine (dans `/var/www/` puis un dump
présent dans `/root/backup_db/`).
