<?php

require_once('../../../../../config.php');

// Check if logged-in user is admin
if(!is_siteadmin()) {
    redirect(new moodle_url('/login/index.php'));
    exit();
}

// Import trigger's lib
use lifecyclestep_deletebackup\local\deleteBackupLib;

// Create deleteBackup
$deleteBackup = new deleteBackupLib();

// Test class: deleteBackup
$deleteBackup->test();
