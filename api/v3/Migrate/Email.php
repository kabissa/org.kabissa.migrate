<?php
set_time_limit(0);

/**
 * Migrate.Email API (only if contact_id is in migration_keys)
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_email($params) {
  $returnValues = array();
  $countCreated = 0;
  $fieldsToBeMigrated = array('location_type_id', 'is_primary', 'email', 'on_hold', 'is_bulkmail');
  $emailQuery = 'SELECT email.*, mk.entity, mk.original_id, mk.entity_id FROM v6_email email
JOIN migrate_keys mk ON email.contact_id = mk.original_id
    AND mk.entity in(%2, %3) WHERE is_process_migration = %1';
  $emailQueryParams = array(
    1 => array(0, 'Integer'),
    2 => array('Individual', 'String'),
    3 => array('Organization', 'String'));
  $daoEmail = CRM_Core_DAO::executeQuery($emailQuery, $emailQueryParams);
  while ($daoEmail->fetch()) {
    _createEmail($daoEmail, $fieldsToBeMigrated, $countCreated);
  }
  $returnValues[] = $countCreated.' emails created';
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Email');
}

/**
 * Function to create each single email from imported values if contact_id is in migrate_keys
 *
 * @param object $daoEmail
 * @param array $fieldsToBeMigrated
 * @param int $countCreated
 * @return string $returnString
 */
function _createEmail($daoEmail, $fieldsToBeMigrated, &$countCreated) {
  if (!empty($daoEmail->contact_id) && !empty($daoEmail->location_type_id)) {
    $newParams = array();
    foreach ($fieldsToBeMigrated as $columnName) {
      $newParams[$columnName] = $daoEmail->$columnName;
    }
    $newParams['contact_id'] = $daoEmail->entity_id;
    $newParams['hold_date'] = date('Ymd', strtotime($daoEmail->hold_date));
    $newParams['reset_date'] = date('Ymd', strtotime($daoEmail->reset_date));
    try {
      civicrm_api3('Email', 'Getsingle', $newParams);
    } catch (CiviCRM_API3_Exception $ex) {
      $newEmail = civicrm_api3('Email', 'Create', $newParams);
      $countCreated++;
      CRM_Migrate_Utils::createMigrateKey($daoEmail->id, 'Email', $newEmail['id']);
      $updateQuery = 'UPDATE v6_email SET is_process_migration = %1 WHERE id = %2';
      $updateParams = array(
        1 => array(1, 'Integer'),
        2 => array($daoEmail->id, 'Integer'));
      CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
    }
  }
}