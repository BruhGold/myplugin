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

function local_myplugin_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    // Create a new category
    $category = new core_user\output\myprofile\category('dmoj', get_string('category_title', 'local_myplugin'), 'miscellaneous');
    
    // add nodes to tree
    $tree->add_category($category);

    // create a node 
    $url = new moodle_url('/local/myplugin/index.php', []);
    $string = get_string('download_user_data', 'local_myplugin');
    $node = new core_user\output\myprofile\node('dmoj', 'download_user_data', $string, null, $url);

    // add the node to the tree
    $tree->add_node($node);
}