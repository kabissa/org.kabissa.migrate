<?php
set_time_limit(0);

/**
 * Migrate.Groups API
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_groups($params) {
  $returnValues = array();
  /*
   * remove all existing groups before migrating
   */
  CRM_Core_DAO::executeQuery('DELETE FROM civicrm_group');
  /*
   * can do all groups at once as old Civi did not use parent/child tree
   */
  $query = 'SELECT * FROM v6_group WHERE source IS NULL AND saved_search_id IS NULL';
  $daoGroup = CRM_Core_DAO::executeQuery($query);
  while ($daoGroup->fetch()) {
    _createGroup($daoGroup);
    $returnValues[] = 'Group '.$daoGroup->title.' created';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Groups');
}

/**
 * Function to create each single group from imported values
 *
 * @param $daoGroup
 * @throws CiviCRM_API3_Exception when API error
 */
function _createGroup($daoGroup) {
  $params = array();
  $groupFields = CRM_Contact_DAO_Group::fieldKeys();
  foreach ($groupFields as $fieldName) {
    if (isset($daoGroup->$fieldName) && $fieldName != 'id') {
      $params[$fieldName] = $daoGroup->$fieldName;
    }
  }
  civicrm_api3('Group', 'Create', $params);
}
