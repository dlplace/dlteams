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

class PluginDlteamsProfile extends Profile {
   static $rightname = "profile";

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == Profile::class) {
         /** @var Profile $item */
         if ($item->getField('id')
            && ($item->getField('interface') != 'helpdesk')) {
            return PluginDlteamsMenu::getTypeName(2);
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == Profile::class) {
         /** @var Profile $item */
         $ID = $item->getID();
         $prof = new self();
         $prof->showForm($ID);
      }
      return true;
   }

   public function showForm($ID, $options = []) {
      $profile = new Profile();
      if (($can_update = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))) {
          echo "<form method='post' action='" . $profile->getFormURL() . "'>";
      }

      $profile->getFromDB($ID);
      if ($profile->getField('interface') == 'central') {

         $rights = $this->getAllRights();
         $profile->displayRightsChoiceMatrix($rights, [
            'canedit' => $can_update,
            'default_class' => 'tab_bg_2',
            'title' => __("General")
         ]);
      }

      if ($can_update) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $ID]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
   }

   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      $profileRight = new ProfileRight();
      $dbu          = new DbUtils();
      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights', [
            'profiles_id' => $profiles_id,
             'name' => $right
             ])
             && $drop_existing) {

            $profileRight->deleteByCriteria([
               'profiles_id' => $profiles_id,
               'name' => $right]);
         }

         if (!$dbu->countElementsInTable('glpi_profilerights', [
            'profiles_id' => $profiles_id,
             'name' => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   static function createFirstAccess($ID) {
      self::addDefaultProfileInfos($ID, [
         'plugin_dlteams_controllerinfo' => CREATE | READ | UPDATE,
         'plugin_dlteams_createpdf' => CREATE,
/* les objets*/
         'plugin_dlteams_accountkey' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_record' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_concernedperson' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_processeddata' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_legalbasi' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_storageperiod' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_thirdpartycategory' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_rightmeasure' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_policieform' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_datacarrier' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_datacatalog' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_appliance' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_account' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_networkport' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_riskassessment' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_audit' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_protectivemeasure' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_trainingcertification' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_trainingsession' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_deliverable' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_knowbaseitem' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_step' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_procedure' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_physicalstorage' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_vehicle' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
/* les intitulés*/
         'plugin_dlteams_activitycategory' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_auditcategory' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
         'plugin_dlteams_catalogclassification' => CREATE | READ | UPDATE | DELETE | PURGE,
         'plugin_dlteams_datacarriercategory' => CREATE | READ | UPDATE | DELETE | PURGE,
         'plugin_dlteams_datacarrierhosting' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_datacarriermanagement' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_datacarriertype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_deliverablevariable' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_procedurevariable' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_datacategory' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_impact' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_keytype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_legalbasistype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_meansofacce' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_mediasupport' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_protectivecategory' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_protectivetype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_rightmeasurecategory' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_sendingreason' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_servertype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_siintegration' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_storageendaction' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_storagetype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_transmissionmethod' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_userprofile' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_process' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_storageunittype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_tickettask' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_vehicletype' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_menu' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_iso27001menu' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_accessopening' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
		 'plugin_dlteams_itilfollowup' => CREATE | READ | UPDATE | DELETE | PURGE | READNOTE | UPDATENOTE,
      ], true);
   }

   static function getAllRights($all = false) {
      $rights = [
         [
            'itemtype' => PluginDlteamsControllerInfo::class,
            'label' => PluginDlteamsControllerInfo::getTypeName(2),
            'field' => PluginDlteamsControllerInfo::$rightname,
            'rights' => [
               CREATE => __("Create"),
               READ => __("Read"),
               UPDATE => __("Update"),
            ]
         ],
         [
            'itemtype' => PluginDlteamsCreatePDF::class,
            'label' => PluginDlteamsCreatePDF::getTypeName(2),
            'field' => PluginDlteamsCreatePDF::$rightname,
            'rights' => [
               CREATE => __("Create"),
            ]
         ],
         [
            'itemtype' => PluginDlteamsAccountKey::class,
            'label' => PluginDlteamsAccountKey::getTypeName(2),
            'field' => PluginDlteamsAccountKey::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsRecord::class,
            'label' => PluginDlteamsRecord::getTypeName(2),
            'field' => PluginDlteamsRecord::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsConcernedPerson::class,
            'label' => PluginDlteamsConcernedPerson::getTypeName(2),
            'field' => PluginDlteamsConcernedPerson::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		   [
            'itemtype' => PluginDlteamsProcessedData::class,
            'label' => PluginDlteamsProcessedData::getTypeName(2),
            'field' => PluginDlteamsProcessedData::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsLegalbasi::class,
            'label' => PluginDlteamsLegalbasi::getTypeName(2),
            'field' => PluginDlteamsLegalbasi::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
    	 [
            'itemtype' => PluginDlteamsStoragePeriod::class,
            'label' => PluginDlteamsStoragePeriod::getTypeName(2),
            'field' => PluginDlteamsStoragePeriod::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsThirdPartyCategory::class,
            'label' => PluginDlteamsThirdPartyCategory::getTypeName(2),
            'field' => PluginDlteamsThirdPartyCategory::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsRightMeasure::class,
            'label' => PluginDlteamsRightMeasure::getTypeName(2),
            'field' => PluginDlteamsRightMeasure::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsPolicieForm::class,
            'label' => PluginDlteamsPolicieForm::getTypeName(2),
            'field' => PluginDlteamsPolicieForm::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsDataCarrier::class,
            'label' => PluginDlteamsDataCarrier::getTypeName(2),
            'field' => PluginDlteamsDataCarrier::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],

		 [
            'itemtype' => PluginDlteamsDataCatalog::class,
            'label' => PluginDlteamsDataCatalog::getTypeName(2),
            'field' => PluginDlteamsDataCatalog::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
          [
              'itemtype' => PluginDlteamsTicketTask::class,
              'label' => PluginDlteamsTicketTask::getTypeName(2),
              'field' => PluginDlteamsTicketTask::$rightname,
              'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
          ],
		 [
            'itemtype' => PluginDlteamsAppliance::class,
            'label' => PluginDlteamsAppliance::getTypeName(2),
            'field' => PluginDlteamsAppliance::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsAccount::class,
            'label' => PluginDlteamsAccount::getTypeName(2),
            'field' => PluginDlteamsAccount::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsNetworkPort::class,
            'label' => PluginDlteamsNetworkPort::getTypeName(2),
            'field' => PluginDlteamsNetworkPort::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsRiskAssessment::class,
            'label' => PluginDlteamsRiskAssessment::getTypeName(1),
            'field' => PluginDlteamsRiskAssessment::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsAudit::class,
            'label' => PluginDlteamsAudit::getTypeName(2),
            'field' => PluginDlteamsAudit::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsPhysicalStorage::class,
            'label' => PluginDlteamsPhysicalStorage::getTypeName(2),
            'field' => PluginDlteamsPhysicalStorage::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsProtectiveMeasure::class,
            'label' => PluginDlteamsProtectiveMeasure::getTypeName(2),
            'field' => PluginDlteamsProtectiveMeasure::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsTrainingCertification::class,
            'label' => PluginDlteamsTrainingCertification::getTypeName(2),
            'field' => PluginDlteamsTrainingCertification::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsTrainingSession::class,
            'label' => PluginDlteamsTrainingSession::getTypeName(2),
            'field' => PluginDlteamsTrainingSession::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsDeliverable::class,
            'label' => PluginDlteamsDeliverable::getTypeName(2),
            'field' => PluginDlteamsDeliverable::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		[
            'itemtype' => PluginDlteamsKnowbaseItem::class,
            'label' => PluginDlteamsKnowbaseItem::getTypeName(2),
            'field' => PluginDlteamsKnowbaseItem::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsProcedure::class,
            'label' => PluginDlteamsProcedure::getTypeName(2),
            'field' => PluginDlteamsProcedure::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
          [
              'itemtype' => PluginDlteamsITILFollowup::class,
              'label' => PluginDlteamsITILFollowup::getTypeName(2),
              'field' => PluginDlteamsITILFollowup::$rightname,
              'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
          ],
  		 [
            'itemtype' => PluginDlteamsStep::class,
            'label' => PluginDlteamsStep::getTypeName(2),
            'field' => PluginDlteamsStep::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
  		 [
            'itemtype' => PluginDlteamsVehicle::class,
            'label' => PluginDlteamsVehicle::getTypeName(2),
            'field' => PluginDlteamsVehicle::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],

		 /* Intitulés */
		 [
            'itemtype' => PluginDlteamsAuditCategory::class,
            'label' => PluginDlteamsAuditCategory::getTypeName(1),
            'field' => PluginDlteamsAuditCategory::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		  [
            'itemtype' => PluginDlteamsCatalogClassification::class,
            'label' => PluginDlteamsCatalogClassification::getTypeName(2),
            'field' => PluginDlteamsCatalogClassification::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsDataCarrierCategory::class,
            'label' => PluginDlteamsDataCarrierCategory::getTypeName(2),
            'field' => PluginDlteamsDataCarrierCategory::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		  [
            'itemtype' => PluginDlteamsDataCarrierHosting::class,
            'label' => PluginDlteamsDataCarrierHosting::getTypeName(2),
            'field' => PluginDlteamsDataCarrierHosting::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsDataCarrierManagement::class,
            'label' => PluginDlteamsDataCarrierManagement::getTypeName(2),
            'field' => PluginDlteamsDataCarrierManagement::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsDataCarrierType::class,
            'label' => PluginDlteamsDataCarrierType::getTypeName(2),
            'field' => PluginDlteamsDataCarrierType::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
          [
              'itemtype' => PluginDlteamsDeliverable_Variable::class,
              'label' => PluginDlteamsDeliverable_Variable::getTypeName(2),
              'field' => PluginDlteamsDeliverable_Variable::$rightname,
              'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
          ],
          [
              'itemtype' => PluginDlteamsProcedure_Variable::class,
              'label' => PluginDlteamsProcedure_Variable::getTypeName(2),
              'field' => PluginDlteamsProcedure_Variable::$rightname,
              'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
          ],
         [
            'itemtype' => PluginDlteamsDataCategory::class,
            'label' => PluginDlteamsDataCategory::getTypeName(2),
            'field' => PluginDlteamsDataCategory::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsImpact::class,
            'label' => PluginDlteamsImpact::getTypeName(2),
            'field' => PluginDlteamsImpact::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsKeytype::class,
            'label' => PluginDlteamsKeytype::getTypeName(2),
            'field' => PluginDlteamsKeytype::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsLegalBasisType::class,
            'label' => PluginDlteamsLegalBasisType::getTypeName(2),
            'field' => PluginDlteamsLegalBasisType::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsMeansOfAcce::class,
            'label' => PluginDlteamsMeansOfAcce::getTypeName(2),
            'field' => PluginDlteamsMeansOfAcce::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsMediaSupport::class,
            'label' => PluginDlteamsMediaSupport::getTypeName(2),
            'field' => PluginDlteamsMediaSupport::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsProtectiveCategory::class,
            'label' => PluginDlteamsProtectiveCategory::getTypeName(2),
            'field' => PluginDlteamsProtectiveCategory::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
		 ],
		 [
            'itemtype' => PluginDlteamsProtectiveType::class,
            'label' => PluginDlteamsProtectiveType::getTypeName(2),
            'field' => PluginDlteamsProtectiveType::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		  [
            'itemtype' => PluginDlteamsActivityCategory::class,
            'label' => PluginDlteamsActivityCategory::getTypeName(2),
            'field' => PluginDlteamsActivityCategory::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsRightMeasureCategory::class,
            'label' => PluginDlteamsRightMeasureCategory::getTypeName(2),
            'field' => PluginDlteamsRightMeasureCategory::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		   [
            'itemtype' => PluginDlteamsSendingReason::class,
            'label' => PluginDlteamsSendingReason::getTypeName(2),
            'field' => PluginDlteamsSendingReason::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsServerType::class,
            'label' => PluginDlteamsServerType::getTypeName(2),
            'field' => PluginDlteamsServerType::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		 [
            'itemtype' => PluginDlteamsSIIntegration::class,
            'label' => PluginDlteamsSIIntegration::getTypeName(2),
            'field' => PluginDlteamsSIIntegration::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
		   [
            'itemtype' => PluginDlteamsStorageEndAction::class,
            'label' => PluginDlteamsStorageEndAction::getTypeName(2),
            'field' => PluginDlteamsStorageEndAction::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsStoragetype::class,
            'label' => PluginDlteamsStoragetype::getTypeName(2),
            'field' => PluginDlteamsStoragetype::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsTransmissionMethod::class,
            'label' => PluginDlteamsTransmissionMethod::getTypeName(2),
            'field' => PluginDlteamsTransmissionMethod::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsUserprofile::class,
            'label' => PluginDlteamsUserprofile::getTypeName(2),
            'field' => PluginDlteamsUserprofile::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsProcess::class,
            'label' => PluginDlteamsProcess::getTypeName(2),
            'field' => PluginDlteamsProcess::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
         [
            'itemtype' => PluginDlteamsVehicleType::class,
            'label' => PluginDlteamsVehicleType::getTypeName(2),
            'field' => PluginDlteamsVehicleType::$rightname,
            'rights' => [CREATE => __("Create"), READ => __("Read"), UPDATE => __("Update"), DELETE => __("Delete"), PURGE => __("Delete permanently"), READNOTE => __("Read notes"), UPDATENOTE => __("Update notes"),]
         ],
      ];

      return $rights;
   }

   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }

   static function initProfile() {
      global $DB;
      $profile = new self();
      foreach ($profile->getAllRights() as $data) {

         if (countElementsInTable('glpi_profilerights', ['name' => $data['field'] ]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      $profiles = $DB->request(
         "SELECT *
          FROM `glpi_profilerights`
          WHERE `profiles_id`='" . $_SESSION['glpiactiveprofile']['id'] . "'
            AND `name` LIKE 'plugin_dlteams_%'"
      );

      foreach ($profiles as $prof) {
          $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

}
