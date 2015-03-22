<?php
set_time_limit(0);

/**
 * Migrate.Phone API (only if contact_id is in migration_keys)
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_phone($params) {
  $returnValues = array();
  $countCreated = 0;
  $fieldsToBeMigrated = array('location_type_id', 'is_primary', 'phone', 'phone_ext', 'phone_type_id');
  $phoneQuery = 'SELECT phone.*, mk.entity, mk.original_id, mk.entity_id FROM v6_phone phone JOIN migrate_keys mk ON phone.contact_id = mk.original_id
    AND mk.entity in(%2, %3) WHERE is_process_migration = %1';
  $phoneQueryParams = array(
    1 => array(0, 'Integer'),
    2 => array('Individual', 'String'),
    3 => array('Organization', 'String'));
  $daoPhone = CRM_Core_DAO::executeQuery($phoneQuery, $phoneQueryParams);
  while ($daoPhone->fetch()) {
    _createPhone($daoPhone, $fieldsToBeMigrated, $countCreated);
  }
  $returnValues[] = $countCreated.' phones created';
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Phone');
}

/**
 * Function to create each single phone from imported values if contact_id is in migrate_keys
 *
 * @param object $daoPhone
 * @param array $fieldsToBeMigrated
 * @param int $countCreated
 * @return string $returnString
 */
function _createPhone($daoPhone, $fieldsToBeMigrated, &$countCreated) {
  if (!empty($daoPhone->contact_id) && !empty($daoPhone->location_type_id)) {
    $newParams = array();
    foreach ($fieldsToBeMigrated as $columnName) {
      $newParams[$columnName] = $daoPhone->$columnName;
    }
    $newParams['contact_id'] = $daoPhone->entity_id;
    try {
      civicrm_api3('Phone', 'Getsingle', $newParams);
    } catch (CiviCRM_API3_Exception $ex) {
      $newPhone = civicrm_api3('Phone', 'Create', $newParams);
      $countCreated++;
      CRM_Migrate_Utils::createMigrateKey($daoPhone->id, 'Phone', $newPhone['id']);
      $updateQuery = 'UPDATE v6_phone SET is_process_migration = %1 WHERE id = %2';
      $updateParams = array(
        1 => array(1, 'Integer'),
        2 => array($daoPhone->id, 'Integer'));
      CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
    }
  }
}