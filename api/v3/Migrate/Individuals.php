<?php
set_time_limit(0);

/**
 * Migrate.Individuals API
 *
 * First step: get all organizations, retrieve their original key and check if there are any relations
 * for them in v6_relationship (old ones). If so, create Individual for those contacts
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_individuals($params) {
  $returnValues = array();
  $orgQuery = 'SELECT id, display_name FROM civicrm_contact WHERE contact_type = %1';
  $orgParams = array(1 => array('Organization', 'String'));
  $daoOrg = CRM_Core_DAO::executeQuery($orgQuery, $orgParams);
  while ($daoOrg->fetch()) {
    $sourceOrgKey = CRM_Migrate_Utils::getMigrateKey(array(
      'entity' => 'Organization',
      'entity_id' => $daoOrg->id));
    if (!empty($sourceOrgKey)) {
      $relationQuery = 'SELECT is_active, contact_id_a FROM v6_relationship WHERE contact_id_b = %1 AND v6_relationship.relationship_type_id = %2';
      $relationParams = array(
        1 => array($sourceOrgKey['original_id'], 'Integer'),
        2 => array(4, 'Integer')
      );
      $daoRelation = CRM_Core_DAO::executeQuery($relationQuery, $relationParams);
      while ($daoRelation->fetch()) {
        _createIndividual($daoRelation, $daoOrg);
      }
    }
  }
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Individuals');
}

/**
 * Function to create each single individual from imported values
 *
 * @param object $daoRelation
 * @param object $daoOrg
 */
function _createIndividual($daoRelation, $daoOrg) {
  $fieldsToBeMigrated = array('nick_name', 'contact_type', 'first_name', 'middle_name', 'last_name', 'prefix_id',
    'email_greeting_id', 'email_greeting_display', 'postal_greeting_id', 'postal_greeting_display', 'addressee_id', 'addressee_display',
    'job_title', 'gender_id', 'birth_date', 'preferred_language', 'sort_name', 'display_name');
  $indQuery = 'SELECT * FROM v6_contact WHERE id = %1 AND v6_contact.is_process_migration = %2';
  $indParams = array(
    1 => array($daoRelation->contact_id_a, 'Integer'),
    2 => array(0, 'Integer'));
  $daoInd = CRM_Core_DAO::executeQuery($indQuery, $indParams);
  if ($daoInd->fetch()) {
    $newParams = array();
    foreach ($fieldsToBeMigrated as $columnName) {
      $newParams[$columnName] = $daoInd->$columnName;
    }
    $newParams['employer_id'] =$daoOrg->id;
    $newParams['organization_name'] = $daoOrg->display_name;

    /*
     * fix for api validation on addressee_id 3, correct afterwards in the database?
     */
    if ($newParams['addressee_id'] == 3) {
      $newParams['addressee_id'] = 1;
    }

    try {
      $newInd = civicrm_api3('Contact', 'Create', $newParams);
      CRM_Migrate_Utils::createMigrateKey($daoInd->id, 'Individual', $newInd['id']);
      $updateQuery = 'UPDATE v6_contact SET is_process_migration = %1 WHERE id = %2';
      $updateParams = array(
        1 => array(1, 'Integer'),
        2 => array($daoInd->id, 'Integer'));
      CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
    } catch (CiviCRM_API3_Exception $ex) {
    }
  }
}
