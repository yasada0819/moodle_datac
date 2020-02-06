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
 * The mod_datac field updated event.
 *
 * @package    mod_datac
 * @copyright  2014 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_datac\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_datac field updated event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string fieldname: the name of the field.
 *      - int dataid: the id of the data activity.
 * }
 *
 * @package    mod_datac
 * @since      Moodle 2.7
 * @copyright  2014 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_updated extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'datac_fields';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventfieldupdated', 'mod_datac');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' updated the field with id '$this->objectid' in the data activity " .
            "with course module id '$this->contextinstanceid'.";
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/datac/field.php', array('d' => $this->other['dataid']));
    }

    /**
     * Get the legacy event log data.
     *
     * @return array
     */
    public function get_legacy_logdata() {
        return array($this->courseid, 'datac', 'fields update', 'field.php?d=' . $this->other['dataid'] .
            '&amp;mode=display&amp;fid=' . $this->objectid, $this->objectid, $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception when validation does not pass.
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['fieldname'])) {
            throw new \coding_exception('The \'fieldname\' value must be set in other.');
        }

        if (!isset($this->other['dataid'])) {
            throw new \coding_exception('The \'dataid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'datac_fields', 'restore' => 'datac_field');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['dataid'] = array('db' => 'datac', 'restore' => 'datac');

        return $othermapped;
    }
}
