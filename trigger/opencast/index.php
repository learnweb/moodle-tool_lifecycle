<?php

require_once('../../../../../config.php');

// Check if logged-in user is admin
if(!is_siteadmin()) {
    redirect(new moodle_url('/login/index.php'));
    exit();
}

// Import trigger's lib
use lifecycletrigger_opencast\local\courseFilterOpencast;

// Create courseFilter
$courseFilter = new courseFilterOpencast();

// Test class: courseFilter
$courseFilter->test();
