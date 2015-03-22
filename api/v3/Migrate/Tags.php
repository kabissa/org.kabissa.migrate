<?php
set_time_limit(0);

/**
 * Migrate.Tags API
 *
 * @author Erik Hommel (hommel@ee-atwork.nl)
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_migrate_tags($params) {
  $returnValues = array();
  /*
   * array for old - new tag_ids for parents
   */
  $tagParents = array();
  /*
   * first all parent tags to make sure children are migrated correctly too
   */
  $daoTag = CRM_Core_DAO::executeQuery('SELECT * FROM v6_tag WHERE parent_id IS NULL');
  while ($daoTag->fetch()) {
    _createTag($daoTag, $tagParents);
    $returnValues[] = 'Tag '.$daoTag->name.' created';
  }
  /*
   * then all child tags
   */
  $daoTag = CRM_Core_DAO::executeQuery('SELECT * FROM v6_tag WHERE parent_id IS NOT NULL');
  while ($daoTag->fetch()) {
    _createTag($daoTag, $tagParents);
    $returnValues[] = 'Tag '.$daoTag->name.' created';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Migrate', 'Tagsgroups');
}

/**
 * Function to create each single tag from imported values
 *
 * @param object $daoTag
 * @param array $tagParents
 * @throws CiviCRM_API3_Exception when API error
 */
function _createTag($daoTag, &$tagParents) {
  $params = array();
  $tagFields = CRM_Core_DAO_Tag::fieldKeys();
  foreach ($tagFields as $fieldName) {
    if (isset($daoTag->$fieldName) && $fieldName != 'id') {
      $params[$fieldName] = $daoTag->$fieldName;
    }
  }
  if (isset($params['parent_id']) && !empty($params['parent_id'])) {
    $params['parent_id'] = $tagParents[$params['parent_id']];
  }
  $newTag = civicrm_api3('Tag', 'Create', $params);
  CRM_Migrate_Utils::addMigrateKey($daoTag->id, 'Tag', $newTag['id']);
  $tagParents[$daoTag->id] = $newTag['id'];
}
