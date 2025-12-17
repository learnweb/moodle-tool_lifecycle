<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'lifecyclestep_uclcontextfreeze';
$plugin->version   = 2025121700;
$plugin->requires  = 2022112800; // Requires Moodle 4.1+.
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '0.1';

// Requires UCL's lifecycle bloxk (so the manager class exists)
$plugin->dependencies = [
    'block_lifecycle' => ANY_VERSION,
];

