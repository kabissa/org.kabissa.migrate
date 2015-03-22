<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
    array (
      'name' => 'Cron:Migrate.CustomGroups',
      'entity' => 'Job',
      'params' =>
        array (
          'version' => 3,
          'name' => 'Migrate CustomGroups',
          'description' => 'Kabissa Migrate CustomGroups (one time migration job)',
          'run_frequency' => 'Daily',
          'api_entity' => 'Migrate',
          'api_action' => 'CustomGroups',
          'is_active' => 0
        ),
    ),
);