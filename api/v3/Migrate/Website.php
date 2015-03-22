<?php
set_time_limit(0);

/**
 * Migrate.Website API (only if contact_id is in migration_keys)
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_website($params) {
  $returnValues = array();
  $countCreated = 0;
  $websiteQuery = 'SELECT website.*, mk.entity, mk.original_id, mk.entity_id FROM v6_website website JOIN migrate_keys mk ON website.contact_id = mk.original_id
    AND mk.entity in(%2, %3) WHERE is_process_migration = %1';
  $websiteQueryParams = array(
    1 => array(0, 'Integer'),
    2 => array('Individual', 'String'),
    3 => array('Organization', 'String'));
  $daoWebsite = CRM_Core_DAO::executeQuery($websiteQuery, $websiteQueryParams);
  while ($daoWebsite->fetch()) {
    _createWebsite($daoWebsite, $countCreated);
  }
  $returnValues[] = $countCreated.' websites created';
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Websites');
}

/**
 * Function to create each single website from imported values if contact_id is in migrate_keys
 *
 * @param object $daoWebsite
 * @param int $countCreated
 * @return string $returnString
 */
function _createWebsite($daoWebsite, &$countCreated) {
  if (!empty($daoWebsite->contact_id)) {
    $newParams = array();
    $newParams['contact_id'] = $daoWebsite->entity_id;
    $newParams['url'] = $daoWebsite->url;
    $newParams['website_type_id'] = $daoWebsite->website_type_id;
    try {
      civicrm_api3('Website', 'Getsingle', $newParams);
    } catch (CiviCRM_API3_Exception $ex) {
      $newWebsite = civicrm_api3('Website', 'Create', $newParams);
      $countCreated++;
      CRM_Migrate_Utils::createMigrateKey($daoWebsite->id, 'Website', $newWebsite['id']);
      $updateQuery = 'UPDATE v6_website SET is_process_migration = %1 WHERE id = %2';
      $updateParams = array(
        1 => array(1, 'Integer'),
        2 => array($daoWebsite->id, 'Integer'));
      CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
    }
  }
}