<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
    array (
      'name' => 'Cron:Migrate.Website',
      'entity' => 'Job',
      'params' =>
        array (
          'version' => 3,
          'name' => 'Migrate Websites',
          'description' => 'Kabissa Migrate Websites',
          'run_frequency' => 'Daily',
          'api_entity' => 'Migrate',
          'api_action' => 'Website',
          'is_active' => 0
        ),
    ),
);