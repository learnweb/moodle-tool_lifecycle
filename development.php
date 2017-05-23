<?php

namespace tool_cleanupcourses;
use tool_cleanupcourses\manager\lib_manager;
use tool_cleanupcourses\manager\step_manager;
require_once(dirname(__FILE__) . '/../../../config.php');
$processor = new cleanup_processor();
$processor->call_trigger();

$steps = step_manager::get_step_types();
/* @var \tool_cleanupcourses\step\base[] $steplibs stores the lib classes of all step subplugins.*/
$steplibs = array();
foreach ($steps as $id => $step) {
    $steplibs[$id] = lib_manager::get_step_lib($id);
    $steplibs[$id]->pre_processing_bulk_operation();
}
$processor->process_courses();
foreach ($steps as $id => $step) {
    $steplibs[$id]->post_processing_bulk_operation();
}

// trigger_manager::register('startdatedelay');
// trigger_manager::register('sitecourse');
// trigger_manager::register('dummy');