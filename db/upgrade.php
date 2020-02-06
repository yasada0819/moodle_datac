<?php
// This file keeps track of upgrades to
// the data module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

defined('MOODLE_INTERNAL') || die();

function xmldb_datac_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016090600) {

        // Define field config to be added to data.
        $table = new xmldb_table('datac');
        $field = new xmldb_field('config', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timemodified');

        // Conditionally launch add field config.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2016090600, 'datac');
    }

    // Automatically generated Moodle v3.2.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2017032800) {

        // Define field completionentries to be added to data. Require a number of entries to be considered complete.
        $table = new xmldb_table('datac');
        $field = new xmldb_field('completionentries', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'config');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2017032800, 'datac');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.4.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    // datac への改修
    if ($oldversion < 2018112903) {

        // Define field completionentries to be added to data. Require a number of entries to be considered complete.
        $table = new xmldb_table('datac');
        $field1 = new xmldb_field('csvfilepath', XMLDB_TYPE_TEXT);
        $field2 = new xmldb_field('useridfieldname', XMLDB_TYPE_TEXT);
        $field3 = new xmldb_field('timecreatedfieldname', XMLDB_TYPE_TEXT);
        $field4 = new xmldb_field('timemodifiedfieldname', XMLDB_TYPE_TEXT);
        $field5 = new xmldb_field('fieldwrapper', XMLDB_TYPE_TEXT);
        $field6 = new xmldb_field('enableautoimport', XMLDB_TYPE_TEXT);
        $field7 = new xmldb_field('timecreatedformat', XMLDB_TYPE_TEXT);
        $field8 = new xmldb_field('timemodifiedformat', XMLDB_TYPE_TEXT);

        // add field1~5
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }
        if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }
        if (!$dbman->field_exists($table, $field6)) {
            $dbman->add_field($table, $field6);
        }
        if (!$dbman->field_exists($table, $field7)) {
            $dbman->add_field($table, $field7);
        }
        if (!$dbman->field_exists($table, $field8)) {
            $dbman->add_field($table, $field8);
        }

        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2018112903, 'datac');
    }

    // ログ保存先パスの追加
    if ($oldversion < 2018120401) {

        // Define field completionentries to be added to data. Require a number of entries to be considered complete.
        $table = new xmldb_table('datac');
        $field1 = new xmldb_field('importlog', XMLDB_TYPE_TEXT);

        // add field1
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2018120401, 'datac');
    }

    // 「日付」タイプのフィールドのデータ形式
    if ($oldversion < 2018120601) {

        // Define field completionentries to be added to data. Require a number of entries to be considered complete.
        $table = new xmldb_table('datac');
        $field1 = new xmldb_field('date_format_type', XMLDB_TYPE_TEXT);

        // add field
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2018120601, 'datac');
    }

    // コースに登録しているユーザに限定する
    if ($oldversion < 2018121701) {

        // Define field completionentries to be added to data. Require a number of entries to be considered complete.
        $table = new xmldb_table('datac');
        $field1 = new xmldb_field('limittoenrolled', XMLDB_TYPE_TEXT);

        // add field
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2018121701, 'datac');
    }

    // グループ名を格納するフィールドの名前
    if ($oldversion < 2018122005) {

        // Define field completionentries to be added to data. Require a number of entries to be considered complete.
        $table = new xmldb_table('datac');
        $field1 = new xmldb_field('groupname', XMLDB_TYPE_TEXT);

        // add field
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2018122005, 'datac');
    }

    return true;
}
