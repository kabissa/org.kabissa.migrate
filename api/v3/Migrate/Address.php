<?php
set_time_limit(0);

/**
 * Migrate.Address API (only if contact_id is in migration_keys) - per 5000
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_address($params) {
  $returnValues = array();
  $countCreate = 0;
  $fieldsToBeMigrated = array('location_type_id', 'is_primary', 'street_address', 'supplemental_address_1',
    'supplemental_address_2', 'city', 'county_id', 'state_province_id', 'postal_code_suffix', 'postal_code',
    'country_id');
  $addressQuery = 'SELECT address.*, mk.entity, mk.original_id, mk.entity_id FROM v6_address address JOIN migrate_keys mk ON address.contact_id = mk.original_id
    AND mk.entity in(%2, %3) WHERE is_process_migration = %1 LIMIT 5000';
  $addressQueryParams = array(
    1 => array(0, 'Integer'),
    2 => array('Individual', 'String'),
    3 => array('Organization', 'String'));
  $daoAddress = CRM_Core_DAO::executeQuery($addressQuery, $addressQueryParams);
  while ($daoAddress->fetch()) {
    _createAddress($daoAddress, $fieldsToBeMigrated, $countCreate);
  }
  $returnValues[] = $countCreate.' addresses created';
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Address');
}

/**
 * Function to create each single address from imported values if contact_id is in migrate_keys
 *
 * @param object $daoAddress
 * @param array $fieldsToBeMigrated
 * @param int $countCreate
 * @return string $returnString
 */
function _createAddress($daoAddress, $fieldsToBeMigrated, &$countCreate) {
  if (!empty($daoAddress->contact_id) && !empty($daoAddress->location_type_id)) {
    $newParams = array();
    foreach ($fieldsToBeMigrated as $columnName) {
      $newParams[$columnName] = $daoAddress->$columnName;
    }
    $newParams['contact_id'] = $daoAddress->entity_id;
    $newParams['latitude'] = $daoAddress->geo_code_1;
    $newParams['longitude'] = $daoAddress->geo_code_2;
    try {
      civicrm_api3('Address', 'Getsingle', $newParams);
    } catch (CiviCRM_API3_Exception $ex) {
      if (CRM_Migrate_Utils::checkContactExists($newParams['contact_id']) == TRUE) {
        try {
          $newAddress = civicrm_api3('Address', 'Create', $newParams);
          $countCreate++;
          CRM_Migrate_Utils::createMigrateKey($daoAddress->id, 'Address', $newAddress['id']);
          $updateQuery = 'UPDATE v6_address SET is_process_migration = %1 WHERE id = %2';
          $updateParams = array(
            1 => array(1, 'Integer'),
            2 => array($daoAddress->id, 'Integer'));
          CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
        } catch (CiviCRM_API3_Exception $ex) {

        }
      }
    }
  }
}
