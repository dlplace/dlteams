<?php
/*
 -------------------------------------------------------------------------
 DLTeams plugin for GLPI
 -------------------------------------------------------------------------
 LICENSE : This file is part of DLTeams Plugin.

 DLTeams Plugin is a GNU Free Copylefted software.
 It disallow others people than DLPlace developers to distribute, sell,
 or add additional requirements to this software.
 Though, a limited set of safe added requirements can be allowed, but
 for private or internal usage only ;  without even the implied warranty
 of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

 You should have received a copy of the GNU General Public License
 along with DLTeams Plugin. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
  @package   dlteams
  @author    DLPlace developers
  @copyright Copyright (c) 2022 DLPlace
  @inspired	 DPO register plugin (Karhel Tmarr) & gdprropa (Yild)
  @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
  @link      https://github.com/dlplace/dlteams
  @since     2021
 --------------------------------------------------------------------------
 */

trait PluginDlteamsExportable
{
    /**
     * Insert the export of sub items in the export
     *
     * @param array $subItems key/value pair list of sub items
     * @param array $export the export of the object
     * @param boolean $remove_uuid
     * @return array
     */
    public function exportChildrenObjects($subItems, $export, $remove_uuid = false)
    {
        global $DB;
        foreach ($subItems as $key => $itemtypes) {
            if (!is_array($itemtypes)) {
                $itemtypes = [$itemtypes];
            }
            $export[$key] = [];
            foreach ($itemtypes as $itemtype) {
                $list = [];
                $allSubItems = $itemtype::getSQLCriteriaToSearchForItem($this->getType(), $this->getID());
                foreach ($DB->request($allSubItems) as $row) {
                    /** @var CommonDBConnexity $subItem */
                    $subItem = new $itemtype();
                    $subItem->getFromDB($row['id']);
                    if (in_array(PluginDlteamsExportableInterface::class, class_implements($subItem))) {
                        /** @var PluginDlteamsExportableInterface $subItem */
                        $list[] = $subItem->exportToDB($remove_uuid);
                    }
                }
                if (!is_array($subItems[$key])) {
                    $export[$key] = $list;
                } else {
                    $export[$key][$itemtype] = $list;
                }
            }
        }

        return $export;
    }

    /**
     * Import children objects
     *
     * @param array PluginDlteamsExportableInterface $item
     * @param PluginDlteamsLinker $linker
     * @param array $input
     * @return void
     */
    public function importChildrenObjects($item, $linker, $subItems, $input)
    {
        $itemId = $item->getID();
        foreach ($subItems as $key => $itemtypes) {
            if (!is_array($itemtypes)) {
                if (!isset($input[$key])) {
                    $input[$key] = [];
                }
                $input[$key] = [$itemtypes => $input[$key]];
                $itemtypes = [$itemtypes];
            }
            foreach ($itemtypes as $itemtype) {
                $importedItems = [];
                if (!isset($input[$key][$itemtype])) {
                    continue;
                }
                foreach ($input[$key][$itemtype] as $subInput) {
                    $importedItem = $itemtype::importToDB(
                        $linker,
                        $subInput,
                        $itemId
                    );

                    // If $importedItem === false the item import is postponed
                    if ($importedItem !== false) {
                        $importedItems[] = $importedItem;
                    }
                }
                // Delete all other restrictions
                /*                $subItem = new $itemtype();
                $subItem->deleteObsoleteItems($item, $importedItems); */
            }
        }
    }

    /**
     * @see PluginDlteamsExportableInterface
     */
    public function exportToDB($subItems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }

