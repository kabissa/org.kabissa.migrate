<?php
set_time_limit(0);

/**
 * Migrate.CustomGroups API
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_customgroups($params) {
  $returnValues = array();
  /*
   * remove all existing custom_groups before migrating
   */
  CRM_Core_DAO::executeQuery('DELETE FROM civicrm_custom_group');
  $query = 'SELECT * FROM v6_custom_group';
  $daoCustomGroup = CRM_Core_DAO::executeQuery($query);
  while ($daoCustomGroup->fetch()) {
    _createCustomGroup($daoCustomGroup);
    $returnValues[] = 'CustomGroup '.$daoCustomGroup->title.' extending '.$daoCustomGroup->extends.'created';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'CustomGroups');
}

/**
 * Function to create each single custom group from imported values
 *
 * @param object $daoCustomGroup
 * @throws CiviCRM_API3_Exception when API error
 */
function _createCustomGroup($daoCustomGroup) {
  $params = array();
  $customGroupFields = CRM_Core_DAO_CustomGroup::fieldKeys();
  foreach ($customGroupFields as $fieldName) {
    if (isset($daoCustomGroup->$fieldName) && $fieldName != 'id') {
      $params[$fieldName] = $daoCustomGroup->$fieldName;
    }
  }
  $newCustomGroup = civicrm_api3('CustomGroup', 'Create', $params);
  CRM_Migrate_Utils::createMigrateKey($daoCustomGroup->id, 'CustomGroup', $newCustomGroup['id']);
  /*
   * create custom fields for group
   */
  _createCustomFields($daoCustomGroup->id, $newCustomGroup['id']);
}

/**
 * Function to retrieve and create custom fields for the custom group
 *
 * @param int $oldCustomGroupId
 * @param int $newCustomGroupId
 */
function _createCustomFields($oldCustomGroupId, $newCustomGroupId) {
  $customFieldFields = CRM_Core_DAO_CustomField::fieldKeys();
  $fieldQuery = 'SELECT * FROM v6_custom_field WHERE custom_group_id = %1';
  $fieldQueryParams = array(1 => array($oldCustomGroupId, 'Integer'));
  $daoField = CRM_Core_DAO::executeQuery($fieldQuery, $fieldQueryParams);
  while ($daoField->fetch()) {
    foreach ($customFieldFields as $fieldName) {
      if ($fieldName != 'id' && $fieldName != 'custom_group_id') {
        $params[$fieldName] = $daoField->$fieldName;
      }
    }
    $params['custom_group_id'] = $newCustomGroupId;
    $newCustomField = civicrm_api3('CustomField', 'Create', $params);
    CRM_Migrate_Utils::createMigrateKey($daoField->id, 'CustomField', $newCustomField['id']);
  }
}
