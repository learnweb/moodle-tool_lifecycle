<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Helper class to perform all required opencast related processes in Opencast Step for deletion
 *
 * @package    lifecyclestep_opencast
 * @copyright  2023 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_opencast;

use tool_lifecycle\local\response\step_response;
use lifecyclestep_opencast\notification_helper;
use lifecyclestep_opencast\log_helper;

/**
 * Helper class to perform all required opencast related processes in Opencast Step for deletion
 *
 * @package    lifecyclestep_opencast
 * @copyright  2023 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opencaststep_process_delete {
    /**
     * Process series and video for delete workflows.
     *
     * @param object $course
     * @param int $ocinstanceid
     * @param string $ocworkflow
     * @param int $instanceid
     * @param bool $ocremoveseriesmappingenabled
     * @param bool $octraceenabled
     * @param bool $ocnotifyadminenabled
     * @param bool $ratelimiterenabled
     *
     * @return string the process response, empty if no waiting is required.
     */
    public static function process(
        $course,
        $ocinstanceid,
        $ocworkflow,
        $instanceid,
        $ocremoveseriesmappingenabled,
        $octraceenabled,
        $ocnotifyadminenabled,
        $ratelimiterenabled
    ) {
        // Prepare series videos cache.
        $seriesvideoscache = \cache::make('lifecyclestep_opencast', 'seriesvideos');

        // Prepare processed videos caching for the step instance.
        $processedvideoscache = \cache::make('lifecyclestep_opencast', 'processedvideos');
        $stepprocessedvideos = [];
        if ($processedvideoscache->has($instanceid)) {
            $processedvideoscacheresult = $processedvideoscache->get($instanceid);
            $stepprocessedvideos = $processedvideoscacheresult->stepprocessedvideos;
        }

        // Get an APIbridge instance for this OCinstance.
        $apibridge = \block_opencast\local\apibridge::get_instance($ocinstanceid);

        // Get the course's series.
        $courseseries = $apibridge->get_course_series($course->id);

        $logtrace = new log_helper($octraceenabled);

        $notificationhelper = new notification_helper($ocnotifyadminenabled);

        // Loop through the series of the course.
        foreach ($courseseries as $series) {
            // Trace.
            $alangobj = (object) [
                'series' => $series->series,
            ];
            $logtrace->print_mtrace(
                get_string('mtrace_start_process_series', 'lifecyclestep_opencast', $alangobj),
                '...',
                4
            );

            // Get the videos within the series.
            $seriesvideos = new \stdClass();
            // Prepare cachable object.
            $seriesvideoscacheobj = new \stdClass();
            $seriesvideoscacheobj->expiry = strtotime('tomorrow midnight');
            if ($cacheresult = $seriesvideoscache->get($series->series)) {
                if ($cacheresult->expiry > time()) {
                    $seriesvideos = $cacheresult->seriesvideos;
                }
            }
            // If it is the first check, we get all videos, otherwise we use caching system to increase performance.
            if (!property_exists($seriesvideos, 'videos')) {
                $seriesvideos = $apibridge->get_series_videos($series->series);
                $seriesvideoscacheobj->seriesvideos = $seriesvideos;
                $seriesvideoscache->set($series->series, $seriesvideoscacheobj);
            }

            // If there was an error retrieving the series videos, skip this series.
            if ($seriesvideos->error) {
                // Trace.
                $logtrace->print_mtrace(
                    get_string('mtrace_error_get_series_videos', 'lifecyclestep_opencast'),
                    '...',
                    4
                );

                // Removing the cache.
                $seriesvideoscache->delete($series->series);
                continue;
            }

            // A flag to decide whether to remove the series mappings of not.
            $removeseriesmapping = false;
            // Up until now, we have all required information about the actual series and its videos.
            // Now we apply the concept of ACL change and Duplicated series.

            // For that we need seriesmappings.
            $seriesmappings = \tool_opencast\seriesmapping::get_records(
                ['series' => $series->series, 'ocinstanceid' => $ocinstanceid]
            );

            // This happens when a series is shared among multiple courses via ACL change.
            if (count($seriesmappings) > 1) {
                // In this case, we only take out the acls from the series and its videos.
                $seriesunlinked = $apibridge->unlink_series_from_course($course->id, $series->series);
                if (!$seriesunlinked) {
                    // Trace.
                    $logtrace->print_mtrace(
                        get_string('mtrace_error_cannot_remove_acl', 'lifecyclestep_opencast'),
                        '...',
                        4
                    );

                    // Notify admin.
                    $notificationhelper->notify_error(
                        $course,
                        $ocinstanceid,
                        $ocworkflow,
                        get_string('error_removeseriestacl', 'lifecyclestep_opencast')
                    );

                    return step_response::WAITING;
                }

                // Trace.
                $logtrace->print_mtrace(
                    get_string('mtrace_success_series_course_unlinked', 'lifecyclestep_opencast'),
                    '...',
                    5
                );

                // Set the flag to remove the seriesmapping record as well.
                $removeseriesmapping = true;
            } else {
                // If we hit here, it means that the series is linked only to a course and its safe to remove the series completely.
                // This would cover both cases of ACL changes and Duplication, because if it was ACL change the previous step would
                // make sure that all other eligible courses are removed.
                // In case of Duplication, we are good to go as it is one to one relationship.

                // Iterate over the videos.
                foreach ($seriesvideos->videos as $video) {
                    // Skip the video if already being processed.
                    if (
                        isset($stepprocessedvideos[$course->id]) &&
                        isset($stepprocessedvideos[$course->id][$series->series]) &&
                        in_array($video->identifier, $stepprocessedvideos[$course->id][$series->series])
                    ) {
                        continue;
                    }

                    // Trace.
                    $alangobj = (object) [
                        'identifier' => $video->identifier,
                    ];
                    $logtrace->print_mtrace(
                        get_string('mtrace_start_process_video', 'lifecyclestep_opencast', $alangobj),
                        '...',
                        5
                    );

                    // If the video is currently processing anything, skip this video.
                    if (in_array($video->processing_state, ['CAPTURING', 'RUNNING'])) {
                        // Trace.
                        $logtrace->print_mtrace(
                            get_string('mtrace_notice_video_is_processing', 'lifecyclestep_opencast'),
                            '...',
                            5
                        );

                        continue;
                    }

                    // Start the configured workflow for this video.
                    $workflowresult = self::perform_delete_event($ocinstanceid, $video->identifier, $ocworkflow);

                    // If the workflow wasn't started successfully, skip this video.
                    if ($workflowresult == false) {
                        // Trace.
                        $logtrace->print_mtrace(
                            get_string('mtrace_error_workflow_cannot_start', 'lifecyclestep_opencast'),
                            '...',
                            5
                        );

                        // Notify admin.
                        $notificationhelper->notify_failed_workflow(
                            $course,
                            $ocinstanceid,
                            $video,
                            $ocworkflow
                        );

                        return step_response::WAITING;

                        // Otherwise.
                    } else {
                        // Trace.
                        $logtrace->print_mtrace(
                            get_string('mtrace_success_delete_workflow_started', 'lifecyclestep_opencast'),
                            '...',
                            5
                        );

                        // Keep track of processed videos to avoid redundancy in the next iterationa.
                        $stepprocessedvideos[$course->id][$series->series][] = $video->identifier;
                        $processedvideoscacheobj = new \stdClass();
                        $processedvideoscacheobj->stepprocessedvideos = $stepprocessedvideos;
                        $processedvideoscache->set($instanceid, $processedvideoscacheobj);

                        // If the rate limiter is enabled.
                        if ($ratelimiterenabled == true) {
                            // Trace.
                            $logtrace->print_mtrace(
                                get_string('mtrace_notice_rate_limiter', 'lifecyclestep_opencast'),
                                '...',
                                5
                            );

                            // Return waiting so that the processing will continue on the next run of this scheduled task.
                            return step_response::WAITING;
                        }
                    }
                }

                // Check if all videos are processed, then we set the flag to remove the seriesmapping as well.
                if (
                    (isset($stepprocessedvideos) &&
                    isset($stepprocessedvideos[$course->id]) &&
                    isset($stepprocessedvideos[$course->id][$series->series]) &&
                    count($stepprocessedvideos[$course->id][$series->series]) === count($seriesvideos->videos)) ||
                    empty($seriesvideos->videos)
                ) {
                    $removeseriesmapping = true;
                }
            }

            // Remove the series videos cache as it is done processing.
            if ($seriesvideoscache->has($series->series)) {
                $seriesvideoscache->delete($series->series);
            }

            // Now that the series has been processed completely, we try to remove the series mapping as well.
            if ($removeseriesmapping && $ocremoveseriesmappingenabled) {
                $mapping = \tool_opencast\seriesmapping::get_record(
                    ['series' => $series->series, 'ocinstanceid' => $ocinstanceid, 'courseid' => $course->id],
                    true
                );

                if ($mapping) {
                    // First remove the series mapping.
                    if (!$mapping->delete()) {
                        // Trace.
                        $logtrace->print_mtrace(
                            get_string('mtrace_error_remove_series_mapping', 'lifecyclestep_opencast'),
                            '...',
                            4
                        );

                        // Notify admin.
                        $notificationhelper->notify_error(
                            $course,
                            $ocinstanceid,
                            $ocworkflow,
                            get_string('error_removeseriesmapping', 'lifecyclestep_opencast')
                        );

                        return step_response::WAITING;
                    }
                }

                // Trace.
                $alangobj = (object) [
                    'series' => $series->series,
                ];
                $logtrace->print_mtrace(
                    get_string('mtrace_finish_process_unlinking_series_course', 'lifecyclestep_opencast', $alangobj),
                    '...',
                    5
                );
            } else {
                // Trace.
                $alangobj = (object) [
                    'series' => $series->series,
                ];
                $logtrace->print_mtrace(
                    get_string('mtrace_notice_no_remove_mapping', 'lifecyclestep_opencast', $alangobj),
                    '...',
                    5
                );
            }

            // Trace.
            $alangobj = (object) [
                'series' => $series->series,
            ];
            $logtrace->print_mtrace(
                get_string('mtrace_finish_process_series', 'lifecyclestep_opencast', $alangobj),
                '...',
                4
            );
        }

        return '';
    }

    /**
     * Performs deletiong of event by starting the workflow on event, and then hand it over to block_opencast_deletejob cron.
     *
     * @param int $ocinstanceid the opencast instance id
     * @param string $videoidentifier video identifier
     * @param string $ocworkflow opencast workflow
     *
     * @return bool whether the workflow has started or not.
     */
    private static function perform_delete_event($ocinstanceid, $videoidentifier, $ocworkflow) {
        global $DB;
        // Get an APIbridge instance.
        $apibridge = \block_opencast\local\apibridge::get_instance($ocinstanceid);
        $workflowresult = $apibridge->start_workflow($videoidentifier, $ocworkflow);
        if ($workflowresult) {
            $deletejobrecord = [
                'opencasteventid' => $videoidentifier,
                'ocinstanceid' => $ocinstanceid,
            ];
            if (!$DB->record_exists('block_opencast_deletejob', $deletejobrecord)) {
                $deletejobrecord['timecreated'] = time();
                $deletejobrecord['timemodified'] = time();
                $deletejobrecord['failed'] = false;
                $DB->insert_record('block_opencast_deletejob', $deletejobrecord);
            }
        }
        return $workflowresult;
    }
}
