<?php

/**
 * Collection of upgrade steps
 */
class CRM_Migrate_Upgrader extends CRM_Migrate_Upgrader_Base {
  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    $this->executeSqlFile('sql/createV6Tags.sql');
    $this->executeSqlFile('sql/createV6Groups.sql');
  }
}
