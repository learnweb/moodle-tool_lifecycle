<?php
namespace tool_cleanupcourses;
require_once(dirname(__FILE__) . '/../../config.php');
$processor = new cleanup_processor();
$processor->call_trigger();