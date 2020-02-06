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
 * @package    mod_datac
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/datac/backup/moodle2/restore_datac_stepslib.php'); // Because it exists (must)

/**
 * data restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_datac_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Data only has one structure step
        $this->add_step(new restore_datac_activity_structure_step('data_structure', 'data.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('datac', array(
                              'intro', 'singletemplate', 'listtemplate', 'listtemplateheader', 'listtemplatefooter',
                              'addtemplate', 'rsstemplate', 'rsstitletemplate', 'asearchtemplate'), 'datac');
        $contents[] = new restore_decode_content('datac_fields', array(
                              'description', 'param1', 'param2', 'param3',
                              'param4', 'param5', 'param6', 'param7',
                              'param8', 'param9', 'param10'), 'datac_field');
        $contents[] = new restore_decode_content('datac_content', array(
                              'content', 'content1', 'content2', 'content3', 'content4'));

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('DATACVIEWBYID', '/mod/datac/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('DATACVIEWBYD', '/mod/datac/index.php?d=$1', 'datac');
        $rules[] = new restore_decode_rule('DATACINDEX', '/mod/datac/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('DATACVIEWRECORD', '/mod/datac/view.php?d=$1&amp;rid=$2', array('datac', 'datac_record'));

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * data logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('datac', 'add', 'view.php?d={datac}&rid={datac_record}', '{datac}');
        $rules[] = new restore_log_rule('datac', 'update', 'view.php?d={datac}&rid={datac_record}', '{datac}');
        $rules[] = new restore_log_rule('datac', 'view', 'view.php?id={course_module}', '{datac}');
        $rules[] = new restore_log_rule('datac', 'record delete', 'view.php?id={course_module}', '{datac}');
        $rules[] = new restore_log_rule('datac', 'fields add', 'field.php?d={datac}&mode=display&fid={datac_field}', '{datac_field}');
        $rules[] = new restore_log_rule('datac', 'fields update', 'field.php?d={datac}&mode=display&fid={datac_field}', '{datac_field}');
        $rules[] = new restore_log_rule('datac', 'fields delete', 'field.php?d={datac}', '[name]');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('datac', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    /**
     * Given a commment area, return the itemname that contains the itemid mappings.
     *
     * @param string $commentarea Comment area name e.g. database_entry.
     * @return string name of the mapping used to determine the itemid.
     */
    public function get_comment_mapping_itemname($commentarea) {
        if ($commentarea == 'database_entry') {
            $itemname = 'datac_record';
        } else {
            $itemname = parent::get_comment_mapping_itemname($commentarea);
        }
        return $itemname;
    }
}
