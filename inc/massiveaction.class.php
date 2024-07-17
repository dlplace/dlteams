<?php



class PluginDlteamsMassiveAction extends CommonDBTM {

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
//        var_dump("jjsl");
//        die();
        switch ($ma->getAction()) {
            case 'copyTo':
                Entity::dropdown([
                    'name' => 'entities_id',
                ]);
                echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                    $itemtype_str = $item->getType();

//                    var_dump($itemtype_str = $item->getType());
//                    die();
                switch ($itemtype_str) {
                    case SupplierType::class:
                        global $DB;

                        $entities_source = $item->fields["entities_id"]; // Entité source
                        $entities_cible = $ma->POST["entities_id"]; // Entité cible

                        foreach ($ids as $id) {
                            $fields_exports = [
                                [SupplierType::getTable(), '`name`, `comment`, `entities_id`,  `is_recursive`,  `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
                            ];

                            foreach ($fields_exports as list($table, $fields)) {
                                // Insertion
                                $insert_query = "
                                    INSERT INTO `glpi_suppliertypes` (name, comment, entities_id, is_recursive, id_model)
                                    SELECT name, comment, $entities_cible, is_recursive, id
                                    FROM `$table`
                                    WHERE `entities_id` = $entities_source AND id NOT IN (
                                        SELECT id_model
                                        FROM `$table`
                                        WHERE entities_id = $entities_cible
                                    )
                                ";
                                                $DB->query($insert_query) or die($DB->error());

                                                // Mise à jour
                                                $update_query = "
                                    UPDATE `$table` AS dest
                                    JOIN `$table` AS src ON dest.id_model = src.id
                                    SET dest.name = src.name,
                                        dest.comment = src.comment,
                                        dest.is_recursive = src.is_recursive
                                    WHERE dest.entities_id = $entities_cible AND src.entities_id = $entities_source
                                ";
                                                $DB->query($update_query) or die($DB->error());
                                            }
                                        }
                                        break;

                    case Appliance::class:
                        $entities_source = $item->fields["entities_id"]; // Entité source
                        $entities_cible = $ma->POST["entities_id"]; // Entité cible

                        foreach ($ids as $id) {
                            $applicance = new Appliance();
                            $applicance_temp = new Appliance();
                            $applicance->getFromDB($id);
                            if(!$applicance_temp->getFromDBByCrit([
                                "name" => $applicance->fields["name"],
                                "entities_id" => $entities_cible
                            ])){
                                $applicance_temp->add([
                                    "name" => $applicance->fields["name"],
                                    "appliancetypes_id" => $applicance->fields["appliancetypes_id"],
                                    "comment" => $applicance->fields["comment"],
                                    "is_recursive" => $applicance->fields["is_recursive"],
                                    "entities_id" => $entities_cible,
                                ]);
                                Session::addMessageAfterRedirect("Copié avec succès");
                            }
                        }
                        break;
                                }
                break;
        }
    }
}