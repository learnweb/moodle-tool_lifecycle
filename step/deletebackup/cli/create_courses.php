<?php

define('CLI_SCRIPT', true);
define('NO_OUTPUT_BUFFERING', true);

require(__DIR__.'/../../../../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Get the necessary files to perform backup and restore
require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot.'/backup/util/includes/restore_includes.php');

global $CFG, $DB;

// Set options for generated test course
$fullname = "Testkurs for Step deletebackup";
$shortname = "db";
$summary = "";
$sizename = "S";
$fixeddataset = "";
$filesizelimit = "";
$quiet = true;

// Amount of courses
$courses = 22;
// Set category id for generated test course
$categoryid = 17;


// Generate courses
$courseids = [];
for($i=1; $i < ($courses + 1); $i++) {
    $courseids[] = create_test_course($fullname." ".$i, $shortname.$i, $summary, $sizename, $fixeddataset, $filesizelimit, $quiet);
}
// Move courses to category
$moved = move_test_courses($categoryid, $courseids);
/*
if($moved) {
    // Create course backups
    $backupfiles = [];
    foreach($courseids as $courseid) {
        $backupfiles[] = create_course_backup($courseid);
    }
    // Check if all values (= files) in array aren't null
    if(empty(array_filter($backupfiles, function ($file) { return $file == null; }))) {
        // Delete backuped courses
        foreach($courseids as $courseid) {
            $deletes[] = delete_test_course($courseid);
        }
    }
}
*/


// Create a test course
function create_test_course($fullname, $shortname, $summary, $sizename, $fixeddataset, $filesizelimit, $quiet) {
    mtrace("");
    mtrace("Create test course: ".$fullname);

    // Check size
    try {
        $size = tool_generator_course_backend::size_for_name($sizename);
    } catch (coding_exception $e) {
        cli_error("Invalid size ($sizename).");
    }
    // Check shortname
    if ($error = tool_generator_course_backend::check_shortname_available($shortname)) {
        cli_error($error);
    }
    // Switch to admin user account
    \core\session\manager::set_user(get_admin());
    $additionalmodulesarray = [];
    if (!empty($options['additionalmodules'])) {
        $additionalmodulesarray = explode(',', trim($options['additionalmodules']));
    }

    // Generate course
    //exec('php admin/tool/generator/cli/maketestcourse.php --fullname="Testkurs for Life Cycle X" --shortname=lcx --size=M"');
    $backend = new tool_generator_course_backend(
        $shortname,
        $size,
        $fixeddataset,
        $filesizelimit,
        $quiet,
        $fullname,
        $summary,
        FORMAT_HTML,
        $additionalmodulesarray
    );
    $id = $backend->make();

    return $id;
}

// Move courses to category (by id)
function move_test_courses($categoryid, $courseids = []) {
    // Move course to category
    mtrace("");
    mtrace("Move courses: ".implode(",", $courseids)." to category: ".$categoryid);
    $success = move_courses($courseids, $categoryid);
    if($success) { mtrace("Moved courses: ".implode(",", $courseids)." to category: ".$categoryid); }
    else { mtrace("ERROR: Move courses: ".implode(",", $courseids)." to category: ".$categoryid." failed!"); }

    return $success;
}

// Create a course backup
function create_course_backup($courseid) {
    mtrace("");
    mtrace("Backup of course: ".$courseid);
    // Get course
    //$course = get_course($courseid);
    // Set backup filename
    $archivefile = date("Y-m-d")."-COURSE-{$courseid}.mbz";

    // Path of backup folder
    $path = get_config('tool_lifecycle', 'backup_path');
    // If path doesn't exist, make it
    if (!is_dir($path)) {
        umask(0000);
        // Create the directory for course backups
        if (!mkdir($path, $CFG->directorypermissions, true)) {
            throw new \moodle_exception(get_string('errorbackuppath', 'tool_lifecycle'));
        }
    }

    // Create backup controller for course
    $bc = new \backup_controller(\backup::TYPE_1COURSE, $courseid, \backup::FORMAT_MOODLE, \backup::INTERACTIVE_NO, \backup::MODE_AUTOMATED, get_admin()->id);
    // Execute backup
    $bc->execute_plan();
    // Get backup results
    $results = $bc->get_results();
    /* @var $file \stored_file instance of the backup file*/
    $file = $results['backup_destination'];
    if (!empty($file)) {
        // Copy backup file to backup folder
        $file->copy_content_to($path . DIRECTORY_SEPARATOR . $archivefile);
        // Delete backup file
        $file->delete();
        mtrace("Backup destination of course ".$courseid.": ".$path.DIRECTORY_SEPARATOR.$archivefile);
    } else { mtrace("ERROR: Backup of course ".$courseid." failed!"); }
    // Destroy backup controller
    $bc->destroy();
    unset($bc);

    return $file;
}

// Delete a course
function delete_test_course($courseid) {
    mtrace("");
    mtrace("Delete course: ".$courseid);
    $result = delete_course($courseid, true);
    if($result) { mtrace("Deleted course: ".$courseid); }
    else { mtrace("ERROR: Delete of course: ".$courseid." failed"); }

    return $result;
}
