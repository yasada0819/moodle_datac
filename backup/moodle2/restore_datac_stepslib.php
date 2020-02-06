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

/**
 * Define all the restore steps that will be used by the restore_data_activity_task
 */

/**
 * Structure step to restore one data activity
 */
class restore_datac_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('datac', '/activity/datac');
        $paths[] = new restore_path_element('datac_field', '/activity/datac/fields/field');
        if ($userinfo) {
            $paths[] = new restore_path_element('datac_record', '/activity/datac/records/record');
            $paths[] = new restore_path_element('datac_content', '/activity/datac/records/record/contents/content');
            $paths[] = new restore_path_element('datac_rating', '/activity/datac/records/record/ratings/rating');
            $paths[] = new restore_path_element('datac_record_tag', '/activity/datac/recordstags/tag');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_datac($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        $data->timeavailablefrom = $this->apply_date_offset($data->timeavailablefrom);
        $data->timeavailableto = $this->apply_date_offset($data->timeavailableto);
        $data->timeviewfrom = $this->apply_date_offset($data->timeviewfrom);
        $data->timeviewto = $this->apply_date_offset($data->timeviewto);
        $data->assesstimestart = $this->apply_date_offset($data->assesstimestart);
        $data->assesstimefinish = $this->apply_date_offset($data->assesstimefinish);

        if ($data->scale < 0) { // scale found, get mapping
            $data->scale = -($this->get_mappingid('scale', abs($data->scale)));
        }

        // Some old backups can arrive with data->notification = null (MDL-24470)
        // convert them to proper column default (zero)
        if (is_null($data->notification)) {
            $data->notification = 0;
        }

        // insert the data record
        $newitemid = $DB->insert_record('datac', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_datac_field($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->dataid = $this->get_new_parentid('datac');

        // insert the datac_fields record
        $newitemid = $DB->insert_record('datac_fields', $data);
        $this->set_mapping('datac_field', $oldid, $newitemid, false); // no files associated
    }

    protected function process_datac_record($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->dataid = $this->get_new_parentid('datac');

        // insert the datac_records record
        $newitemid = $DB->insert_record('datac_records', $data);
        $this->set_mapping('datac_record', $oldid, $newitemid, false); // no files associated
    }

    protected function process_datac_content($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->fieldid = $this->get_mappingid('datac_field', $data->fieldid);
        $data->recordid = $this->get_new_parentid('datac_record');

        // insert the datac_content record
        $newitemid = $DB->insert_record('datac_content', $data);
        $this->set_mapping('datac_content', $oldid, $newitemid, true); // files by this itemname
    }

    /**
     * Add tags to restored records.
     *
     * @param stdClass $data Tag
     */
    protected function process_datac_record_tag($data) {
        $data = (object)$data;

        if (!core_tag_tag::is_enabled('mod_datac', 'datac_records')) { // Tags disabled in server, nothing to process.
            return;
        }

        if (!$itemid = $this->get_mappingid('datac_record', $data->itemid)) {
            // Some orphaned tag, we could not find the data record for it - ignore.
            return;
        }

        $tag = $data->rawname;
        $context = context_module::instance($this->task->get_moduleid());
        core_tag_tag::add_item_tag('mod_datac', 'datac_records', $itemid, $context, $tag);
    }

    protected function process_datac_rating($data) {
        global $DB;

        $data = (object)$data;

        // Cannot use ratings API, cause, it's missing the ability to specify times (modified/created)
        $data->contextid = $this->task->get_contextid();
        $data->itemid    = $this->get_new_parentid('datac_record');
        if ($data->scaleid < 0) { // scale found, get mapping
            $data->scaleid = -($this->get_mappingid('scale', abs($data->scaleid)));
        }
        $data->rating = $data->value;
        $data->userid = $this->get_mappingid('user', $data->userid);

        // We need to check that component and ratingarea are both set here.
        if (empty($data->component)) {
            $data->component = 'mod_datac';
        }
        if (empty($data->ratingarea)) {
            $data->ratingarea = 'entry';
        }

        $newitemid = $DB->insert_record('rating', $data);
    }

    protected function after_execute() {
        global $DB;
        // Add data related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_datac', 'intro', null);
        // Add content related files, matching by itemname (datac_content)
        $this->add_related_files('mod_datac', 'content', 'datac_content');
        // Adjust the data->defaultsort field
        if ($defaultsort = $DB->get_field('datac', 'defaultsort', array('id' => $this->get_new_parentid('datac')))) {
            if ($defaultsort = $this->get_mappingid('datac_field', $defaultsort)) {
                $DB->set_field('datac', 'defaultsort', $defaultsort, array('id' => $this->get_new_parentid('datac')));
            }
        }
    }
}
