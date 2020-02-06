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
 * Contains interface datacfield_provider
 *
 * @package mod_datac
 * @copyright 2018 Marina Glancy
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_datac\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface datacfield_provider, all datacfield plugins need to implement it
 *
 * @package mod_datac
 * @copyright 2018 Marina Glancy
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface datacfield_provider extends \core_privacy\local\request\plugin\subplugin_provider {

    /**
     * Exports data about one record in {datac_content} table.
     *
     * datacfield plugins providers should implement this method to:
     * - preprocess references to files in the response (examples - textarea, picture, file)
     * - make content more human-readable (example - replace values separators in multimenu, format date in date)
     * - add more information about the field itself (example - list all options for menu, multimenu, radio)
     *
     * Sample implementation (from datacfield_textarea):
     *
     *    $defaultvalue->content = writer::with_context($context)
     *        ->rewrite_pluginfile_urls([$recordobj->id, $contentobj->id], 'mod_datac', 'content', $contentobj->id,
     *        $defaultvalue->content);
     *    writer::with_context($context)->export_data([$recordobj->id, $contentobj->id], $defaultvalue);
     *
     * @param \context_module $context
     * @param \stdClass $recordobj record from DB table {datac_records}
     * @param \stdClass $fieldobj record from DB table {datac_fields}
     * @param \stdClass $contentobj record from DB table {datac_content}
     * @param \stdClass $defaultvalue pre-populated default value that most of plugins will use
     */
    public static function export_datac_content($context, $recordobj, $fieldobj, $contentobj, $defaultvalue);

    /**
     * Allows plugins to delete locally stored data.
     *
     * Usually datacfield plugins do not store anything and this method will be empty.
     *
     * @param \context_module $context
     * @param \stdClass $recordobj record from DB table {datac_records}
     * @param \stdClass $fieldobj record from DB table {datac_fields}
     * @param \stdClass $contentobj record from DB table {datac_content}
     */
    public static function delete_datac_content($context, $recordobj, $fieldobj, $contentobj);
}
