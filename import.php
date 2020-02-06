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
 * This file is part of the Database module for Moodle
 *
 * @copyright 2005 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_datac
 */

require_once('../../config.php');
require_once('lib.php');
require_once('locallib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once('import_form.php');

$id              = optional_param('id', 0, PARAM_INT);  // course module id
$d               = optional_param('d', 0, PARAM_INT);   // database id
$rid             = optional_param('rid', 0, PARAM_INT); // record id
$fielddelimiter  = optional_param('fielddelimiter', ',', PARAM_CLEANHTML); // characters used as field delimiters for csv file import
$fieldenclosure = optional_param('fieldenclosure', '', PARAM_CLEANHTML);   // characters used as record delimiters for csv file import

// ユーザ詳細
$withuserdetails = optional_param('withuserdetails', "0", PARAM_TEXT);
$useridfieldname = optional_param('useridfieldname', get_string('username'), PARAM_TEXT);
$limittoenrolled = optional_param('limittoenrolled', "1", PARAM_TEXT);
$groupname = optional_param('groupname', "", PARAM_TEXT);

// 追加/修正日時
$withtime = optional_param('withtime', "0", PARAM_TEXT);
$timecreatedfieldname = optional_param('timecreatedfieldname', get_string('timeadded', 'datac'), PARAM_TEXT);
$timecreatedformat = optional_param('timecreatedformat', get_string('strftimerecentfull'), PARAM_TEXT);
$timemodifiedfieldname = optional_param('timemodifiedfieldname', get_string('timemodified', 'datac'), PARAM_TEXT);
$timemodifiedformat = optional_param('timemodifiedformat', get_string('strftimerecentfull'), PARAM_TEXT);

// 日付フィールドのフォーマット
$date_format_type = optional_param('date_format_type', 'UnixTime', PARAM_TEXT);

$url = new moodle_url('/mod/datac/import.php');
if ($rid !== 0) {
    $url->param('rid', $rid);
}
if ($fielddelimiter !== '') {
    $url->param('fielddelimiter', $fielddelimiter);
}
if ($fieldenclosure !== '') {
    $url->param('fieldenclosure', $fieldenclosure);
}

if ($id) {
    $url->param('id', $id);
    $PAGE->set_url($url);
    $cm     = get_coursemodule_from_id('datac', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $data   = $DB->get_record('datac', array('id'=>$cm->instance), '*', MUST_EXIST);

} else {
    $url->param('d', $d);
    $PAGE->set_url($url);
    $data   = $DB->get_record('datac', array('id'=>$d), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$data->course), '*', MUST_EXIST);
    $cm     = get_coursemodule_from_instance('datac', $data->id, $course->id, false, MUST_EXIST);
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/datac:manageentries', $context);
$form = new mod_datac_import_form(new moodle_url('/mod/datac/import.php'));
$course_context = context_course::instance($course->id);

/// Print the page header
$PAGE->navbar->add(get_string('add', 'datac'));
$PAGE->set_title($data->name);
$PAGE->set_heading($course->fullname);
navigation_node::override_active_url(new moodle_url('/mod/datac/import.php', array('d' => $data->id)));
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('uploadrecords', 'mod_datac'), 'uploadrecords', 'mod_datac');

/// Groups needed for Add entry tab
$currentgroup = groups_get_activity_group($cm);
$groupmode = groups_get_activity_groupmode($cm);

if (!$formdata = $form->get_data()) {
    /// Upload records section. Only for teachers and the admin.
    echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    require_once('import_form.php');
    $form = new mod_datac_import_form(new moodle_url('/mod/datac/import.php'));
    $formdata = new stdClass();
    $formdata->d = $data->id;
    $form->set_data($formdata);
    $form->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die;
} else {
    // Large files are likely to take their time and memory. Let PHP know
    // that we'll take longer, and that the process should be recycled soon
    // to free up memory.
    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_EXTRA);

    $iid = csv_import_reader::get_new_iid('moddata');
    $cir = new csv_import_reader($iid, 'moddata');

    $filecontent = $form->get_file_content('recordsfile');
    $readcount = $cir->load_csv_content($filecontent, $formdata->encoding, $formdata->fielddelimiter);
    unset($filecontent);
    if (empty($readcount)) {
        print_error('csvfailed','datac',"{$CFG->wwwroot}/mod/datac/edit.php?d={$data->id}");
    } else {
        if (!$fieldnames = $cir->get_columns()) {
            print_error('cannotreadtmpfile', 'error');
        }
        $fieldnames = array_flip($fieldnames);
        // check the fieldnames are valid
        $rawfields = $DB->get_records('datac_fields', array('dataid' => $data->id), '', 'name, id, type');
        $fields = array();
        // 11/29追加 userid, timecreated, timemodifiedで使う
        $optfields = array();
        $errorfield = '';
        $safetoskipfields = array(get_string('user'), get_string('username'), get_string('email'),
            get_string('timeadded', 'datac'), get_string('timemodified', 'datac'),
            get_string('approved', 'datac'), get_string('tags', 'datac'),
            $useridfieldname, 
            $timecreatedfieldname, 
            $timemodifiedfieldname,
            $groupname
        );

        $enablefields = 0;
        foreach ($fieldnames as $name => $id) {
            if (!isset($rawfields[$name])) {
                if (!in_array($name, $safetoskipfields)) {
                    $errorfield .= "'$name' ";
                }
                // 11/29追加 $optfields に各オプションのフィールド番号を格納
                switch($name){
                    case $useridfieldname :
                        $optfields['userid'] = $id;
                        break;
                    case $timecreatedfieldname :
                        $optfields['timecreated'] = $id;
                        break;
                    case $timemodifiedfieldname :
                        $optfields['timemodified'] = $id;
                        break;
                    case $groupname :
                        $optfields['groupname'] = $id;
                        break;
                    default :
                        break;
                }
            } else {
                $field = $rawfields[$name];
                require_once("$CFG->dirroot/mod/datac/field/$field->type/field.class.php");
                $classname = 'datac_field_' . $field->type;
                $fields[$name] = new $classname($field, $data, $cm);
                $enablefields++;
            }
        }
        if($enablefields < 1){
            print_error('nothingenablefields', 'datac',"{$CFG->wwwroot}/mod/datac/import.php?d={$data->id}");
        }

        $cir->init();
        $recordsadded = 0;
        $recordserror = 0;
        $recordcount = 0;
        $opt = array();
        while ($record = $cir->next()) {
            $recordcount++;
            // 11/29 追加 $withuserdetails === 1 のときはエントリのユーザ情報をＤＢに格納
            if($withuserdetails === "1"){
                $userrec = $DB->get_record('user', array('username'=>$record[$optfields['userid']]));
                if(empty($userrec)){
                    // ユーザ情報が空の場合はそのレコードを登録しない
                    $mes = "[recordsfile:$recordcount]"." user not found"."(".$record[$optfields['userid']].")";
                    datac_import_log($data, $mes);
                    $recordserror++;
                    continue;
                }else if( $limittoenrolled === "1" && !is_enrolled($course_context, $userrec->id) ){
                    // ユーザがそのコースに登録されていない場合はそのレコードを登録しない
                    $mes = "[recordsfile:$recordcount]"." user not enrolled"."(".$record[$optfields['userid']].")";
                    datac_import_log($data, $mes);
                    $recordserror++;
                    continue;
                }else{
                    $opt['userid'] = $userrec->id;
                }
            }
            // 11/29 追加 $withtime === 1 のときはエントリの追加日時と修正日時をＤＢに格納
            if($withtime === "1"){
                $cre = $record[$optfields['timecreated']];
                $mod = $record[$optfields['timemodified']];
                if($cre === NULL || $cre === ''){
                }else{
                    $opt['timecreated'] = datac_datetimescan($cre, $timecreatedformat);
                }
                if($mod === NULL || $mod === ''){
                }else{
                    $opt['timemodified'] = datac_datetimescan($mod, $timemodifiedformat);
                }
            }

            // グループid関係の処理
            if( empty($groupname) 
                || !array_key_exists('groupname', $optfields)
                || $optfields['groupname'] === ""){
                $groupid = 0;
            }else{
                // グループの情報を取得
                $group = $DB->get_record('groups', array('courseid' => $course->id, 'name' => $record[$optfields['groupname']]));
                if(empty($group)){
                    $groupid = 0;
                }else{
                    $groupid = $group->id;
                }
            }

            // レコードの登録
            if ($recordid = datac_add_record($data, $groupid, $opt)) {  // add instance to datac_record
                foreach ($fields as $field) {
                    $fieldid = $fieldnames[$field->field->name];
                    if (isset($record[$fieldid])) {
                        // 12/06 type が date であった場合、フォーマットによっては値を変換する
                        if($field->field->type == 'date'){
                            if($date_format_type === 'YYYY-MM-DD'){
                                if( empty($record[$fieldid]) ){
                                    $value = 0;
                                }else{
                                    $value = strtotime($record[$fieldid]. " 09:00");
                                }
                            }else{
                                $value = $record[$fieldid] + (9 * 3600);
                            }
                        }else{
                            $value = $record[$fieldid];
                        }
                    } else {
                        $value = '';
                    }

                    if (method_exists($field, 'update_content_import')) {
                        $field->update_content_import($recordid, $value, 'field_' . $field->field->id);
                    } else {
                        $content = new stdClass();
                        $content->fieldid = $field->field->id;
                        $content->content = $value;
                        $content->recordid = $recordid;
                        $DB->insert_record('datac_content', $content);
                    }
                }

                if (core_tag_tag::is_enabled('mod_datac', 'datac_records') &&
                        isset($fieldnames[get_string('tags', 'datac')])) {
                    $columnindex = $fieldnames[get_string('tags', 'datac')];
                    $rawtags = $record[$columnindex];
                    $tags = explode(',', $rawtags);
                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if (empty($tag)) {
                            continue;
                        }
                        core_tag_tag::add_item_tag('mod_datac', 'datac_records', $recordid, $context, $tag);
                    }
                }

                $recordsadded++;
                print get_string('added', 'moodle', $recordsadded) . ". " . get_string('entry', 'datac') . " (ID $recordid)<br />\n";
            }
        }
        $cir->close();
        $cir->cleanup(true);
    }
}

if ($recordsadded > 0) {
    echo $OUTPUT->notification($recordsadded. ' '. get_string('recordssaved', 'datac'), '');
} else {
    echo $OUTPUT->notification(get_string('recordsnotsaved', 'datac'), 'notifysuccess');
}
if($recordserror > 0){
    echo $OUTPUT->notification($recordserror. ' '. get_string('recordserror', 'datac'), '');
}
if (!empty($errorfield)) {
    echo $OUTPUT->notification(get_string('undefinedfield', 'datac')."<br />$errorfield", '');
}

echo $OUTPUT->continue_button('import.php?d='.$data->id);

/// Finish the page
echo $OUTPUT->footer();
