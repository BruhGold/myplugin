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
require_once(__DIR__ . '/classes/api_request.php');

function local_myplugin_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $DB, $USER;
    $domain = get_config('local_myplugin', 'dmoj_domain');
    // Create a new category
    $category = new core_user\output\myprofile\category('dmoj', get_string('category_title', 'local_myplugin'), 'miscellaneous');
    
    // add nodes to tree
    $tree->add_category($category);
    $check = $DB->get_record('myplugin_dmoj_users', ['moodle_user_id' => $user->id], 'dmoj_user_id');

    if (!$check) {
        // If the user does not have a DMOJ account, might as well ask the admin to link it for them through the database
        // I am doing this because the requirement for Capstone is linking should be automatic
        // If it is not working correctly, maybe some bugs arise and i fucked up
    } else {
        // If the user does have a DMOJ account
        // You should leave all the nodes that need a DMOJ account here
        // create node "download user data button"
        $url = new moodle_url('/local/myplugin/index.php', []);
        $string = get_string('download_user_data', 'local_myplugin');
        $node_download = new core_user\output\myprofile\node('dmoj', 'download_user_data', $string, null, $url);
    }
}