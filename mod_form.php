<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_datac_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('intro', 'datac'));

        // ----------------------------------------------------------------------
        // 改修にあたって追加
        $mform->addElement('header', 'datac_config', get_string('csvautoimportconfig', 'datac'));

        // 自動インポートを有効化する／しない
        $mform->addElement('selectyesno', 'enableautoimport', get_string('enableautoimport', 'datac'));
        $mform->setDefault('enableautoimport', '0');

        // 読み込みCSVファイルパス
        $mform->addElement('text', 'csvfilepath', get_string('csvfilepath', 'datac'), 'size="50"');
        $mform->addHelpButton('csvfilepath', 'csvfilepath', 'datac');
        $mform->setDefault('csvfilepath', 'path/to/csvfile');
        $mform->disabledIf('csvfilepath', 'enableautoimport', 'eq', 0);

        // ユーザＩＤを格納するフィールド名
        $mform->addElement('text', 'useridfieldname', get_string('useridfieldname', 'datac'));
        $mform->setDefault('useridfieldname', get_string('username'));
        $mform->disabledIf('useridfieldname', 'enableautoimport', 'eq', 0);

        // グループ名を格納するフィールド名
        $mform->addElement('text', 'groupname', get_string('groupname', 'datac'));
        $mform->setDefault('groupname', '');
        $mform->disabledIf('groupname', 'enableautoimport', 'eq', 0);

        // コースに登録されているユーザに限定する
        $mform->addElement('selectyesno', 'limittoenrolled', get_string('limittoenrolled', 'datac'));
        $mform->setDefault('limittoenrolled', 1);
        $mform->disabledIf('limittoenrolled', 'enableautoimport', 'eq', 0);

        // 追加日時を格納するフィールド名
        $mform->addElement('text', 'timecreatedfieldname', get_string('timecreatedfieldname', 'datac'));
        $mform->setDefault('timecreatedfieldname', get_string('timeadded', 'datac'));
        $mform->disabledIf('timecreatedfieldname', 'enableautoimport', 'eq', 0);

        // 追加日時のフォーマット
        $mform->addElement('text', 'timecreatedformat', get_string('timecreatedformat', 'datac'), 'size="50"');
        $mform->setDefault('timecreatedformat', get_string('strftimerecentfull'));
        $mform->disabledIf('timecreatedformat', 'enableautoimport', 'eq', 0);

        // 更新日時を格納するフィールド名
        $mform->addElement('text', 'timemodifiedfieldname', get_string('timemodifiedfieldname', 'datac'));
        $mform->setDefault('timemodifiedfieldname', get_string('timemodified', 'datac'));
        $mform->disabledIf('timemodifiedfieldname', 'enableautoimport', 'eq', 0);

        // 更新日時のフォーマット
        $mform->addElement('text', 'timemodifiedformat', get_string('timemodifiedformat', 'datac'), 'size="50"');
        $mform->setDefault('timemodifiedformat', get_string('strftimerecentfull'));
        $mform->disabledIf('timemodifiedformat', 'enableautoimport', 'eq', 0);

        // 12/06追加 「日付」タイプのフィールドのフォーマット
        $mform->addElement('radio', 'date_format_type', get_string('date_format_type', 'datac'), 'UnixTime', 'UnixTime');
        $mform->addElement('radio', 'date_format_type', '', 'YYYY-MM-DD', 'YYYY-MM-DD');
        $mform->setDefault('date_format_type', 'YYYY-MM-DD');
        $mform->disabledIf('date_format_type', 'enableautoimport', 'eq', 0);

        // フィールド囲み文字
        $mform->addElement('text', 'fieldwrapper', get_string('fieldwrapper', 'datac'));
        $mform->setDefault('fieldwrapper', '"');
        $mform->disabledIf('fieldwrapper', 'enableautoimport', 'eq', 0);

        // ログ出力先
        $mform->addElement('text', 'importlog', get_string('importlog', 'datac'), 'size="50"');
        $mform->setDefault('importlog', get_string('importlog_default', 'datac'));
        $mform->disabledIf('importlog', 'enableautoimport', 'eq', 0);

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'entrieshdr', get_string('entries', 'datac'));

        $mform->addElement('selectyesno', 'approval', get_string('requireapproval', 'datac'));
        $mform->addHelpButton('approval', 'requireapproval', 'datac');

        $mform->addElement('selectyesno', 'manageapproved', get_string('manageapproved', 'datac'));
        $mform->addHelpButton('manageapproved', 'manageapproved', 'datac');
        $mform->setDefault('manageapproved', 1);
        $mform->disabledIf('manageapproved', 'approval', 'eq', 0);

        $mform->addElement('selectyesno', 'comments', get_string('allowcomments', 'datac'));

        $countoptions = array(0=>get_string('none'))+
                        (array_combine(range(1, DATA_MAX_ENTRIES), // Keys.
                                        range(1, DATA_MAX_ENTRIES))); // Values.
        /*only show fields if there are legacy values from
         *before completionentries was added*/
        if (!empty($this->current->requiredentries)) {
            $group = array();
            $group[] = $mform->createElement('select', 'requiredentries',
                    get_string('requiredentries', 'datac'), $countoptions);
            $mform->addGroup($group, 'requiredentriesgroup', get_string('requiredentries', 'datac'), array(''), false);
            $mform->addHelpButton('requiredentriesgroup', 'requiredentries', 'datac');
            $mform->addElement('html', $OUTPUT->notification( get_string('requiredentrieswarning', 'datac')));
        }

        $mform->addElement('select', 'requiredentriestoview', get_string('requiredentriestoview', 'datac'), $countoptions);
        $mform->addHelpButton('requiredentriestoview', 'requiredentriestoview', 'datac');

        $mform->addElement('select', 'maxentries', get_string('maxentries', 'datac'), $countoptions);
        $mform->addHelpButton('maxentries', 'maxentries', 'datac');

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'availibilityhdr', get_string('availability'));

        $mform->addElement('date_time_selector', 'timeavailablefrom', get_string('availablefromdate', 'datac'),
                           array('optional' => true));

        $mform->addElement('date_time_selector', 'timeavailableto', get_string('availabletodate', 'datac'),
                           array('optional' => true));

        $mform->addElement('date_time_selector', 'timeviewfrom', get_string('viewfromdate', 'datac'),
                           array('optional' => true));

        $mform->addElement('date_time_selector', 'timeviewto', get_string('viewtodate', 'datac'),
                           array('optional' => true));

        // ----------------------------------------------------------------------
        if ($CFG->enablerssfeeds && $CFG->data_enablerssfeeds) {
            $mform->addElement('header', 'rsshdr', get_string('rss'));
            $mform->addElement('select', 'rssarticles', get_string('numberrssarticles', 'datac') , $countoptions);
        }

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    /**
     * Enforce validation rules here
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array
     **/
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check open and close times are consistent.
        if ($data['timeavailablefrom'] && $data['timeavailableto'] &&
                $data['timeavailableto'] < $data['timeavailablefrom']) {
            $errors['timeavailableto'] = get_string('availabletodatevalidation', 'datac');
        }
        if ($data['timeviewfrom'] && $data['timeviewto'] &&
                $data['timeviewto'] < $data['timeviewfrom']) {
            $errors['timeviewto'] = get_string('viewtodatevalidation', 'datac');
        }

        return $errors;
    }

    /**
     * Display module-specific activity completion rules.
     * Part of the API defined by moodleform_mod
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform = & $this->_form;
        $group = array();
        $group[] = $mform->createElement('checkbox', 'completionentriesenabled', '',
                get_string('completionentriescount', 'datac'));
        $group[] = $mform->createElement('text', 'completionentries',
                get_string('completionentriescount', 'datac'), array('size' => '1'));

        $mform->addGroup($group, 'completionentriesgroup', get_string('completionentries', 'datac'),
                array(' '), false);
        $mform->disabledIf('completionentries', 'completionentriesenabled', 'notchecked');
        $mform->setDefault('completionentries', 1);
        $mform->setType('completionentries', PARAM_INT);
        /* This ensures the elements are disabled unless completion rules are enabled */
        return array('completionentriesgroup');
    }

    /**
     * Called during validation. Indicates if a module-specific completion rule is selected.
     *
     * @param array $data
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return ($data['completionentries'] != 0);
    }

    /**
     * Set up the completion checkbox which is not part of standard data.
     *
     * @param array $defaultvalues
     *
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);
        $defaultvalues['completionentriesenabled'] = !empty($defaultvalues['completionentries']) ? 1 : 0;
        if (empty($defaultvalues['completionentries'])) {
            $defaultvalues['completionentries'] = 1;
        }
    }

    /**
     * Allows modules to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionentriesenabled) || !$autocompletion) {
                $data->completionentries = 0;
            }
        }
    }

}
