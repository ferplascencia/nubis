<?php

/*
  ------------------------------------------------------------------------
  Copyright (C) 2014 Bart Orriens, Albert Weerman

  This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
  ------------------------------------------------------------------------
 */

/* check for 'javascript enabled' indicator */
isJavascriptEnabled();

/* set path */
setPath();

/* database */
require_once('database.php');
global $loaded;
$db = new Database();
if ($db->getDb() == null) { //no connection with DB.. Errormessage!     
    if ($_SESSION['SYSTEM_ENTRY'] != USCIC_SMS) {
        if (file_exists("error.html")) {
            $contents = file_get_contents("error.html");
            if ($contents != "") {
                echo $contents;
                exit;
            }
        }
        echo "<html><body><font face=arial>System not available!</font></body></html>";
        exit;
    }
    else {
        
        // in SMS mode and no correct config, then we run install        
        if ($loaded == 2) {
            require_once('install.php');        
            $install = new Install(loadvar('p'));
            exit;
        }
        else if ($loaded == 1) {
            $contents = file_get_contents("errorsms.html");
            if ($contents != "") {
                echo str_replace('$Error$', 'NubiS could not locate its configuration file (conf.php).', $contents);
                exit;
            }
            echo "<html><body><font face=arial>NubiS could not locate its configuration file (conf.php).</font></body></html>";
            exit;
        }
        else {
            $contents = file_get_contents("errorsms.html");
            if ($contents != "") {
                echo str_replace('$Error$', 'NubiS could not access the database. <br/>Please verify your configuration settings in the conf.php file.', $contents);
                exit;
            }
            echo "<html><body><font face=arial>NubiS could not access the database. Please verify your configuration settings in the conf.php file.</font></body></html>";
            exit;
        }
    }
}

ini_set("error_reporting", "ALL");

/* startup */
require_once('action.php');
require_once('login.php');

/* SMS admin extensions */
if ($_SESSION['SYSTEM_ENTRY'] == USCIC_SMS) {
    require_once('sms.php');
    require_once('sysadmin.php');
    require_once("compiler.php");
    require_once("checker.php");
    require_once("track.php");
    require_once('supervisor.php');
    require_once('lab.php');
    require_once('nurse.php');
    require_once('translator.php');
    require_once('researcher.php');    
}

/* SMS admin and survey extensions */
if (Common::smsUsage()) {
    require_once('interviewer.php');
    require_once('remarks.php');    
}

if (isTestmode() || Common::smsUsage()) {
    require_once('user.php');
    require_once('users.php');
    require_once('tester.php');
}

/* core objects */
require_once('object.php');
require_once('component.php');
require_once('basicengine.php');
require_once('basicfill.php');
require_once('basicinlinefield.php');
require_once('variable.php');
require_once('variabledescriptive.php');
require_once('setting.php');
require_once('progressbar.php');
require_once('section.php');
require_once('type.php');
require_once('group.php');
require_once('state.php');
require_once('logaction.php');
require_once('logactions.php');
require_once('datarecord.php');
require_once('survey.php');
require_once('surveys.php');
require_once("languagebase.php");

/* SMS admin and survey extensions */
if (Common::smsUsage()) {
    require_once('households.php');
    require_once('household.php');
    require_once('respondents.php');
    require_once('respondent.php');
}


/* core display */
require_once('display/display.php');
require_once('display/displaylogin.php');
require_once('display/displayquestion.php');
require_once('templates/default.php');
require_once('templates/tabletemplate.php');
require_once('templates/multicolumntable.php');

/* core SMS in survey display */
require_once('display/displayrespondent.php'); // only core if SMS is used
require_once('display/displayinterviewer.php'); // only core if SMS is used
require_once('display/displaytester.php'); // only core if SMS is used

/* error checking */
require_once("errorcheck.php");
require_once("errorchecks.php");

/* answer type add-ons */
require_once('gps.php');
require_once('customfunctions.php');
require_once('customanswertypes.php');

/* SMS extensions */
if ($_SESSION['SYSTEM_ENTRY'] == USCIC_SMS) {
    require_once('display/displayloginsms.php');
    require_once('display/displaysysadmin.php');
    require_once('display/displayoutput.php');
    require_once('display/displayusers.php');
    require_once('display/displaysupervisor.php');
    require_once('display/displaytranslator.php');
    require_once('display/displaysearch.php');
    require_once('display/displaysms.php');
    //require_once('display/displaynurse.php');
    require_once('display/displayresearcher.php');    
    require_once("data.php");
    require_once('dataexport.php');
    require_once('communication.php');
}

/* SMS admin and survey extensions */
if (Common::smsUsage()) {
    require_once('psu.php');
    require_once('psus.php');
    require_once('proxypermission.php');    
}

if (isTestmode() || Common::smsUsage()) {
    require_once('contact.php');
    require_once('contacts.php');    
}


/* check for execution mode */
if (inArray(loadvar(POST_PARAM_SURVEY_EXECUTION_MODE), array(SURVEY_EXECUTION_MODE_NORMAL, SURVEY_EXECUTION_MODE_TEST))) {
    $_SESSION[SURVEY_EXECUTION_MODE] = loadvar(POST_PARAM_SURVEY_EXECUTION_MODE);
}

if (!isset($_SESSION[SURVEY_EXECUTION_MODE])) {
    $_SESSION[SURVEY_EXECUTION_MODE] = SURVEY_EXECUTION_MODE_NORMAL; // by default normal mode
}

// set timezone
date_default_timezone_set(Config::timezone());
$logActions = new LogActions();

/* global variables */
$suid = getSurvey();
$survey = new Survey($suid);

/* set the template for the questions display */
require_once('displayquestionsms.php');
require_once('displayquestiontest.php');
require_once('displayquestionnurse.php');


$mode = null; // wait with calling this until later!
$modechange = null;
$version = null; // wait with calling this until later!
$language = null; // wait with calling this until later!
$languagechange = null;
$template= null; // wait with calling this until later!
$templatechange = null;
$currentseid = null;
$currentmainseid = null;
$baseseid = null;
$defaultlanguage = null; //getDefaultSurveyLanguage();
$defaultmode = null; //getDefaultSurveyMode();

/* testing stuff */
//$queries = array();

?>
