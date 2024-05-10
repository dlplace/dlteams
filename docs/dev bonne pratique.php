<?php

// renvoyer le dossier de GLPI : 
$glpiRoot=str_replace('\\', '/', GLPI_ROOT);
?? $CFG_GLPI['root_doc']
// renvoyer l'entité courante 
echo "<input type='hidden' name='entities_id' value='" . $entity->fields['id'] . "'>";




// PLUGIN DATAINJECTION -> tout pour manipuler des fichiers
// vérification des droits
      if (Session::haveRight(static::$rightname, UPDATE)) {
         $url           = Toolbox::getItemTypeSearchURL('PluginDatainjectionModel');
         $buttons[$url] = PluginDatainjectionModel::getTypeName();
         $title         = "";
         Html::displayTitle(
             Plugin::getWebDir('datainjection') . "/pics/datainjection.png",
             PluginDatainjectionModel::getTypeName(), $title, $buttons);}
// ???
   function showForm($ID, $options = []) {
      echo "<form method='post' name=form action='".Toolbox::getItemTypeFormURL(__CLASS__)."'"."enctype='multipart/form-data'>";
// obtenir le user id de l'utilisateur courant : Session::getLoginUserID()
      $models = PluginDatainjectionModel::getModels(Session::getLoginUserID(), 'name',
          $_SESSION['glpiactive_entity'], false      );
         if (Session::haveRight('plugin_datainjection_model', CREATE)) {
// ??
      if (PluginDatainjectionSession::getParam('models_id')) {
          $p['models_id'] = PluginDatainjectionSession::getParam('models_id');
         switch (PluginDatainjectionSession::getParam('step')) {
            case self::STEP_UPLOAD :
// retrouver l'url d'un dossier 
                   $url = Plugin::getWebDir('datainjection')."/ajax/dropdownSelectModel.php";
                   Ajax::updateItem("span_injection", $url, $p); break;
// créer une pop up avec un message 
      PluginDatainjectionDropdown::dropdownFileEncoding();
      if ($confirm) {if ($confirm == 'creation') {$message = __s('Warning : existing mapped column will be overridden', 'datainjection');
         } else {$message = __s("Watch out, you're about to inject data into GLPI. Are you sure you want to do it ?",'datainjection');}
		 $alert = "OnClick='return window.confirm(\"$message\");'";
      }
      if (!isset($options['submit'])) {
         $options['submit'] = __('Launch the import', 'datainjection');
      }
      echo "<input type='submit' class='submit' name='upload' value='".
           htmlentities($options['submit'], ENT_QUOTES, 'UTF-8'). "' $alert>";
      echo "&nbsp;&nbsp;<input type='submit' class='submit' name='cancel' value=\""._sx('button', 'Cancel')."\">";
      if ($add_form) {
         Html::closeForm();
// barre de progession
      Html::createProgressBar(__('Injection of the file', 'datainjection'));
      self::processInjection($model, $entities_id);
      // To prevent problem of execution time during injection
      ini_set("max_execution_time", "0");
      // Disable recording each SQL request in $_SESSION
      $CFG_GLPI["debug_sql"] = 0;

      $nblines         = PluginDatainjectionSession::getParam('nblines');
      $clientinjection = new PluginDatainjectionClientInjection;

      //New injection engine
      $engine = new PluginDatainjectionEngine(
          $model, PluginDatainjectionSession::getParam('infos'),
          $entities_id
      );
      $backend = $model->getBackend();
      $model->loadSpecificModel();
      //Open CSV file
      $backend->openFile();
      $index = 0;
      //Read CSV file
      $line = $backend->getNextLine();
      //If header is present, then get the second line
      if ($model->getSpecificModel()->isHeaderPresent()) {
          $line = $backend->getNextLine();
      }
       //While CSV file is not EOF
       $prev = '';
       $deb  = time();
      while ($line != null) {
          //Inject line
          $injectionline              = $index + ($model->getSpecificModel()->isHeaderPresent()?2:1);
          $clientinjection->results[] = $engine->injectLine($line[0], $injectionline);
         //EOF : change progressbar to 100% !
         Html::changeProgressBarPosition(100, 100,sprintf(__('%1$s (%2$s)'),__('Injection finished', 'datainjection'),Html::timestampToString(time()-$deb, true)));
         //Close CSV file
         $backend->closeFile();
         //Delete CSV file
         $backend->deleteFile();
         unset($_SESSION['datainjection']['go']);
         $url = Plugin::getWebDir('datainjection')."/ajax/results.php";
         Ajax::updateItem("span_injection", $url, $p);}
         $di_base_url = Plugin::getWebDir('datainjection');