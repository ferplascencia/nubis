<?php/*  ------------------------------------------------------------------------  Copyright (C) 2014 Bart Orriens, Albert Weerman  This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.  This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.  You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA  ------------------------------------------------------------------------ */class Importer {    private $importdb;    private $db;    private $sourcetable;    private $targettable;    private $addtosuid;    private $suid;    private $syid;    function __construct() {        global $db;        $this->db = $db;        $this->sourcetable = loadvar("databaseTablename");        $this->targettable = Config::dbSurvey();    }    function import() {        $importtype = loadvar(SETTING_IMPORT_TYPE);        switch ($importtype) {            case IMPORT_TYPE_NUBIS:                return $this->importNubis();            case IMPORT_TYPE_MMIC:                return $this->importMMIC();            case IMPORT_TYPE_BLAISE:                break;        }    }    function importNubis() {        $arr = $_FILES[SETTING_IMPORT_TEXT];        if (sizeof($arr) == 0) {            return Language::messageImportNoFile();        }                $name = $arr["name"];        if (!endsWith($name, EXPORT_FILE_NUBIS)) {            return Language::messageImportInvalidFile();        }        $str = file_get_contents($arr["tmp_name"]);        if ($str == "") {            return Language::messageImportInvalidFile();        }        $urid = $_SESSION['URID'];        $user = new User($urid);        if (loadvar(SETTING_IMPORT_AS) == IMPORT_TARGET_ADD) {            $surveys = new Surveys();            $this->addtosuid = $surveys->getMaximumSuid() + 1;        }        // replace        else if (loadvar(SETTING_IMPORT_AS) == IMPORT_TARGET_REPLACE) {            $this->addtosuid = 1;            /* delete existing content */            $tables = Common::surveyTables();            foreach ($tables as $table) {                $query = "delete from " . Config::dbSurvey() . $table;                $this->db->executeQuery($query);            }            /* delete existing data */            $tables = Common::surveyDataTables();            foreach ($tables as $table) {                if ($table == "_actions") {                    $query = "delete from " . Config::dbSurvey() . $table . " where suid != ''";                } else {                    $query = "delete from " . Config::dbSurvey() . $table;                }                $this->db->executeQuery($query);            }            /* delete test data */            $tables = Common::surveyTestDataTables();            foreach ($tables as $table) {                if ($table == "_actions") {                    $query = "delete from " . Config::dbSurvey() . $table . " where suid != ''";                } else {                    $query = "delete from " . Config::dbSurvey() . $table;                }                $this->db->executeQuery($query);            }        }        // add suid and urid        $str = str_ireplace(EXPORT_PLACEHOLDER_URID, $urid, $str);        $str = str_ireplace(EXPORT_PLACEHOLDER_SUID, $this->addtosuid, $str);        $queries = explode("\r\n", $str);        $tables = Common::surveyExportTables();        foreach ($queries as $q) {            $q = explode(EXPORT_DELIMITER, trim($q));            if (sizeof($q) != 3) {                continue;            }            if (!inArray($q[0], $tables)) {                continue;            }            //echo $q[1];            $fields = sizeof(explode(",", $q[1]));            $f = "";            for ($i = 0; $i < $fields; $i++) {                if ($f != "") {                    $f .= ",";                }                $f .= "?";            }            $query = IMPORT_STATEMENT_INSERT . ' ' . Config::dbSurvey() . $q[0] . " (" . $q[1] . ") " . IMPORT_STATEMENT_INSERT_VALUES . " (" . $f . ")";            $bp = new BindParam();            $fields2 = sizeof(explode(",", $q[2]));            if ($fields != $fields2) {                continue; // mismatch column count and value count            }            $it = explode(",", $q[2]);            for ($i = 0; $i < $fields2; $i++) {                $val = & prepareImportString($it[$i]);                $bp->add(MYSQL_BINDING_STRING, $val);                //echo 'adding: ' . $val . '----';            }            //echo print_r($bp->get()) . "<br/>";            $this->db->executeBoundQuery($query, $bp->get());        }        // prepare        set_time_limit(0);                // compile        $survey = new Survey($this->addtosuid);        $compiler = new Compiler($this->addtosuid, getSurveyVersion($survey));        // sections        $sections = $survey->getSections();        foreach ($sections as $section) {            $mess = $compiler->generateEngine($section->getSeid());                    }        $mess = $compiler->generateSections();                $mess = $compiler->generateVariableDescriptives();        $mess = $compiler->generateTypes();        $mess = $compiler->generateGetFills();        $mess = $compiler->generateSetFills();        $mess = $compiler->generateInlineFields();        $mess = $compiler->generateGroups();        $user = new User($_SESSION['URID']);        $mods = explode("~", $survey->getAllowedModes());        foreach ($mods as $m) {            $user->setLanguages($this->addtosuid, $m, $survey->getAllowedLanguages($m));        }        $user->saveChanges();        // return result        return "";    }    function importMMIC() {        set_time_limit(0);        $this->importdb = new Database();        $server = loadvar(SETTING_IMPORT_SERVER);        if ($server == "") {            $server = "localhost";        }        if ($this->importdb->connect($server, loadvar(SETTING_IMPORT_DATABASE), loadvar(SETTING_IMPORT_USER), loadvar(SETTING_IMPORT_PASSWORD)) == false) {            $display = new Display();            return $display->displayError(Language::messageToolsImportDbFailure());        }        $this->sourcetable = loadvar(SETTING_IMPORT_TABLE);        // add        if (loadvar(SETTING_IMPORT_AS) == IMPORT_TARGET_ADD) {            $surveys = new Surveys();            $this->addtosuid = $surveys->getMaximumSuid();        }        // replace         else if (loadvar(SETTING_IMPORT_AS) == IMPORT_TARGET_REPLACE) {            $this->addtosuid = 0;            /* delete existing content */            $tables = Common::surveyTables();            foreach ($tables as $table) {                $query = "delete from " . Config::dbSurvey() . $table;                $this->db->executeQuery($query);            }            /* delete existing data */            $tables = Common::surveyDataTables();            foreach ($tables as $table) {                if ($table == "_actions") {                    $query = "delete from " . Config::dbSurvey() . $table . " where suid != ''";                } else {                    $query = "delete from " . Config::dbSurvey() . $table;                }                $this->db->executeQuery($query);            }            /* delete test data */            $tables = Common::surveyTestDataTables();            foreach ($tables as $table) {                if ($table == "_actions") {                    $query = "delete from " . Config::dbSurvey() . $table . " where suid != ''";                } else {                    $query = "delete from " . Config::dbSurvey() . $table;                }                $this->db->executeQuery($query);            }        }        /* convert */        $this->convertSurveys();        // return result        return Language::messageToolsImportOK();    }    function convertSurveys() {        $query = "select * from " . $this->sourcetable . "_surveys order by syid";        if (!$res = $this->importdb->selectQuery($query)) {            $query = "select * from " . $this->sourcetable . "_survey order by syid";            $res = $this->importdb->selectQuery($query);        }        if ($res) {            if ($this->importdb->getNumberOfRows($res) > 0) {                while ($row = $this->importdb->getRow($res)) {                    $this->suid = $row["syid"] + $this->addtosuid;                    $this->syid = $row["syid"];                    $this->convertSurveySettings($row);                    $this->convertSections();                    $this->convertVariables();                    $this->convertRouting();                    $this->convertTypes();                }            }        }    }    function convertSurveySettings($row) {        $query = "replace into " . Config::dbSurvey() . "_surveys (suid, name, description) values (";        $query .= prepareDatabaseString($this->suid) . ",";        $query .= "'" . prepareDatabaseString($row["header"]) . "',";        $query .= "'')";        $this->db->executeQuery($query);        $query = "replace into " . Config::dbSurvey() . "_versions (suid, vnid, name, description) values (";        $query .= prepareDatabaseString($this->suid) . ",";        $query .= prepareDatabaseString(1) . ",";        $query .= "'Current',";        $query .= "'Current version')";        $this->db->executeQuery($query);        /* add default survey */        $setting = new Setting();        $setting->setSuid($this->suid);        $setting->setObject(USCIC_SURVEY);        $setting->setObjectType(OBJECT_SURVEY);        $setting->setName(SETTING_DEFAULT_SURVEY);        $setting->setValue($this->suid);        $setting->setMode(MODE_CASI); // dummy        $setting->setLanguage(1); // dummy        $setting->save();        /* add default mode */        $setting = new Setting();        $setting->setSuid($this->suid);        $setting->setObject(USCIC_SURVEY);        $setting->setObjectType(OBJECT_SURVEY);        $setting->setName(SETTING_DEFAULT_MODE);        $setting->setMode(MODE_CASI);        $setting->setLanguage(1); // dummy        $setting->setValue(MODE_CASI);        $setting->save();        /* add default language */        $setting = new Setting();        $setting->setSuid($this->suid);        $setting->setObject(USCIC_SURVEY);        $setting->setObjectType(OBJECT_SURVEY);        $setting->setName(SETTING_DEFAULT_LANGUAGE);        $setting->setMode(MODE_CASI);        $setting->setLanguage(1); // dummy        $setting->setValue(1); // english        $setting->save();    }    function convertSections() {        $query = "select meid as seid, name as name, parentmeid as pid, description as description, visible as hidden, qorder from " . $this->sourcetable . "_module where syid=" . $this->syid . " order by meid";//echo $query;        if ($res = $this->importdb->selectQuery($query)) {            if ($this->importdb->getNumberOfRows($res) > 0) {                while ($row = $this->importdb->getRow($res)) {                    $query = "replace into " . $this->targettable . "_sections (suid, seid, name, position, pid) values (";                    $query .= prepareDatabaseString($this->suid) . ",";                    $query .= prepareDatabaseString($row["seid"]) . ",";                    $query .= "'" . prepareDatabaseString($row["name"]) . "',";                    $query .= prepareDatabaseString($row["qorder"]) . ",";                    $query .= prepareDatabaseString($row["pid"]) . ")";                    $this->db->executeQuery($query);                    /* add rest as settings */                    $this->addSetting($row["seid"], OBJECT_SECTION, SETTING_DESCRIPTION, $row["description"]);                    $this->addSetting($row["seid"], OBJECT_SECTION, SETTING_HIDDEN, $row["hidden"]);                }            }        }    }    function convertVariables() {        $query = "select qnid as id, fullvariablename as variablename, questiontext as question, questiontype as answertype, description, answer as options, keep, arraysize, meid as seid, emptyallowed as requireanswer, visible as hidden, mmicparsetext as settings, fills, qorder from " . $this->sourcetable . "_question where syid=" . $this->syid . " order by qnid";        if ($res = $this->importdb->selectQuery($query)) {            if ($this->importdb->getNumberOfRows($res) > 0) {                while ($row = $this->importdb->getRow($res)) {                    $query = "replace into " . $this->targettable . "_variables (suid, vsid, seid, variablename, position) values (";                    $query .= prepareDatabaseString($this->suid) . ",";                    $query .= prepareDatabaseString($row["id"]) . ",";                    $query .= prepareDatabaseString($row["seid"]) . ",";                    $query .= "'" . prepareDatabaseString($row["variablename"]) . "',";                    $query .= prepareDatabaseString($row["qorder"]) . ")";                    $this->db->executeQuery($query);                    /* add rest as settings */                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_DESCRIPTION, $row["description"]);                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_QUESTION, $row["question"]);                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_ANSWERTYPE, $this->convertAnswerType($row["answertype"], $row["settings"]));                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_OPTIONS, $row["options"]);                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_REQUIREANSWER, $row["requireanswer"]);                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_KEEP, $row["keep"]);                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_ARRAY, $this->isArray($row["arraysize"]));                    $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_HIDDEN, $row["hidden"]);                    if (trim($row["fills"]) != "") {                        $t = $row["fills"];                        for ($i = 0; $i < 20; $i++) {                            $t = str_ireplace("\$Fill" . $i . "\$", VARIABLE_VALUE_FILL . $i, $t);                        }                        $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_FILLCODE, $t);                        $this->addSetting($row["id"], OBJECT_VARIABLEDESCRIPTIVE, SETTING_FILLTEXT, $row["question"]);                    }                    /* add settings */                    $this->convertSettings($row);                }                // update names                $updates = array(                    "update " . $this->targettable . "_variables set variablename='" . VARIABLE_BEGIN . "' where suid = " . $this->suid . " and variablename='tsstart'",                    "update " . $this->targettable . "_variables set variablename='" . VARIABLE_END . "' where suid = " . $this->suid . " and variablename='tsend'",                    "update " . $this->targettable . "_variables set variablename='" . VARIABLE_THANKS . "' where suid = " . $this->suid . " and variablename='thanks1'",                    "update " . $this->targettable . "_variables set variablename='" . VARIABLE_COMPLETED . "' where suid = " . $this->suid . " and variablename='completed1'"                );                foreach ($updates as $update) {                    $this->db->executeQuery($update);                }                // delete not needed                $deletes = array("intro1", "welcome1", "return1", "direct1", "finished1", "closed1", "timeout1", "browserinfo", "illegal1", "eligible1");                foreach ($deletes as $delete) {                    $query = "delete from " . $this->targettable . "_variables where suid = " . $this->suid . " and variablename='" . prepareDatabaseString($delete) . "'";                    $this->db->executeQuery($query);                }            }        }    }    function addSetting($object, $objecttype, $settingname, $value, $language = 1) {        $query = "replace into " . $this->targettable . "_settings (suid, object, objecttype, name, value, language, mode) values (";        $query .= prepareDatabaseString($this->suid) . ",";        $query .= prepareDatabaseString($object) . ",";        $query .= prepareDatabaseString($objecttype) . ",";        $query .= "'" . prepareDatabaseString($settingname) . "',";        $query .= "'" . prepareDatabaseString($value, false) . "',"; // allow for html/scripts        $query .= "'" . prepareDatabaseString($language) . "',";        $query .= "" . MODE_CASI . ")"; // interview mode: always assume web        $this->db->executeQuery($query);    }    function convertSettings($row, $objecttype = OBJECT_VARIABLEDESCRIPTIVE) {        $in = $row["answertype"];        /* process layout settings */        $settings = explode("\r\n", $row["settings"]);        foreach ($settings as $setting) {            if (startsWith($setting, "MMICJavascript")) {                $v = trim(str_ireplace("MMICJavascript(", "", $setting));                $v = substr($v, 0, strlen($v) - 1);                $this->addSetting($row["id"], $objecttype, SETTING_JAVASCRIPT_WITHIN_ELEMENT, $v, 1);            } else if (startsWith($setting, "MMICExtraJavascript")) {                $v = trim(str_ireplace("MMICExtraJavascript(", "", $setting));                $v = substr($v, 0, strlen($v) - 1);                /* strip script tags */                $pos = strpos($v, ">");                $v = substr($v, $pos + 1);                $pos = strrpos($v, "<");                $v = substr($v, 0, $pos);                /* add */                $this->addSetting($row["id"], $objecttype, SETTING_JAVASCRIPT_WITHIN_PAGE, $v, 1);            } else if (startsWith($setting, "MMICNoBack")) {                $this->addSetting($row["id"], $objecttype, SETTING_BACK_BUTTON, BUTTON_NO, 1);            } else if (startsWith($setting, "MMICNoNext")) {                $this->addSetting($row["id"], $objecttype, SETTING_NEXT_BUTTON, BUTTON_NO, 1);            } else if (startsWith($setting, "MMICShowDKButton")) {                $v = str_replace(")", "", str_replace("MMICShowDKButton(", "", $setting));                if (strtoupper($v) == "ON") {                    $this->addSetting($row["id"], $objecttype, SETTING_DK_BUTTON, BUTTON_YES, 1);                }            } else if (startsWith($setting, "MMICShowRFButton")) {                $v = str_replace(")", "", str_replace("MMICShowRFButton(", "", $setting));                if (strtoupper($v) == "ON") {                    $this->addSetting($row["id"], $objecttype, SETTING_RF_BUTTON, BUTTON_YES, 1);                }            } else if (startsWith($setting, "MMICShowUpdateButton")) {                $v = str_replace(")", "", str_replace("MMICShowUpdateButton(", "", $setting));                if (strtoupper($v) == "ON") {                    $this->addSetting($row["id"], $objecttype, SETTING_UPDATE_BUTTON, BUTTON_YES, 1);                }            } else if (startsWith($setting, "MMICHint")) {                $v = str_replace(")", "", str_replace("MMICHint(", "", $setting));                $va = explode('$Inputtype$', $v);                /* first one: can be before or after */                if (isset($va[0])) {                    /* before */                    if (strpos($v, trim($va[0])) < strpos($v, '$Inputtype$')) {                        $this->addSetting($row["id"], $objecttype, SETTING_PRETEXT, trim($va[0]), 1);                    }                    /* after */ else {                        $this->addSetting($row["id"], $objecttype, SETTING_POSTTEXT, trim($va[0]), 1);                    }                }                /* second one, so must be after */                if (isset($va[1])) {                    $this->addSetting($row["id"], $objecttype, SETTING_POSTTEXT, trim($va[1]), 1);                }            }        }        switch ($in) {            case 1://string                /* add setting for max length */                if ($row["options"] != "") {                    $this->addSetting($row["id"], $objecttype, SETTING_MAXIMUM_LENGTH, $row["options"], 1);                }                break;            case 2://integer                break;            case 3://range                /* add settings for min and max */                $r = explode("..", $row["options"]);                $this->addSetting($row["id"], $objecttype, SETTING_MINIMUM_RANGE, $r[0], 1);                $this->addSetting($row["id"], $objecttype, SETTING_MAXIMUM_RANGE, $r[1], 1);            case 4://enumerated                break;            case 5://set of enumerated                /* check and add minimum/maximum selected settings */                $settings = explode("\r\n", $row["settings"]);                foreach ($setting as $setting) {                    if (startsWith($setting, "MMICMinimumSetSize")) {                        $min = str_replace(")", "", str_replace("MMICMinimumSetSize(", "", $setting));                        $this->addSetting($row["id"], $objecttype, SETTING_MINIMUM_SELECTED, $min, 1);                    } else if (startsWith($setting, "MMICMaximumSetSize")) {                        $max = str_replace(")", "", str_replace("MMICMaximumSetSize(", "", $setting));                        $this->addSetting($row["id"], $objecttype, SETTING_MAXIMUM_SELECTED, $max, 1);                    } else if (startsWith($setting, "MMICInvalidSubSets")) {                        $subs = str_replace(")", "", str_replace("MMICInvalidSubSets(", "", $setting));                        $subs = str_replace("[", "", $subs);                        $subs = str_replace("]", "", $subs);                        $subs = str_replace(",", ";", $subs);                        $subs = str_replace("~", ",", $subs);                        $this->addSetting($row["id"], SETTING_INVALIDSUB_SELECTED, $subs, 1);                    } else if (startsWith($setting, "MMICInvalidSets")) {                        $subs = str_replace(")", "", str_replace("MMICInvalidSets(", "", $setting));                        $subs = str_replace("[", "", $subs);                        $subs = str_replace("]", "", $subs);                        $subs = str_replace(",", ";", $subs);                        $subs = str_replace("~", ",", $subs);                        $this->addSetting($row["id"], $objecttype, SETTING_INVALID_SELECTED, $subs, 1);                    }                }            case 6://open                /* add setting for max length */                if ($row["options"] != "") {                    $this->addSetting($row["id"], $objecttype, SETTING_MAXIMUM_LENGTH, $row["options"], 1);                }                break;            case 7://real                break;            case 8://no input                break;            case 9://module                break;            case 10://datetype                break;            case 11://timetype                break;        }        /* button settings */        $settings = explode("\r\n", $row["settings"]);        foreach ($setting as $setting) {            if (startsWith($setting, "MMICShowDKButton")) {                $on = str_replace(")", "", str_replace("MMICShowDKButton(", "", $setting));                if ($on == "on") {                    $this->addSetting($row["id"], $objecttype, SETTING_DK_BUTTON, BUTTON_YES, 1);                } else if ($on == "off") {                    $this->addSetting($row["id"], $objecttype, SETTING_DK_BUTTON, BUTTON_NO, 1);                }            } else if (startsWith($setting, "MMICShowRFButton")) {                $on = str_replace(")", "", str_replace("MMICShowRFButton(", "", $setting));                if ($on == "on") {                    $this->addSetting($row["id"], $objecttype, SETTING_RF_BUTTON, BUTTON_YES, 1);                } else if ($on == "off") {                    $this->addSetting($row["id"], $objecttype, SETTING_RF_BUTTON, BUTTON_NO, 1);                }            } else if (startsWith($setting, "MMICShowUpdateButton")) {                $on = str_replace(")", "", str_replace("MMICShowUpdateButton(", "", $setting));                if ($on == "on") {                    $this->addSetting($row["id"], $objecttype, SETTING_UPDATE_BUTTON, BUTTON_YES, 1);                } else if ($on == "off") {                    $this->addSetting($row["id"], $objecttype, SETTING_UPDATE_BUTTON, BUTTON_NO, 1);                }            } else if (startsWith($setting, "MMICNoBack")) {                $this->addSetting($row["id"], $objecttype, SETTING_BACK_BUTTON, BUTTON_NO, 1);            } else if (startsWith($setting, "MMICNoNext")) {                $this->addSetting($row["id"], $objecttype, SETTING_NEXT_BUTTON, BUTTON_NO, 1);            }        }    }    function convertAnswerType($in, $settings) {        switch ($in) {            case 1://string                                   return ANSWER_TYPE_STRING;            case 2://integer                return ANSWER_TYPE_INTEGER;            case 3://range                                return ANSWER_TYPE_RANGE;            case 4://enumerated                if (contains($settings, "MMICComboBox")) {                    return ANSWER_TYPE_DROPDOWN;                }                return ANSWER_TYPE_ENUMERATED;            case 5://set of enumerated                                if (contains($settings, "MMICList")) {                    return ANSWER_TYPE_MULTIDROPDOWN;                }                return ANSWER_TYPE_SETOFENUMERATED;            case 6://open                                return ANSWER_TYPE_OPEN;            case 7://real                return ANSWER_TYPE_DOUBLE;            case 8://no input                return ANSWER_TYPE_NONE;            case 9://module                return ANSWER_TYPE_SECTION;            case 10://datetype                return ANSWER_TYPE_DATE;            case 11://timetype                return ANSWER_TYPE_TIME;        }    }    function isArray($size) {        if ($size > 0) {            return 1;        }        return 0;    }    function convertRouting() {        $query = "select meid as seid, rules from " . $this->sourcetable . "_module where syid=" . $this->syid . " order by meid";        if ($res = $this->importdb->selectQuery($query)) {            if ($this->importdb->getNumberOfRows($res) > 0) {                global $db;                while ($row = $this->importdb->getRow($res)) {                    $rules = explode("\r\n", $row["rules"]);                    $cnt = 1;                    foreach ($rules as $rule) {                        $query = "replace into " . $this->targettable . "_routing (suid, seid, rgid, rule) values (";                        $query .= prepareDatabaseString($this->suid) . ",";                        $query .= prepareDatabaseString($row["seid"]) . ",";                        $query .= prepareDatabaseString($cnt) . ",";                        $query .= "'" . prepareDatabaseString($rule) . "')";                        $this->db->executeQuery($query);                        $cnt++;                    }                }                $query = "select * from " . $this->targettable . "_routing where suid=" . $this->suid . " and trim(rule) like 'begincombine%' order by rgid asc";                if ($res = $this->db->selectQuery($query)) {                    $survey = new Survey($this->suid);                    if ($this->db->getNumberOfRows($res) > 0) {                        while ($row = $this->db->getRow($res)) {                            $rule = trim($row["rule"]);                            $line = trim(str_replace(")", "", substr($rule, strpos($rule, "(") + 1)));                            if ($line == "") {                                $line = "shortcombinegroup";                            }                            $query = "update " . $this->targettable . "_routing set rule='group." . $line . "' where suid=" . $this->suid . " and seid=" . $row["seid"] . " and rgid=" . $row["rgid"];                            //echo $query;                            $this->db->executeQuery($query);                            /* add group */                            if ($line != "") {                                $exgr = $survey->getGroupByName($line);                                if ($exgr->getGid() == "") {                                    $group = new Group();                                    $group->setSuid($this->suid);                                    $group->setName($line);                                    $group->save();                                }                            }                        }                    }                }                $query = "select * from " . $this->targettable . "_routing where suid=" . $this->suid . " and rule like 'jumpback(%' order by rgid asc";                if ($res = $this->db->selectQuery($query)) {                    if ($this->db->getNumberOfRows($res) > 0) {                        //echo 'jjjj';                        while ($row = $db->getRow($res)) {                            $line = str_replace(")", "", substr($row["rule"], strpos($row["rule"], "(") + 1));                            $query = "update " . $this->targettable . "_routing set rule='moveBackward." . $line . "' where suid=" . $this->suid . " and seid=" . $row["seid"] . " and rgid=" . $row["rgid"];                            //echo $query;                            $this->db->executeQuery($query);                        }                    }                }                $query = "select * from " . $this->targettable . "_routing where suid=" . $this->suid . " and rule like 'jump(%' order by rgid asc";                if ($res = $this->db->selectQuery($query)) {                    if ($this->db->getNumberOfRows($res) > 0) {                        //echo 'aaaaj';                        while ($row = $this->importdb->getRow($res)) {                            $line = str_replace(")", "", substr($row["rule"], strpos($row["rule"], "(") + 1));                            $query = "update " . $this->targettable . "_routing set rule='moveForward." . $line . "' where suid=" . $this->suid . " and seid=" . $row["seid"] . " and rgid=" . $row["rgid"];                            //echo $query;                            $this->db->executeQuery($query);                        }                    }                }                $query = "update " . $this->targettable . "_routing set rule='endgroup' where suid=" . $this->suid . " and trim(rule)='endcombine'";                $this->db->executeQuery($query);            }        }    }    function convertTypes() {        $query = "select teid as id, name as name, questiontype as answertype, answer as options from " . $this->sourcetable . "_type where syid=" . $this->syid . " order by teid";        if ($res = $this->importdb->selectQuery($query)) {            if ($this->importdb->getNumberOfRows($res) > 0) {                while ($row = $this->importdb->getRow($res)) {                    $query = "replace into " . $this->targettable . "_types (suid, tyd, name) values (";                    $query .= prepareDatabaseString($this->suid) . ",";                    $query .= prepareDatabaseString($row["id"]) . ",";                    $query .= "'" . prepareDatabaseString($row["name"]) . "')";                    $this->db->executeQuery($query);                    /* add rest as settings */                    $this->addSetting($row["id"], OBJECT_TYPE, SETTING_ANSWERTYPE, $this->convertAnswerType($row["answertype"], $row["settings"]));                    $this->addSetting($row["id"], OBJECT_TYPE, SETTING_OPTIONS, $row["options"]);                    /* add usage in variables */                    $query = "select * from " . $this->targettable . "_settings where suid=" . $this->suid . " and name='" . SETTING_OPTIONS . "' and objecttype=" . OBJECT_VARIABLEDESCRIPTIVE . " and value='" . $row["name"] . "'";//                    echo $query;                    $res1 = $this->db->selectQuery($query);                    if ($res1) {                        //echo 'found some for ' . $row["tyd"] . "-----" . $query . "<br/>";                        if ($this->db->getNumberOfRows($res1) > 0) {                            while ($row1 = $this->db->getRow($res1)) {                                $q = "update " . $this->targettable . "_variables set tyd=" . $row["id"] . " where suid=" . $this->suid . " and vsid=" . $row1["object"];                                $this->db->executeQuery($q);                                // remove options in settings for variable, so it does not override the type's options                                $q = "delete from " . $this->targettable . "_settings where suid=" . $this->suid . " and object=" . $row1["object"] . " and name='" . SETTING_OPTIONS . "' and objecttype=" . OBJECT_VARIABLEDESCRIPTIVE;                                $this->db->executeQuery($q);                            }                        }                    }                    /* add settings */                    $this->convertSettings($row, OBJECT_TYPE);                }                // update answer types                $updates = array(                    "update " . $this->targettable . "_settings set value=" . $this->convertAnswerType(3) . " where suid=" . $this->suid . " and objecttype=" . OBJECT_TYPE . " and name='" . SETTING_ANSWERTYPE . "' and value=3",                    "update " . $this->targettable . "_settings set value=" . $this->convertAnswerType(4) . " where suid=" . $this->suid . " and objecttype=" . OBJECT_TYPE . " and name='" . SETTING_ANSWERTYPE . "' and value=4",                    "update " . $this->targettable . "_settings set value=" . $this->convertAnswerType(5) . " where suid=" . $this->suid . " and objecttype=" . OBJECT_TYPE . " and name='" . SETTING_ANSWERTYPE . "' and value=5",                    "update " . $this->targettable . "_settings set value=" . $this->convertAnswerType(6) . " where suid=" . $this->suid . " and objecttype=" . OBJECT_TYPE . " and name='" . SETTING_ANSWERTYPE . "' and value=6"                );                foreach ($updates as $update) {                    //echo $update;                    $this->db->executeQuery($update);                }            }        }    }}?>