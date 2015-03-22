<?php
set_time_limit(0);

/**
 * Migrate.Organizations API (per 2500)
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_organizations($params) {
  $returnValues = array();
  $orgQuery = 'SELECT * FROM v6_contact WHERE is_process_migration = %1
    AND contact_type = %2 LIMIT 2500';
  $orgQueryParams = array(
    1 => array(0, 'Integer'),
    2 => array('Organization', 'String'));
  $daoOrg = CRM_Core_DAO::executeQuery($orgQuery, $orgQueryParams);
  while ($daoOrg->fetch()) {
    _createOrganization($daoOrg);
    $returnValues[] = 'Organization '.$daoOrg->display_name.' migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Organizations');
}

/**
 * Function to create each single organization from imported values
 *
 * @param object $daoOrg
 */
function _createOrganization($daoOrg) {
  $fieldsToBeMigrated = array('nick_name', 'contact_type', 'sort_name', 'display_name', 'addressee_id', 'addressee_display',
    'organization_name', 'preferred_language');
  $newParams = array();
  foreach ($fieldsToBeMigrated as $columnName) {
    $newParams[$columnName] = $daoOrg->$columnName;
  }
  $newOrg = civicrm_api3('Contact', 'Create', $newParams);
  CRM_Migrate_Utils::createMigrateKey($daoOrg->id, 'Organization', $newOrg['id']);
  $updateQuery = 'UPDATE v6_contact SET is_process_migration = %1 WHERE id = %2';
  $updateParams = array(
    1 => array(1, 'Integer'),
    2 => array($daoOrg->id, 'Integer'));
  CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
}
