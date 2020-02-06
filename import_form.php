<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');

class mod_datac_import_form extends moodleform {

    function definition() {
        global $CFG;
        $mform =& $this->_form;
        $cmid = $this->_customdata['id'];

        $mform->addElement('filepicker', 'recordsfile', get_string('csvfile', 'datac'));

        $delimiters = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'fielddelimiter', get_string('fielddelimiter', 'datac'), $delimiters);
        $mform->setDefault('fielddelimiter', 'comma');

        $mform->addElement('text', 'fieldenclosure', get_string('fieldenclosure', 'datac'));
        $mform->setType('fieldenclosure', PARAM_CLEANHTML);
        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('fileencoding', 'mod_datac'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        // 11/29追加 ユーザＩＤのフィールド名の入力を求める
        $mform->addElement('selectyesno', 'withuserdetails', get_string('withuserdetails', 'datac'));
        $mform->setDefault('withuserdetails', 0);
        // ユーザＩＤフィールド名
        $mform->addElement('text', 'useridfieldname', get_string('useridfieldname', 'datac'));
        $mform->setDefault('useridfieldname', get_string('username'));
        $mform->disabledIf('useridfieldname', 'withuserdetails', 'eq', 0);
        // グループ名フィールド名
        $mform->addElement('text', 'groupname', get_string('groupname', 'datac'));
        $mform->setDefault('groupname', '');
        $mform->disabledIf('groupname', 'withuserdetails', 'eq', 0);
        // コースに登録されていないユーザのデータのみインポートする
        $mform->addElement('selectyesno', 'limittoenrolled', get_string('limittoenrolled', 'datac'));
        $mform->setDefault('limittoenrolled', 1);
        $mform->disabledIf('limittoenrolled', 'withuserdetails', 'eq', 0);

        // 11/29追加 データ追加日時／修正日時のフィールド名などの入力を求める
        $mform->addElement('selectyesno', 'withtime', get_string('withtime', 'datac'));
        $mform->setDefault('withtime', 0);
        // 追加日時フィールド名
        $mform->addElement('text', 'timecreatedfieldname', get_string('timecreatedfieldname', 'datac'));
        $mform->setDefault('timecreatedfieldname', get_string('timeadded', 'datac'));
        $mform->disabledIf('timecreatedfieldname', 'withtime', 'eq', 0);
        // 追加日時フォーマット
        $mform->addElement('text', 'timecreatedformat', get_string('timecreatedformat', 'datac'), 'size="50"');
        $mform->setDefault('timecreatedformat', get_string('strftimerecentfull'));
        $mform->disabledIf('timecreatedformat', 'withtime', 'eq', 0);
        // 修正日時
        $mform->addElement('text', 'timemodifiedfieldname', get_string('timemodifiedfieldname', 'datac'));
        $mform->setDefault('timemodifiedfieldname', get_string('timemodified', 'datac'));
        $mform->disabledIf('timemodifiedfieldname', 'withtime', 'eq', 0);
        // 修正日時フォーマット
        $mform->addElement('text', 'timemodifiedformat', get_string('timemodifiedformat', 'datac'), 'size="50"');
        $mform->setDefault('timemodifiedformat', get_string('strftimerecentfull'));
        $mform->disabledIf('timemodifiedformat', 'withtime', 'eq', 0);
        // 12/06追加 「日付」タイプのフィールドのフォーマット
        $mform->addElement('radio', 'date_format_type', get_string('date_format_type', 'datac'), 'UnixTime', 'UnixTime');
        $mform->addElement('radio', 'date_format_type', '', 'YYYY-MM-DD', 'YYYY-MM-DD');
        $mform->setDefault('date_format_type', 'YYYY-MM-DD');

        $submit_string = get_string('submit');
        // data id
        $mform->addElement('hidden', 'd');
        $mform->setType('d', PARAM_INT);

        $this->add_action_buttons(false, $submit_string);
    }
}
