# Standards du projet

## Code 

### Standards GLPI

Un plugin GLPI respecte la structure de répertoire instaurée dans la 
[documentation](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/guidelines.html).

Le formatage du code PHP suit (ou du moins tente de suivre) les [standards de 
codages instaurés par GLPI](https://glpi-developer-documentation.readthedocs.io/en/master/codingstandards.html).
Les points notables sont :

* Indentation à 3 espaces
* Pas d'utilisation de namspaces php


### Standards propres à ce plugin

Quelques points tentent d'être établis pour garder un certain ordre et une 
certaine cohérence.

* Il ne faut pas modifier GLPI core (le coeur de GLPI, ce 
  qui est hors du dossier du plugin), pour permettre les mises à jour 
  facilement GLPI sans avoir à réappliquer les modifications, plus 
  d'informations dans `modifier_glpi.md`
  
* Les commentaires dans le code, les noms de variables, classes, méthodes, 
  les messages dans les commits sont en anglais, seules ces pages markdown 
  sont en francais.
  
* Les strings sont en anglais puis, en suite, traduits (dossier `locales/`) 
  (souvent à l'aide du logiciel Poedit, JC s'en occupe). 

## Organisation - workflow

### Taches

La [gestion de projets sur Github](https://github.com/dlplace/dlplacedpo/projects)
est utilisée pour suivre l'avancée des taches : un projet contient des 
"issues" classées selon leur état : **ToDo**, **In progress** et **Done**

Pour chaque tache qui est effectuée ou demandée, une
[issue](https://github.com/dlplace/dlplacedpo/issues/new) est ouverte, et 
renseignée (labels et projects à compléter). Les taches en cours de 
réalisation sont placées manuellement dans la 2ème colonne, et finissent 
dans la 3ème automatiquement lorsqu'un commit sur la branche master l'indique.

### Versionnage

L'outil de version utilisé est GIT, il est nécessaire d'en connaître les 
rudiments (`status`, `commit`, `merge --no-ff`, `pull`, `push`, `checkout`,
`remote` puis éventuellement `reset`, `log --graph`). 

Il y a 2 branches principales : 
* `master` c'est la branche de développement, les modifications y sont 
  directement apportées par fusion de branches de fonctionnalités. Sur la 
  machine cela équivaut au site [dev.dlplace.eu](https://dev.dlplace.eu/)
  
* `prod` c'est la branche de production, elle reçoit (par rebasage) 
  occasionnellement les avancées de la branche master quand celles-ci ont 
  été testées sur le site de la dev au paravent. Cela correspond au 
  site-produit principal [app.dlplace.eu](https://app.dlplace.eu/)

Pour réaliser une tache on suit ces étapes :

1. on crée une branche ayant pour préfixe le numéro de l'issue qu'on veut 
   adresser (exemple : `102-menu-grc` pour adresser l'issue #102)
   
2. On commit sur cette branche les modifications qui permettent de résoudre 
   cette issue
   
3. Une fois le résultat souhaité sur la branche, on fusionne cette branche 
   avec la branche master en spécifiant [un mot clé pour fermer l'issue](https://docs.github.com/en/issues/tracking-your-work-with-issues/linking-a-pull-request-to-an-issue#linking-a-pull-request-to-an-issue-using-a-keyword)
   depuis le message de fusion(exemple depuis la branche master : `git merge 
   102-menu-grc --no-ff -m "Fix #102 Add GRC menu"`)

4. Les modifications sont ensuite poussées sur github (par ssh avec `git 
   push`) puis sur le serveur de développement avec la remote correspondante 
   (cf. `machine.md`).
   
5. Une fois que les implementations correspondent au attentes et 
   fonctionnent bien sur la dev, on peut les passer sur la prod, on se place 
   donc sur cette dernière branche et on rebase au point qu'on souhaite (le 
   plus souvent `git rebase master`). Souvent on crée un tag à l'emplacement 
   pour repérer l'avancée de la prod et connaitre facilement l'emplacement 
   des anciennes versions (exemple : `git tag 2.0.4`) puis on pousse cette 
   branche (sur la machine et sur github)





