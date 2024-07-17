# dlteams

## Plugin [GLPI](https://github.com/glpi-project/glpi/) implémente le produit "DLTeams"
Réalisé par et pour DLPLACE.

## Où trouver la documentation ?

* Documentation technique plus détaillée sur le plugin : répertoire `docs/` 
  ces 6 fichiers peuvent aider à démarrer et guider un nouveau développeur 
  sur le projet
  * `fonctionnement_général.md`
  * `historique.md`
  * `machine.md`
  * `modifier_glpi.md`
  * `setup-env-local.md`
  * `standards.md`
    
* Documentation sur GLPI et les plugins : 
[glpi-developer-documentation.readthedocs.io](https://glpi-developer-documentation.readthedocs.io/en/master/)


## Fonctionalités principales

* Registre (enregistrement) des traitements
* Dictionnaire des catégories de données personnelles (peut être imbriqué)
* Plusieurs catégories de personnes concernées par enregistrement
* Conservation des données (base légale, autre)
* Plusieurs bases légales par enregistrement
* Dictionnaire des mesures de sécurité (peut être séparé par entité)
* Génération des rapports sous format PDF et HTML
* Chaque entité peut avoir des informations de contrôleur séparées : 
  représentant légal, DPO et nom qui apparaissent sur les rapports générés
* Configuration supplémentaire

# Inspiration
Plug'in de Yild et d'autres contributeurs [GDRPRoPA](https://github.com/yild/gdprropa) 
