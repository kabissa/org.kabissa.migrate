<?php
set_time_limit(0);

/**
 * Migrate.OrgData API
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_orgdata($params) {
  $returnValues = array();
  $orgQuery = 'SELECT * FROM v6_value_kabissa WHERE is_processed = %1 LIMIT 2500';
  $orgQueryParams = array(1 => array(0, 'Integer'));
  $daoOrg = CRM_Core_DAO::executeQuery($orgQuery, $orgQueryParams);
  while ($daoOrg->fetch()) {
    _createOrganization($daoOrg);
    $returnValues[] = 'Organization data '.$daoOrg->entity_id.' migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'OrgData');
}

/**
 * Function to create each single organization data from imported values
 *
 * @param object $daoOrg
 */
function _createOrganization($daoOrg) {

  $migrateKeyParams = array(
    'entity' => 'Organization',
    'original_id' => $daoOrg->entity_id);
  $migrateKeys = CRM_Migrate_Utils::getMigrateKey($migrateKeyParams);
  if (isset($migrateKeys['entity_id'])) {
    $insertColumns = array();
    $insertParams = array();
    $insertColumns[1] = 'entity_id = %1';
    $insertParams[1] = array($migrateKeys['entity_id'], 'Integer');
    $counter = 2;

    if (isset($daoOrg->mission_statement) && !empty($daoOrg->mission_statement)) {
      $counter++;
      $insertColumns[$counter] = 'mission_statement = %' . $counter;
      $insertParams[$counter] = array($daoOrg->mission_statement, 'String');
    }

    if (isset($daoOrg->other_keyword) && !empty($daoOrg->other_keyword)) {
      $counter++;
      $insertColumns[$counter] = 'keywords = %' . $counter;
      $insertParams[$counter] = array($daoOrg->other_keyword, 'String');
    }

    if (isset($daoOrg->desc_activities) && !empty($daoOrg->desc_activities)) {
      $counter++;
      $insertColumns[$counter] = 'org_activities = %' . $counter;
      $insertParams[$counter] = array($daoOrg->desc_activities, 'String');
    }

    if (isset($daoOrg->beneficiaries_area) && !empty($daoOrg->beneficiaries_area)) {
      $counter++;
      $insertColumns[$counter] = 'beneficiaries_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->beneficiaries_area, 'String');
    }

    if (isset($daoOrg->status_id) && !empty($daoOrg->status_id)) {
      $counter++;
      $insertColumns[$counter] = 'kabissa_status_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->status_id, 'Integer');
    }

    if (isset($daoOrg->notice_id) && !empty($daoOrg->notice_id)) {
      $counter++;
      $insertColumns[$counter] = 'notice_level_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->notice_id, 'Integer');
    }

    if (isset($daoOrg->region_id) && !empty($daoOrg->region_id)) {
      $counter++;
      $insertColumns[$counter] = 'kabissa_region_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->region_id, 'String');
    }

    if (isset($daoOrg->scope_id) && !empty($daoOrg->scope_id)) {
      $counter++;
      $insertColumns[$counter] = 'geo_scope_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->scope_id, 'String');
    }

    if (isset($daoOrg->focus_id) && !empty($daoOrg->focus_id)) {
      $counter++;
      $insertColumns[$counter] = 'org_focus_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->focus_id, 'String');
    }

    if (isset($daoOrg->org_size_id) && !empty($daoOrg->org_size_id)) {
      $counter++;
      $insertColumns[$counter] = 'org_size_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->org_size_id, 'Integer');
    }

    if (isset($daoOrg->signup_date) && !empty($daoOrg->signup_date) && $daoOrg->signup_date != '0000-00-00') {
        $counter++;
        $insertColumns[$counter] = 'signup_date = %' . $counter;
        $insertParams[$counter] = array(date('Ymd', strtotime($daoOrg->signup_date)), 'Date');
    }

    if (isset($daoOrg->last_updated_date) && !empty($daoOrg->last_updated_date) && $daoOrg->last_updated_date != '0000-00-00') {
        $counter++;
        $insertColumns[$counter] = 'last_update_date = %' . $counter;
        $insertParams[$counter] = array(date('Ymd', strtotime($daoOrg->last_updated_date)), 'Date');
    }

    if (isset($daoOrg->project_id_27) && !empty($daoOrg->project_id_27)) {
      $counter++;
      $insertColumns[$counter] = 'global_giving_project_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->project_id_27, 'String');
    }

    if (isset($daoOrg->hide_profile_26) && !empty($daoOrg->hide_profile_26)) {
      $counter++;
      $insertColumns[$counter] = 'hide_profile = %' . $counter;
      $insertParams[$counter] = array($daoOrg->hide_profile_26, 'Integer');
    }

    if (isset($daoOrg->rss_field_28) && !empty($daoOrg->rss_field_28)) {
      $counter++;
      $insertColumns[$counter] = 'rss_field = %' . $counter;
      $insertParams[$counter] = array($daoOrg->rss_field_28, 'String');
    }

    if (isset($daoOrg->global_giving_organization_id_37) && !empty($daoOrg->global_giving_organization_id_37)) {
      $counter++;
      $insertColumns[$counter] = 'global_giving_org_id = %' . $counter;
      $insertParams[$counter] = array($daoOrg->global_giving_organization_id_37, 'String');
    }

    $insertQuery = 'REPLACE INTO civicrm_value_orgdata SET ' . implode(', ', $insertColumns);

    CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
    $updateQuery = 'UPDATE v6_value_kabissa SET is_processed = %1 WHERE id = %2';
    $updateParams = array(
      1 => array(1, 'Integer'),
      2 => array($daoOrg->id, 'Integer'));
    CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
  }
}
