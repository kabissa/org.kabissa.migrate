<?php
set_time_limit(0);

/**
 * Migrate.GroupMembers API (individuals that are members of a special group)
 *
 * Get all group contacts of selected groups, determine if they already exist in new situation and create if not, then add
 * to new group
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_groupmembers($params) {
  $returnValues = array();

  $migrateGroups = array(
    3 => 2,
    9 => 4,
    22 => 8,
    48 => 21,
    51 => 23,
    119 => 29,
    123 => 30
  );

  $selectOldGroups = array();
  foreach ($migrateGroups as $oldGroupId => $newGroupId) {
    $selectOldGroups[] = $oldGroupId;
  }

  $groupContactQuery = 'SELECT gc.*, cont.contact_type
    FROM v6_group_contact gc INNER JOIN v6_contact cont ON gc.contact_id = cont.id
    WHERE status = "Added" AND group_id IN('.implode(', ', $selectOldGroups).') LIMIT 2500';

  $daoGroupContact = CRM_Core_DAO::executeQuery($groupContactQuery);
  while ($daoGroupContact->fetch()) {
    _processGroupContact($daoGroupContact, $migrateGroups[$daoGroupContact->group_id]);
  }
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'GroupMembers');
}
/**
 * Function to process group contact record (check if ind or org
 * needs to be created and add to group
 *
 * @param object $daoGroupContact
 * @param int $newGroupId
 */
function _processGroupContact($daoGroupContact, $newGroupId) {

  $sourceKey = CRM_Migrate_Utils::getMigrateKey(array(
    'entity' => $daoGroupContact->contact_type,
    'original_id' => $daoGroupContact->contact_id));
  /*
   * contact does not exist yet
   */
  if (empty($sourceKey)) {
    switch ($daoGroupContact->contact_type) {
      case 'Individual':
        $newContactId = _createIndividual($daoGroupContact->contact_id);
        break;
      case 'Organization':
        $newContactId = _createOrganization($daoGroupContact->contact_id);
        break;
    }
  } else {
    $newContactId = $sourceKey['entity_id'];
  }
  if (!empty($newContactId)) {
    $groupContactParams = array(
      'contact_id' => $newContactId,
      'group_id' => $newGroupId
    );
    try {
      civicrm_api3('GroupContact', 'Create', $groupContactParams);
      $updateGroupContact = 'UPDATE v6_group_contact SET is_process_migration = 1 WHERE id = %1';
      $updateParams = array(1 => array($daoGroupContact->id, 'Integer'));
      CRM_Core_DAO::executeQuery($updateGroupContact, $updateParams);
    } catch (CiviCRM_API3_Exception $ex) {}
  }
}

/**
 * Function to create each single individual from imported values
 *
 * @param int $sourceContactId
 * @return int $createdContactId
 */
function _createIndividual($sourceContactId) {
  $createdContactId = 0;
  $fieldsToBeMigrated = array('nick_name', 'contact_type', 'first_name', 'middle_name', 'last_name', 'prefix_id',
    'email_greeting_id', 'email_greeting_display', 'postal_greeting_id', 'postal_greeting_display', 'addressee_id', 'addressee_display',
    'job_title', 'gender_id', 'birth_date', 'preferred_language', 'sort_name', 'display_name');
  $indQuery = 'SELECT * FROM v6_contact WHERE id = %1 AND v6_contact.is_process_migration = %2 AND is_deleted = %2';
  $indParams = array(
    1 => array($sourceContactId, 'Integer'),
    2 => array(0, 'Integer'));
  $daoInd = CRM_Core_DAO::executeQuery($indQuery, $indParams);
  if ($daoInd->fetch()) {
    $newParams = array();
    foreach ($fieldsToBeMigrated as $columnName) {
      $newParams[$columnName] = $daoInd->$columnName;
    }
    /*
     * fix for api validation on addressee_id 3, correct afterwards in the database?
     */
    if ($newParams['addressee_id'] == 3) {
      $newParams['addressee_id'] = 1;
    }

    /*
     * prevent weird contacts coming in
     */
    if (!is_numeric($newParams['first_name']) && !is_numeric($newParams['last_name'])) {

      try {
        $newInd = civicrm_api3('Contact', 'Create', $newParams);
        CRM_Migrate_Utils::createMigrateKey($daoInd->id, 'Individual', $newInd['id']);
        $updateQuery = 'UPDATE v6_contact SET is_process_migration = %1 WHERE id = %2';
        $updateParams = array(
          1 => array(1, 'Integer'),
          2 => array($daoInd->id, 'Integer'));
        CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
        $createdContactId = $newInd['id'];
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
  }
  return $createdContactId;
}

/**
 * Function to create organization
 *
 * @param int $sourceContactId
 * @return int $createdContactId
 */
function _createOrganization($sourceContactId) {
  $createdContactId = 0;
  $fieldsToBeMigrated = array('nick_name', 'contact_type', 'sort_name', 'display_name', 'addressee_id', 'addressee_display',
    'organization_name', 'preferred_language');
  $orgQuery = 'SELECT * FROM v6_contact WHERE id = %1 AND v6_contact.is_process_migration = %2 AND is_deleted=%2';
  $orgParams = array(
    1 => array($sourceContactId, 'Integer'),
    2 => array(0, 'Integer'));
  $daoOrg = CRM_Core_DAO::executeQuery($orgQuery, $orgParams);
  if ($daoOrg->fetch()) {
    $newParams = array();
    foreach ($fieldsToBeMigrated as $columnName) {
      $newParams[$columnName] = $daoOrg->$columnName;
    }
    try {
      $newOrg = civicrm_api3('Contact', 'Create', $newParams);
      CRM_Migrate_Utils::createMigrateKey($daoOrg->id, 'Organization', $newOrg['id']);
      $updateQuery = 'UPDATE v6_contact SET is_process_migration = %1 WHERE id = %2';
      $updateParams = array(
        1 => array(1, 'Integer'),
        2 => array($daoOrg->id, 'Integer'));
      CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
      $createdContactId = $newOrg['id'];
    } catch (CiviCRM_API3_Exception $ex) {
    }
  }
  return $createdContactId;
}

