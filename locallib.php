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
 * Helper functions for tool_lifecycle.
 * @package    tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Check if a plugin is installed.
 * @param string $plugin name of the plugin
 * @param string $plugintype name of the plugin's plugin type
 * @return bool
 */
function lifecycle_is_plugin_installed($plugin, $plugintype) {
    $pluginsinstalled = core_component::get_plugin_list($plugintype);
    $found = false;
    foreach ($pluginsinstalled as $installed => $path) {
        if ($plugin == $installed) {
            $found = true;
            break;
        }
    }
    return $found;
}

/**
 * Generates html for a select field to switch from one workflow to another
 *
 * @param string $activewf the current workflow
 * @return void
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 * @throws dml_exception
 */
function lifecycle_select_change_workflow($activewf) {
    global $OUTPUT, $DB, $PAGE;

    $records = $DB->get_records_sql(
        'SELECT id, title, timeactive, timedeactive FROM {tool_lifecycle_workflow} ORDER BY title ASC');

    $url = $PAGE->url;
    $actionmenu = new \action_menu();
    foreach ($records as $record) {
        if ($record->id == $activewf) {
            continue;
        }
        $actionmenu->add_secondary_action(
            new \action_menu_link_secondary(
                new \moodle_url($url, ['wf' => $record->id]),
                null,
                $record->title
            )
        );
    }

    $actionmenu->set_menu_trigger(get_string('switchworkflow', 'tool_lifecycle'));
    echo $OUTPUT->render_action_menu($actionmenu);
}

/**
 * Check if a discussion for this workflow already exists and if not create one. Return the url to this discussion.
 * @param int $workflowid id of the workflow
 * @param int $forumid id of the dedicated workflows discussion forum instance
 * @return int[] discussionid or postid when new discussion
 */
function lifecycle_get_workflow_discussion($workflowid, $forumid) {
    global $DB, $USER;

    if (!$forum = $DB->get_record('forum', ['id' => $forumid])) {
        throw new moodle_exception('invalidcoursemodule', 'moodle');
    }
    $workflow = $DB->get_record('tool_lifecycle_workflow', ['id' => $workflowid]);

    $postid = "";
    if (!$discussionid = $DB->get_field('tool_lifecycle_workflow', 'forum_discussion', ['id' => $workflowid])) {

        $timenow = time();

        $discussion = new stdClass();
        $discussion->course          = $forum->course;
        $discussion->forum           = $forum->id;
        $discussion->name            = get_string('workflow', 'tool_lifecycle').": ".$workflow->title;
        $discussion->assessed        = $forum->assessed;
        $discussion->message         = "";
        $discussion->messageformat   = $forum->introformat;
        $discussion->messagetrust    = true;
        $discussion->mailnow         = false;
        $discussion->groupid         = -1;

        $post = new \stdClass();
        $post->discussion    = 0;
        $post->parent        = 0;
        $post->privatereplyto = 0;
        $post->userid        = $USER->id;
        $post->created       = $timenow;
        $post->modified      = $timenow;
        $post->mailed        = 0;
        $post->subject       = $discussion->name;
        $post->message       = "";
        $post->messageformat = $discussion->messageformat;
        $post->messagetrust  = $discussion->messagetrust;
        $post->attachments   = null;
        $post->forum         = $forum->id;
        $post->course        = $forum->course;
        $post->mailnow       = $discussion->mailnow;

        \mod_forum\local\entities\post::add_message_counts($post);
        $post->id = $DB->insert_record("forum_posts", $post);

        $discussion->firstpost    = $post->id;
        $discussion->timemodified = $timenow;
        $discussion->usermodified = $post->userid;
        $discussion->userid       = $USER->id;
        $discussion->assessed     = 0;

        $discussionid = $DB->insert_record("forum_discussions", $discussion);

        // Set the pointer to the discussion on the post.
        $DB->set_field("forum_posts", "discussion", $discussionid, ["id" => $post->id]);

        // Make it the workflow's discussion.
        $DB->set_field("tool_lifecycle_workflow", "forum_discussion", $discussionid, ["id" => $workflowid]);

        $postid = $post->id;
        $discussionid = "";
    }

    return [$discussionid, $postid];
}