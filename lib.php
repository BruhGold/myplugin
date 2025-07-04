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
 * Readme file for local customisations
 *
 * @package    local_myplugin
 * @copyright  Dinh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/local/myplugin/classes/APIRequest.php');
require_once($CFG->dirroot . '/local/myplugin/classes/RequestsToDMOJ.php');

function local_myplugin_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $DB, $USER;
    // Create a new category
    $category = new core_user\output\myprofile\category('dmoj', get_string('category_title', 'local_myplugin'), 'miscellaneous');
    
    // add nodes to tree
    $tree->add_category($category);

    $check = $DB->get_record('myplugin_dmoj_users', ['moodle_user_id' => $user->id], 'dmoj_user_id');

    if (!$check) {
        // If the user does not have a DMOJ account
        // after linking, the user shouldn't see this section again
        $linked = optional_param('link', null, PARAM_INT);
        if ($linked) {
            $request = new GetUserDMOJId([$user->id]);
            $response = $request->run();
            $body = json_decode($response['body'], true);
            $record = $body[$user->id] ?? null;

            // insert moodle user id and dmoj user id into the database
            if ($record) {
                $DB->insert_record('myplugin_dmoj_users', [
                    'moodle_user_id' => $user->id,
                    'dmoj_user_id' => $record['user_id'],
                ]);
                redirect(new moodle_url('/user/profile.php', ['id' => $USER->id]), get_string('dmoj_link_success', 'local_myplugin'), null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                debugging("No matching DMOJ id for moodle_user_id = {$user->id}");
            }
        }

        // create node "create account link button"
        $profileurl = new moodle_url('/user/profile.php', ['id' => $USER->id]); # Get the current user's profile URL
        $url = new moodle_url(DOMAIN . '/login/moodle/', [
            'next' => $profileurl->out(false) . '&link=1'
        ]);
        $string = get_string('dmoj_link', 'local_myplugin');
        $node = new core_user\output\myprofile\node('dmoj', 'dmoj_link', $string, null, $url);

        // add the node to the tree
        $tree->add_node($node);
    } else {
        // If the user does have a DMOJ account
        // You should leave all the nodes that need a DMOJ account here
        // create a node 
        $url = new moodle_url('/local/myplugin/index.php', []);
        $string = get_string('download_user_data', 'local_myplugin');
        $node = new core_user\output\myprofile\node('dmoj', 'download_user_data', $string, null, $url);

        // add the node to the tree
        $tree->add_node($node);
    }
}