        $export = $this->fields;
        // Remove unused key
        unset(
            $export['users_id_creator'],
            $export['users_id_lastupdater'],
            $export['date_creation'],
            $export['date_mod']
        );
        return $export;
    }

    /**
     * @see PluginDlteamsExportableInterface
     */
    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = [])
    {
        global $DB;

        $input['_skip_checks'] = true;
        $item = new self();

        $originalId = $input['id'];
        unset($input['id']);

        // Escape all fields (just in case)
        foreach ($input as $key => $element) {
            $input[$key] = $DB->escape($element);
        }

        $itemId = $item->add($input, [], false);
        if ($itemId === false) {
            $typeName = strtolower(self::getTypeName());
            throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
        }
        // add to the linker
        $linker->addObject($originalId, $item);

        return $itemId;
    }

    /**
     * @see PluginDlteamsExportableInterface
     */
    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
    }


    /**
     * Copy a record to another entity. Execute copy action for massive action.
     * @param integer $entity id of the target entity
     *
     * @return Boolean true if success, false otherwize.
     */
    public function copy($entity, $id, CommonDBTM $item)
    {
        /*        highlight_string("<?php\n\$id =\n" . var_export($id, true) . ";\n?>");*/
        /*        highlight_string("<?php\n\$entity =\n" . var_export($entity, true) . ";\n?>");*/
        /*        highlight_string("<?php\n\$item =\n" . var_export($item, true) . ";\n?>");*/
        global $DB, $entitiesA_id, $entitiesB_id, $recordsA_id, $recordsB_id, $processeddataB_id, $itemsA_id, $itemsA_id1, $concernedpersons_items_id, $processeddatas_items_id,
		$records_items_idA, $records_items_idA, $concernedpersons_items_idB, $itemtype1A, $test ;
		$message = "Copie de traitements". nl2br("\n");
        $dbu = new DbUtils();
        // $name = str_replace('"', '', addslashes($item->fields['name']));
        // $content = str_replace('"', '', addslashes($item->fields['content']));
        // $right_information = str_replace('"', '', addslashes($item->fields['right_information']));
        // $right_opposition = str_replace('"', '', addslashes($item->fields['right_opposition']));
        // $right_portability = str_replace('"', '', addslashes($item->fields['right_portability']));
        // $specific_security_measures = str_replace('"', '', addslashes($item->fields['specific_security_measures']));
        // $additional_info = str_replace('"', '', addslashes($item->fields['additional_info']));
		$entitiesA_id = $item->fields['entities_id'];
		$entitiesB_id = $entity;
		$recordsA_id = $item->fields['id'];
        $iduser = Session::getLoginUserID();
		$userC_id = Session::getLoginUserID();

		// RECORD : Existe-il dans l'entité cible un traitement avec le même nom que celui à copier ?
        $req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_records` WHERE `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_records` WHERE `id` = $id)");
		// var_dump (count($req)) ;
        if (count($req)) { // oui existe -> on quitte
            foreach ($req as $id => $row) {
				$id = $row['id'];
				// pour tests -- $DB->request("DELETE FROM glpi_plugin_dlteams_records WHERE id = $id");
				// var_dump ("Traitement déjà existant (entité, record) : ", $entitiesB_id,", ", $id);
				$message .= (sprintf(__('Traitement déjà existant : %s', 'dlteams'), $item->getName())) . nl2br("\n") ;
			}
            Session::addMessageAfterRedirect($message, false, ERROR);
            Html::back();
        }
        else { // non -> on le créée
            //foreach ($req as $id => $row) {
		        $DB->query("INSERT INTO glpi_plugin_dlteams_records (entities_id, is_recursive, is_deleted, date_mod, date_creation, id_model, entity_model, date_majmodel, type_majmodel, number, parentnumber, completenumber, name, content, additional_info, 
					states_id, first_entry_date, consent_json, consent_type, consent_explicit, users_id_creator, users_id_lastupdater, users_id_responsible, 
					users_id_auditor, users_id_actor, diffusion, right_information, right_opposition, right_portability, profiling, profiling_auto, external_process, impact_person, impact_organism, specific_security_measures, transmissionmethod, mediasupport, siintegration, collect_comment) 
							SELECT $entity, is_recursive, is_deleted, NOW(), NOW(), id_model, entity_model, date_majmodel, type_majmodel, number, parentnumber, completenumber,  name, content, additional_info, 
					states_id, first_entry_date, consent_json, consent_type, consent_explicit, $userC_id, $userC_id, 0, 
					0, 0, diffusion, right_information, right_opposition, right_portability, profiling, profiling_auto, external_process, impact_person, impact_organism, specific_security_measures, transmissionmethod, mediasupport, siintegration, collect_comment
						FROM `glpi_plugin_dlteams_records` WHERE `id` = $recordsA_id");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_records`'); // or die("Echec recuperation lastinsertid dans la table ");
			$data = $DB->fetchAssoc($result); $recordsB_id = $data['LAST_INSERT_ID()'];
			//  var_dump ("traitement N° ", $recordsB_id, " créé"); echo "<br>" ;
			$message .= (sprintf(__('Traitement créé : %s', 'dlteams'), $item->getName())) . nl2br("\n") ;
        }
		// CONCERNEDPERSON + PROCESSEDATA selection des lignes de records_items pour le record source et itemtype =  PluginDlteamsConcernedPerson
		$req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_records_items` WHERE `records_id` = $recordsA_id AND `itemtype` = 'PluginDlteamsConcernedPerson'");
        foreach ($req as $items_id => $row) { //pour chaque ligne
			$records_items_idA = $row['id']; $itemsA_id = $row['items_id']; $itemtype1A = $row['itemtype1'] ; $itemsA_id1 = $row['items_id1'];  // on recupere les datas pour la copie

			// concerned_persons
			//  var_dump ("Pour records_item id = ", $records_items_idA ," et l'itemsA_id = ", $itemsA_id) ; //on prend l'items_id et on vérifie si le name existe dans l'entité cible
			$reqexist = $DB->request("SELECT * FROM `glpi_plugin_dlteams_concernedpersons` WHERE 
                                                    `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_concernedpersons` WHERE `id` = $itemsA_id)");
			if (count($reqexist)) {
				//  var_dump (count($reqexist), $entitiesB_id, "concernedpersons existe, il faut prendre l'id : ") ;}
				foreach ($reqexist as $id => $row2) {$concernedpersonsB_id = $row2['id'];}
				//  var_dump ("l'idB est ", $concernedpersonsB_id) ; }
			} else { // si il existe pas on le créée
				//  var_dump (count($reqexist), $entitiesB_id, "concernedpersons n'existe pas, il faut créer -> ") ;
				$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_concernedpersons` (`entities_id`, `id_model`, `entity_model`, `date_majmodel`, `name`, `content`, `comment`, `date_creation`, `users_id`)
					SELECT $entitiesB_id, $itemsA_id, $entitiesA_id, `date_mod`, `name`, `content`, `comment`, NOW(), $userC_id
					FROM `glpi_plugin_dlteams_concernedpersons` WHERE `id` = $itemsA_id");
				// et on récupère l'id
				$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_concernedpersons`');
				$data = $DB->fetchAssoc($result); $concernedpersonsB_id = $data['LAST_INSERT_ID()'];
				//  var_dump ("concernedpersons créé, id = ", $concernedpersonsB_id); echo "<br>" ;
			}
			// on insert l'enregistrement dans records_items, puis concernedpersons_items
			$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_records_items` (`records_id`, `itemtype`, `items_id`, `comment`, `date_creation`, `mandatory`)
				SELECT $recordsB_id, `itemtype`, $concernedpersonsB_id, `comment`, NOW(), `mandatory` FROM `glpi_plugin_dlteams_records_items` WHERE `id` = $records_items_idA");
				$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_records_items`');
				$data = $DB->fetchAssoc($result); $records_items_idB = $data['LAST_INSERT_ID()'];
				//  var_dump ("records_items créé, id = ", $records_items_idB, "items_id = ", $concernedpersonsB_id); echo "<br>" ;
			$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_concernedpersons_items` (`concernedpersons_id`, `items_id`, `itemtype`, `comment`, `date_creation`)
				SELECT $concernedpersonsB_id, $recordsB_id, 'PluginDlteamsRecord', `comment`, NOW() FROM `glpi_plugin_dlteams_records_items` AS T1 WHERE T1.`id` = $records_items_idA");
				$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_concernedpersons_items`');
				$data = $DB->fetchAssoc($result); $concernedpersons_items_idB = $data['LAST_INSERT_ID()'];
				//  var_dump ("concernedpersons_items créé, ligne : ", $concernedpersons_items_idB, ", concernedpersons_id = ", $concernedpersonsB_id, " modèle = ", $records_items_idA); echo "<br>" ;

			// processedatas (itemtype1 = PluginDlteamsProcessedData)
			if ($itemtype1A === 'PluginDlteamsProcessedData') { //si itemtype1 = PluginDlteamsProcessedData (concernedperons = personnes concernées)
				//pour items_id1, on vérifie si le name existe dans l'entité cible
				$reqexist = $DB->request("SELECT * FROM `glpi_plugin_dlteams_processeddatas` WHERE 
                                                    `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_processeddatas` WHERE `id` = $itemsA_id1)");
				// //  var_dump ("test ProcessedData existe : records_items id = ", $records_items_idA ,", itemsA_id1 = ", $itemsA_id1) ;
				if (count($reqexist)) { // si il existe on prend l'id
					//  var_dump (count($reqexist), $entitiesB_id, "processeddatas existe, il faut prendre l'id") ;
					foreach ($reqexist as $id => $row2) {$processeddataB_id = $row2['id'];}
					//  var_dump ("l'idB est ", $processeddataB_id) ;
				} else { // si il existe pas on le créée
					//  var_dump (count($reqexist), $entitiesB_id, "processeddatas n'existe pas, il faut créer -> ") ;
					$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_processeddatas` (`entities_id`, `id_model`, `entity_model`, `date_majmodel`, `name`, `content`, `comment`, `date_creation`, `users_id`)
						SELECT $entitiesB_id, $itemsA_id, $entitiesA_id, `date_mod`, `name`, `content`, `comment`, NOW(), $userC_id
						FROM `glpi_plugin_dlteams_processeddatas` WHERE `id` = $itemsA_id1");
					// et on récupère l'id
					$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_processeddatas`');
					$data = $DB->fetchAssoc($result); $processeddataB_id = $data['LAST_INSERT_ID()'];
					//  var_dump ("processeddatas créé, id = ", $processeddataB_id, ); echo "<br>" ;
				}
				// update de records_items.items_id1
				$reqinsert = $DB->query("UPDATE `glpi_plugin_dlteams_records_items` SET itemtype1 = 'PluginDlteamsProcessedData', `items_id1` = $processeddataB_id WHERE `id` = $records_items_idB");
				//  var_dump ("records_items", $records_items_idB, " mis à jour avec processeddatas = ", $processeddataB_id); echo "<br>";
				// puis update de concernedperons
				$reqinsert = $DB->query("UPDATE `glpi_plugin_dlteams_concernedpersons_items` SET itemtype1 = 'PluginDlteamsProcessedData', `items_id1` = $processeddataB_id 
								WHERE `id` = $concernedpersons_items_idB AND `itemtype1` = 'PluginDlteamsProcessedData'");
				//  var_dump ("concernedpersons_items", $concernedpersons_items_idB, " mis à jour avec processeddatas = ", $processeddataB_id); echo "<br>" ;
				// puis on ajoute un enregistrement processeddatas_items,
				$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_processeddatas_items` (`processeddatas_id`, `items_id`, `itemtype`, `items_id1`, `itemtype1`, `comment`, `date_creation`)
					SELECT $processeddataB_id, $recordsB_id, 'PluginDlteamsRecord', $concernedpersonsB_id, 'PluginDlteamsConcernedPerson' , `comment`, NOW()
					FROM `glpi_plugin_dlteams_records_items` AS T1 WHERE T1.`id` = $records_items_idA");
				//  var_dump ("processeddatas_items créé, id = ??","processedatas_id = ", $processeddataB_id, ", modèle id = ", $itemsA_id1); echo "<br>" ; echo "<br>" ; echo "<br>" ; echo "<br>" ;
			}
			if ($itemtype1A === 'PluginDlteamsSendingReason') { //si itemtype1 = PluginDlteamsSendingReason (concernedperons = destinataires)
				$reqinsert = $DB->query("UPDATE `glpi_plugin_dlteams_records_items` SET itemtype1 = 'PluginDlteamsSendingReason', `items_id1` = $itemsA_id1 WHERE `id` = $records_items_idB");
				//  var_dump ("records_items", $records_items_idB, " mis à jour avec SendingReason = ", $itemsA_id1); echo "<br>";
				// puis update de concernedperons
				$reqinsert = $DB->query("UPDATE `glpi_plugin_dlteams_concernedpersons_items` SET itemtype1 = 'PluginDlteamsSendingReason', `items_id1` = `items_id1` 
							WHERE `id` = $concernedpersons_items_idB AND `itemtype1` = 'PluginDlteamsSendingReason'");
				//  var_dump ("concernedpersons_items", $concernedpersons_items_idB, " mis à jour avec SendingReason = ", $concernedpersonsB_id); echo "<br>" ;
			}
		}

		// LEGALBASIS selection des lignes de records_items pour le record source et itemtype =  PluginDlteamsLegalBasi
		global $legalbasisB_id, $legalbasis_items_idB;
		$req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_records_items` WHERE `records_id` = $recordsA_id AND `itemtype` = 'PluginDlteamsLegalBasi'");
        foreach ($req as $items_id => $row) { //pour chaque ligne
			$records_items_idA = $row['id']; $itemsA_id = $row['items_id']; $itemtype1A = $row['itemtype1'] ; $itemsA_id1 = $row['items_id1'];  // on recupere les datas pour la copie

			//  var_dump ("Pour records_item id = ", $records_items_idA ," et l'itemsA_id = ", $itemsA_id) ; //on prend l'items_id et on vérifie si le name existe dans l'entité cible
			$reqexist = $DB->request("SELECT * FROM `glpi_plugin_dlteams_legalbasis` WHERE 
                                                    `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_legalbasis` WHERE `id` = $itemsA_id)");
			if (count($reqexist)) {
				//  var_dump (count($reqexist), $entitiesB_id, "legalbasis existe, il faut prendre l'id : ") ;
				foreach ($reqexist as $id => $row2) {$legalbasisB_id = $row2['id'];}
				//  var_dump ("l'idB est ", $legalbasisB_id) ;
			} else { // si il existe pas on le créée
				//  var_dump (count($reqexist), $entitiesB_id, "legalbasis n'existe pas, il faut créer -> ") ;
				$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_legalbasis` (`entities_id`, `id_model`, `entity_model`, `date_majmodel`, `name`, `content`, `comment`, `plugin_dlteams_legalbasistypes_id`, `date_creation`, `users_id`)
					SELECT $entitiesB_id, $itemsA_id, $entitiesA_id, `date_mod`, `name`, `content`, `comment`, `plugin_dlteams_legalbasistypes_id`, NOW(), $userC_id
					FROM `glpi_plugin_dlteams_legalbasis` WHERE `id` = $itemsA_id");
				// et on récupère l'id
				$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_legalbasis`');
				$data = $DB->fetchAssoc($result); $legalbasisB_id = $data['LAST_INSERT_ID()'];
				//  var_dump ("legalbasis créé, id = ", $legalbasisB_id); echo "<br>" ;
			}
		// on insert l'enregistrement dans records_items, puis legalbasis_items
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_records_items` (`records_id`, `itemtype`, `items_id`, `comment`, `date_creation`, `mandatory`)
			SELECT $recordsB_id, `itemtype`, $legalbasisB_id, `comment`, NOW(), `mandatory` FROM `glpi_plugin_dlteams_records_items` WHERE `id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_records_items`');
			$data = $DB->fetchAssoc($result); $records_items_idB = $data['LAST_INSERT_ID()'];
			//  var_dump ("records_items créé, id = ", $records_items_idB, "items_id = ", $legalbasisB_id); echo "<br>" ;
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_legalbasis_items` (`legalbasis_id`, `items_id`, `itemtype`, `comment`, `date_creation`)
			SELECT $legalbasisB_id, $recordsB_id, 'PluginDlteamsRecord', `comment`, NOW() FROM `glpi_plugin_dlteams_records_items` AS T1 WHERE T1.`id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_legalbasis_items`');
			$data = $DB->fetchAssoc($result); $legalbasis_items_idB = $data['LAST_INSERT_ID()'];
						//  var_dump ("legalbasis_items créé, ligne : ", $legalbasis_items_idB, ", legalbasis_id = ", $legalbasisB_id, " modèle = ", $records_items_idA); echo "<br>" ;
		}

		// STORAGEPERIOD selection des lignes de records_items pour le record source et itemtype =  PluginDlteamsStoragePeriod
		global $storageperiodsB_id, $storageperiods_items_idB ;
		$req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_records_items` WHERE `records_id` = $recordsA_id AND `itemtype` = 'PluginDlteamsStoragePeriod'");
        foreach ($req as $items_id => $row) { //pour chaque ligne
			$records_items_idA = $row['id']; $itemsA_id = $row['items_id']; $itemtype1A = $row['itemtype1'] ; $itemsA_id1 = $row['items_id1'];  // on recupere les datas pour la copie
			//  var_dump ("Pour records_item id = ", $records_items_idA ," et l'itemsA_id = ", $itemsA_id) ; //on prend l'items_id et on vérifie si le name existe dans l'entité cible
			$reqexist = $DB->request("SELECT * FROM `glpi_plugin_dlteams_storageperiods` WHERE 
                                                    `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_storageperiods` WHERE `id` = $itemsA_id)");
			if (count($reqexist)) {
				//  var_dump (count($reqexist), $entitiesB_id, "storageperiods existe, il faut prendre l'id : ") ;
				foreach ($reqexist as $id => $row2) {$storageperiodsB_id = $row2['id'];}
				//  var_dump ("l'idB est ", $storageperiodsB_id) ;
			} else { // si il existe pas on le créée
				//  var_dump (count($reqexist), $entitiesB_id, "storageperiods n'existe pas, il faut créer -> ") ;
				$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_storageperiods` (`entities_id`, `id_model`, `entity_model`, `date_majmodel`, `name`, `content`, `comment`, `plugin_dlteams_storagetypes_id`, `date_creation`, `users_id`)
					SELECT $entitiesB_id, $itemsA_id, $entitiesA_id, `date_mod`, `name`, `content`, `comment`, `plugin_dlteams_storagetypes_id`, NOW(), $userC_id
					FROM `glpi_plugin_dlteams_storageperiods` WHERE `id` = $itemsA_id");
				// et on récupère l'id
				$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_storageperiods`');
				$data = $DB->fetchAssoc($result); $storageperiodsB_id = $data['LAST_INSERT_ID()'];
				//  var_dump ("storageperiods créé, id = ", $storageperiodsB_id); echo "<br>" ;
			}
		// on insert l'enregistrement dans records_items, puis storageperiods_items
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_records_items` (`records_id`, `itemtype`, `items_id`, `comment`, `date_creation`, `mandatory`)
			SELECT $recordsB_id, `itemtype`, $storageperiodsB_id, `comment`, NOW(), `mandatory` FROM `glpi_plugin_dlteams_records_items` WHERE `id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_records_items`');
			$data = $DB->fetchAssoc($result); $records_items_idB = $data['LAST_INSERT_ID()'];
			//  var_dump ("records_items créé, id = ", $records_items_idB, "items_id = ", $storageperiodsB_id); echo "<br>" ;
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_storageperiods_items` (`storageperiods_id`, `items_id`, `itemtype`, `comment`, `plugin_dlteams_storagetypes_id`, `plugin_dlteams_storageendactions_id`, `date_creation`)
			SELECT $storageperiodsB_id, $recordsB_id, 'PluginDlteamsRecord', `comment`, `plugin_dlteams_storagetypes_id`, `plugin_dlteams_storageendactions_id`, NOW() FROM `glpi_plugin_dlteams_records_items` AS T1 WHERE T1.`id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_storageperiods_items`');
			$data = $DB->fetchAssoc($result); $storageperiods_items_idB = $data['LAST_INSERT_ID()'];
						//  var_dump ("storageperiods_items créé, ligne : ", $storageperiods_items_idB, ", storageperiods_id = ", $storageperiodsB_id, " modèle = ", $records_items_idA); echo "<br>" ;
		}

		// THIRDCATEGORY selection des lignes de records_items pour le record source et itemtype =  PluginDlteamsThirdpartyCategory
		global $thirdpartycategorysB_id, $thirdpartycategorys_items_idB ;
		$req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_records_items` WHERE `records_id` = $recordsA_id AND `itemtype` = 'PluginDlteamsThirdpartyCategory'");
        foreach ($req as $items_id => $row) { //pour chaque ligne
			$records_items_idA = $row['id']; $itemsA_id = $row['items_id']; $itemtype1A = $row['itemtype1'] ; $itemsA_id1 = $row['items_id1'];  // on recupere les data de la ligne source

			// //  var_dump ("Pour records_item id = ", $records_items_idA ," et l'itemsA_id = ", $itemsA_id) ; //on prend l'items_id et on vérifie si le name existe dans l'entité cible
			$reqexist = $DB->request("SELECT * FROM `glpi_plugin_dlteams_thirdpartycategories` WHERE 
                                                    `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_thirdpartycategories` WHERE `id` = $itemsA_id)");
			if (count($reqexist)) {
				//  var_dump (count($reqexist), $entitiesB_id, "thirdpartycategorys existe, il faut prendre l'id : ") ;
				foreach ($reqexist as $id => $row2) {$thirdpartycategorysB_id = $row2['id'];}
				//  var_dump ("l'idB est ", $thirdpartycategorysB_id) ;
			} else { // si il existe pas on le créée
				//  var_dump (count($reqexist), $entitiesB_id, "thirdpartycategorys n'existe pas, il faut créer -> ") ;
				$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_thirdpartycategories` (`entities_id`, `id_model`, `entity_model`, `date_majmodel`, `name`, `content`, `comment`, `date_creation`, `users_id`)
					SELECT $entitiesB_id, $itemsA_id, $entitiesA_id, `date_mod`, `name`, `content`, `comment`, NOW(), $userC_id
					FROM `glpi_plugin_dlteams_thirdpartycategories` WHERE `id` = $itemsA_id");
				// et on récupère l'id
				$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_thirdpartycategories`');
				$data = $DB->fetchAssoc($result); $thirdpartycategorysB_id = $data['LAST_INSERT_ID()'];
				//  var_dump ("thirdpartycategorys créé, id = ", $thirdpartycategorysB_id); echo "<br>" ;
			}

		// on insert l'enregistrement dans records_items, puis thirdpartycategorys_items ; si itemtype1 is null -> thirdpartycategory = acteur
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_records_items` (`records_id`, `itemtype`, `items_id`, `comment`, `date_creation`, `mandatory`)
			SELECT $recordsB_id, `itemtype`, $thirdpartycategorysB_id, `comment`, NOW(), `mandatory` FROM `glpi_plugin_dlteams_records_items` WHERE `id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_records_items`');
			$data = $DB->fetchAssoc($result); $records_items_idB = $data['LAST_INSERT_ID()'];
			//  var_dump ("records_items créé, id = ", $records_items_idB, "items_id = ", $thirdpartycategorysB_id); echo "<br>" ;
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_thirdpartycategories_items` (`thirdpartycategories_id`, `items_id`, `itemtype`, `comment`, `itemtype1`, `items_id1`, `date_creation`)
			SELECT $thirdpartycategorysB_id, $recordsB_id, 'PluginDlteamsRecord', `comment`, `itemtype1`, `items_id1`, NOW() FROM `glpi_plugin_dlteams_records_items` AS T1 WHERE T1.`id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_thirdpartycategories_items`');
			$data = $DB->fetchAssoc($result); $thirdpartycategorys_items_idB = $data['LAST_INSERT_ID()'];
						//  var_dump ("thirdpartycategorys_items créé, ligne : ", $thirdpartycategorys_items_idB, ", thirdpartycategories_id = ", $thirdpartycategorysB_id, " modèle = ", $records_items_idA); echo "<br>" ;

		if ($itemtype1A === 'PluginDlteamsSendingReason') { //alors thirdpartycategory = acteur -> on ajoute itemptype1 + items_id1)
			//  var_dump ("la ligne records_items = ", $records_items_idB, " a pour itemtype1A : ", $itemtype1A); echo "<br>";
			$reqinsert = $DB->query("UPDATE `glpi_plugin_dlteams_records_items` SET itemtype1 = 'PluginDlteamsSendingReason', `items_id1` = $itemsA_id1 WHERE `id` = $records_items_idB");
			//  var_dump ("records_items", $records_items_idB, " mis à jour avec SendingReason = ", $itemsA_id1); echo "<br>";
			}
		}

		// PROTECTIVEMEASURES selection des lignes de records_items pour le record source et itemtype =  PluginDlteamsProtectivemeasure
		global $protectivemeasuresB_id, $protectivemeasures_items_idB ;
		$req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_records_items` WHERE `records_id` = $recordsA_id AND `itemtype` = 'PluginDlteamsProtectivemeasure'");
        foreach ($req as $items_id => $row) { //pour chaque ligne
			$records_items_idA = $row['id']; $itemsA_id = $row['items_id']; // on recupere les datas de la ligne source

			//  var_dump ("Pour records_item id = ", $records_items_idA ," et l'itemsA_id = ", $itemsA_id) ; //on prend l'items_id et on vérifie si le name existe dans l'entité cible
			$reqexist = $DB->request("SELECT * FROM `glpi_plugin_dlteams_protectivemeasures` WHERE 
                                                    `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_protectivemeasures` WHERE `id` = $itemsA_id)");
			if (count($reqexist)) {
				//  var_dump (count($reqexist), $entitiesB_id, "protectivemeasures existe, il faut prendre l'id : ") ;
				foreach ($reqexist as $id => $row2) {$protectivemeasuresB_id = $row2['id'];}
				//  var_dump ("l'idB est ", $protectivemeasuresB_id) ;
			} else { // si il existe pas on le créée
				//  var_dump (count($reqexist), $entitiesB_id, "protectivemeasures n'existe pas, il faut créer -> ") ;
				$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_protectivemeasures` (`entities_id`, `id_model`, `entity_model`, `date_majmodel`, `name`, `content`, `comment`, `date_creation`, `plugin_dlteams_protectivetypes_id`, `plugin_dlteams_protectivecategories_id`)
					SELECT $entitiesB_id, $itemsA_id, $entitiesA_id, `date_mod`, `name`, `content`, `comment`, NOW(), `plugin_dlteams_protectivetypes_id`, `plugin_dlteams_protectivecategories_id`
					FROM `glpi_plugin_dlteams_protectivemeasures` WHERE `id` = $itemsA_id");
				// et on récupère l'id
				$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_protectivemeasures`');
				$data = $DB->fetchAssoc($result); $protectivemeasuresB_id = $data['LAST_INSERT_ID()'];
				//  var_dump ("protectivemeasures créé, id = ", $protectivemeasuresB_id); echo "<br>" ;
			}
		// on insert l'enregistrement dans records_items, puis protectivemeasures_items
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_records_items` (`records_id`, `itemtype`, `items_id`, `comment`, `date_creation`, `mandatory`)
			SELECT $recordsB_id, `itemtype`, $protectivemeasuresB_id, `comment`, NOW(), `mandatory` FROM `glpi_plugin_dlteams_records_items` WHERE `id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_records_items`');
			$data = $DB->fetchAssoc($result); $records_items_idB = $data['LAST_INSERT_ID()'];
			//  var_dump ("records_items créé, id = ", $records_items_idB, "items_id = ", $protectivemeasuresB_id); echo "<br>" ;
		$reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_protectivemeasures_items` (`protectivemeasures_id`, `items_id`, `itemtype`, `comment`, `date_creation`)
			SELECT $protectivemeasuresB_id, $recordsB_id, 'PluginDlteamsRecord', `comment`, NOW() FROM `glpi_plugin_dlteams_records_items` AS T1 WHERE T1.`id` = $records_items_idA");
			$result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_protectivemeasures_items`');
			$data = $DB->fetchAssoc($result); $protectivemeasures_items_idB = $data['LAST_INSERT_ID()'];
			//  var_dump ("protectivemeasures_items créé, ligne : ", $protectivemeasures_items_idB, ", protectivemeasures_id = ", $protectivemeasuresB_id, " modèle = ", $records_items_idA); echo "<br>" ;
		}



//        Type de documents (policiform)
        global $policieformB_id, $policieform_items_idB;
        $req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_records_items` WHERE `records_id` = $recordsA_id AND `itemtype` = 'PluginDlteamsPolicieForm'");
        foreach ($req as $items_id => $row) { //pour chaque ligne
            $records_items_idA = $row['id']; $itemsA_id = $row['items_id']; $itemtype1A = $row['itemtype1'] ; $itemsA_id1 = $row['items_id1'];  // on recupere les datas pour la copie

            //  var_dump ("Pour records_item id = ", $records_items_idA ," et l'itemsA_id = ", $itemsA_id) ; //on prend l'items_id et on vérifie si le name existe dans l'entité cible
            $reqexist = $DB->request("SELECT * FROM `glpi_plugin_dlteams_policieforms` WHERE 
                                                    `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_policieforms` WHERE `id` = $itemsA_id)");
            if (count($reqexist)) {
                //  var_dump (count($reqexist), $entitiesB_id, "legalbasis existe, il faut prendre l'id : ") ;
                foreach ($reqexist as $id => $row2) {$legalbasisB_id = $row2['id'];}
                //  var_dump ("l'idB est ", $legalbasisB_id) ;
            } else { // si il existe pas on le créée
                //  var_dump (count($reqexist), $entitiesB_id, "legalbasis n'existe pas, il faut créer -> ") ;
                $reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_policieforms` (`entities_id`, `id_model`, `entity_model`, `date_majmodel`, `name`, `content`, `comment`, `date_creation`)
					SELECT $entitiesB_id, $itemsA_id, $entitiesA_id, `date_mod`, `name`, `content`, `comment`, NOW()
					FROM `glpi_plugin_dlteams_legalbasis` WHERE `id` = $itemsA_id");
                // et on récupère l'id
                $result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_policieforms`');
                $data = $DB->fetchAssoc($result); $policieformB_id = $data['LAST_INSERT_ID()'];

            }
            // on insert l'enregistrement dans records_items, puis legalbasis_items
            $reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_records_items` (`records_id`, `itemtype`, `items_id`, `comment`, `date_creation`, `mandatory`)
			SELECT $recordsB_id, `itemtype`, $policieformB_id, `comment`, NOW(), `mandatory` FROM `glpi_plugin_dlteams_records_items` WHERE `id` = $records_items_idA");
            $result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_records_items`');
            $data = $DB->fetchAssoc($result); $policieform_items_idB = $data['LAST_INSERT_ID()'];
            //  var_dump ("records_items créé, id = ", $records_items_idB, "items_id = ", $legalbasisB_id); echo "<br>" ;
            $reqinsert = $DB->query("INSERT INTO `glpi_plugin_dlteams_policieforms_items` (`policieforms_id`, `items_id`, `itemtype`, `comment`, `date_creation`)
			SELECT $policieformB_id, $recordsB_id, 'PluginDlteamsRecord', `comment`, NOW() FROM `glpi_plugin_dlteams_policieforms_items` AS T1 WHERE T1.`id` = $policieform_items_idB");
            $result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_policieforms_items`');
            $data = $DB->fetchAssoc($result); $policieform_items_idB = $data['LAST_INSERT_ID()'];
            //  var_dump ("legalbasis_items créé, ligne : ", $legalbasis_items_idB, ", legalbasis_id = ", $legalbasisB_id, " modèle = ", $records_items_idA); echo "<br>" ;
        }
		// die ;
		return true;
    }


    /**
     * Copy a record to another entity. Execute copy action for massive action.
     * @param integer $entity id of the target entity
     *
     * @return Boolean true if success, false otherwize.
     */
    public function copyDatacatalog($entity, $id, CommonDBTM $item)
    {
        global $DB, $entitiesA_id, $entitiesB_id, $recordsA_id, $recordsB_id, $processeddataB_id, $itemsA_id, $itemsA_id1, $concernedpersons_items_id, $processeddatas_items_id,
               $records_items_idA, $records_items_idA, $concernedpersons_items_idB, $itemtype1A, $test ;
        $message = "Copie de catalogues de données". nl2br("\n");
        $dbu = new DbUtils();
        $DB->beginTransaction();

        $entitiesA_id = $item->fields['entities_id'];
        $entitiesB_id = $entity;
        $datacatalogA_id = $item->fields['id'];
        $iduser = Session::getLoginUserID();
        $userC_id = Session::getLoginUserID();

        // DATACATALOG : Existe-il dans l'entité cible un traitement avec le même nom que celui à copier ?
        $req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_datacatalogs` WHERE `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_datacatalogs` WHERE `id` = $id)");


        // var_dump (count($req)) ;
        if (count($req)) { // oui existe -> on quitte
            foreach ($req as $id => $row) {
                $id = $row['id'];
                // pour tests -- $DB->request("DELETE FROM glpi_plugin_dlteams_records WHERE id = $id");
                // var_dump ("Traitement déjà existant (entité, record) : ", $entitiesB_id,", ", $id);
                $message .= (sprintf(__('Catalogue de données déjà existant : %s', 'dlteams'), $item->getName())) . nl2br("\n") ;
            }
            Session::addMessageAfterRedirect($message, false, ERROR);
            Html::back();
        }
        else { // non -> on le créée
            //foreach ($req as $id => $row) {
            $query = "INSERT INTO `glpi_plugin_dlteams_datacatalogs` (`is_deleted`, `entities_id`, `is_recursive`, `name`, `profil_name`, `plugin_dlteams_catalogclassifications_id`, `completename`, `data_category`, `content`, `comment`, `plugin_dlteams_datacarriercategories_id`, `users_id_tech`, `groups_id_tech`, `suppliers_id`, `contacts_id`, `visible_datas`, `profile_rights`, `access_means`, `date_mod`, `date_creation`, `is_helpdesk_visible`, `users_id`, `plugin_dlteams_databasetypes_id`, `external_supplier`, `plugin_dlteams_datacatalogs_id`, `level`, `ancestors_cache`, `sons_cache`) 
                        SELECT is_deleted, $entity, is_recursive, name, profil_name, plugin_dlteams_catalogclassifications_id, completename, data_category, content, comment, plugin_dlteams_datacarriercategories_id, users_id_tech, groups_id_tech, suppliers_id, contacts_id, visible_datas, profile_rights, access_means, date_mod, NOW(), NOW(), is_helpdesk_visible, users_id, plugin_dlteams_databasetypes_id, external_supplier, plugin_dlteams_datacatalogs_id, level, ancestors_cache, sons_cache
                         FROM `glpi_plugin_dlteams_datacatalogs` WHERE `id` = $datacatalogA_id";
            $DB->query($query);

            $result = $DB->query('SELECT LAST_INSERT_ID() FROM `glpi_plugin_dlteams_datacatalogs`'); // or die("Echec recuperation lastinsertid dans la table ");
            $data = $DB->fetchAssoc($result); $datacatalogB_id = $data['LAST_INSERT_ID()'];

            $message .= (sprintf(__('Catalogue de donné créée : %s', 'dlteams'), $item->getName())) . nl2br("\n") ;
        }
        // CONCERNEDPERSON + PROCESSEDATA selection des lignes de records_items pour le record source et itemtype =  PluginDlteamsConcernedPerson
        $req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_datacatalogs_items` WHERE `datacatalogs_id` = $datacatalogA_id");
        foreach ($req as $items_id => $row) {

            $dt_itemtype = $row["itemtype"];

            switch ($row["itemtype"]){
                case PluginDlteamsDataCarrierType::class:
                    $datacarrier_item = new PluginDlteamsDataCarrierType_Item();
                    if(!$datacarrier_item->add([
                        "datacarriertypes_id" => $row["items_id"],
                        "itemtype" => PluginDlteamsDataCatalog::class,
                        "items_id" => $datacatalogB_id,
                        "comment" => $row["comment"]
                    ])){
                        if(Session::DEBUG_MODE)
                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);

                    }

                    $datacatalog_item = new PluginDlteamsDataCatalog_Item();
                    $datacatalog_item->add([
                       "datacatalogs_id" => $datacatalogB_id,
                        "itemtype" => "PluginDlteamsDataCarrierType",
                        "items_id" => $row["items_id"],
                        "comment" => $row["comment"]
                    ]);
            }

        }


        return true;
    }



    /**
     * Copy a record to another entity. Execute copy action for massive action.
     * @param integer $entity id of the target entity
     *
     * @return Boolean true if success, false otherwize.
     */
    public function copyPolicieForm($entity, $id, CommonDBTM $item)
    {
        global $DB;
        $message = "Copie de type de documents". nl2br("\n");

        $ent = new Entity();
        $ent->getFromDB($entity);
        $policieform = new PluginDlteamsPolicieForm();
        $policieform_temp = new PluginDlteamsPolicieForm();
        $policieform->getFromDB($id);
        $from_fields = $policieform->fields;

        $old_id = $from_fields["id"];

//        $array = [
//            ...$item->fields,
//            "content" => addslashes($item->fields["content"]),
//            "entities_id" => $entity
//        ];

        $criteria = [
//            "name" => htmlspecialchars($from_fields["name"]), // id_model
            "id_model" => $old_id,
            "entities_id" => $entity,
            "is_deleted" => false
        ];

        $iterator = $DB->request([
            "FROM" => PluginDlteamsPolicieForm::getTable(),
            "WHERE" => $criteria
        ]);

        $count = 0;
        $idexist = null;
        foreach ($iterator as $pf){
            $idexist = $pf["id"];
            $count++;
            break;
        }


//        var_dump($entity);
//        die();
        if($count > 0){
//            ok ca existe deja, on fait le update en fonction du model
            if(!$policieform_temp->update([
                ...$from_fields,
                "content" => htmlspecialchars($from_fields["content"]),
                "name" => htmlspecialchars($from_fields["name"]),
                "entities_id" => $entity,
                "date_creation" => $_SESSION['glpi_currenttime'],
                "date_mod" => $_SESSION['glpi_currenttime'],
                "id_model" => $old_id,
                "id" => $idexist
            ])){
                $DB->rollback();
                Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
                return false;
            }


            Session::addMessageAfterRedirect(sprintf("Type de document %s mis a jour", $from_fields["name"]));

            $req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_policieforms_items` WHERE `policieforms_id` = $old_id");

            foreach ($req as $items_id => $row) {

                $dt_itemtype = $row["itemtype"];
                switch ($row["itemtype"]) {

                    case PluginDlteamsConcernedPerson::class:
//                        $a = new PluginDlteamsConcernedPerson_Item();
//                    create new concerned person
                        $concernedperson = new PluginDlteamsConcernedPerson();
                        $concernedperson->getFromDB($row["items_id"]);
                        $cparray = [
                            ...$concernedperson->fields,
                            "entities_id" => $entity
                        ];
                        unset($cparray["id"]);
                        unset($cparray["date_creation"]);
                        unset($cparray["date_mod"]);
                        $cparray["name"] = addslashes($cparray["name"]??"");
                        if(!$concernedperson->getFromDBByCrit($cparray) && $cpid = $concernedperson->add($cparray)){
                            Session::addMessageAfterRedirect(sprintf("(%s) %s copié vers %s", $concernedperson::getTypeName(), $cparray["name"], $ent->fields["name"]));

//                            $concernedperson_item = new PluginDlteamsConcernedPerson_Item();
                            $a = new PluginDlteamsConcernedPerson_Item();
                            if($row["itemtype1"]){
                                $processeddata = new PluginDlteamsProcessedData();
                                $processeddata->getFromDB($row["items_id1"]);
                                $pdarray = [
                                    ...$processeddata->fields,
                                    "name" => addslashes($processeddata->fields["name"]??""),
                                    "entities_id" => $entity
                                ];
                                unset($pdarray["id"]);
                                unset($pdarray["date_creation"]);
                                unset($pdarray["date_mod"]);

                                if(!$processeddata->getFromDBByCrit($pdarray))
                                    $pdid = $processeddata->add($pdarray);
                                else
                                    $pdid = $processeddata->fields["id"];
                            }
                            $c = new PluginDlteamsProcessedData_Item();
                            if($a->add([
                                    "concernedpersons_id" => $cpid,
                                    "itemtype" => PluginDlteamsPolicieForm::class,
                                    "items_id" => $idexist,
                                    "itemtype1" => $row["itemtype1"],
                                    "items_id1" => $pdid??0,
                                    "comment" => addslashes($row["comment"]??"")
                                ]) && ( // et itemtype a été defini
                                    ($row["itemtype1"] &&
                                        $c->add([
                                            "processeddatas_id" => $pdid,
                                            "itemtype" => PluginDlteamsPolicieForm::class,
                                            "items_id" => $idexist,
                                            "itemtype1" => "PluginDlteamsConcernedPerson",
                                            "items_id1" => $cpid??0,
                                            "comment" => addslashes($row["comment"]??"")
                                        ])
                                    )
                                    ||
                                    !$row["itemtype1"]
                                )
                            ){
//                        ok ajouté processeddata_item et concrnedperson_item. on peut ajouter policieform_item
                                $b = new PluginDlteamsPolicieForm_Item();
                                $b->add([
                                    "policieforms_id" => $idexist,
                                    "itemtype" => $row["itemtype"],
                                    "itemtype1" => $row["itemtype1"],
                                    "items_id" => $cpid,
                                    "items_id1" => $pdid??0,
                                    "mandatory" => $row["mandatory"],
                                    "comment" => addslashes($row["comment"]??"")
                                ]);
                            }
                            else{
                                if(Session::DEBUG_MODE)
                                    Session::addMessageAfterRedirect($DB->error(), false, ERROR);

                                Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
                                return false;
                            }
                        }
                        break;
                }
            }

            return true;
        }


        unset($from_fields["id"]);
        global $DB;
        $DB->beginTransaction();
        if(!$policieform_temp->add([
            ...$from_fields,
            "content" => htmlspecialchars($from_fields["content"]),
            "name" => htmlspecialchars($from_fields["name"]),
            "entities_id" => $entity,
            "date_creation" => $_SESSION['glpi_currenttime'],
            "date_mod" => $_SESSION['glpi_currenttime'],
            "id_model" => $old_id
        ])){
            $DB->rollback();
            Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
//            die();
            return false;
        }

        $newid = $policieform_temp->fields["id"];

        if(!$newid){
            $DB->rollback();
            Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
//            die();
            return false;
        }

//        copie des traitements

        $req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_policieforms_items` WHERE `policieforms_id` = $old_id");
        foreach ($req as $items_id => $row) {

            $dt_itemtype = $row["itemtype"];
            switch ($row["itemtype"]){

                case PluginDlteamsConcernedPerson::class:
                    $a = new PluginDlteamsConcernedPerson_Item();
//                    create new concerned person
                $concernedperson = new PluginDlteamsConcernedPerson();
                $concernedperson->getFromDB($row["items_id"]);
                $cparray = [
                    ...$concernedperson->fields,
                    "entities_id" => $entity
                ];
                unset($cparray["id"]);
                unset($cparray["date_creation"]);
                unset($cparray["date_mod"]);
                    $cparray["name"] = addslashes($cparray["name"]??"");
                if(!$concernedperson->getFromDBByCrit($cparray))
                    $cpid = $concernedperson->add($cparray);
                else
                    $cpid = $concernedperson->fields["id"];

                    if($row["itemtype1"]){
                        $processeddata = new PluginDlteamsProcessedData();
                        $processeddata->getFromDB($row["items_id1"]);
                        $pdarray = [
                            ...$processeddata->fields,
                            "name" => addslashes($processeddata->fields["name"]??""),
                            "entities_id" => $entity
                        ];
                        unset($pdarray["id"]);
                        unset($pdarray["date_creation"]);
                        unset($pdarray["date_mod"]);

                        if(!$processeddata->getFromDBByCrit($pdarray))
                            $pdid = $processeddata->add($pdarray);
                        else
                            $pdid = $processeddata->fields["id"];
                    }
                    $c = new PluginDlteamsProcessedData_Item();
                    if($a->add([
                        "concernedpersons_id" => $cpid,
                        "itemtype" => PluginDlteamsPolicieForm::class,
                        "items_id" => $newid,
                        "itemtype1" => $row["itemtype1"],
                        "items_id1" => $pdid??0,
                            "comment" => addslashes($row["comment"]??"")
                    ]) && ( // et itemtype a été defini
                            ($row["itemtype1"] &&
                            $c->add([
                                "processeddatas_id" => $pdid,
                                "itemtype" => PluginDlteamsPolicieForm::class,
                                "items_id" => $newid,
                                "itemtype1" => "PluginDlteamsConcernedPerson",
                                "items_id1" => $cpid??0,
                                "comment" => addslashes($row["comment"]??"")
                            ])
                            )
                                ||
                                !$row["itemtype1"]
                        )
                    ){
//                        ok ajouté processeddata_item et concrnedperson_item. on peut ajouter policieform_item
                        $b = new PluginDlteamsPolicieForm_Item();
                        $b->add([
                            "policieforms_id" => $newid,
                            "itemtype" => $row["itemtype"],
                            "itemtype1" => $row["itemtype1"],
                            "items_id" => $cpid,
                            "items_id1" => $pdid??0,
                            "mandatory" => $row["mandatory"],
                            "comment" => addslashes($row["comment"]??"")
                        ]);
                    }
                    else{
                        if(Session::DEBUG_MODE)
                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);

                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
                        return false;
                    }
                    break;
                case PluginDlteamsDataCarrierType::class:
                    $dct = new PluginDlteamsDataCarrierType();

                    $dct->getFromDB($row["items_id"]);
                    $dctarray = [
                        ...$dct->fields,
                        "entities_id" => $entity
                    ];
                    unset($dctarray["id"]);
                    unset($dctarray["date_creation"]);
                    unset($dctarray["date_mod"]);
                    if(!$dct->getFromDBByCrit($dctarray))
                        $dctid = $dct->add($dctarray);
                    else
                        $dctid = $dct->fields["id"];

                    $dct_item = new PluginDlteamsDataCarrierType_Item();
                    $b = new PluginDlteamsPolicieForm_Item();
                    $dct = new PluginDlteamsDataCarrierType();
                    $dct->getFromDB($dctid);

                    if($dct_item->add([
                        "datacarriertypes_id" => $dctid,
                        "itemtype" => PluginDlteamsPolicieForm::class,
                        "items_id" => $newid,
                        "comment" => $row["comment"]??$dct->fields["comment"]
                    ]) && $b->add([
                            "policieforms_id" => $newid,
                            "itemtype" => $row["itemtype"],
                            "items_id" => $dctid,
                            "comment" => $row["comment"]??$dct->fields["comment"]
                        ])){
//                        ok ajouté
                    }
                    else{
                        if(Session::DEBUG_MODE)
                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);

                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
                        return false;
                    }

                    break;
                case PluginDlteamsLegalbasi::class:
                    $lb = new PluginDlteamsLegalbasi();

                    $lb->getFromDB($row["items_id"]);
                    $lbtarray = [
                        ...$lb->fields,
                        "entities_id" => $entity
                    ];
                    unset($dctarray["id"]);
                    unset($dctarray["date_creation"]);
                    unset($dctarray["date_mod"]);
                    if(!$lb->getFromDBByCrit($lbtarray))
                        $lbtid = $lb->add($dctarray);
                    else
                        $lbtid = $lb->fields["id"];

                    $lb_item = new PluginDlteamsLegalbasi_Item();
                    $b = new PluginDlteamsPolicieForm_Item();
                    if($lb_item->add([
                            "legalbasis_id" => $lbtid,
                            "itemtype" => PluginDlteamsPolicieForm::class,
                            "items_id" => $newid,
                            "comment" => $row["comment"]
                        ]) && $b->add([
                            "policieforms_id" => $newid,
                            "itemtype" => $row["itemtype"],
                            "items_id" => $lbtid,
                            "comment" => $row["comment"]
                        ])){
//                        ok ajouté
                    }
                    else{
                        if(Session::DEBUG_MODE)
                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);

                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
                        return false;
                    }

                    break;
                case PluginDlteamsStoragePeriod::class:
                    $sp = new PluginDlteamsStoragePeriod();

                    $sp->getFromDB($row["items_id"]);
                    $sptarray = [
                        ...$sp->fields,
                        "entities_id" => $entity
                    ];
                    unset($sptarray["id"]);
                    unset($sptarray["date_creation"]);
                    unset($sptarray["date_mod"]);
                    if(!$sp->getFromDBByCrit($sptarray))
                        $spid = $sp->add($sptarray);
                    else
                        $spid = $sp->fields["id"];

                    $sp_item = new PluginDlteamsStoragePeriod_Item();
                    $b = new PluginDlteamsPolicieForm_Item();
                    if($sp_item->add([
                            "storageperiods_id" => $spid,
                            "itemtype" => PluginDlteamsPolicieForm::class,
                            "items_id" => $newid,
                            "comment" => $row["comment"]
                        ]) && $b->add([
                            "policieforms_id" => $newid,
                            "itemtype" => $row["itemtype"],
                            "items_id" => $spid,
                            "comment" => $row["comment"]
                        ])){
//                        ok ajouté
                    }
                    else{
                        if(Session::DEBUG_MODE)
                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);

                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
                        return false;
                    }

                    break;
//                case PluginDlteamsProcessedData::class:
//                    $a = new PluginDlteamsProcessedData_Item();
//                    if(!$a->add([
//                        "processeddatas_id" => $row["items_id"],
//                        "itemtype" => PluginDlteamsPolicieForm::class,
//                        "items_id" => $newid,
//                        "comment" => $row["comment"]
//                    ])){
//                        if(Session::DEBUG_MODE)
//                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
//
//                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
//                        return false;
//                    }
//
//                    $b = new PluginDlteamsPolicieForm_Item();
//                    $b->add([
//                        "policieforms_id" => $newid,
//                        "itemtype" => PluginDlteamsProcessedData::class,
//                        "items_id" => $row["items_id"],
//                        "comment" => $row["comment"]
//                    ]);
//                    break;
//                case PluginDlteamsProtectiveMeasure::class:
//                    $a = new PluginDlteamsProtectiveMeasure_Item();
//                    if(!$a->add([
//                        "protectivemeasures_id" => $row["items_id"],
//                        "itemtype" => PluginDlteamsPolicieForm::class,
//                        "items_id" => $newid,
//                        "comment" => $row["comment"]
//                    ])){
//                        if(Session::DEBUG_MODE)
//                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
//
//                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
//                        return false;
//                    }
//
//                    $b = new PluginDlteamsPolicieForm_Item();
//                    $b->add([
//                        "policieforms_id" => $newid,
//                        "itemtype" => PluginDlteamsProtectiveMeasure::class,
//                        "items_id" => $row["items_id"],
//                        "comment" => $row["comment"]
//                    ]);
//                    break;
//                case Document::class:
//                    $a = new PluginDlteamsDocument_Item();
//                    if(!$a->add([
//                        "documents_id" => $row["items_id"],
//                        "itemtype" => PluginDlteamsPolicieForm::class,
//                        "items_id" => $newid,
//                        "comment" => $row["comment"]
//                    ])){
//                        if(Session::DEBUG_MODE)
//                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
//
//                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
//                        return false;
//                    }
//
//                    $b = new PluginDlteamsPolicieForm_Item();
//                    $b->add([
//                        "policieforms_id" => $newid,
//                        "itemtype" => Document::class,
//                        "items_id" => $row["items_id"],
//                        "comment" => $row["comment"]
//                    ]);
//                    break;
//                case PluginDlteamsLegalBasi::class:
//                    $a = new PluginDlteamsLegalbasi_Item();
//                    if(!$a->add([
//                        "legalbasis_id" => $row["items_id"],
//                        "itemtype" => PluginDlteamsPolicieForm::class,
//                        "items_id" => $newid,
//                        "comment" => $row["comment"]
//                    ])){
//                        if(Session::DEBUG_MODE)
//                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
//
//                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
//                        return false;
//                    }
//
//                    $b = new PluginDlteamsPolicieForm_Item();
//                    if(!$b->add([
//                        "policieforms_id" => $newid,
//                        "itemtype" => PluginDlteamsLegalbasi::class,
//                        "items_id" => $row["items_id"],
//                        "comment" => $row["comment"]
//                    ])){
//                        if(Session::DEBUG_MODE)
//                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
//
//                        Session::addMessageAfterRedirect("Une erreur s'est produite.", 0, ERROR);
//                        return false;
//                    }
//                    break;
            }
        }


        $DB->commit();
       $message.=sprintf("<a target='_blank' href='%s'>%s</a> éffectué avec succès", PluginDlteamsPolicieForm::getFormURLWithID($policieform->fields["id"]), $policieform->fields["name"]);

       Session::addMessageAfterRedirect($message);

        return true;
    }





/*        // personal data categorie
        $req = $DB->request("SELECT id FROM glpi_plugin_dlteams_records WHERE name = '$name' AND entities_id = '$entity'");
        //if ($row = $req->next()) {
        foreach ($req as $id => $row) {
            $idRecord = $row['id']; //get id of copied record
        }
        $reqpersonalcategory = $DB->request("SELECT * FROM glpi_plugin_dlteams_records_items
                                                    WHERE (records_id = '$id_ori' AND itemtype='PluginDlteamsConcernedPerson' AND itemtype1='PluginDlteamsProcessedData')
                                                    OR (records_id = '$id_ori' AND itemtype IS NULL AND itemtype1 = 'PluginDlteamsProcessedData')
                                                    OR (records_id = '$id_ori' AND itemtype = 'PluginDlteamsConcernedPerson' AND itemtype1 IS NULL)
                                                    ");
        if (count($reqpersonalcategory)) {
            foreach ($reqpersonalcategory as $id => $row) {
                //echo($row['plugin_dlteams_processeddatas_id']);
                $concernedperson_id = $row['items_id']??0;
                $processeddata_id = $row['items_id1']??0;
                $comment = $row['comment'] ? str_replace('"', '', addslashes($row['comment'])) : '';
                $ismandatory = $row['mandatory'];
				//$DB->request("INSERT INTO glpi_plugin_dlteams_records_personalanddatacategories (plugin_dlteams_records_id, plugin_dlteams_concernedpersons_id, plugin_dlteams_processeddatas_id, mandatory) SELECT '$idRecord', '$val1', '$val2', '$val3'");
                $DB->request("INSERT INTO glpi_plugin_dlteams_records_items (records_id, itemtype, items_id, itemtype1, items_id1, comment, mandatory)
						SELECT '$idRecord', 'PluginDlteamsConcernedPerson', '$concernedperson_id', 'PluginDlteamsProcessedData', '$processeddata_id', '$comment', '$ismandatory'");
				//Si on a une concerned person
                if ($row['itemtype'] && isset($row['items_id']) && $row['items_id'] != null && $row['items_id'] > 0) {
                    $DB->request("INSERT INTO glpi_plugin_dlteams_concernedpersons_items (concernedpersons_id,itemtype,items_id,itemtype1, items_id1, comment)
                                        SELECT '$concernedperson_id', 'PluginDlteamsRecord', '$idRecord', 'PluginDlteamsProcessedData', '$processeddata_id','$comment'");
                }
                if ($row['itemtype1'] && isset($row['items_id1']) && $row['items_id1'] != null && $row['items_id1'] > 0) {
                    $DB->request("INSERT INTO glpi_plugin_dlteams_processeddatas_items (processeddatas_id,itemtype,items_id,itemtype1, items_id1, comment) SELECT '$processeddata_id', 'PluginDlteamsRecord', '$idRecord', 'PluginDlteamsConcernedPerson', '$concernedperson_id','$comment'");
                }
            }
        } else {
        }

        //
        $reqlegalbasis = $DB->request("SELECT * FROM glpi_plugin_dlteams_legalbasis_items WHERE itemtype='PluginDlteamsRecord' AND items_id='$id_ori'");
        if (count($reqlegalbasis)) {
            foreach ($reqlegalbasis as $id => $row) {
                $legalbasis_id = $row["legalbasis_id"];
                $comment = $row['comment'] ? str_replace('"', '', addslashes($row['comment'])) : '';
                $DB->request("INSERT INTO glpi_plugin_dlteams_legalbasis_items (legalbasis_id,items_id,itemtype,comment) SELECT '$legalbasis_id', '$idRecord', 'PluginDlteamsRecord', '$comment'");


                $DB->request("INSERT INTO glpi_plugin_dlteams_records_items (records_id,items_id,itemtype,comment)
								SELECT '$idRecord', '$legalbasis_id', 'PluginDlteamsLegalBasi', '$comment'");
            }
        } else {
        }

        // storage period new
		// $reqstorageperiod = $DB->request("SELECT * FROM glpi_plugin_dlteams_records_storages WHERE plugin_dlteams_records_id = '$id_ori'");
        $reqstorageperiod = $DB->request("SELECT * FROM glpi_plugin_dlteams_records_items WHERE records_id = '$recordsA_id' AND itemtype='PluginDlteamsStoragePeriod'");
        if (count($reqstorageperiod)) {
            foreach ($reqstorageperiod as $id => $row) {
                $storageperiods_id = $row['items_id'];
                $storageendactions_id = $row['plugin_dlteams_storageendactions_id'] ?? 0;
                $storagetypes_id = $row['plugin_dlteams_storagetypes_id'] ?? 0;
                $comment = $row['comment'] ? str_replace('"', '', addslashes($row['comment'])) : '';
		// $DB->request("INSERT INTO glpi_plugin_dlteams_records_storages (plugin_dlteams_records_id, plugin_dlteams_storageperiods_id, plugin_dlteams_storagetypes_id, plugin_dlteams_storageendactions_id, storage_comment, storage_action) SELECT '$idRecord', '$val1', '$val2', '$val3', '$val4', '$val5'");
                $DB->request("INSERT INTO glpi_plugin_dlteams_records_items (records_id, itemtype, items_id, plugin_dlteams_storageendactions_id, plugin_dlteams_storagetypes_id, comment)
                                    SELECT '$idRecord', 'PluginDlteamsStoragePeriod', '$storageperiods_id', '$storageendactions_id', '$storagetypes_id', '$comment'");
	    // TODO: Insert into storageperiod_item
                $DB->request("INSERT INTO glpi_plugin_dlteams_storageperiods_items (storageperiods_id, itemtype, items_id, comment)
                                    SELECT '$storageperiods_id', 'PluginDlteamsRecord', '$idRecord', '$comment'");
            }
            // from record storage to all item
        } else {
        }

		// storage period new
        // record external new
		// Acteurs du traitement : groupes/personnes de l'organisme et sous-traitants ayant accès aux données
        $reqexternal = $DB->request("SELECT * FROM glpi_plugin_dlteams_records_items
                                            WHERE records_id = '$id_ori'
                                              AND itemtype1 IS NULL
                                              AND (itemtype='User' OR itemtype='Supplier' OR itemtype='Group' OR itemtype='PluginDlteamsThirdPartyCategory')");
        if (count($reqexternal)) {
            foreach ($reqexternal as $id => $row) {
                $itemtype = $row['itemtype'];
                $items_id = $row['items_id'];
                $comment = $row['comment'] ? str_replace('"', '', addslashes($row['comment'])) : '';
                $DB->request("INSERT INTO glpi_plugin_dlteams_records_items (records_id, itemtype, items_id, comment)
						SELECT '$idRecord', '$itemtype', '$items_id', '$comment'");
//                        TODO: switch case itemtype and insert into corresponding item_table
                switch ($itemtype) {
                    case 'User':
                        $DB->request("INSERT INTO glpi_plugin_dlteams_users_items (users_id, itemtype, items_id, comment) SELECT '$items_id', 'PluginDlteamsRecord', '$idRecord', '$comment'");
                        break;
                    case 'Supplier':
                        $DB->request("INSERT INTO glpi_plugin_dlteams_suppliers_items (suppliers_id, itemtype, items_id, comment) SELECT '$items_id', 'PluginDlteamsRecord', '$idRecord', '$comment'");
                        break;
                    case 'Group':
                        $DB->request("INSERT INTO glpi_plugin_dlteams_groups_items (groups_id, itemtype, items_id, comment) SELECT '$items_id', 'PluginDlteamsRecord', '$idRecord', '$comment'");
                        break;
                    case 'PluginDlteamsThirdPartyCategory':
                        $DB->request("INSERT INTO glpi_plugin_dlteams_thirdpartycategories_items (thirdpartycategories_id, itemtype, items_id, comment) SELECT '$items_id', 'PluginDlteamsRecord', '$idRecord', '$comment'");
                        break;
                }
            }
//                Destinataires : à quels organismes ou personnes les données sont communiquées et pour quelles finalités ?
            $req2 = $DB->request("SELECT items_id, itemtype, items_id, itemtype1, items_id1, comment FROM glpi_plugin_dlteams_records_items
                                        WHERE records_id='$id_ori' AND (
                                            (itemtype='Supplier' AND itemtype1='PluginDlteamsSendingReason')
                                            OR (itemtype='PluginDlteamsThirdPartyCategory' AND itemtype1='PluginDlteamsSendingReason')
                                            OR (itemtype='PluginDlteamsConcernedPerson' AND itemtype1='PluginDlteamsSendingReason')
                                            )
                                        ");
            foreach ($req2 as $id => $row) {
                $itemtype = $row['itemtype'];
                $itemtype1 = $row['itemtype1'];
                $items_id = $row['items_id']??0;
                $items_id1 = $row['items_id1']??0;
                $comment = $row['comment'] ? str_replace('"', '', addslashes($row['comment'])) : '';
//                selectionner la sending reason ayant comment entité l'entité vers laquelle on fait la copie
                $sendingreasons = $DB->request("SELECT name FROM glpi_plugin_dlteams_sendingreasons WHERE id='$items_id1' AND entities_id='$entity'");
//                        si cette sending reason n'existe pas
                if (count($sendingreasons) <= 0) {
//                            on la créé
                    if (count($sendingreasons) > 0) {
                        $request_insert_sendingreasons = $DB->request("INSERT INTO glpi_plugin_dlteams_sendingreasons (name, comment, entities_id, is_recursive, date_creation, users_id_creator, date_mod, users_id_lastupdater, type)
                                                                                                       SELECT name, comment, '$entity', is_recursive, date_creation, users_id_creator, date_mod, users_id_lastupdater, type");
                    }
                }
                $sendingreasons = $DB->request("SELECT * FROM glpi_plugin_dlteams_sendingreasons WHERE id='$items_id1'");
                foreach ($sendingreasons as $id => $row1) {
                    $sendingreasons_id = $row1['id'];
                }
                switch ($itemtype) {
                    case 'PluginDlteamsConcernedPerson':
                        $DB->request("INSERT INTO glpi_plugin_dlteams_concernedpersons_items (concernedpersons_id, itemtype, items_id, itemtype1, items_id1, comment) SELECT '$items_id', 'PluginDlteamsRecord', '$idRecord', 'PluginDlteamsSendingReason', '$sendingreasons_id', '$comment'");
                        break;
                    case 'PluginDlteamsThirdPartyCategory':
                        $DB->request("INSERT INTO glpi_plugin_dlteams_thirdpartycategories_items (thirdpartycategories_id, itemtype, items_id, itemtype1, items_id1, comment) SELECT '$items_id', 'PluginDlteamsRecord', '$idRecord', 'PluginDlteamsSendingReason', '$sendingreasons_id', '$comment'");
                        break;
                    case 'Supplier':
                        $DB->request("INSERT INTO glpi_plugin_dlteams_suppliers_items (suppliers_id, itemtype, items_id, itemtype1, items_id1, comment) SELECT '$items_id', 'PluginDlteamsRecord', '$idRecord', 'PluginDlteamsSendingReason', '$sendingreasons_id', '$comment'");
                        break;
                }
                $DB->request("INSERT INTO glpi_plugin_dlteams_sendingreasons_items (sendingreasons_id, itemtype, items_id, itemtype1, items_id1, comment)
                                                    SELECT '$sendingreasons_id', 'PluginDlteamsRecord', '$idRecord', '$itemtype', '$items_id', '$comment'");
                $DB->request("INSERT INTO glpi_plugin_dlteams_records_items (records_id, itemtype, items_id, itemtype1, items_id1, comment)
                                            SELECT '$idRecord', '$itemtype', '$items_id', '$itemtype1', '$sendingreasons_id', '$comment'");
            }
        } else {
        }

        //
        $reqprotective = $DB->request("SELECT * FROM glpi_plugin_dlteams_protectivemeasures_items WHERE itemtype='PluginDlteamsRecord' AND items_id='$id_ori'");
        if (count($reqprotective)>0) {
            foreach ($reqprotective as $id => $row) {
                $protectivemeasures_id = $row['protectivemeasures_id'];
                $comment = $row['comment'] ? str_replace('"', '', addslashes($row['comment'])) : '';

                $DB->request("INSERT INTO glpi_plugin_dlteams_protectivemeasures_items (protectivemeasures_id,itemtype,items_id,comment)
                                    SELECT '$protectivemeasures_id', 'PluginDlteamsRecord', '$idRecord', '$comment'");

                $DB->request("INSERT INTO glpi_plugin_dlteams_records_items (records_id,itemtype,items_id,comment)
                                    SELECT '$idRecord', 'PluginDlteamsProtectiveMeasure', '$protectivemeasures_id', '$comment'");
            }
        }

        // Vérification des erreurs
        if ($DB->error()) {
            echo "db error";
            // Annulation de la transaction en cas d'erreur
            $DB->rollBack();
        } else {
            // Validation de la transaction
            $DB->commit();
        }
        return true;
    } */

    public function deletewithallchildren($entity, $id, $item)
    {

    }

    /**
     * Copy the row ind DB of an item.
     * @param mixed $item
     * @param String $itemType
     * @param integer $entity
     *
     * @return integer
     */
    static public function fast_copy($item, $itemType, $entity)
    {
        global $DB;
        $copy = $item->fields;
        $copy['entities_id'] = $entity;
        // Remove unused key
        unset(
            $copy['users_id_creator'],
            $copy['users_id_lastupdater'],
            $copy['date_creation'],
            $copy['date_mod']
        );
        $copy['_skip_checks'] = true;
        $itemImport = new $itemType();
        $originalId = $copy['id'];
        unset($copy['id']);
        // Escape text fields
        foreach ($copy as $key => $element) {
            $copy[$key] = $DB->escape($element);
        }
        $itemId = $itemImport->add($copy, [], false);
        if ($itemId === false) {
            $typeName = strtolower(self::getTypeName());
            throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $copy['name']));
        }
        return $itemId;
    }
}
