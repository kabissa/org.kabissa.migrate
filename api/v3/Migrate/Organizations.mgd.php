<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
    array (
      'name' => 'Cron:Migrate.Organizations',
      'entity' => 'Job',
      'params' =>
        array (
          'version' => 3,
          'name' => 'Migrate Organizations',
          'description' => 'Kabissa Migrate Organization (one time migration job)',
          'run_frequency' => 'Daily',
          'api_entity' => 'Migrate',
          'api_action' => 'Organizations',
          'is_active' => 0
        ),
    ),
);