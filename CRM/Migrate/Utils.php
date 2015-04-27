<?php
/**
 * Created by PhpStorm.
 * User: Erik Hommel <hommel@ee-atwork.nl>
 * Date: 13-3-15
 * Time: 8:12
 *
 * Class with general static util methods
 */

class CRM_Migrate_Utils {
  /**
   * Method to create a migrate key holding the old entity key and the new one
   * @param $originalId
   * @param $entity
   * @param $entityId
   * @throws Exception when one of the params is empty
   */
  public static function createMigrateKey($originalId, $entity, $entityId) {
    if (empty($originalId) || empty($entity) || empty($entityId)) {
      throw new Exception ('originalId, entity and entityId are mandatory when setting a migrate key');
    }
    $insert = 'INSERT INTO migrate_keys (original_id, entity, entity_id, created_date) VALUES(%1, %2, %3, %4)';
    $insertParams = array(
      1 => array($originalId, 'Integer'),
      2 => array($entity, 'String'),
      3 => array($entityId, 'Integer'),
      4 => array(date('Ymd'), 'Date'));
    CRM_Core_DAO::executeQuery($insert, $insertParams);
  }

  /**
   * Method to get the migrate keys with old or new key
   *
   * @param array $params
   * @return array $result
   * @throws Exception when params invalid
   * @access public
   */
  public static function getMigrateKey($params) {
    $selectParams = array();
    if (!isset($params['entity'])) {
      throw new Exception('Entity is mandatory');
    }
    if (!isset($params['entity_id']) && !isset($params['original_id'])) {
      throw new Exception('Either entity_id or original_id has to be set');
    }
    if (empty($params['entity_id']) && empty($params['original_id'])) {
      throw new Exception('Either entity_id or original_id has to be set');
    }
    $whereClauses[] = 'entity = %1';
    $selectParams[1] = array($params['entity'], 'String');
    if (isset($params['entity_id'])) {
      $whereClauses[] = 'entity_id = %2';
      $selectParams[2] = array($params['entity_id'], 'String');
    }
    if (isset($params['original_id'])) {
      $whereClauses[] = 'original_id = %3';
      $selectParams[3] = array($params['original_id'], 'Integer');
    }
    $result = array();
    $query = 'SELECT * FROM migrate_keys WHERE '.implode(' AND ', $whereClauses);
    $dao = CRM_Core_DAO::executeQuery($query, $selectParams);
    if ($dao->fetch()) {
      $result['id'] = $dao->id;
      $result['entity'] = $dao->entity;
      $result['original_id'] = $dao->original_id;
      $result['entity_id'] = $dao->entity_id;
    }
    return $result;
  }

  /**
   * Method to check if old contact_id has already been migrated
   *
   * @param int $oldId
   * @return bool
   * @access public
   * @static
   */
  public static function oldIdInMigrateKeys($oldId) {
    $query = 'SELECT COUNT(*) As keyCount FROM migrate_keys WHERE original_id = %1';
    $params = array(1 => array($oldId, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      if ($dao->keyCount > 0) {
        return TRUE;
      }
    }
    return FALSE;
  }
}