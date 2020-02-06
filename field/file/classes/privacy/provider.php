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
 * Privacy Subsystem implementation for datacfield_file.
 *
 * @package    datacfield_file
 * @copyright  2018 Carlos Escobedo <carlos@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace datacfield_file\privacy;
use mod_datac\privacy\datacfield_provider;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();
/**
 * Privacy Subsystem for datacfield_file implementing null_provider.
 *
 * @copyright  2018 Carlos Escobedo <carlos@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider,
        datacfield_provider {
    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }

    /**
     * Exports data about one record in {datac_content} table.
     *
     * @param \context_module $context
     * @param \stdClass $recordobj record from DB table {datac_records}
     * @param \stdClass $fieldobj record from DB table {datac_fields}
     * @param \stdClass $contentobj record from DB table {datac_content}
     * @param \stdClass $defaultvalue pre-populated default value that most of plugins will use
     */
    public static function export_datac_content($context, $recordobj, $fieldobj, $contentobj, $defaultvalue) {
        if ($fieldobj->param3) {
            $defaultvalue->field['maxbytes'] = $fieldobj->param3;
        }
        // Change file name to file path.
        $defaultvalue->file = writer::with_context($context)
            ->rewrite_pluginfile_urls([$recordobj->id, $contentobj->id], 'mod_datac', 'content', $contentobj->id,
                '@@PLUGINFILE@@/' . $defaultvalue->content);
        unset($defaultvalue->content);
        writer::with_context($context)->export_data([$recordobj->id, $contentobj->id], $defaultvalue);
    }

    /**
     * Allows plugins to delete locally stored data.
     *
     * @param \context_module $context
     * @param \stdClass $recordobj record from DB table {datac_records}
     * @param \stdClass $fieldobj record from DB table {datac_fields}
     * @param \stdClass $contentobj record from DB table {datac_content}
     */
    public static function delete_datac_content($context, $recordobj, $fieldobj, $contentobj) {

    }
}