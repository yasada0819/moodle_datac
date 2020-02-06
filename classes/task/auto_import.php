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
 * Auto import task
 * @package   mod_datac
 * @author    FUJI-SOFT
 * @copyright Copyright (c) 2018 FUJI-SOFT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_datac\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Auto import task class
 * @package   mod_datac
 * @author    FUJI-SOFT
 * @copyright Copyright (c) 2018 FUJI-SOFT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auto_import extends \core\task\scheduled_task {
    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('autoimporttask', 'datac');
    }

    /**
     * Run task for synchronising users.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot. '/mod/datac/lib.php');
        require_once($CFG->dirroot. '/mod/datac/locallib.php');
        require_once($CFG->libdir.'/csvlib.class.php');

        // ©“®“o˜^‚ğON‚É‚µ‚Ä‚¢‚éDB‚Ìid‚ÌƒŠƒXƒg‚ğæ“¾
        $tergetdbs = $DB->get_records('datac', array('enableautoimport'=>'1'));
        foreach($tergetdbs as $tergetdb){
            $csvfilepath = $CFG->dataroot . '/' . $tergetdb->csvfilepath;
            if( $count = auto_import_exec($tergetdb) ){
                datac_import_log($tergetdb, "[$csvfilepath] import $count records.");
            }else{
                datac_import_log($tergetdb, "[$csvfilepath] import failed.");
            }
            if(file_exists($csvfilepath)){
                try{
                    $newname = $csvfilepath.date("YmdHi");
                    rename($csvfilepath, $newname);
                    datac_import_log($tergetdb, "[$csvfilepath] rename to $newname");
                }catch(exception $e){
                    datac_import_log($tergetdb, "[$csvfilepath] ".$e->message());
                }
            }
        }
    }

}
