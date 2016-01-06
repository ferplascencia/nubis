<?php

/*
  ------------------------------------------------------------------------
  Copyright (C) 2014 Bart Orriens, Albert Weerman

  This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
  ------------------------------------------------------------------------
 */

require_once("../constants.php");
require_once("../functions.php");
require_once("../dbConfig.php");
require_once("../config.php");
require_once("../globals.php");
require_once("../user.php");
require_once('reportissue.php');
require_once('watchwindow.php');
require_once('jumpback.php');

if (loadvar('r') != '') {
    getSessionParamsPost(loadvar('r'));
}

// include language
$_SESSION['SYSTEM_ENTRY'] = USCIC_SMS;
$l = getSMSLanguage();
if (file_exists("language/language" . getSMSLanguagePostFix($l) . ".php")) {
    require_once('language_' . getSMSLanguagePostFix($l) . '.php');
} else {
    require_once('language_en.php'); // fall back on english language  file
}
$_SESSION['SYSTEM_ENTRY'] = USCIC_SURVEY; // switch back to survey


$page = getFromSessionParams('testpage');

$_SESSION[SURVEY_EXECUTION_MODE] = SURVEY_EXECUTION_MODE_TEST;
switch ($page) {
    case "watch":
        $watch = new Watcher();
        $watch->watch();        
        break;
    case "report":
        $reportissue = new ReportIssue();
        $reportissue->report();
        break;
    case "reportRes":
        $reportissue = new ReportIssue();
        $reportissue->reportRes();
        break;
    case "jumpback":        
        $jumper = new JumpBack();
        $jumper->jump();
        break;
    case "jumpbackRes":        
        $jumper = new JumpBack();
        $jumper->jumpRes();
        break;
    default:
        //$reportissue->report();
        break;
}
$_SESSION[SURVEY_EXECUTION_MODE] = SURVEY_EXECUTION_MODE_NORMAL;

?>
