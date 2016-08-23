<?php

/*
  ------------------------------------------------------------------------
  Copyright (C) 2014 Bart Orriens, Albert Weerman

  This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
  ------------------------------------------------------------------------
 */

class DisplayQuestionBasic extends Display {

    protected $primkey;
    protected $state;
    protected $errormessages;
    protected $engine;
    protected $subs;
    protected $language;
    protected $padding;
    protected $datatables;
    private $lastparse;
    private $showheader;
    private $showfooter;
    private $queryvariables;
    private $showdk;
    private $dkrfna;
    private $dkrfnainline;
    private $combobox;
    var $inlineeditable;
    var $inlineediticon;

    function __construct($prim, $engine) {
        $this->primkey = $prim;
        $this->engine = $engine;
        $this->errorchecks = array();
        $this->errormessages = array();
        $this->lastparse = true;
        $this->showheader = true;
        $this->showfooter = true;
        $this->inlineeditable = '';
        $this->inlineediticon = '';
        $this->combobox = false;
    }

    function getEngine() {
        return $this->engine;
    }

    function useDataTables() {
        $this->datatables = true;
    }

    /* START INLINE EDITING */

    function enableInlineEditable() {
        $this->inlineeditable = " uscic-inline-editable ";
        $this->inlineediticon = ''; //"<span class='pull-right glyphicon glyphicon-pencil uscic-inline-editable-icon'>&nbsp;</span>";        
    }

    function getInlineEditIcon() {
        return $this->inlineediticon;
    }

    /* END INLINE EDITING */

    function showSubGroupQuestions($group, $parentgroup) {

        // extract group number
        $explode = explode(".", $group);
        $groupnumber = explode("_", $explode[1]);

        // find sub display for group
        $sub = $this->subs[$groupnumber[1]];
        $vars = $sub["variables"];
        $template = $sub["template"];

        // get real variable names in subgroup
        $realvariablenames = $this->getRealVariables(explode("~", $vars));
        //echo "template: " . $template . '---' . $realvariablenames . "<br/>";
        // get display for sub group
        return $this->showQuestions($vars, $realvariablenames, $template, $parentgroup);
    }

    function showQuestions($variablenames, $realvariablenames, $groupname = "", $parentgroup = null) {

        /*
         * 
         * group
          intro
          subgroup
          q1
          q2
          subgroup
          q3
          endsubgroup
          endsubgroup

          endgroup
         * 
         */

        // strip preceding ~ (in case of group statements)
        if (startsWith($variablenames, "~")) {
            $variablenames = substr($variablenames, 1);
        }
        $variables = explode("~", $variablenames);
        if (startsWith($realvariablenames, "~")) {
            $realvariablenames = substr($realvariablenames, 1);
        }
        $realvariables = explode("~", $realvariablenames);

        /* no template specified */
        $group = null;
        if (trim($groupname) == "") {
            $group = new Group();
            $template = "default";
            $options = "";
        } else {

            $groupname = strtolower($groupname);
            //echo $groupname . '----' . $this->engine->replaceFills($groupname);
            $group = $this->engine->getGroup($this->engine->replaceFills($groupname));
            $template = $group->getTemplate();

            // specified template not found, then use default for display
            if ($template != TABLE_TEMPLATE_CUSTOM && file_exists(getBase() . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $template . '.php') == false) {
                $template = "default";
            }
        }

        /* handle display */
        $returnStr = "";

        /* allowed template and definition present */
        if (file_exists(getBase() . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $template . '.php')) {
            require_once($template . '.php');
            try {
                $class = new ReflectionClass($template . "Template");
                if ($class) {
                    if ($class->hasMethod(FUNCTION_SHOW)) {
                        if ($parentgroup == null) {
                            $parentgroup = new Group();
                        }
                        $group->setParentGroup($parentgroup);
                        $instance = $class->newInstanceArgs(array($this->engine, $group));
                        if ($instance) {
                            $method = $class->getMethod(FUNCTION_SHOW);
                            $returnStr .= $method->invokeArgs($instance, array($variables, $realvariables, $this->language));
                            $success = true;
                        }
                    }
                }
            } catch (Exception $e) {
                
            }
        }

        /* return result */
        return $returnStr;
    }

    function displayBody() {
        $returnStr = '';
        if (getSurveyModeAllowChange() == MODE_CHANGE_RESPONDENT_ALLOWED) {
            $returnStr .= $this->showMode();
        }
        if (getSurveyLanguageAllowChange() == LANGUAGE_CHANGE_RESPONDENT_ALLOWED) {
            $returnStr .= $this->showLanguage();
        }
        //$returnStr .= '<link href="css/uscic.css" type="text/css" rel="stylesheet">';
        $returnStr .= '<form id="form" role="form" method="post" autocapitalize="off" spellcheck="false" autocorrect="off" autocomplete="off">';

        if ($this->padding) {
            $returnStr .= '<div class="uscic-wrap-with-sms">';
        } else {
            $returnStr .= '<div class="uscic-wrap">';
        }
        //$returnStr .= $header;
        $returnStr .= '<div class="container"><p>';
        $returnStr .= "<input type=hidden id=navigation name=navigation>";
        $returnStr .= '<div id="uscic-mainpanel" class="panel panel-default uscic-mainpanel">';
        $returnStr .= '<div id="uscic-mainbody" class="panel-body">';
        return $returnStr;
    }

    function showQuestion($variablenames, $rgid, $groupname = "") {

        // get language
        $this->language = getSurveyLanguage();

        // get sub display info
        $this->subs = $this->engine->getSubDisplays();

        // strip preceding ~ (in case of group statements)
        if (startsWith($variablenames, "~")) {
            $variablenames = substr($variablenames, 1);
        }

        /* filter out any subgroups so we get the real variable names only */
        $variables = explode("~", $variablenames);
        $realvariables = $this->getRealVariables($variables);
        $queryvariables = explode("~", $realvariables);
        $this->queryvariables = $queryvariables;

        /* determine query object that holds settings */
        //if (sizeof($queryvariables) == 1) {
        if (trim($groupname) == "") { // go to variable only if not a group name, otherwise assume group object
            $queryobject = $this->engine->getVariableDescriptive($queryvariables[0]);
        } else {
            $queryobject = $this->engine->getGroup($groupname);
        }

        /* header and body start */
        global $survey;

        /* add post parameters */
        $returnStr = setSessionParamsPost(array(SESSION_PARAM_LASTACTION => $this->engine->getLastSurveyAction(), SESSION_PARAM_SURVEY => $survey->getSuid(), SESSION_PARAM_PRIMKEY => $this->primkey, SESSION_PARAM_RGID => $rgid, SESSION_PARAM_VARIABLES => $realvariables, SESSION_PARAM_GROUP => $groupname, SESSION_PARAM_LANGUAGE => getSurveyLanguage(), SESSION_PARAM_MODE => getSurveyMode(), SESSION_PARAM_TEMPLATE => getSurveyTemplate(), SESSION_PARAM_VERSION => getSurveyVersion(), SESSION_PARAM_TIMESTAMP => time(), SESSION_PARAM_SEID => $this->engine->getSeid(), SESSION_PARAM_MAINSEID => $this->engine->getMainSeid()));

        /* determine variable objects */
        $varobjects = array();
        $sectionheaders = array();
        $sectionfooters = array();
        $pagejavascript = array();
        $scriptarray = array();

        //if (sizeof($queryvariables) == 1) {
        if (trim($groupname) == "") {
            if ($queryobject->isShowSectionHeader()) {
                $section = $this->engine->getSection($queryobject->getSeid());
                $sectionheaders[$queryobject->getSeid()] = $this->engine->replaceFills($section->getHeader());
            }
            if ($queryobject->isShowSectionFooter()) {
                $section = $this->engine->getSection($queryobject->getSeid());
                $sectionfooters[$queryobject->getSeid()] = $this->engine->replaceFills($section->getFooter());
            }
        } else {

            foreach ($queryvariables as $a) {
                $var = $this->engine->getVariableDescriptive($a);
                $varobjects[] = $var;
                $pagejavascript[] = $this->engine->getFill($a, $var, SETTING_JAVASCRIPT_WITHIN_PAGE);
                $scriptarray[] = $this->engine->getFill($var->getName(), $var, SETTING_SCRIPTS);
                $scriptarray[] = $this->engine->getFill($var->getName(), $var, SETTING_STYLE_WITHIN_PAGE);
            }

            foreach ($varobjects as $a) {
                if ($a->isShowSectionHeader()) {
                    $section = $this->engine->getSection($a->getSeid());
                    $sectionheaders[$a->getSeid()] = $this->engine->replaceFills($section->getHeader());
                }
            }
            foreach ($varobjects as $a) {
                if ($a->isShowSectionFooter()) {
                    $section = $this->engine->getSection($a->getSeid());
                    $sectionfooters[$a->getSeid()] = $this->engine->replaceFills($section->getFooter());
                }
            }
        }

        $sectionheaders = array_unique($sectionheaders);
        foreach ($sectionheaders as $k => $v) {
            if (trim($v) == "") {
                unset($sectionheaders[$k]);
            }
        }
        $sectionfooters = array_unique($sectionfooters);
        foreach ($sectionfooters as $k => $v) {
            if (trim($v) == "") {
                unset($sectionfooters[$k]);
            }
        }

        /* add section header(s) */
        if (sizeof($sectionheaders) > 0) {

            $returnStr .= "<div id='uscic-section-headers'>";
            foreach ($sectionheaders as $k => $h) {
                $returnStr .= '<div id="seid_' . $k . '" uscic-texttype="' . SETTING_PAGE_HEADER . '" class="' . $this->inlineeditable . '">';
                $returnStr .= $h;
                $returnStr .= "</div>";
            }
            //$returnStr .= implode("", $sectionheaders);
            $returnStr .= "</div>";
        }

        /* error display at top */
        if ($queryobject && $queryobject->getErrorPlacement() == ERROR_PLACEMENT_AT_TOP) {
            $returnStr .= "<div id='uscic-errors' class='form-group'></div>";
        }


        /* determine what needs to be included or not */
        $this->showdk = false;
        $this->dkrfna = false;
        $this->dkrfnainline = false;
        $doempty = false;
        $doerror = false;
        $screendumps = false;
        $ifempty = null;
        $iferror = null;
        $inputmasking = false;
        $keyboardbinding = false;
        $paradata = false;

        // no group
        if (trim($groupname) == "") {
            $pagejavascript[] = $this->engine->getFill($queryobject->getName(), $queryobject, SETTING_JAVASCRIPT_WITHIN_PAGE);
            $scriptarray[] = $this->engine->getFill($queryobject->getName(), $queryobject, SETTING_SCRIPTS);
            $scriptarray[] = $this->engine->getFill($queryobject->getName(), $queryobject, SETTING_STYLE_WITHIN_PAGE);
            $td = $queryobject->getIfEmpty();
            if ($td != IF_EMPTY_ALLOW) {
                $doempty = true;
                $ifempty = $td;
                $returnStr .= "<input type=hidden id=em name=em value=0>";
            }
            $td = $queryobject->getIfError();
            if ($td != IF_ERROR_ALLOW) {
                $doerror = true;
                $iferror = $td;
                $returnStr .= "<input type=hidden id=er name=er value=0>";
            }
            $inputmasking = $queryobject->isInputMaskEnabled();
            $keyboardbinding = $queryobject->isKeyboardBindingEnabled();
            $screendumps = $queryobject->isScreendumpStorage();
            $paradata = $queryobject->isParadata();
            $this->dkrfna = $queryobject->isIndividualDKRFNA();
            $this->dkrfnainline = $queryobject->isIndividualDKRFNAInline();
            if (!inArray($queryobject->getAnswerType(), array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION))) {
                $this->showdk = true;
            }
        }
        // get group object
        else {

            $paradata = $queryobject->isParadata();
            $screendumps = $queryobject->isScreendumpStorage();

            foreach ($varobjects as $a) {
                if (!inArray($a->getAnswerType(), array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION))) {
                    $this->showdk = true;
                    break;
                }
            }

            foreach ($varobjects as $a) {
                $t = $a->getAnswerType();
                if (!inArray($t, array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION)) && $a->isInputMaskEnabled() == true) {
                    $inputmasking = true;
                    break;
                }
            }
            $this->dkrfna = $queryobject->isIndividualDKRFNA();
            $this->dkrfnainline = $queryobject->isIndividualDKRFNAInline();
            $keyboardbinding = $queryobject->isKeyboardBindingEnabled();
            foreach ($varobjects as $a) {
                $t = $a->getAnswerType();
                if (!inArray($t, array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION)) && $a->getIfEmpty() != IF_EMPTY_ALLOW) {
                    $doempty = true;
                    break;
                }
            }

            foreach ($varobjects as $a) {
                $t = $a->getAnswerType();
                if (!inArray($t, array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION)) && $a->getIfError() != IF_ERROR_ALLOW) {
                    $doerror = true;
                    break;
                }
            }

            $ifempty = IF_EMPTY_WARN;
            foreach ($varobjects as $a) {
                $t = $a->getAnswerType();
                if (!inArray($t, array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION)) && $a->getIfEmpty() == IF_EMPTY_NOTALLOW) {
                    $ifempty = IF_EMPTY_NOTALLOW;
                    break;
                }
            }

            $iferror = IF_ERROR_WARN;
            foreach ($varobjects as $a) {
                $t = $a->getAnswerType();
                if (!inArray($t, array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION)) && $a->getIfError() == IF_ERROR_NOTALLOW) {
                    $iferror = IF_ERROR_NOTALLOW;
                    break;
                }
            }

            /* check group setting for group error checking */
            $td = $queryobject->getIfError();
            if ($td == IF_ERROR_NOTALLOW) {
                $iferror = IF_ERROR_NOTALLOW;
            }
            if ($td != IF_ERROR_ALLOW) {
                $doerror = true;
            }

            if ($doempty) {
                $returnStr .= "<input type=hidden id=em name=em value=0>";
            }
            if ($doerror) {
                $returnStr .= "<input type=hidden id=er name=er value=0>";
            }

            /* TODO: add any group page javascripts and external scripts */
            //$pagejavascript[] = $this->engine->getFill($a->getName(), $a, SETTING_JAVASCRIPT_WITHIN_PAGE);
        }

        /* process dk/rf/na so we have it when showing questions */
        if ($this->showdk == true) {

            // see if any dk/rf/na
            foreach ($queryvariables as $q) {
                $this->engine->processAnswer($q);
            }
        }

        /* question and answer display */
        //echo $realvariables . '||||';
        $this->engine->determineDisplayNumbers($realvariables);
        if (sizeof($queryvariables) > 1) {
            $returnStr .= $this->showQuestions($variablenames, $realvariables, $groupname);
        } else {
            $returnStr .= $this->showQuestions($variablenames, $realvariables, $groupname);
        }

        /* error display at bottom */
        if ($queryobject && $queryobject->getErrorPlacement() == ERROR_PLACEMENT_AT_BOTTOM) {
            $returnStr .= "<div id=uscic-errors class='form-group'></div>";
        }

        /* add section footer(s) */
        if (sizeof($sectionfooters) > 0) {
            $returnStr .= "<div id='uscic-section-footers'>";
            foreach ($sectionfooters as $k => $h) {
                $returnStr .= '<div id="seid_' . $k . '" uscic-texttype="' . SETTING_PAGE_FOOTER . '" class="' . $this->inlineeditable . '">';
                $returnStr .= $h;
                $returnStr .= "</div>";
            }
            //$returnStr .= implode("", $sectionfooters);
            $returnStr .= "</div>";
        }

        /* button panel */
        $qa = "";
        if ($queryobject) {
            $align = $queryobject->getButtonAlignment();
            switch ($align) {
                case ALIGN_LEFT:
                    $qa = "text-left";
                    break;
                case ALIGN_RIGHT:
                    $qa = "text-right";
                    break;
                case ALIGN_JUSTIFIED:
                    $qa = "text-justify";
                    break;
                case ALIGN_CENTER:
                    $qa = "text-center";
                    break;
                default:
                    break;
            }
        }

        $returnStr .= $this->displayEndBody();
        $back = false;
        $returnStrButtons = "";
        if ($inputmasking == true) {
            $unmask = "unmaskForm();";
        }

        /* add script to capture screenshot of currently displayed screen on button click */
        $screenshotscript = "";
        $extrascripts = "";
        $extracss = "";
        if ($screendumps == true) {
            $screenshotscript = 'captureScreenshot(); ';

            // http://stackoverflow.com/questions/982717/how-do-i-get-the-entire-pages-html-with-jquery
            global $survey;
            //$params = array(POST_PARAM_DEFAULT_LANGUAGE => getDefaultSurveyLanguage(), POST_PARAM_DEFAULT_MODE => getDefaultSurveyMode(), POST_PARAM_LANGUAGE => getSurveyLanguage(), POST_PARAM_MODE => getSurveyMode(), POST_PARAM_VERSION => getSurveyVersion(), POST_PARAM_STATEID => $this->engine->getStateId(), POST_PARAM_DISPLAYED => $this->engine->getDisplayed(), POST_PARAM_PRIMKEY => $this->engine->getPrimaryKey(), POST_PARAM_SUID => $this->engine->getSuid());
            //$r = setSessionsParamString($params);
            $returnStr .= "<input type=hidden name='" . POST_PARAM_SCREENSHOT . "' id='" . POST_PARAM_SCREENSHOT . "' value=''/>";
            $extrascripts .= minifyScript("<script type='text/javascript'>
                            function captureScreenshot() {                           
                                $('#" . POST_PARAM_SCREENSHOT . "').val(encodeURIComponent(getDocTypeAsString() + $('html')[0].outerHTML));                                 
                            }
                            
                            var getDocTypeAsString = function () { 
                                var node = document.doctype;
                                return node ? \"<!DOCTYPE \" + node.name + (node.publicId ? ' PUBLIC \"' + node.publicId + '\"' : '') + (!node.publicId && node.systemId ? ' SYSTEM' : '') + (node.systemId ? ' \"' + node.systemId + '\"' : '') + '>\\n' : '';
                            };
                            </script>");
        }


        /* we have object that holds display settings */
        $returnStrButtons = "";
        if ($queryobject) {

            //$labels = array($queryobject->getLabelNextButton(), $queryobject->getLabelDKButton(), $queryobject->getLabelRFButton(), $queryobject->getLabelNAButton());
            $buttonformat = $queryobject->getButtonFormatting();
            if ($this->engine->getForward() == true) {
                if ($queryobject->getShowBackButton() == BUTTON_YES) {
                    $clickback = $this->engine->replaceFills($queryobject->getClickBack());
                    $clickback = str_replace("'", "", $clickback);
                    $back = true;
                    $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" name="backbutton" id="uscic-backbutton" value="' . $this->engine->replaceFills($queryobject->getLabelBackButton()) . '" type="button" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelBackButton())) . '"; ' . $unmask . ' ' . $clickback . ' document.getElementById("form").submit();\'>' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelBackButton()), $buttonformat) . '</button>';
                }
            } else {

                if ($this->engine->isFirstState() == false) {
                    if ($queryobject->getShowBackButton() == BUTTON_YES) {
                        $clickback = $this->engine->replaceFills($queryobject->getClickBack());
                        $clickback = str_replace("'", "", $clickback);
                        $back = true;
                        $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" name="backbutton" id="uscic-backbutton" value="' . $this->engine->replaceFills($queryobject->getLabelBackButton()) . '" type="button" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelBackButton())) . '"; ' . $unmask . ' ' . $clickback . ' document.getElementById("form").submit(); \'>' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelBackButton()), $buttonformat) . '</button>';
                    }
                }
            }
            if ($queryobject->getShowNextButton() == BUTTON_YES) {

                $clicknext = $this->engine->replaceFills($queryobject->getClickNext());
                $clicknext = str_replace("'", "", $clicknext);

                /* we do empty and/or error checking for single variable */
                //if (sizeof($queryvariables) == 1) {
                if (trim($groupname) == "") {
                    if ($doempty || $doerror) {

                        $extra_empty_check = "";
                        $lastcheck = "";
                        if ($doempty) {
                            $lastcheck .= 'empty == true';
                            // THIS IS CURRENT, ALLOWS TO CHECK ONLY ONCE AND THEN JUST NOT CHECK ANYMORE: $extra_empty_check = "var empty = false; if (document.getElementById(\"em\").value == 0 && validateFormEmpty() == false) {";
                            $extra_empty_check = "var empty = false; if (validateFormEmpty() == false) {";
                            if ($ifempty == IF_EMPTY_WARN) {
                                $extra_empty_check .= "document.getElementById(\"em\").value=1; ";
                            }
                            $extra_empty_check .= "empty = true;}";
                        }

                        $extra_error_check = "";
                        if ($doerror) {
                            if ($lastcheck != "") {
                                $lastcheck .= '||';
                            }
                            $lastcheck .= 'error == true';
                            $extra_error_check = "var error = false;";
                            // THIS IS CURRENT, ALLOWS TO CHECK ONLY ONCE AND THEN JUST NOT CHECK ANYMORE: $extra_error_check .= "if (document.getElementById(\"er\").value == 0 && validateFormError() == false) {";
                            $extra_error_check .= "if (validateFormError() == false) {";
                            if ($iferror == IF_ERROR_WARN) {
                                $extra_error_check .= "document.getElementById(\"er\").value=1; ";
                            }
                            $extra_error_check .= "error = true;}";
                        }
                        if ($lastcheck != "") {
                            $lastcheck = "if (" . $lastcheck . ") { enableButtons(); return false;}";
                        }
                        $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" name="nextbutton" id="uscic-nextbutton" type="button" onclick=\'disableButtons(); ' . $screenshotscript . ' clearForm();' . $extra_empty_check . ' ' . $extra_error_check . ' ' . $lastcheck . ' document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelNextButton())) . '"; ' . $unmask . ' ' . $clicknext . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelNextButton()) . '">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelNextButton()), $buttonformat) . '</button>';
                    }
                    //  no empty and/or error checking
                    else {
                        $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" name="nextbutton" id="uscic-nextbutton" type="button" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelNextButton())) . '"; ' . $unmask . ' ' . $clicknext . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelNextButton()) . '">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelNextButton()), $buttonformat) . '</button>';
                    }
                }
                //  group
                else {

                    if ($doempty || $doerror) {
                        $extra_empty_check = "";
                        $lastcheck = "";
                        if ($doempty) {
                            $lastcheck .= 'empty == true';
                            // THIS IS CURRENT, ALLOWS TO CHECK ONLY ONCE AND THEN JUST NOT CHECK ANYMORE: $extra_empty_check = "var empty = false; if (document.getElementById(\"em\").value == 0 && validateFormEmpty() == false) {";
                            $extra_empty_check = "var empty = false; if (validateFormEmpty() == false) {";
                            //echo 'jjjjj' . $queryobject->getIfEmpty();
                            if ($ifempty == IF_EMPTY_WARN) {
                                $extra_empty_check .= "document.getElementById(\"em\").value=1; ";
                            }
                            if ($this->datatables == true) {
                                $extra_empty_check .= 'resizeDataTables(); ';
                            }
                            $extra_empty_check .= "empty = true;}";
                        }

                        $extra_error_check = "";
                        if ($doerror) {
                            if ($lastcheck != "") {
                                $lastcheck .= '||';
                            }
                            $lastcheck .= 'error == true';
                            $extra_error_check = "var error = false;";
                            // THIS IS CURRENT, ALLOWS TO CHECK ONLY ONCE AND THEN JUST NOT CHECK ANYMORE: $extra_error_check .= "if (document.getElementById(\"er\").value == 0 && validateFormError() == false) {";
                            $extra_error_check .= "if (validateFormError() == false) {";
                            if ($iferror == IF_ERROR_WARN) {
                                $extra_error_check .= "document.getElementById(\"er\").value=1; ";
                            }
                            if ($this->datatables == true) {
                                $extra_error_check .= 'resizeDataTables(); ';
                            }
                            $extra_error_check .= "error = true;}";
                        }
                        if ($lastcheck != "") {
                            $lastcheck = "if (" . $lastcheck . ") { enableButtons(); return false;}";
                        }
                        $returnStrButtons .= '<button class="btn btn-default" name="nextbutton" id="uscic-nextbutton" type="button" onclick=\'disableButtons(); ' . $screenshotscript . 'clearForm();' . $extra_empty_check . ' ' . $extra_error_check . ' ' . $lastcheck . ' document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelNextButton())) . '"; ' . $unmask . ' ' . $clicknext . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelNextButton()) . '">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelNextButton()), $buttonformat) . '</button>';
                    }
                    //  no empty and/or error checking
                    else {
                        $returnStrButtons .= '<button class="btn btn-default" name="nextbutton" id="uscic-nextbutton" type="button" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelNextButton())) . '"; ' . $unmask . ' ' . $clicknext . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelNextButton()) . '">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelNextButton()), $buttonformat) . '</button>';
                    }
                }
            }

            /* handle dk/rf/na buttons */
            if ($this->showdk == true) {

                /* if not individual buttons OR only one question */
                if ($this->dkrfna == false) {
                    if ($queryobject->getShowDKButton() == BUTTON_YES || $queryobject->getShowRFButton() == BUTTON_YES || $queryobject->getShowNAButton() == BUTTON_YES || $queryobject->getShowRemarkButton() == BUTTON_YES) {
                        $returnStrButtons .= '<span class="pull-right">';
                        if ($queryobject->getShowDKButton() == BUTTON_YES) {
                            $highlight = "";
                            $click = $this->engine->replaceFills($queryobject->getClickDK());
                            $click = str_replace("'", "", $click);
                            if (sizeof($this->engine->getDKAnswers()) > 0) {
                                $highlight = "uscic-dkbutton-active";
                            }
                            $returnStrButtons .= '<button disabled="disabled" name="dkbutton" class="btn btn-default ' . $highlight . '" id="uscic-dkbutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelDKButton())) . '"; ' . $click . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelDKButton()) . '" type="button">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelDKButton()), $buttonformat) . '</button>';
                        }
                        if ($queryobject->getShowRFButton() == BUTTON_YES) {
                            $highlight = "";
                            $click = $this->engine->replaceFills($queryobject->getClickRF());
                            $click = str_replace("'", "", $click);
                            if (sizeof($this->engine->getRFAnswers()) > 0) {
                                $highlight = "uscic-rfbutton-active";
                            }
                            $returnStrButtons .= '<button disabled="disabled" name="rfbutton" class="btn btn-default ' . $highlight . '" id="uscic-rfbutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelRFButton())) . '"; ' . $click . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelRFButton()) . '" type="button">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelRFButton()), $buttonformat) . '</button>';
                        }
                        if ($queryobject->getShowNAButton() == BUTTON_YES) {
                            $highlight = "";
                            $click = $this->engine->replaceFills($queryobject->getClickNA());
                            $click = str_replace("'", "", $click);
                            if (sizeof($this->engine->getNAAnswers()) > 0) {
                                $highlight = "uscic-nabutton-active";
                            }
                            $returnStrButtons .= '<button disabled="disabled" name="nabutton" class="btn btn-default ' . $highlight . '" id="uscic-nabutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelNAButton())) . '"; ' . $click . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelNAButton()) . '" type="button">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelNAButton()), $buttonformat) . '</button>';
                        }
                    }
                }
            }

            /* handle update button */
            if ($queryobject->getShowUpdateButton() == BUTTON_YES) {

                if ($this->dkrfna == true) {
                    $returnStrButtons .= '<span class="pull-right">';
                }
                $click = $this->engine->replaceFills($queryobject->getClickUpdate());
                $click = str_replace("'", "", $click);
                $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" name="updatebutton" id="uscic-updatebutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($this->engine->replaceFills($queryobject->getLabelUpdateButton())) . '"; ' . $click . ' document.getElementById("form").submit();\' value="' . $this->engine->replaceFills($queryobject->getLabelUpdateButton()) . '" type="button">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelUpdateButton()), $buttonformat) . '</button>';
            }

            /* handle remark button */
            if ($queryobject->getShowRemarkButton() == BUTTON_YES) {

                if ($this->dkrfna == true && $queryobject->getShowUpdateButton() != BUTTON_YES) {
                    $returnStrButtons .= '<span class="pull-right">';
                }

                $rem = $this->engine->loadRemark();
                $returnStrButtons .= $this->showRemarkModal($queryobject, $qa, $rem);
                $highlight = "";
                if (trim($rem) != "") {
                    $returnStrButtons .= "<input type='hidden' name='" . POST_PARAM_REMARK_INDICATOR . "' id='" . POST_PARAM_REMARK_INDICATOR . "' value='1'>";
                    $highlight = "uscic-remarkbutton-active";
                } else {
                    $returnStrButtons .= "<input type='hidden' name='" . POST_PARAM_REMARK_INDICATOR . "' id='" . POST_PARAM_REMARK_INDICATOR . "'>";
                }
                $returnStrButtons .= '<button disabled="disabled" class="btn btn-default ' . $highlight . '" name="remark name="nextbutton"button" id="uscic-remarkbutton" data-toggle="modal" data-target="#remarkmodal" type="button">' . $this->applyFormatting($this->engine->replaceFills($queryobject->getLabelRemarkButton()), $buttonformat) . '</button>';
            }
            if (($this->showdk == true && $this->dkrfna == false) || $queryobject->getShowUpdateButton() == BUTTON_YES || $queryobject->getShowRemarkButton() == BUTTON_YES) {
                $returnStrButtons .= '</span>';
            }
        }
        // no object that holds display settings --> THIS SHOULD NEVER HAPPEN
        else {
            if ($this->engine->getForward() == true) {
                $back = true;
                $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" name="backbutton" id="uscic-backbutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes(Language::buttonBack()) . '"; ' . $unmask . ' document.getElementById("form").submit();\' value="' . Language::buttonBack() . '" type="button">' . Language::buttonBack() . '</button>';
            } else {
                if ($this->engine->isFirstState() == false) {
                    $back = true;
                    $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" name="backbutton" id="uscic-backbutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes(Language::buttonBack()) . '"; ' . $unmask . ' document.getElementById("form").submit();\' value="' . Language::buttonBack() . '" type="button">' . Language::buttonBack() . '</button>';
                }
            }

            // next button
            $returnStrButtons .= '<button class="btn btn-default" id="uscic-nextbutton" name="nextbutton" type="button" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes(Language::buttonNext()) . '"; ' . $unmask . ' document.getElementById("form").submit();\' value="' . Language::buttonNext() . '">' . Language::buttonNext() . '</button>';

            /* generic dk/rf/na buttons button */
            if ($this->showdk == true) {

                /* if not individual buttons OR only one question */
                if ($this->dkrfna == false) {
                    $returnStrButtons .= '<span class="pull-right">';
                    $highlight = "";
                    if (sizeof($this->engine->getDKAnswers()) > 0) {
                        $highlight = "uscic-dkbutton-active";
                    }
                    $returnStrButtons .= '<button disabled="disabled" class="btn btn-default ' . $highlight . '" name="dkbutton" id="uscic-dkbutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes(Language::buttonDK()) . '"; document.getElementById("form").submit();\' value="' . Language::buttonDK() . '" type="button">' . Language::buttonDK() . '</button>';
                    $highlight = "";
                    if (sizeof($this->engine->getRFAnswers()) > 0) {
                        $highlight = "uscic-rfbutton-active";
                    }
                    $returnStrButtons .= '<button disabled="disabled" class="btn btn-default ' . $highlight . '" name="rfbutton" id="uscic-rfbutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes(Language::buttonRF()) . '"; document.getElementById("form").submit();\' value="' . Language::buttonRF() . '" type="button">' . Language::buttonRF() . '</button>';
                    $highlight = "";
                    if (sizeof($this->engine->getNAAnswers()) > 0) {
                        $highlight = "uscic-nabutton-active";
                    }
                    $returnStrButtons .= '<button disabled="disabled" class="btn btn-default ' . $highlight . '" name="nabutton" id="uscic-nabutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes(Language::buttonNA()) . '"; document.getElementById("form").submit();\' value="' . Language::buttonNA() . '" type="button">' . Language::buttonNA() . '</button>';
                    $returnStrButtons .= '</span>';
                }
            }

            /* handle update button */
            if ($queryobject->getShowUpdateButton() == BUTTON_YES) {

                if ($this->dkrfna == true) {
                    $returnStrButtons .= '<span class="pull-right">';
                }

                $returnStrButtons .= '<button disabled="disabled" class="btn btn-default" id="uscic-updatebutton" name="updatebutton" onclick=\'disableButtons(); ' . $screenshotscript . 'document.getElementById("navigation").value="' . addslashes($queryobject->getLabelUpdateButton()) . '"; document.getElementById("form").submit();\' value="' . $queryobject->getLabelUpdateButton() . '" type="button">' . $this->applyFormatting($queryobject->getLabelUpdateButton(), $buttonformat) . '</button>';
            }

            if (($this->showdk == true && $this->dkrfna == false) || $queryobject->getShowUpdateButton() == BUTTON_YES || $queryobject->getShowRemarkButton() == BUTTON_YES) {
                $returnStrButtons .= '</span>';
            }
        }

        // add button panel
        if (trim($returnStrButtons) != "") {
            $returnStr .= '<div id="uscic-buttonpanel" class="panel-footer ' . $qa . ' uscic-buttonpanel">';
            $returnStr .= $returnStrButtons;
            $returnStr .= '</div>'; // end of button panel
        }
        $returnStr .= '</div>'; // end of footer panel
        // progress bar          
        $returnStr .= $this->showProgress($rgid, $queryobject);

        $returnStr .= "</div>";
        $returnStr .= "</div>";

        if ($paradata == true) {
            $returnStr .= "<input type='hidden' id='pid' name='" . POST_PARAM_PARADATA . "' value=''/>";
        }

        $returnStr .= '</form>';

        /* add any page javascript */
        if (sizeof($pagejavascript) > 0) {
            $returnStr .= "<script type='text/javascript'>";
            $returnStr .= implode("\r\n", $pagejavascript);
            $returnStr .= "</script>";
        }

        /* anything below buttons panel */
        $returnStr .= $this->displayBelowButtons();

        /* validation */
        if ($queryobject) {
            if ($ifempty || $iferror) {
                $extrascripts .= $this->displayValidation($paradata, $this->errorchecks, $iferror, $ifempty, $queryobject->getErrorPlacement());
            }
        } else {
            $extrascripts .= $this->displayValidation($paradata, $this->errorchecks);
        }

        /* input masking */
        if ($inputmasking == true) {
            $extrascripts .= $this->displayMaskingScripts();
        }

        /* data tables */
        if ($this->datatables == true) {
            $extrascripts .= $this->displayDataTablesScripts(array(), false);
            $extracss .= $this->displayDataTablesCSS();
        }

        /* keyboard binding */
        if ($keyboardbinding == true) {
            $extrascripts .= $this->displayKeyBoardBinding($this->engine, $queryobject, $back);
        }

        /* button switches for dk/rf/na */
        if ($this->showdk == true && $this->dkrfna == true) {
            $extrascripts .= $this->displayRadioSwitch(false);
            $extracss .= $this->displayRadioSwitchCSS();
        }

        // button disabling
        $extrascripts .= $this->displayButtonToggling();

        /* button enabling on load */
        $extrascripts .= minifyScript('<script type="text/javascript">
                                $(document).ready(function() {
                                    enableButtons();
                                });
                            </script>');


        // session timeout handling
        if ($survey->getTimeout() == 1) {
            $extrascripts .= $this->displayTimeoutScripts();
        }

        /* enter submit link to next button click 
         * (adapted from http://stackoverflow.com/questions/895171/prevent-users-from-submitting-form-by-hitting-enter)
         */
        $extrascripts .= minifyScript("<script type='text/javascript'>
                        $(document).ready(function() {\$(':input').not('textarea').keypress(function(event) { if (event.keyCode != 13) { return true;} else {\$('#nextbutton').click(); return false;} });});</script>");

        $header = "";
        $footer = "";
        if ($queryobject) {
            //if (sizeof($queryvariables) == 1) {
            if (trim($groupname) == "") {
                $header = $this->engine->getFill($queryobject->getName(), $queryobject, SETTING_PAGE_HEADER);
                $footer = $this->engine->getFill($queryobject->getName(), $queryobject, SETTING_PAGE_FOOTER);
            } else {
                $header = $this->engine->replaceInlineFields($this->engine->replaceFills($queryobject->getPageHeader()));
                $footer = $this->engine->replaceInlineFields($this->engine->replaceFills($queryobject->getPageFooter()));
            }
        } else {
            
        }
        
        // combo box css
        if ($this->combobox = true) {
            $extracss .= $this->displayComboBoxCSS();
        }

        /* add header now that we know all the scripts */
        if ($this->showheader == true) {
            $returnStrHeader = $this->showSurveyHeader($survey->getTitle(), implode("", array_unique($scriptarray)) . '<link href="bootstrap/css/sticky-footer-navbar.min.css" rel="stylesheet">' . $extracss);
            if ($header != "") {
                $returnStrHeader .= $header;
            }
        }

        // combo box dropdown
        if ($this->combobox) {
            $extrascripts .= $this->displayComboBox(false);        
        }

        // paradata
        $extrascripts .= $this->displayParadataScripts($paradata);

        /* footer */
        if ($footer != "") {
            $returnStr .= $footer;
        }

        $returnStr .= "</div>"; // end of wrap

        if ($this->showfooter == true) {
            $returnStr .= $this->showSurveyFooter($extrascripts);
        }
        $returnStrHeader .= $this->displayBody();

        $result = $returnStrHeader . $returnStr;

        if ($this->lastparse == true) {
            $result = lastParse($result);
        }

        /* store screendump of newly displayed screen */
        if ($screendumps == true) {

            // don't capture screenshots for direct access only (timed out)
            if (sizeof($queryvariables) == 1) {
                if (!inArray($variablenames, array(VARIABLE_DIRECT))) {
                    captureScreenshot($result);
                }
            }
        }

        /* return result */
        return $result;
    }

    function showRemarkModal($queryobject, $qa, $current) {
        $params = array(POST_PARAM_DEFAULT_LANGUAGE => getDefaultSurveyLanguage(), POST_PARAM_DEFAULT_MODE => getDefaultSurveyMode(), POST_PARAM_LANGUAGE => getSurveyLanguage(), POST_PARAM_MODE => getSurveyMode(), POST_PARAM_GROUP => $this->engine->getTemplate() ,POST_PARAM_TEMPLATE => getSurveyTemplate(), POST_PARAM_VERSION => getSurveyVersion(), POST_PARAM_STATEID => $this->engine->getStateId(), POST_PARAM_RGID => $this->engine->getRgid(), POST_PARAM_DISPLAYED => $this->engine->getDisplayed(), POST_PARAM_PRIMKEY => $this->engine->getPrimaryKey(), POST_PARAM_SUID => $this->engine->getSuid());
        $r = setSessionsParamString($params);
        $returnStr = minifyScript("<script type='text/javascript'>
                            function storeRemark() { 
                                var val=$('#remarkfield').val();
                                if (val == '') {
                                    if ($('#uscic-remarkbutton').hasClass('uscic-remarkbutton-active') == true) {
                                        $('#uscic-remarkbutton').removeClass('uscic-remarkbutton-active');
                                        $('#" . POST_PARAM_REMARK_INDICATOR . "').val('');
                                        $.ajax({
                                            type: 'POST',
                                            url: 'ajax/index.php',
                                            data: {p: 'removeremark', ajaxr: '" . $r . "'},
                                            async: true
                                          });
                                     }
                                }
                                else {
                                    $('#uscic-remarkbutton').addClass('uscic-remarkbutton-active');
                                    $('#" . POST_PARAM_REMARK_INDICATOR . "').val(1);
                                    $.ajax({
                                        type: 'POST',
                                        url: 'ajax/index.php',
                                        data: {p: 'storeremark', ajaxr: '" . $r . "', " . POST_PARAM_REMARK . ": encodeURIComponent(val)},
                                        async: true
                                      });
                                }
                            }</script>");
        $inlineclass = '';
        $returnStr .= '<div class="modal fade" id="remarkmodal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header uscic-modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h4 class="modal-title" id="remarktitle">' . Language::remarkTitle() . '</h4>
            </div>
            <div class="modal-body">
                <h3>
                <div class="form-group uscic-formgroup' . $inlineclass . '">
                        <label>
                            <div class="uscic-remarkarea">
                                <textarea class="form-control uscic-form-control" id="remarkfield" name="remarkfield">' . $current . '</textarea>
                            </div>            
                        </label>
                </div>
                </h3>
            </div>';

        if ($queryobject->getShowCloseButton() == BUTTON_YES || $queryobject->getShowRemarkSaveButton() == BUTTON_YES) {
            $returnStr .= '<div class="panel-footer ' . $qa . ' uscic-buttonpanel">';

            if ($queryobject->getShowCloseButton() == BUTTON_YES) {
                $returnStr .= '<button type="button" class="btn btn-default" data-dismiss="modal">' . $queryobject->getLabelCloseButton() . '</button>';
            }
            if ($queryobject->getShowRemarkSaveButton() == BUTTON_YES) {
                $returnStr .= '<button type="button" class="btn btn-default" onclick=\'storeRemark(); $("#remarkmodal").modal("hide"); return false;\'>' . $queryobject->getLabelRemarkSaveButton() . '</button>';
            }
            $returnStr .= '</div>';
        }
        $returnStr .= '</div>
  </div>
</div>';
        return $returnStr;
    }

    function getRealVariables($variables) {
        $out = array();
        foreach ($variables as $variable) {

            // ignore subgroup statements
            if (startsWith($variable, ROUTING_IDENTIFY_SUBGROUP) == false && startsWith($variable, ROUTING_IDENTIFY_ENDSUBGROUP) == false) {
                $out[] = $variable;
            }
        }
        return implode("~", $out);
    }

    function addErrorCheck($name, $error, $message) {
        $message = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $message);
        $message = str_replace("\n", "", $message);
        if (isset($this->errorchecks[strtoupper($name)])) {
            $object = $this->errorchecks[strtoupper($name)];
            $error->setMessage($message);
            $object->addErrorCheck($error);
            $messages = $this->errorcheckmessages[strtoupper($name)];
            $messages["data-msg-" . strtolower($error->getType())] = $message;
            $this->errorcheckmessages[strtoupper($name)] = $messages;
        } else {
            $object = new ErrorChecks($name);
            $error->setMessage($message);
            $object->addErrorCheck($error);
            $this->errorchecks[strtoupper($name)] = $object;
            $messages = array();
            $messages["data-msg-" . strtolower($error->getType())] = $message;
            $this->errorcheckmessages[strtoupper($name)] = $messages;
        }
    }

    function getErrorCheckMessages($name) {
        if (isset($this->errorchecks[strtoupper($name)])) {
            return $this->errorchecks[strtoupper($name)];
        }
    }

    function getErrorTextString($name) {
        if (!isset($this->errorcheckmessages[strtoupper($name)])) {
            return "";
        }
        $messages = $this->errorcheckmessages[strtoupper($name)];
        $str = "";
        foreach ($messages as $k => $v) {
            $str .= $k . "='" . str_replace("\n", "", str_replace("'", "", strip_tags(convertHTLMEntities($v, ENT_QUOTES)))) . "' ";
        }

        // add allow empty and allow error indicators
        $numbers = $this->engine->getDisplayNumbers();
        $var = $this->engine->getVariableDescriptive(array_search(str_replace("answer", "", $name), $numbers));
        $str .= " data-validation-empty=" . $var->getIfEmpty();
        $iferror = $var->getIfError();
        $ifgroup = $var->getIfErrorGroup();

        // if this is the first variable in a group, it might have errors that must be fixed, so we check for that
        if ($this->engine->getTemplate() != "" && $iferror != $ifgroup && $ifgroup == 1) {
            $iferror = $ifgroup;
        }
        $str .= " data-validation-error=" . $iferror;
        return $str;
    }

    function displayEndBody() {
        return '';
    }

    function displayBelowButtons() {
        return '';
    }

    function applyFormatting($text, $format) {
        $beginformat = "";
        $endformat = "";
        $format = explode("~", $format);
        if (inArray(FORMATTING_BOLD, $format)) {
            $beginformat .= "<b>";
            $endformat .= "</b>";
        }
        if (inArray(FORMATTING_ITALIC, $format)) {
            $beginformat .= "<i>";
            $endformat .= "</i>";
        }
        if (inArray(FORMATTING_UNDERLINED, $format)) {
            $beginformat .= "<u>";
            $endformat .= "</u>";
        }
        return $beginformat . $text . $endformat;
    }

    function showQuestiontext($variable, $var, $class = "uscic-question") {

        //if ($this->engine->isInlineField($variable)) {
        //    return "";
        //}
        $text = $this->engine->getFill($variable, $var, SETTING_QUESTION);

        if (trim($text) == "") {
            return "";
        }

        $questionalign = $var->getQuestionAlignment();
        $qa = "";
        switch ($questionalign) {
            case ALIGN_LEFT:
                $qa = "text-left";
                break;
            case ALIGN_RIGHT:
                $qa = "text-right";
                break;
            case ALIGN_JUSTIFIED:
                $qa = "text-justify";
                break;
            case ALIGN_CENTER:
                $qa = "text-center";
                break;
            default:
                break;
        }
        $beginformat = "";
        $endformat = "";
        $questionformat = explode("~", $var->getQuestionFormatting());
        if (inArray(FORMATTING_BOLD, $questionformat)) {
            $beginformat .= "<b>";
            $endformat .= "</b>";
        }
        if (inArray(FORMATTING_ITALIC, $questionformat)) {
            $beginformat .= "<i>";
            $endformat .= "</i>";
        }
        if (inArray(FORMATTING_UNDERLINED, $questionformat)) {
            $beginformat .= "<u>";
            $endformat .= "</u>";
        }

        $returnStr = '<div id="vsid_' . $var->getVsid() . '" uscic-target="vsid_' . $var->getVsid() . '" uscic-texttype="' . SETTING_QUESTION . '" class="' . $this->inlineeditable . $class . ' ' . $qa . '">' . $this->applyFormatting($text, $questionformat) . $this->inlineediticon . "</div>";
        //$returnStr = '<div id="vsid_' . $var->getVsid() . '" uscic-target="vsid_' . $var->getVsid() . '" uscic-texttype="' . SETTING_QUESTION . '">' . $this->applyFormatting($text, $questionformat) . $this->inlineediticon . "</div>";
        return $returnStr;
    }

    function showEnumeratedVertical($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript) {
        $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);
        $orderedoptions = $options;
        $order = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_RANDOMIZER);

        if ($order != "") {
            $arr = $this->engine->getAnswer($order);
            //print_r($arr);
            if (is_array($arr) && sizeof($arr) > 0) {
                $orderedoptions = array();
                foreach ($arr as $a) {
                    foreach ($options as $option) {
                        if ($option["code"] == $a) {
                            $orderedoptions[] = $option;
                            break;
                        }
                    }
                }
            }
        }
        $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">';
        $ids = array();
        $optioncodes = array();
        foreach ($orderedoptions as $option) {
            $optioncodes[] = $option["code"];
            if (trim($option["label"] != "")) {
                $ids[] = $id . '_' . $option["code"];
            }
        }
        $this->addInlineFieldChecks($varname, $variable, $var, $ids);
        $order = $var->getEnumeratedOrder();
        $itemspercolumn = $var->getEnumeratedColumns();
        if ($itemspercolumn == "") {
            $itemspercolumn = sizeof($orderedoptions);
        }
        $splitup = array_chunk($orderedoptions, $itemspercolumn);
        $tableid = $var->getSeid() . '-' . $var->getVsid();
        $returnStr .= "<table id='table-" . $tableid . "' >";

        $textbox = $var->isEnumeratedTextbox();
        if ($textbox) {
            $message = $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_ENUMERATED_ENTERED);
            $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_ENUMERATED_ENTERED, '"' . implode("-", $optioncodes) . '"'), $message);
        }

        $dkrfna = $this->addDKRFNAButton($varname, $var, $variable);
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            }
        }

        $label = $var->getEnumeratedLabel();
        $cnt = 1;
        //print_r($splitup[1]);
        // TODO: SPLIT INTO NUMBER OF ARRAYS DEPENDING ON NUMBER OF COLUMNS, THEN PICK ONE FROM EACH ARRAY AND ADD AS A TD
        //foreach ($orderedoptions as $option) {
        for ($i = 0; $i < $itemspercolumn; $i++) {
            $returnStr .= "<tr>";
            $j = 0;
            foreach ($splitup as $s) {
                if (isset($s[$i])) {
                    $option = $s[$i];
                    if (trim($option["label"] != "")) {
                        $selected = '';
                        if ($option["code"] == $previousdata) {
                            $selected = ' CHECKED';
                        }

                        switch ($label) {
                            case ENUMERATED_LABEL_INPUT_ONLY:
                                $labelstr = "";
                                break;
                            case ENUMERATED_LABEL_LABEL_ONLY:
                                $labelstr = $option["label"];
                                break;
                            case ENUMERATED_LABEL_LABEL_CODE:
                                $labelstr = "(" . $option["code"] . ") " . $option["label"];
                                break;
                            case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                                $acr = "";
                                if (trim($option["acronym"]) != "") {
                                    $acr = " " . $option["acronym"];
                                }
                                $labelstr = "(" . $option["code"] . $acr . ") " . $option["label"];
                                break;
                            default:
                                $labelstr = $option["label"];
                                break;
                        }

                        $disabled = '';
                        if ($this->isEnumeratedActive($variable, $var, $option["code"]) == false) {
                            $disabled = ' disabled ';
                        }                        
                        if ($j > 0) {
                            $returnStr .= '<td class="uscic-enumerated-column-padding"><nobr/></td>';
                        }
                        if ($order == ORDER_LABEL_FIRST) {
                            $returnStr .= '<td class="uscic-table-row-cell-enumerated' . $disabled . '" id="cell' . $id . '_' . $option["code"] . '" ><label class="uscic-radio-label-first-label' . $disabled . '" for="' . $id . '_' . $option["code"] . '">' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '</label>                        
                                            </td>
                                                    <td class="uscic-table-row-cell-enumerated' . $disabled . '">
                                                    <div class="radio uscic-radio-label-first-radio' . $disabled . '">

                                                        <input ' . $disabled . $dkrfnaclass . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' type=radio id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>

                                                    </div>';
                            if ($disabled == '') {
                                $returnStr .= $this->displayRadioButtonScript($id . '_' . $option["code"], true);
                            }
                            $returnStr .= '</td>';
                        } else {
                            $returnStr .= '<td class="uscic-table-row-cell-enumerated' . $disabled . '"><div class="radio uscic-radio"><label class="uscic-radio-label' . $disabled . '" for="' . $id . '_' . $option["code"] . '">                                            
                                                    <div class="uscic-radiobutton ' . $inlineclass . ' ' . $qa . '">

                                                        <input ' . $disabled . $dkrfnaclass . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' type=radio id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '
                                                    </div> 
                                                    </label>
                                                </div></td>';
                            /* $returnStr .= '<tr><td class="uscic-table-row-cell-enumerated' . $disabled . '"><div class="radio uscic-radio" style="margin-top: 0px;"><label class="uscic-radio-label' . $disabled . '" for="' . $id . '_' . $option["code"] . '">                                            
                              <div class="uscic-radiobutton ' . $inlineclass . ' ' . $qa . '">

                              <input ' . $disabled . $dkrfnaclass . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' type=radio id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '
                              </div>
                              </label>
                              </div></td></tr>'; */
                            if ($disabled == '') {
                                $returnStr .= $this->displayRadioButtonScript($id . '_' . $option["code"]);
                            }
                        }
                    }
                }
                $j++;
            }
            $returnStr .= "</tr>";
        }

        //if ($order == ORDER_LABEL_FIRST) {
        $returnStr .= "</table>";
        //}



        if ($textbox) {
            $returnStr .= "<div class='uscic-radio-vertical-textbox'>";
            $returnStr .= $this->addEnumeratedTextBox($variable, $var, $varname, $id, $previousdata);
            $returnStr .= '</div>';
            $returnStr .= '</div>';
        } else {
            $returnStr .= '</div>';
        }

        $returnStr .= $dkrfna;

        return $returnStr;
    }

    function isEnumeratedActive($variable, $var, $code) {

        if ($var->isInputMaskEnabled() == false) {
            return true;
        }

        // check for minimum/maximum/equal to/not equal to
        $eq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_EQUAL_TO));
        $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO));
        $ge = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER));
        $geq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER_EQUAL_TO));
        $se = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER));
        $seq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER_EQUAL_TO));

        if ($eq != "") {
            $values = explode(SEPARATOR_COMPARISON, $eq);
            foreach ($values as $v) {
                if (is_numeric($v)) {
                    if ($v != $code) {
                        return false;
                    }
                }
            }
        }

        if ($neq != "") {
            $values = explode(SEPARATOR_COMPARISON, $neq);
            foreach ($values as $v) {
                if (is_numeric($v)) {
                    if ($v == $code) {
                        return false;
                    }
                }
            }
        }
        if ($ge != "") {
            $values = explode(SEPARATOR_COMPARISON, $ge);
            foreach ($values as $v) {
                if (is_numeric($v)) {
                    if ($code <= $v) {
                        return false;
                    }
                }
            }
        }
        if ($geq != "") {
            $values = explode(SEPARATOR_COMPARISON, $geq);
            foreach ($values as $v) {
                if (is_numeric($v)) {
                    if ($code < $v) {
                        return false;
                    }
                }
            }
        }
        if ($se != "") {
            $values = explode(SEPARATOR_COMPARISON, $se);
            foreach ($values as $v) {
                if (is_numeric($v)) {
                    if ($code >= $v) {
                        return false;
                    }
                }
            }
        }
        if ($seq != "") {
            $values = explode(SEPARATOR_COMPARISON, $seq);
            foreach ($values as $v) {
                if (is_numeric($v)) {
                    if ($code > $v) {
                        return false;
                    }
                }
            }
        }

        // active
        return true;
    }

    function showEnumeratedHorizontal($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript) {

        $returnStr = "";

        $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);
        $orderedoptions = $options;
        $order = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_RANDOMIZER);
        if ($order != "") {
            $arr = $this->engine->getAnswer($order);
            //print_r($arr);
            if (is_array($arr) && sizeof($arr) > 0) {
                $orderedoptions = array();
                foreach ($arr as $a) {
                    foreach ($options as $option) {
                        if ($option["code"] == $a) {
                            $orderedoptions[] = $option;
                            break;
                        }
                    }
                }
            }
        }

        $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">';
        $ids = array();
        $optioncodes = array();
        foreach ($orderedoptions as $option) {
            $optioncodes[] = $option["code"];
            if (trim($option["label"] != "")) {
                $ids[] = $id . '_' . $option["code"];
            }
        }
        $this->addInlineFieldChecks($varname, $variable, $var, $ids);
        $textbox = $var->isEnumeratedTextbox();
        if ($textbox) {
            $message = $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_ENUMERATED_ENTERED);
            $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_ENUMERATED_ENTERED, '"' . implode("-", $optioncodes) . '"'), $message);
        }

        $align = $var->getAnswerAlignment();
        $qa = "";
        switch ($align) {
            case ALIGN_LEFT:
                $qa = "text-left";
                break;
            case ALIGN_RIGHT:
                $qa = "text-right";
                break;
            case ALIGN_JUSTIFIED:
                $qa = "text-justify";
                break;
            case ALIGN_CENTER:
                $qa = "text-center";
                break;
            default:
                break;
        }

        /* check for splitting */
        $split = $var->isEnumeratedSplit();
        $bordered = "";
        if ($var->isEnumeratedBordered()) {
            $bordered = "table-bordered";
        }

        /* start table */
        if ($var->isTableMobile() == true) {
            $returnStr .= $this->displayTableSaw();
        }
        $noofcolumns = sizeof($orderedoptions);
        $cellwidth = "width=" . round(100 / $noofcolumns) . "%";
        $label = $var->getEnumeratedLabel();
        $order = $var->getEnumeratedOrder();        
            
        if ($order == ORDER_LABEL_FIRST) {
            $nolabels = "data-tablesaw-preappend";
            if ($var->isTableMobileLabels() == false) {
                $nolabels = "data-tablesaw-no-labels";
            }    
            $returnStr .= '<table data-tablesaw-firstcolumn ' . $nolabels . ' data-tablesaw-mode="stack" id="table_' . $var->getName() . '" class="tablesaw tablesaw-stack table ' . $bordered . ' uscic-table-enumerated-horizontal">';
        } else {
            $nolabels = "data-tablesaw-postappend";
            if ($var->isTableMobileLabels() == false) {
                $nolabels = "data-tablesaw-no-labels";
            } 
            $returnStr .= '<table data-tablesaw-firstcolumn ' . $nolabels. ' data-tablesaw-mode="stack" id="table_' . $var->getName() . '" class="tablesaw tablesaw-stack table ' . $bordered . ' uscic-table-enumerated-horizontal">';
        }

        /* split, then add header */
        if ($split == true) {

            if ($order == ORDER_LABEL_FIRST) {
                $returnStr .= "<thead><tr class='uscic-table-row-header-enumerated'>";
            } else {
                $returnStr .= "<thead><tr style='display: none;'>";
            }

            $headeralign = $var->getHeaderAlignment();
            $hqa = "";
            switch ($headeralign) {
                case ALIGN_LEFT:
                    $hqa = "text-left";
                    break;
                case ALIGN_RIGHT:
                    $hqa = "text-right";
                    break;
                case ALIGN_JUSTIFIED:
                    $hqa = "text-justify";
                    break;
                case ALIGN_CENTER:
                    $hqa = "text-center";
                    break;
                default:
                    break;
            }

            foreach ($orderedoptions as $option) {

                switch ($label) {
                    case ENUMERATED_LABEL_INPUT_ONLY:
                        $labelstr = "";
                        break;
                    case ENUMERATED_LABEL_LABEL_ONLY:
                        $labelstr = $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE:
                        $labelstr = "(" . $option["code"] . ") " . $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                        $acr = "";
                        if (trim($option["acronym"]) != "") {
                            $acr = " " . $option["acronym"];
                        }
                        $labelstr = "(" . $option["code"] . $acr . ") " . $option["label"];
                        break;
                    default:
                        $labelstr = $option["label"];
                        break;
                }

                $returnStr .= "<th class='uscic-table-row-cell-header-enumerated'><div class='" . $hqa . "'>" . $this->applyFormatting($labelstr, $var->getHeaderFormatting()) . "</div></th>";
            }

            $returnStr .= "</tr></thead>";
        } else {
            $returnStr .= "<thead><tr style='display: none;'>";
            foreach ($orderedoptions as $o) {
                $returnStr .= "<th></th>";
            }
            $returnStr .= '</tr></thead>';
        }
        $returnStr .= "<tbody><tr class='uscic-table-row-enumerated-horizontal'>";

        $dkrfna = $this->addDKRFNAButton($varname, $var, $variable);
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            }
        }


        /* split between label and input */
        foreach ($orderedoptions as $option) {

            if (trim($option["label"]) != "") {
                $selected = '';
                if ($option["code"] == $previousdata) {
                    $selected = ' CHECKED';
                }

                $disabled = '';
                if ($this->isEnumeratedActive($variable, $var, $option["code"]) == false) {
                    $disabled = ' disabled ';
                }

                if ($split == false) {

                    switch ($label) {
                        case ENUMERATED_LABEL_INPUT_ONLY:
                            $labelstr = "";
                            break;
                        case ENUMERATED_LABEL_LABEL_ONLY:
                            $labelstr = $option["label"];
                            break;
                        case ENUMERATED_LABEL_LABEL_CODE:
                            $labelstr = $option["code"] . " " . $option["label"];
                            break;
                        case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                            $acr = "";
                            if (trim($option["acronym"]) != "") {
                                $acr = " (" . $option["acronym"] . ")";
                            }
                            $labelstr = $option["code"] . $acr . " " . $option["label"];
                            break;
                        default:
                            $labelstr = $option["label"];
                            break;
                    }

                    if ($order == ORDER_LABEL_FIRST) {
                        $returnStr .= '<td ' . $cellwidth . ' id="cell' . $id . '_' . $option["code"] . '" class="uscic-table-row-cell-enumerated-horizontal' . $disabled . '"><div class="radio uscic-enumerated-horizontal">
                                            <label for="' . $id . '_' . $option["code"] . '" id="label' . $id . '_' . $option["code"] . '" class="uscic-table-enumerated-label' . $disabled . '">' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '</label>
                                                <div class="uscic-radio' . $inlineclass . ' ' . $qa . '">
                                                    <input ' . $disabled . ' class="uscic-radio-table ' . $dkrfnaclass . '" ' . $this->getErrorTextString($varname) . ' ' . $inlinejavascript . ' type=radio id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>';
                        $returnStr .= '</div>
                                            </div>';
                    } else {
                        $returnStr .= '<td ' . $cellwidth . ' id="cell' . $id . '_' . $option["code"] . '" class="uscic-table-row-cell-enumerated-horizontal' . $disabled . '"><div class="radio uscic-enumerated-horizontal">

                                            <label for="' . $id . '_' . $option["code"] . '" id="label' . $id . '_' . $option["code"] . '" class="uscic-table-enumerated-label' . $disabled . '">

                                                <div class="uscic-radio' . $inlineclass . ' ' . $qa . '">

                                                    <input ' . $disabled . ' class="uscic-radio-table ' . $dkrfnaclass . '" ' . $this->getErrorTextString($varname) . ' ' . $inlinejavascript . ' type=radio id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>' . $this->applyFormatting($labelstr, $var->getAnswerFormatting());
                        $returnStr .= '</div></label>
                                            </div>';
                    }
                } else {

                    $returnStr .= '<td id="cell' . $id . '_' . $option["code"] . '" class="uscic-table-row-cell-enumerated-horizontal' . $disabled . '" ' . $cellwidth . '><div class="' . $qa . '">
                                        <label id="label' . $id . '_' . $option["code"] . '" for="' . $id . '_' . $option["code"] . '" class="uscic-table-enumerated-label' . $disabled . '">
                                        <div class="form-group uscic-table-row-cell-form-group">
                                        <input ' . $disabled . ' class="uscic-radio-table ' . $dkrfnaclass . '" ' . $this->getErrorTextString($varname) . ' ' . $inlinejavascript . ' type=radio id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>
                                        </div>
                                        </label>
                                   </div>';
                }

                if ($disabled == '') {
                    $returnStr .= $this->displayRadioButtonScript($id . '_' . $option["code"], true);
                }
                $returnStr .= '</td>';
            } else {
                $returnStr .= '<td class="uscic-table-row-cell-enumerated"></td>';
            }
        }
        $returnStr .= "</tr>";
        $returnStr .= '</tbody>';

        /* split, then add footer */
        if ($split == true) {

            if ($order == ORDER_OPTION_FIRST) {
                $returnStr .= "<tfoot><tr class='uscic-table-row-footer-enumerated'>";

                $headeralign = $var->getHeaderAlignment();
                $hqa = "";
                switch ($headeralign) {
                    case ALIGN_LEFT:
                        $hqa = "text-left";
                        break;
                    case ALIGN_RIGHT:
                        $hqa = "text-right";
                        break;
                    case ALIGN_JUSTIFIED:
                        $hqa = "text-justify";
                        break;
                    case ALIGN_CENTER:
                        $hqa = "text-center";
                        break;
                    default:
                        break;
                }

                foreach ($orderedoptions as $option) {

                    switch ($label) {
                        case ENUMERATED_LABEL_INPUT_ONLY:
                            $labelstr = "";
                            break;
                        case ENUMERATED_LABEL_LABEL_ONLY:
                            $labelstr = $option["label"];
                            break;
                        case ENUMERATED_LABEL_LABEL_CODE:
                            $labelstr = $option["code"] . " " . $option["label"];
                            break;
                        case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                            $acr = "";
                            if (trim($option["acronym"]) != "") {
                                $acr = " (" . $option["acronym"] . ")";
                            }
                            $labelstr = $option["code"] . $acr . " " . $option["label"];
                            break;
                        default:
                            $labelstr = $option["label"];
                            break;
                    }
                    $returnStr .= "<th class='uscic-table-row-cell-footer-enumerated'><div class='" . $hqa . "'>" . $this->applyFormatting($labelstr, $var->getHeaderFormatting()) . "</div></th>";
                }
                $returnStr .= "</tr></tfoot>";
            }
        }

        $returnStr .= '</table>';

        if ($textbox) {
            $returnStr .= "<div class='uscic-radio-horizontal-textbox'>";
            $returnStr .= $this->addEnumeratedTextBox($variable, $var, $varname, $id, $previousdata);
            $returnStr .= '</div>';
            $returnStr .= $dkrfna . '</div>';
        } else {
            $returnStr .= $dkrfna . '</div>';
        }
        return $returnStr;
    }

    function showEnumeratedCustom($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript) {
        $returnStr = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_CUSTOM);
        if (trim($returnStr) == "") {
            global $survey;
            if ($survey->getEnumeratedDisplay() == ORIENTATION_VERTICAL) {
                return $this->showEnumeratedVertical($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
            } else {
                return $this->showEnumeratedHorizontal($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
            }
        }

        $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);
        $orderedoptions = $options;
        $order = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_RANDOMIZER);
        if ($order != "") {
            $arr = $this->engine->getAnswer($order);
            //print_r($arr);
            if (is_array($arr) && sizeof($arr) > 0) {
                $orderedoptions = array();
                foreach ($arr as $a) {
                    foreach ($options as $option) {
                        if ($option["code"] == $a) {
                            $orderedoptions[] = $option;
                            break;
                        }
                    }
                }
            }
        }
        $ids = array();
        $optioncodes = array();
        foreach ($orderedoptions as $option) {
            $optioncodes[] = $option["code"];
            if (trim($option["label"] != "")) {
                $ids[] = $id . '_' . $option["code"];
            }
        }
        $textbox = $var->isEnumeratedTextbox();
        if ($textbox) {
            $message = $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_ENUMERATED_ENTERED);
            $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_ENUMERATED_ENTERED, '"' . implode("-", $optioncodes) . '"'), $message);
        }

        $dkrfna = $this->addDKRFNAButton($varname, $var, $variable);
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            }
        }


        $this->addInlineFieldChecks($varname, $variable, $var, $ids);
        $label = $var->getEnumeratedLabel();
        foreach ($orderedoptions as $option) {
            if (trim($option["label"] != "")) {
                $selected = '';
                if ($option["code"] == $previousdata) {
                    $selected = ' CHECKED';
                }

                $disabled = '';
                if ($this->isEnumeratedActive($variable, $var, $option["code"]) == false) {
                    $disabled = ' disabled ';
                }

                $inputstr = '<input ' . $disabled . $dkrfnaclass . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' type=radio id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>';

                switch ($label) {
                    case ENUMERATED_LABEL_INPUT_ONLY:
                        $labelstr = "";
                        break;
                    case ENUMERATED_LABEL_LABEL_ONLY:
                        $labelstr = $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE:
                        $labelstr = "(" . $option["code"] . ") " . $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                        $acr = "";
                        if (trim($option["acronym"]) != "") {
                            $acr = " " . $option["acronym"];
                        }
                        $labelstr = "(" . $option["code"] . $acr . ") " . $option["label"];
                        break;
                    default:
                        $labelstr = $option["label"];
                        break;
                }
                $labelstr = $this->applyFormatting($labelstr, $var->getAnswerFormatting());
                $returnStr = str_replace(PLACEHOLDER_ENUMERATED_OPTION . $option["code"] . '$', $inputstr, $returnStr);
                $returnStr = str_replace(PLACEHOLDER_ENUMERATED_TEXT . $option["code"] . '$', $labelstr, $returnStr);
            }
        }

        for ($i = 0; $i < 100; $i++) {
            $returnStr = str_replace(PLACEHOLDER_ENUMERATED_OPTION . $i . '$', $inputstr, $returnStr);
            $returnStr = str_replace(PLACEHOLDER_ENUMERATED_TEXT . $i . '$', $labelstr, $returnStr);
        }

        /* add form group for error display */
        $returnStr = '<div class="form-group uscic-formgroup' . $inlineclass . '">' . $returnStr;

        if ($textbox) {
            $returnStr .= "<div class='uscic-radio-custom-textbox'>";
            $returnStr .= $this->addEnumeratedTextBox($variable, $var, $varname, $id);
            $returnStr .= '</div>';
            $returnStr .= $dkrfna . '</div>';
        } else {
            $returnStr .= $dkrfna . '</div>';
        }

        /* return result */
        return $returnStr;
    }

    function addEnumeratedTextbox($variable, $var, $varname, $id, $previousdata) {
        $returnStr = "";
        $pretext = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_TEXTBOX_LABEL);
        $pretext = '<span class="input-group-addon uscic-inputaddon-pretext">' . $this->applyFormatting($pretext, $var->getAnswerFormatting()) . '</span>';
        $inputgroupstart = '<div class="input-group uscic-inputgroup-pretext">';
        $inputgroupend = "</div>";
        $style = "";

        $align = $var->getAnswerAlignment();
        $qa = "";
        switch ($align) {
            case ALIGN_LEFT:
                $qa = "text-left";
                break;
            case ALIGN_RIGHT:
                $qa = "text-right";
                break;
            case ALIGN_JUSTIFIED:
                $qa = "text-justify";
                break;
            case ALIGN_CENTER:
                $qa = "text-center";
                break;
            default:
                break;
        }

        if ($qa == "text-center") {
            $style = "style='display: block; margin-left: 40%; margin-right: 40%;'";
        } else if ($qa == "text-right") {
            $style = "style='display: block; margin-left: 80%; margin-right: 0%;'";
        }

        $mask = "integer";
        $m = "\"'alias': '" . $mask . "'\"";
        $placeholder = $this->engine->getFill($variable, $var, SETTING_INPUT_MASK_PLACEHOLDER);
        $textmask = "data-inputmask=" . $m . " data-inputmask-placeholder='" . $placeholder . "'";
        $returnStr .= '<div ' . $style . ' class="uscic-radio-textbox ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input id="' . $id . '_textbox" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $textmask . ' class="form-control uscic-form-control" type=text value="' . addslashes($previousdata) . '">                                    
                                    ' . $inputgroupend . '
                                </div>
                                ';
        $returnStr .= "<script type=text/javascript>";
        $returnStr .= minifyScript('$("#' . $id . '_textbox").keyup(
                                    function(event) {
                                        if ($(this).val() == "") {
                                            $("input[name=\'' . $varname . '\']").each(function(index) {
                                                $(this).prop("checked", false);
                                            });
                                        }
                                        else {
                                            if ($("#' . $id . '_' . '" + $(this).val() + ":enabled").length) {    
                                                $("#' . $id . '_' . '" + $(this).val() + ":enabled").prop("checked", true);
                                            }
                                            else {
                                                $("input[name=\'' . $varname . '\']").each(function(index) {
                                                    $(this).prop("checked", false);
                                                });
                                            }    
                                        }
                                    });
                                $("input[name=\'' . $varname . '\']").on(\'change\', function(event) {
                                        if ($(this).prop("checked") == true) {
                                            $("#' . $id . '_textbox").val($(this).val());
                                        }
                                        else {
                                            $("#' . $id . '_textbox").val("");
                                            $("input[name=\'' . $varname . '\']").each(function(index) {
                                                if ($(this).prop("checked") == true) {
                                                    $("#' . $id . '_textbox").val($(this).val());
                                                    return;
                                                }        
                                            });
                                        }
                                });    
                                ');
        $returnStr .= "</script>";
        return $returnStr;
    }

    function showSetOfEnumeratedVertical($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript) {
        $returnStr = "";
        $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);

        $orderedoptions = $options;
        $order = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_RANDOMIZER);
        if ($order != "") {
            $arr = $this->engine->getAnswer($order);
            //print_r($arr);
            if (is_array($arr) && sizeof($arr) > 0) {
                $orderedoptions = array();
                foreach ($arr as $a) {
                    foreach ($options as $option) {
                        if ($option["code"] == $a) {
                            $orderedoptions[] = $option;
                            break;
                        }
                    }
                }
            }
        }

        $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">';
        $ids = array();
        $optioncodes = array();
        foreach ($orderedoptions as $option) {
            $optioncodes[] = $option["code"];
            if (trim($option["label"] != "")) {
                $ids[] = $id . '_' . $option["code"];
            }
        }


        $realvarname = $varname;

        /* we will have a text box entry OR a hidden field that tracks the entries, so that will be the real variable we look at in POST */
        $varname = $id . "_name[]";
        $textbox = $var->isEnumeratedTextbox();
        if ($textbox) {
            $message = $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_SETOFENUMERATED_ENTERED);
            $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUMERATED_ENTERED, '"' . implode("-", $optioncodes) . '"'), $message);
        }

        $this->addSetOfEnumeratedChecks($varname, $variable, $var, ANSWER_TYPE_SETOFENUMERATED);
        $this->addInlineFieldChecks($varname, $variable, $var, $ids);
        $order = $var->getEnumeratedOrder();
        $itemspercolumn = $var->getEnumeratedColumns();
        if ($itemspercolumn == "") {
            $itemspercolumn = sizeof($orderedoptions);
        }
        $splitup = array_chunk($orderedoptions, $itemspercolumn);
        $tableid = $var->getSeid() . '-' . $var->getVsid();
        $returnStr .= "<table id='table-" . $tableid . "' >";
        $label = $var->getEnumeratedLabel();
        $dkrfna = $this->addDKRFNAButton(substr($realvarname, 0, strlen($realvarname) - 2), $var, $variable, false, '', $id);
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            }
        }

        //foreach ($orderedoptions as $option) {
        for ($i = 0; $i < $itemspercolumn; $i++) {
            $returnStr .= "<tr>";
            $j = 0;
            foreach ($splitup as $s) {
                if (isset($s[$i])) {
                    $option = $s[$i];
                    if (trim($option["label"] != "")) {

                        switch ($label) {
                            case ENUMERATED_LABEL_INPUT_ONLY:
                                $labelstr = "";
                                break;
                            case ENUMERATED_LABEL_LABEL_ONLY:
                                $labelstr = $option["label"];
                                break;
                            case ENUMERATED_LABEL_LABEL_CODE:
                                $labelstr = "(" . $option["code"] . ") " . $option["label"];
                                break;
                            case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                                $acr = "";
                                if (trim($option["acronym"]) != "") {
                                    $acr = " " . $option["acronym"];
                                }
                                $labelstr = "(" . $option["code"] . $acr . ") " . $option["label"];
                                break;
                            default:
                                $labelstr = $option["label"];
                                break;
                        }

                        $disabled = '';
                        if ($this->isEnumeratedActive($variable, $var, $option["code"]) == false) {
                            $disabled = ' disabled ';
                        }

                        $selected = '';
                        if (inArray($option["code"], explode(SEPARATOR_SETOFENUMERATED, $previousdata))) {
                            $selected = ' CHECKED';
                        }
                        
                        if ($j > 0) {
                            $returnStr .= '<td class="uscic-enumerated-column-padding"><nobr/></td>';
                        }

                        if ($order == ORDER_LABEL_FIRST) {
                            $returnStr .= '<td class="uscic-table-row-cell-enumerated' . $disabled . '" id="cell' . $id . '_' . $option["code"] . '" ><label class="uscic-checkbox-label-first-label' . $disabled . '" for="' . $id . '_' . $option["code"] . '"><span id="vsid_option' . $var->getVsid() . $option["code"] . '" uscic-target="vsid_' . $var->getVsid() . '" uscic-answercode="' . $option["code"] . '" uscic-texttype="' . SETTING_OPTIONS . '" class="' . $this->inlineeditable . '">' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '</span></label>
                                        <script type="text/javascript">' . minifyScript('$( document ).ready(function() {

                                                $("#' . $id . '_' . $option["code"] . '").click(function (e) {     
                                                    e.stopPropagation();    
                                                });

                                                    $("#cell' . $id . '_' . $option["code"] . '").click(function (e) {  
                                                        e.preventDefault();
                                                                          if ($("#' . $id . '_' . $option["code"] . '").prop("checked")) {
                                                                              $("#' . $id . '_' . $option["code"] . '").prop("checked", false);
                                                                              $("#' . $id . '_' . $option["code"] . '").change();
                                                                          }      
                                                                          else {
                                                                              $("#' . $id . '_' . $option["code"] . '").prop("checked", true);
                                                                              $("#' . $id . '_' . $option["code"] . '").change();
                                                                          }      
                                                                          
                                                                          e.stopPropagation();  

                                                                          });
                                                                         });') . '</script>


                                        </td>
                                                <td class="uscic-table-row-cell-enumerated' . $disabled . '">
                                                <div class="checkbox uscic-checkbox-label-first-checkbox' . $disabled . '">
                                                
                                                    <input ' . $disabled . $dkrfnaclass . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' type=checkbox id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>
                                                
                                                </div>
                                             </td>
                                            ';
                        } else {
                            $returnStr .= '<td class="uscic-table-row-cell-enumerated' . $disabled . '"><div class="checkbox uscic-checkbox' . $inlineclass . ' ' . $qa . '">
                                        <label class="uscic-checkbox-label' . $disabled . '" for="' . $id . '_' . $option["code"] . '">
                                        <div class="uscic-checkbox-box">                                            
                                       <input ' . $disabled . $dkrfnaclass . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' type=checkbox id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '><span id="vsid_option' . $var->getVsid() . $option["code"] . '" uscic-target="vsid_' . $var->getVsid() . '" uscic-answercode="' . $option["code"] . '" uscic-texttype="' . SETTING_OPTIONS . '" class="' . $this->inlineeditable . '">' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '</span>                                        
                                        </div>           
                                        </label>
                                        </div></td>
                                        ';
                        }
                    }
                }
                $j++;
            }
            $returnStr .= "</tr>";
        }

        if ($var->isInputMaskEnabled()) {
            $returnStr .= $this->displayCheckBoxUnchecking($id, $var->getInvalidSubSelected());
        }

        $returnStr .= "</table>";        

        if ($textbox) {
            $returnStr .= "<div class='uscic-checkbox-vertical-textbox'>";
            $returnStr .= $this->addSetOfEnumeratedTextBox($variable, $var, $realvarname, $varname, $id, $previousdata);
            $returnStr .= '</div>';
            $returnStr .= '</div>';
        } else {
            $returnStr .= '</div>';
            $returnStr .= $this->addSetOfEnumeratedHidden($variable, $var, $realvarname, $varname, $id, $previousdata);
        }

        // add dk/rf/na
        $returnStr .= $dkrfna;

        return $returnStr;
    }

    function showSetOfEnumeratedHorizontal($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript) {

        $returnStr = "";
        $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);

        $orderedoptions = $options;
        $order = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_RANDOMIZER);
        if ($order != "") {
            $arr = $this->engine->getAnswer($order);
            //print_r($arr);
            if (is_array($arr) && sizeof($arr) > 0) {
                $orderedoptions = array();
                foreach ($arr as $a) {
                    foreach ($options as $option) {
                        if ($option["code"] == $a) {
                            $orderedoptions[] = $option;
                            break;
                        }
                    }
                }
            }
        }
        $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">';
        $ids = array();
        $optioncodes = array();
        foreach ($orderedoptions as $option) {
            $optioncodes[] = $option["code"];
            if (trim($option["label"] != "")) {
                $ids[] = $id . '_' . $option["code"];
            }
        }


        $realvarname = $varname;

        /* we will have a text box entry OR a hidden field that tracks the entries, so that will be the real variable we look at in POST */
        $varname = $id . "_name[]";
        $textbox = $var->isEnumeratedTextbox();
        if ($textbox) {
            $message = $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_SETOFENUMERATED_ENTERED);
            $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUMERATED_ENTERED, '"' . implode("-", $optioncodes) . '"'), $message);
        }

        $this->addSetOfEnumeratedChecks($varname, $variable, $var, ANSWER_TYPE_SETOFENUMERATED);
        $this->addInlineFieldChecks($varname, $variable, $var, $ids);
        $inlinejavascript = $this->engine->getFill($variable, $var, SETTING_JAVASCRIPT_WITHIN_ELEMENT);

        $align = $var->getAnswerAlignment();
        $qa = "";
        switch ($align) {
            case ALIGN_LEFT:
                $qa = "text-left";
                break;
            case ALIGN_RIGHT:
                $qa = "text-right";
                break;
            case ALIGN_JUSTIFIED:
                $qa = "text-justify";
                break;
            case ALIGN_CENTER:
                $qa = "text-center";
                break;
            default:
                break;
        }

        $label = $var->getEnumeratedLabel();

        /* check for splitting */
        $split = $var->isEnumeratedSplit();
        $bordered = "";
        if ($var->isEnumeratedBordered()) {
            $bordered = "table-bordered";
        }

        /* start table */
        if ($var->isTableMobile() == true) {
            $returnStr .= $this->displayTableSaw();
        }
        $noofcolumns = sizeof($orderedoptions);
        $cellwidth = "width=" . round(100 / $noofcolumns) . "%";
        $order = $var->getEnumeratedOrder();
        if ($order == ORDER_LABEL_FIRST) {
            $nolabels = "data-tablesaw-preappend";
            if ($var->isTableMobileLabels()) {
                $nolabels = "data-tablesaw-no-labels";
            }             
            $returnStr .= '<table data-tablesaw-firstcolumn ' . $nolabels . ' data-tablesaw-mode="stack" id="table_' . $var->getName() . '" class="tablesaw tablesaw-stack table ' . $bordered . ' uscic-table-setofenumerated-horizontal">';
        } else {
            $nolabels = "data-tablesaw-postappend";
            if ($var->isTableMobileLabels() == false) {
                $nolabels = "data-tablesaw-no-labels";
            } 
            //echo $nolabels;
            $returnStr .= '<table data-tablesaw-firstcolumn ' . $nolabels. ' data-tablesaw-mode="stack" id="table_' . $var->getName() . '" class="tablesaw tablesaw-stack table ' . $bordered . ' uscic-table-setofenumerated-horizontal">';
        }

        /* split, then add header */
        if ($split == true) {

            if ($order == ORDER_LABEL_FIRST) {
                $returnStr .= "<thead><tr class='uscic-table-row-header-enumerated'>";
            } else {
                $returnStr .= "<thead><tr style='display: none;'>";
            }

            $headeralign = $var->getHeaderAlignment();
            $hqa = "";
            switch ($headeralign) {
                case ALIGN_LEFT:
                    $hqa = "text-left";
                    break;
                case ALIGN_RIGHT:
                    $hqa = "text-right";
                    break;
                case ALIGN_JUSTIFIED:
                    $hqa = "text-justify";
                    break;
                case ALIGN_CENTER:
                    $hqa = "text-center";
                    break;
                default:
                    break;
            }

            foreach ($orderedoptions as $option) {

                switch ($label) {
                    case ENUMERATED_LABEL_INPUT_ONLY:
                        $labelstr = "";
                        break;
                    case ENUMERATED_LABEL_LABEL_ONLY:
                        $labelstr = $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE:
                        $labelstr = "(" . $option["code"] . ") " . $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                        $acr = "";
                        if (trim($option["acronym"]) != "") {
                            $acr = " " . $option["acronym"];
                        }
                        $labelstr = "(" . $option["code"] . $acr . ") " . $option["label"];
                        break;
                    default:
                        $labelstr = $option["label"];
                        break;
                }
                $returnStr .= "<th class='uscic-table-row-cell-header-setofenumerated'><div class='" . $hqa . "'><span id='vsid_option" . $var->getVsid() . $option["code"] . "' uscic-target='vsid_" . $var->getVsid() . "' uscic-answercode='" . $option["code"] . "' uscic-texttype='" . SETTING_OPTIONS . "' class='" . $this->inlineeditable . "'>" . $this->applyFormatting($labelstr, $var->getHeaderFormatting()) . "</span></div></th>";
            }

            $returnStr .= "</tr></thead>";
        } else {
            $returnStr .= "<thead><tr style='display: none;'>";
            foreach ($orderedoptions as $o) {
                $returnStr .= "<th></th>";
            }
            $returnStr .= '</tr></thead>';
        }


        $returnStr .= "<tbody><tr class='uscic-table-row-enumerated-horizontal'>";

        $dkrfna = $this->addDKRFNAButton(substr($realvarname, 0, strlen($realvarname) - 2), $var, $variable, false, '', $id);
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            }
        }


        /* split between label and input */
        foreach ($orderedoptions as $option) {

            if (trim($option["label"]) != "") {
                $selected = '';
                if (inArray($option["code"], explode(SEPARATOR_SETOFENUMERATED, $previousdata))) {
                    $selected = ' CHECKED';
                }

                $disabled = '';
                if ($this->isEnumeratedActive($variable, $var, $option["code"]) == false) {
                    $disabled = ' disabled ';
                }

                if ($split == false) {

                    if ($split == false) {
                        switch ($label) {
                            case ENUMERATED_LABEL_INPUT_ONLY:
                                $labelstr = "";
                                break;
                            case ENUMERATED_LABEL_LABEL_ONLY:
                                $labelstr = $option["label"];
                                break;
                            case ENUMERATED_LABEL_LABEL_CODE:
                                $labelstr = $option["code"] . " " . $option["label"];
                                break;
                            case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                                $acr = "";
                                if (trim($option["acronym"]) != "") {
                                    $acr = " (" . $option["acronym"] . ")";
                                }
                                $labelstr = $option["code"] . $acr . " " . $option["label"];
                                break;
                            default:
                                $labelstr = $option["label"];
                                break;
                        }

                        if ($order == ORDER_LABEL_FIRST) {
                            $returnStr .= '<td ' . $cellwidth . ' id="cell' . $id . '_' . $option["code"] . '" class="uscic-table-row-cell-setofenumerated-horizontal' . $disabled . '"><div class="checkbox uscic-setofenumerated-horizontal">
                                        <label for="' . $id . '_' . $option["code"] . '" id="label' . $id . '_' . $option["code"] . '" class="uscic-table-setofenumerated-label' . $disabled . '"></label>';
                            $returnStr .= '<span id="vsid_option' . $var->getVsid() . $option["code"] . '" uscic-target="vsid_' . $var->getVsid() . '" uscic-answercode="' . $option["code"] . '" uscic-texttype="' . SETTING_OPTIONS . '" class="' . $this->inlineeditable . '">' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '</span>
                                            <div class="uscic-checkbox' . $inlineclass . ' ' . $qa . '">
                                                <input ' . $disabled . ' class="uscic-checkbox-table ' . $dkrfnaclass . '" ' . $this->getErrorTextString($varname) . ' ' . $inlinejavascript . ' type=checkbox id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>';
                            $returnStr .= '</div></div>';
                        } else {
                            $returnStr .= '<td ' . $cellwidth . ' id="cell' . $id . '_' . $option["code"] . '" class="uscic-table-row-cell-setofenumerated-horizontal' . $disabled . '"><div class="checkbox uscic-setofenumerated-horizontal">
                                        <label for="' . $id . '_' . $option["code"] . '" id="label' . $id . '_' . $option["code"] . '" class="uscic-table-setofenumerated-label' . $disabled . '">

                                            <div class="uscic-checkbox' . $inlineclass . ' ' . $qa . '">

                                                <input ' . $disabled . ' class="uscic-checkbox-table ' . $dkrfnaclass . '" ' . $this->getErrorTextString($varname) . ' ' . $inlinejavascript . ' type=checkbox id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>';

                            $returnStr .= '<span id="vsid_option' . $var->getVsid() . $option["code"] . '" uscic-target="vsid_' . $var->getVsid() . '" uscic-answercode="' . $option["code"] . '" uscic-texttype="' . SETTING_OPTIONS . '" class="' . $this->inlineeditable . '">' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '</span>';
                            $returnStr .= '</div></label></div>';
                        }
                    }
                } else {

                    $returnStr .= '<td id="cell' . $id . '_' . $option["code"] . '" class="uscic-table-row-cell-setofenumerated-horizontal' . $disabled . '" ' . $cellwidth . '><div class="' . $qa . '">

                                        <label id="label' . $id . '_' . $option["code"] . '" for="' . $id . '_' . $option["code"] . '" class="uscic-table-setofenumerated-label' . $disabled . '">

                                        <div class="form-group uscic-table-row-cell-form-group">

                                        <input ' . $disabled . ' class="uscic-checkbox-table ' . $dkrfnaclass . '" ' . $this->getErrorTextString($varname) . ' ' . $inlinejavascript . ' type=checkbox id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>

                                        </div>

                                        </label>

                                   </div>';
                }
                if ($disabled == '') {
                    $returnStr .= '<script type="text/javascript">' . minifyScript('$( document ).ready(function() {

                                                    $("#' . $id . '_' . $option["code"] . '").click(function (e) {     
                                                        e.stopPropagation();    
                                                    });

                                                        $("#cell' . $id . '_' . $option["code"] . '").click(function (e) {                                                        
                                                            e.preventDefault();
                                                                              if ($("#' . $id . '_' . $option["code"] . '").prop("checked")) {
                                                                                  $("#' . $id . '_' . $option["code"] . '").prop("checked", false);
                                                                                  $("#' . $id . '_' . $option["code"] . '").change();
                                                                              }      
                                                                              else {
                                                                                    $("#' . $id . '_' . $option["code"] . '").prop("checked", true);
                                                                                    $("#' . $id . '_' . $option["code"] . '").change();
                                                                              }      

                                                                              e.stopPropagation();  

                                                                              });
                                                                             });') . '</script>';
                }
                $returnStr .= '</td>';
            } else {
                $returnStr .= '<td class="uscic-table-row-cell-setofenumerated"></td>';
            }
        }
        $returnStr .= '</tr>';
        $returnStr .= '</tbody>';

        /* split, then add footer */
        if ($split == true) {

            if ($order == ORDER_OPTION_FIRST) {
                $returnStr .= "<tfoot><tr class='uscic-table-row-footer-enumerated'>";

                $headeralign = $var->getHeaderAlignment();
                $hqa = "";
                switch ($headeralign) {
                    case ALIGN_LEFT:
                        $hqa = "text-left";
                        break;
                    case ALIGN_RIGHT:
                        $hqa = "text-right";
                        break;
                    case ALIGN_JUSTIFIED:
                        $hqa = "text-justify";
                        break;
                    case ALIGN_CENTER:
                        $hqa = "text-center";
                        break;
                    default:
                        break;
                }

                foreach ($orderedoptions as $option) {

                    switch ($label) {
                        case ENUMERATED_LABEL_INPUT_ONLY:
                            $labelstr = "";
                            break;
                        case ENUMERATED_LABEL_LABEL_ONLY:
                            $labelstr = $option["label"];
                            break;
                        case ENUMERATED_LABEL_LABEL_CODE:
                            $labelstr = $option["code"] . " " . $option["label"];
                            break;
                        case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                            $acr = "";
                            if (trim($option["acronym"]) != "") {
                                $acr = " (" . $option["acronym"] . ")";
                            }
                            $labelstr = $option["code"] . $acr . " " . $option["label"];
                            break;
                        default:
                            $labelstr = $option["label"];
                            break;
                    }
                    $returnStr .= "<th class='uscic-table-row-cell-footer-setofenumerated'><div class='" . $hqa . "'><span id='vsid_option" . $var->getVsid() . $option["code"] . "' uscic-target='vsid_" . $var->getVsid() . "' uscic-answercode='" . $option["code"] . "' uscic-texttype='" . SETTING_OPTIONS . "' class='" . $this->inlineeditable . "'>" . $this->applyFormatting($labelstr, $var->getHeaderFormatting()) . "</span></div></th>";
                }

                $returnStr .= "</tr></tfoot>";
            }
        }
        $returnStr .= '</table>';

        if ($var->isInputMaskEnabled()) {
            $returnStr .= $this->displayCheckBoxUnchecking($id, $var->getInvalidSubSelected());
        }

        if ($textbox) {
            $returnStr .= "<div class='uscic-checkbox-horizontal-textbox'>";
            $returnStr .= $this->addSetOfEnumeratedTextBox($variable, $var, $realvarname, $varname, $id, $previousdata);
            $returnStr .= '</div>';
            $returnStr .= $dkrfna . '</div>';
        } else {
            $returnStr .= $dkrfna . '</div>';
            $returnStr .= $this->addSetOfEnumeratedHidden($variable, $var, $realvarname, $varname, $id, $previousdata);
        }
        return $returnStr;
    }

    function showSetOfEnumeratedCustom($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript) {
        $returnStr = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_CUSTOM);
        if (trim($returnStr) == "") {
            global $survey;
            if ($survey->getEnumeratedDisplay() == ORIENTATION_VERTICAL) {
                return $this->showSetOfEnumeratedVertical($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
            } else {
                return $this->showSetOfEnumeratedHorizontal($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
            }
        }

        $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);
        $orderedoptions = $options;
        $order = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_RANDOMIZER);
        if ($order != "") {
            $arr = $this->engine->getAnswer($order);
            //print_r($arr);
            if (is_array($arr) && sizeof($arr) > 0) {
                $orderedoptions = array();
                foreach ($arr as $a) {
                    foreach ($options as $option) {
                        if ($option["code"] == $a) {
                            $orderedoptions[] = $option;
                            break;
                        }
                    }
                }
            }
        }
        $ids = array();
        $optioncodes = array();
        foreach ($orderedoptions as $option) {
            $optioncodes[] = $option["code"];
            if (trim($option["label"] != "")) {
                $ids[] = $id . '_' . $option["code"];
            }
        }

        $realvarname = $varname;

        /* we will have a text box entry OR a hidden field that tracks the entries, so that will be the real variable we look at in POST */
        $varname = $id . "_name[]";
        $textbox = $var->isEnumeratedTextbox();
        if ($textbox) {
            $message = $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_SETOFENUMERATED_ENTERED);
            $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUMERATED_ENTERED, '"' . implode("-", $optioncodes) . '"'), $message);
        }

        $this->addSetOfEnumeratedChecks($varname, $variable, $var, ANSWER_TYPE_SETOFENUMERATED);
        $this->addInlineFieldChecks($varname, $variable, $var, $ids);
        $label = $var->getEnumeratedLabel();

        $dkrfna = $this->addDKRFNAButton(substr($realvarname, 0, strlen($realvarname) - 2), $var, $variable, false, '', $id);
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "class='dkrfna'";
            }
        }


        foreach ($orderedoptions as $option) {
            if (trim($option["label"] != "")) {
                $selected = '';
                if (inArray($option["code"], explode(SEPARATOR_SETOFENUMERATED, $previousdata))) {
                    $selected = ' CHECKED';
                }

                $disabled = '';
                if ($this->isEnumeratedActive($variable, $var, $option["code"]) == false) {
                    $disabled = ' disabled ';
                }

                $inputstr = '<input ' . $disabled . $dkrfnaclass . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' type=checkbox id=' . $id . '_' . $option["code"] . ' name=' . $varname . ' value=' . $option["code"] . $selected . '>';
                switch ($label) {
                    case ENUMERATED_LABEL_INPUT_ONLY:
                        $labelstr = "";
                        break;
                    case ENUMERATED_LABEL_LABEL_ONLY:
                        $labelstr = $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE:
                        $labelstr = "(" . $option["code"] . ") " . $option["label"];
                        break;
                    case ENUMERATED_LABEL_LABEL_CODE_VALUELABEL:
                        $acr = "";
                        if (trim($option["acronym"]) != "") {
                            $acr = " " . $option["acronym"];
                        }
                        $labelstr = "(" . $option["code"] . $acr . ") " . $option["label"];
                        break;
                    default:
                        $labelstr = $option["label"];
                        break;
                }
                $labelstr = '<span id="vsid_option' . $var->getVsid() . $option["code"] . '" uscic-target="vsid_' . $var->getVsid() . '" uscic-answercode="' . $option["code"] . '" uscic-texttype="' . SETTING_OPTIONS . '" class="' . $this->inlineeditable . '">' . $this->applyFormatting($labelstr, $var->getAnswerFormatting()) . '</span>';
                $returnStr = str_replace(PLACEHOLDER_ENUMERATED_OPTION . $option["code"] . '$', $inputstr, $returnStr);
                $returnStr = str_replace(PLACEHOLDER_ENUMERATED_TEXT . $option["code"] . '$', $labelstr, $returnStr);
            }
        }

        for ($i = 0; $i < 100; $i++) {
            $returnStr = str_replace(PLACEHOLDER_ENUMERATED_OPTION . $i . '$', $inputstr, $returnStr);
            $returnStr = str_replace(PLACEHOLDER_ENUMERATED_TEXT . $i . '$', $labelstr, $returnStr);
        }

        /* add form group for error display */
        $returnStr = '<div class="form-group uscic-formgroup' . $inlineclass . '">' . $returnStr;

        if ($textbox) {
            $returnStr .= "<div class='uscic-checkbox-custom-textbox'>";
            $returnStr .= $this->addSetOfEnumeratedTextBox($variable, $var, $varname, $id, $previousdata);
            $returnStr .= '</div>';

            $returnStr .= $dkrfna . '</div>';
        } else {
            $returnStr .= $dkrfna . '</div>';
            $returnStr .= $this->addSetOfEnumeratedHidden($variable, $var, $realvarname, $varname, $id, $previousdata);
        }

        if ($var->isInputMaskEnabled()) {
            $returnStr .= $this->displayCheckBoxUnchecking($id, $var->getInvalidSubSelected());
        }

        /* return result */
        return $returnStr;
    }

    function addSetOfEnumeratedTextbox($variable, $var, $varname, $refername, $id, $previousdata) {
        $returnStr = "";
        $pretext = $this->engine->getFill($variable, $var, SETTING_ENUMERATED_TEXTBOX_LABEL);
        $pretext = '<span class="input-group-addon uscic-inputaddon-pretext">' . $this->applyFormatting($pretext, $var->getAnswerFormatting()) . '</span>';
        $inputgroupstart = '<div class="input-group uscic-inputgroup-pretext">';
        $inputgroupend = "</div>";
        $style = "";

        $align = $var->getAnswerAlignment();
        $qa = "";
        switch ($align) {
            case ALIGN_LEFT:
                $qa = "text-left";
                break;
            case ALIGN_RIGHT:
                $qa = "text-right";
                break;
            case ALIGN_JUSTIFIED:
                $qa = "text-justify";
                break;
            case ALIGN_CENTER:
                $qa = "text-center";
                break;
            default:
                break;
        }

        if ($qa == "text-center") {
            $style = "style='display: block; margin-left: 40%; margin-right: 40%;'";
        } else if ($qa == "text-right") {
            $style = "style='display: block; margin-left: 80%; margin-right: 0%;'";
        }

        $max = sizeof($var->getOptions());
        $m = "\"'mask': '[9[" . SEPARATOR_SETOFENUMERATED . "]]', 'greedy': false, 'repeat': " . $max . "\"";
        $placeholder = $this->engine->getFill($variable, $var, SETTING_INPUT_MASK_PLACEHOLDER);
        $textmask = "data-inputmask=" . $m . " data-inputmask-placeholder='" . $placeholder . "'";
        $returnStr .= '<div ' . $style . ' class="uscic-checkbox-textbox ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input name="' . $varname . '" id="' . $id . '_textbox" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $textmask . ' class="form-control uscic-form-control" type=text value="' . addslashes($previousdata) . '">                                    
                                    ' . $inputgroupend . '
                                </div>
                                ';
        $returnStr .= "<script type=text/javascript>";
        $returnStr .= minifyScript('$( document ).ready(function() {
                            $("#' . $id . '_textbox").keyup(
                                    function(event) {
                                        var str = $(this).val();
                                        $("input[name=\'' . $refername . '\']").each(function(index) {
                                                $(this).prop("checked", false);                                                
                                            });
                                            
                                            var arr = str.split("' . SEPARATOR_SETOFENUMERATED . '");
                                            var outarr = [];
                                            for (var i=0; i < arr.length; i++) {
                                                if ($("#' . $id . '_' . '" + arr[i] + ":enabled").length) {    
                                                    $("#' . $id . '_' . '" + arr[i] + ":enabled").prop("checked", true);
                                                    $("#' . $id . '_' . '" + arr[i]).change();
                                                    outarr[i] = arr[i];
                                                }
                                            }     
                                            if (event.keyCode == 32 || event.keyCode == 189){
                                                $("#' . $id . '_textbox").val(outarr.join("' . SEPARATOR_SETOFENUMERATED . '") + "-");
                                            }
                                            else {
                                                $("#' . $id . '_textbox").val(arr.join("' . SEPARATOR_SETOFENUMERATED . '"));
                                            }
                                    });
                                $("input[name=\'' . $refername . '\']").on(\'change\', function(event) {                                        
                                        var str = $("#' . $id . '_textbox").val();
                                        var arr = [];    
                                        if (str != "") {    
                                            arr = str.split("' . SEPARATOR_SETOFENUMERATED . '");    
                                        }    
                                        var index = -1;
                                        var val = $(this).val();
                                        for (var i=0; i < arr.length; i++) {
                                            if (arr[i] == val) {
                                                index=i;
                                                break;
                                            }
                                        }
                                        if ($(this).prop("checked") == true) {
                                            if (index == -1) {                                                    
                                                arr.push($(this).val());
                                            }
                                        }
                                        else {
                                            if (index > -1) {
                                                arr.splice(index, 1);
                                            }
                                        }                                          
                                        $("#' . $id . '_textbox").val(arr.join("' . SEPARATOR_SETOFENUMERATED . '"));
                                        return;
                                });    
                                });
                                ');
        $returnStr .= "</script>";
        return $returnStr;
    }

    function addSetOfEnumeratedHidden($variable, $var, $varname, $refername, $id, $previousdata) {
        $returnStr = "";

        if ($this->dkrfna == true) {
            if (inArray($previousdata, array(ANSWER_DK, ANSWER_RF, ANSWER_NA))) {
                $previousdata = "";
            }
        }

        $returnStr .= '<input name="' . $varname . '" id="' . $id . '_hidden" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" type=hidden value="' . addslashes($previousdata) . '">';
        $returnStr .= "<script type=text/javascript>";
        $returnStr .= minifyScript('$( document ).ready(function() {
                                $("input[name=\'' . $refername . '\']").on(\'change\', function(event) {                                        
                                        var str = $("#' . $id . '_hidden").val();
                                        var arr = [];    
                                        if (str != "") {    
                                            arr = str.split("' . SEPARATOR_SETOFENUMERATED . '");    
                                        }    
                                        var index = -1;
                                        var val = $(this).val();
                                        for (var i=0; i < arr.length; i++) {
                                            if (arr[i] == val) {
                                                index=i;
                                                break;
                                            }
                                        }
                                        if ($(this).prop("checked") == true) {
                                            if (index == -1) {                                                    
                                                arr.push($(this).val());
                                            }
                                        }
                                        else {
                                            if (index > -1) {
                                                arr.splice(index, 1);
                                            }
                                        }
                                        $("#' . $id . '_hidden").val(arr.join("' . SEPARATOR_SETOFENUMERATED . '"));
                                        return;
                                });
                                });
                                ');
        $returnStr .= "</script>";
        return $returnStr;
    }

    function addDKRFNAButton($id, $var, $variable, $inline = false, $enumid = '', $setofenumname = "") {

        if ($this->dkrfna == false) {
            return '';
        }

        if ($inline == true && $this->dkrfnainline == false) {
            return '';
        }

        $str = "";
        $dk = $var->getShowDKButton();
        $rf = $var->getShowRFButton();
        $na = $var->getShowNAButton();
        if (!($dk == BUTTON_YES || $rf == BUTTON_YES || $na == BUTTON_YES)) {
            return '';
        }

        $type = $var->getAnswerType();
        if ($inline == true) { //inArray($type, array(ANSWER_TYPE_DATE, ANSWER_TYPE_DATETIME, ANSWER_TYPE_TIME, ANSWER_TYPE_INTEGER, ANSWER_TYPE_RANGE, ANSWER_TYPE_DOUBLE, ANSWER_TYPE_STRING))) {
            $str .= '<span class="form-group uscic-formgroup-dkrfna-inline">';
        }
        // below: open, radio/checkbox (not in enumerated table)
        else {
            $str .= '<span class="form-group uscic-formgroup-dkrfna">';
        }

        $linkedto = "";
        if ($inline == true && trim($enumid) != "") {
            $linkedto = ' linkedto="' . $enumid . '" ';
        }

        if ($dk == BUTTON_YES) {
            $sel = "";
            if ($this->engine->isDKAnswer($variable)) {
                $sel = "checked='true'";
            }
            $str .= '<input ' . $sel . $linkedto . ' class="bootstrapswitch" data-radio-all-off="true" data-label-text="' . ANSWER_DK . '" data-on-color="success" data-off-color="primary" data-on-text="<span class=\'glyphicon glyphicon-ok\'></span>" data-off-text="<span class=\'glyphicon glyphicon-pencil\'></span>" data-size="mini" id="' . $id . '_dk" type="radio" name="' . $id . '_dkrfna" value="' . ANSWER_DK . '">';
        }
        if ($rf == BUTTON_YES) {
            $sel = "";
            if ($this->engine->isRFAnswer($variable)) {
                $sel = "checked='true'";
            }
            $str .= '<input ' . $sel . $linkedto . ' class="bootstrapswitch" data-radio-all-off="true" data-label-text="' . ANSWER_RF . '" data-on-color="success" data-off-color="primary" data-on-text="<span class=\'glyphicon glyphicon-ok\'></span>" data-off-text="<span class=\'glyphicon glyphicon-pencil\'></span>" data-size="mini" id="' . $id . '_rf" type="radio" name="' . $id . '_dkrfna" value="' . ANSWER_RF . '">';
        }
        if ($na == BUTTON_YES) {
            $sel = "";
            if ($this->engine->isNAAnswer($variable)) {
                $sel = "checked='true'";
            }
            $str .= '<input ' . $sel . $linkedto . ' class="bootstrapswitch" data-radio-all-off="true" data-label-text="' . ANSWER_NA . '" data-on-color="success" data-off-color="primary" data-on-text="<span class=\'glyphicon glyphicon-ok\'></span>" data-off-text="<span class=\'glyphicon glyphicon-pencil\'></span>" data-size="mini" id="' . $id . '_na" type="radio" name="' . $id . '_dkrfna" value="' . ANSWER_NA . '">';
        }
        $str .= '</span>';

        // add script to handle input
        $strscript = "<script type='text/javascript'>";

        // text boxes
        if (inArray($type, array(ANSWER_TYPE_INTEGER, ANSWER_TYPE_RANGE, ANSWER_TYPE_DOUBLE, ANSWER_TYPE_STRING))) {
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
            $strscript .= '                       
                    if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {                        
                        $("input[name=\'' . $id . '\']").addClass("dkrfna");
                        $("[name=\'' . $id . '\']").val(""); 
                    }
                    else {
                        $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    }
                    ';
            $strscript .= "});";

            $strscript .= "$('[name=\"" . $id . "\"]').on('keyup', function(event) {if (this.value != ''){";
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    }';
            $strscript .= "});";
        } else if (inArray($type, array(ANSWER_TYPE_OPEN))) {
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
            $strscript .= '
                    if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {                        
                        $("[name=\'' . $id . '\']").addClass("dkrfna");
                        $("[name=\'' . $id . '\']").val("");                        
                    }
                    else {
                        $("[name=\'' . $id . '\']").removeClass("dkrfna");
                    }
                    ';
            $strscript .= "});";

            $strscript .= "$('[name=\"" . $id . "\"]').on('keyup', function(event) {if (this.value != ''){";
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("[name=\'' . $id . '\']").removeClass("dkrfna");
                    }';
            $strscript .= "});";
        } else if (inArray($type, array(ANSWER_TYPE_DATE, ANSWER_TYPE_DATETIME, ANSWER_TYPE_TIME))) {
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
            $strscript .= 'if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {                        
                        $("input[name=\'' . $id . '\']").addClass("dkrfna");
                        $("[name=\'' . $id . '\']").val("");    
                    }
                    else {
                        $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    }
                    ';
            $strscript .= "});";

            $strscript .= "$('input[name=\"" . $id . "\"]').on('click', function(event) {"; // if (this.value != ''){
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    '; // }
            $strscript .= "});";
        }
        // radio
        else if ($type == ANSWER_TYPE_ENUMERATED) {
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
            $strscript .= '                                   
                    if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) { 
                        $("input[name=\'' . $id . '\']").prop("checked", false); 
                        $("input[name=\'' . $id . '\']").addClass("dkrfna");
                    }
                    else {
                        $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    }    
                    ';
            $strscript .= "});";

            $strscript .= "$('input[name=\"" . $id . "\"]').on('change', function(event) {";
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    ';
            $strscript .= "});";
        }
        // checkbox
        else if ($type == ANSWER_TYPE_SETOFENUMERATED) {
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";

            // with textbox
            $textbox = $var->isEnumeratedTextbox();
            if ($textbox) {
                $strscript .= '$("input[name=\'' . $id . '[]\']").val("");';
                $strscript .= '
                    if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {                        
                        $("input[name=\'' . $setofenumname . '_name[]\']").addClass("dkrfna");                        
                        $("input[name=\'' . $setofenumname . '_name[]\']").prop("checked", false);    
                    }
                    else {
                        $("input[name=\'' . $setofenumname . '_name[]\']").removeClass("dkrfna");
                    }
                    ';
            }
            // without textbox
            else {
                $strscript .= '$("#' . $id . '_hidden").val("");';
                $strscript .= '                    
                    if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {                        
                        $("input[name=\'' . $setofenumname . '_name[]\']").addClass("dkrfna");
                        $("input[name=\'' . $setofenumname . '_name[]\']").prop("checked", false);
                    }
                    else {
                        $("input[name=\'' . $setofenumname . '_name[]\']").removeClass("dkrfna");
                    }
                    ';
            }

            $strscript .= "});";

            $strscript .= "$('input[name=\"" . $setofenumname . "_name[]\"]').on('change', function(event) {";
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("input[name=\'' . $setofenumname . '_name[]\']").removeClass("dkrfna");
                    ';
            $strscript .= "});";
        }
        // dropdown
        else if ($type == ANSWER_TYPE_DROPDOWN) {
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
            $strscript .= '                    
                    if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {                        
                        $("[name=\'' . $id . '\']").addClass("dkrfna");
                        $("[name=\'' . $id . '\']").selectpicker("val","");
                    }
                    else {
                        $("[name=\'' . $id . '\']").removeClass("dkrfna");
                    }
                    ';
            $strscript .= "});";

            $strscript .= "$('[name=\"" . $id . "\"]').on('change', function(event) {if ($(this).val() != ''){";
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("[name=\'' . $id . '\']").removeClass("dkrfna");
                    }';
            $strscript .= "});";
        } else if ($type == ANSWER_TYPE_MULTIDROPDOWN) {
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
            $strscript .= 'if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {
                        $("[name=\'' . $id . '[]\']").selectpicker("val", "");    
                        $("[name=\'' . $id . '[]\']").addClass("dkrfna");
                     }       
                     else {
                        $("[name=\'' . $id . '[]\']").removeClass("dkrfna");
                     }       
                    ';
            $strscript .= "});";

            $strscript .= "$('[name=\"" . $id . "[]\"]').on('change', function(event) {if ($(this).val() != ''){";
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("[name=\'' . $id . '[]\']").removeClass("dkrfna");
                    }';
            $strscript .= "});";
        } else if ($type == ANSWER_TYPE_SLIDER) {
            $realid = $var->getID();
            if ($realid == "") {
                $realid = $id;
            }
            $strscript .= "$('input[name=\"" . $id . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
            $strscript .= '
                     if ($("input[name=\'' . $id . '_dkrfna\']:checked").val()) {                        
                        var x = $("#' . $realid . '").slider();
                        x.slider("setValue", ""); 
                        $("#' . $realid . '_textbox").val("");
                        $("input[name=\'' . $id . '\']").addClass("dkrfna");
                    }
                    else {
                        $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    }
                    ';
            $strscript .= "});";

            // handle text box
            $textbox = $var->isTextbox();
            if ($textbox) {
                $strscript .= '$("#' . $realid . '_textbox").keyup(
                                    function(event) {
                                        $("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false")
                                    });';
            }

            // handle sliding
            $strscript .= "$('#" . $realid . "').on('slideStop', function(slideEvt) {";
            $strscript .= '$("input[name=\'' . $id . '_dkrfna\']").bootstrapSwitch("state", false, "false");
                    $("input[name=\'' . $id . '\']").removeClass("dkrfna");
                    ';
            $strscript .= "});";
        }


        $strscript .= "</script>";

        return $str . minifyScript($strscript);
    }

    function showAnswer($number, $variable, $var, $previousdata, $inline = false, $enumid = "") {

        //echo '<br/>showing: ' . $variable;
        /* if inline field, then don't show it UNLESS it is inline display */
        if ($this->engine->isInlineField($variable) && $inline == false) {
            return "";
        }
        $inlineclass = "";
        $hovertext = $this->engine->getFill($variable, $var, SETTING_HOVERTEXT);
        if ($hovertext != "") {
            $returnStr = "<div title='" . str_replace("'", "", $hovertext) . "' class='uscic-answer'>";
        } else {
            $returnStr = "<div class='uscic-answer'>";
        }
        if ($inline) {
            $inlineclass = "-inline";
            $returnStr = "";
        }

        $language = getSurveyLanguage();
        $varname = SESSION_PARAMS_ANSWER . $number;
        $id = $this->engine->getFill($variable, $var, SETTING_ID);
        if (trim($id) == "") {
            $id = $varname;
        }
        $answertype = $var->getAnswerType();
        if (inArray($answertype, array(ANSWER_TYPE_SETOFENUMERATED, ANSWER_TYPE_MULTIDROPDOWN))) {
            $varname .= "[]";
        }

        /* add required error check */
        if ($var->getIfEmpty() != IF_EMPTY_ALLOW) {

            /* if not inline OR inline but not in enumerated/set of enumerated */
            if ($inline == false || ($inline == true && trim($enumid) == "")) {
                if (inArray($var->getAnswerType(), array(ANSWER_TYPE_SETOFENUMERATED))) { // custom name for set of enumerated question, since we use a hidden field/textbox to track the real answer(s); we just use this custom name for referencing in the error checking
                    $this->addErrorCheck(SESSION_PARAMS_ANSWER . $number . "_name[]", new ErrorCheck(ERROR_CHECK_REQUIRED, "true"), $this->engine->getFill($variable, $var, SETTING_EMPTY_MESSAGE));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_REQUIRED, "true"), $this->engine->getFill($variable, $var, SETTING_EMPTY_MESSAGE));
                }
            }
        }
        $align = $var->getAnswerAlignment();
        $qa = "";
        switch ($align) {
            case ALIGN_LEFT:
                $qa = "text-left";
                break;
            case ALIGN_RIGHT:
                $qa = "text-right";
                break;
            case ALIGN_JUSTIFIED:
                $qa = "text-justify";
                break;
            case ALIGN_CENTER:
                $qa = "text-center";
                break;
            default:
                break;
        }

        /* hide dk/rf/na */
        if (inArray($previousdata, array(ANSWER_DK, ANSWER_RF, ANSWER_NA))) {
            $previousdata = "";
        }

        $pretext = "";
        $posttext = "";
        $inputgroupstart = '';
        $inputgroupend = "";
        if (inArray($answertype, array(ANSWER_TYPE_STRING, ANSWER_TYPE_INTEGER, ANSWER_TYPE_RANGE, ANSWER_TYPE_DOUBLE))) {
            $pretext = $this->engine->getFill($variable, $var, SETTING_PRETEXT);
            $posttext = $this->engine->getFill($variable, $var, SETTING_POSTTEXT);
            $answerformat = "";
            if ($pretext != "") {
                $answerformat = $var->getAnswerFormatting();
                $pretext = '<span id="vsid_' . $var->getVsid() . '" uscic-texttype="' . SETTING_PRETEXT . '" class="input-group-addon uscic-inputaddon-pretext' . $this->inlineeditable . '">' . $this->applyFormatting($pretext, $answerformat) . '</span>';
                $inputgroupstart = '<div class="input-group uscic-inputgroup-pretext">';
                $inputgroupend = "</div>";
            }
            if ($posttext != "") {
                if ($answerformat == "") {
                    $answerformat = $var->getAnswerFormatting();
                }
                $posttext = '<div id="vsid_' . $var->getVsid() . '" uscic-texttype="' . SETTING_POSTTEXT . '" class="input-group-addon uscic-inputaddon-posttext' . $this->inlineeditable . '">' . $this->applyFormatting($posttext, $answerformat) . '</div>';
                $inputgroupstart = '<div class="input-group uscic-inputgroup-posttext">';
                $inputgroupend = "</div>";
            }
        }

        $inlinejavascript = $this->engine->getFill($variable, $var, SETTING_JAVASCRIPT_WITHIN_ELEMENT);
        $inlinestyle = $this->engine->getFill($variable, $var, SETTING_STYLE_WITHIN_ELEMENT);
        $placeholder = $this->engine->getFill($variable, $var, SETTING_PLACEHOLDER);
        if (trim($placeholder) != "") {
            $placeholder = " placeholder='" . $placeholder . "' ";
        }
        $linkedto = "";
        if ($inline == true && trim($enumid) != "") {
            $linkedto = ' linkedto="' . $enumid . '" ';
        }

        /* add any comparison checks */
        $this->addComparisonChecks($var, $variable, $varname);

        /* any individual dk/rf/na */
        $dkrfna = $this->addDKRFNAButton($varname, $var, $variable, $inline, $enumid);
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            }
        }

        /* add answer display */
        switch ($answertype) {
            case ANSWER_TYPE_STRING: //string                
                $minimumlength = $this->engine->getFill($variable, $var, SETTING_MINIMUM_LENGTH);
                $maximumlength = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_LENGTH);
                if ($minimumlength > 0) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MINLENGTH, $minimumlength), replacePlaceHolders(array(PLACEHOLDER_MINIMUM_LENGTH => $minimumlength), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MINIMUM_LENGTH)));
                }
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MAXLENGTH, $maximumlength), replacePlaceHolders(array(PLACEHOLDER_MAXIMUM_LENGTH => $maximumlength), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MAXIMUM_LENGTH)));

                $minwords = $this->engine->getFill($variable, $var, SETTING_MINIMUM_WORDS);
                $maxwords = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_WORDS);
                if ($minwords > 0) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MINWORDS, $minwords), replacePlaceHolders(array(PLACEHOLDER_MINIMUM_WORDS => $minwords), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MINIMUM_WORDS)));
                }
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MAXWORDS, $maxwords), replacePlaceHolders(array(PLACEHOLDER_MAXIMUM_WORDS => $maxwords), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MAXIMUM_WORDS)));

                /* input masking */
                $textmask = $this->addInputMasking($varname, $variable, $var);

                // placeholder: , "placeholder": "*"
                $pattern = $this->engine->getFill($variable, $var, SETTING_PATTERN);
                if (trim($pattern) != "") {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_PATTERN, $pattern), replacePlaceHolders(array(PLACEHOLDER_PATTERN => $pattern), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_PATTERN)));
                }
                if ($inline) {
                    $returnStr .= '<label>
                                <div class="uscic-string ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input class="form-control uscic-form-control-inline ' . $dkrfnaclass . '" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $linkedto . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="uscic-string' . $inlineclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                    ' . $posttext . $inputgroupend . $dkrfna . '
                                </div>
                                </label>';
                } else {
                    $returnStr .= $this->displayZipScripts();
                    $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">
                                <label>
                                    <div class="uscic-string ' . $qa . '">' . $inputgroupstart . $pretext . '                                    
                                    <input spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="form-control uscic-form-control ' . $dkrfnaclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                        ' . $posttext . $inputgroupend . $dkrfna . '
                                    </div>    
                                </label>                                
                                </div>
                                ';
                }
                break;
            case ANSWER_TYPE_ENUMERATED: //enumerated    
                $dis = $var->getEnumeratedDisplay();
                if ($dis == ORIENTATION_HORIZONTAL) {
                    $returnStr .= $this->showEnumeratedHorizontal($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
                } else if ($dis == ORIENTATION_VERTICAL) {
                    $returnStr .= $this->showEnumeratedVertical($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
                } else {
                    $returnStr .= $this->showEnumeratedCustom($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
                }
                break;
            case ANSWER_TYPE_SETOFENUMERATED: //set of enumerated   
                $dis = $var->getEnumeratedDisplay();
                if ($dis == ORIENTATION_HORIZONTAL) {
                    $returnStr .= $this->showsetOfEnumeratedHorizontal($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
                } else if ($dis == ORIENTATION_VERTICAL) {
                    $returnStr .= $this->showSetOfEnumeratedVertical($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
                } else {
                    $returnStr .= $this->showSetOfEnumeratedCustom($id, $varname, $variable, $var, $previousdata, $inlineclass, $inlinestyle, $inlinejavascript);
                }
                break;
            case ANSWER_TYPE_DROPDOWN: //drop down
                $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);
                $returnStr .= $this->showComboBox($variable, $var, $varname, $id, $options, $previousdata, $inline, "", $linkedto);
                break;
            case ANSWER_TYPE_MULTIDROPDOWN: //multiple selection dropdown                
                $this->addSetOfEnumeratedChecks($varname, $variable, $var, ANSWER_TYPE_MULTIDROPDOWN);
                $options = $this->engine->getFill($variable, $var, SETTING_OPTIONS);
                $returnStr .= $this->showComboBox($variable, $var, $varname, $id, $options, $previousdata, $inline, "multiple", $linkedto);
                break;
            case ANSWER_TYPE_INTEGER: //integer 

                /* input masking */
                $textmask = $this->addInputMasking($varname, $variable, $var);

                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_INTEGER, "true"), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INTEGER));
                if ($inline) {
                    $returnStr .= '<div class="form-group uscic-formgroup-inline"><label>
                                <div class="uscic-integer ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input class="form-control uscic-form-control-inline ' . $dkrfnaclass . '" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $linkedto . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="uscic-integer' . $inlineclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                    ' . $posttext . $inputgroupend . $dkrfna . '
                                </div>
                                </label></div>';
                } else {
                    $returnStr .= '<div class="form-group uscic-formgroup">
                                <label>
                                <div class="uscic-integer ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="form-control uscic-form-control ' . $dkrfnaclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                    ' . $posttext . $inputgroupend . $dkrfna . '
                                </div>
                                </label>
                                </div>';
                }
                break;
            case ANSWER_TYPE_DOUBLE: //double

                /* input masking */
                $textmask = $this->addInputMasking($varname, $variable, $var);

                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_NUMBER, "true"), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_DOUBLE));
                if ($inline) {
                    $returnStr .= '<div class="form-group uscic-formgroup-inline"><label>
                                <div class="uscic-double ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input class="form-control uscic-form-control-inline ' . $dkrfnaclass . '" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $linkedto . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="uscic-double' . $inlineclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                    ' . $posttext . $inputgroupend . $dkrfna . '
                                </div>
                                </label></div>';
                } else {
                    $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">
                                <label>
                                <div class="uscic-double ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="form-control uscic-form-control ' . $dkrfnaclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                ' . $posttext . $inputgroupend . $dkrfna . '</div>
                                </label>
                                </div>';
                }
                break;
            case ANSWER_TYPE_RANGE: //range

                /* input masking */
                $textmask = $this->addInputMasking($varname, $variable, $var);
                $minimum = $this->engine->getFill($variable, $var, SETTING_MINIMUM_RANGE);
                if ($minimum == "" || !is_numeric($minimum)) {
                    $minimum = ANSWER_RANGE_MINIMUM;
                }
                $maximum = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_RANGE);
                if ($maximum == "" || !is_numeric($maximum)) {
                    $maximum = ANSWER_RANGE_MAXIMUM;
                }
                $others = $this->engine->getFill($variable, $var, SETTING_OTHER_RANGE);
                if (trim($others) == "") {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_RANGE, "[" . $minimum . "," . $maximum . "]"), replacePlaceHolders(array(PLACEHOLDER_MINIMUM => $minimum, PLACEHOLDER_MAXIMUM => $maximum), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_RANGE)));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_RANGE_CUSTOM, "'" . $minimum . "," . $maximum . ";" . $others . "'"), replacePlaceHolders(array(PLACEHOLDER_MINIMUM => $minimum, PLACEHOLDER_MAXIMUM => $maximum), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_RANGE)));
                }
                if (!(contains($minimum, ".") || contains($maximum, "."))) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_INTEGER, "true"), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INTEGER));
                }
                if ($inline) {
                    $returnStr .= '<div class="form-group uscic-formgroup-inline"><label>
                                <div class="uscic-range ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input class="form-control uscic-form-control-inline ' . $dkrfnaclass . '" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $linkedto . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="uscic-range' . $inlineclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                    ' . $posttext . $inputgroupend . $dkrfna . '
                                </div>
                                </label></div>';
                } else {
                    $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">                                
                                <label>
                                <div class="uscic-range ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $textmask . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $this->getErrorTextString($varname) . ' class="form-control uscic-form-control ' . $dkrfnaclass . '" type=text id=' . $id . ' name=' . $varname . ' value="' . convertHTLMEntities($previousdata, ENT_QUOTES) . '">
                                    ' . $posttext . $inputgroupend . $dkrfna .
                            '</div>
                                </label>
                                </div>';
                }
                break;
            case ANSWER_TYPE_SLIDER: //slider
                $minimum = $this->engine->getFill($variable, $var, SETTING_MINIMUM_RANGE);
                $maximum = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_RANGE);
                if ($minimum == "" || !is_numeric($minimum)) {
                    $minimum = ANSWER_RANGE_MINIMUM;
                }
                $maximum = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_RANGE);
                if ($maximum == "" || !is_numeric($maximum)) {
                    $maximum = ANSWER_RANGE_MAXIMUM;
                }
                $orientation = "horizontal";
                if ($var->getSliderOrientation() == ORIENTATION_VERTICAL) {
                    $orientation = "vertical";
                }
                $step = $this->engine->replaceFills($var->getIncrement());
                $tooltip = "show";
                if ($var->getTooltip() == TOOLTIP_NO) {
                    $tooltip = "hide";
                }

                $returnStr .= $this->displaySlider($variable, $var, $varname, $id, $previousdata, $minimum, $maximum, $this->getErrorTextString($varname), $qa, $inlineclass, $step, $tooltip, $orientation, $dkrfna, $linkedto);
                break;
            case ANSWER_TYPE_DATE: //date                
                $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">
                              <div class="uscic-date' . $inlineclass . ' ' . $qa . '">
                                <label>
                                ' . $this->displayDateTimePicker($varname, $id, $previousdata, getSurveyLanguagePostFix(getSurveyLanguage()), "true", "false", Config::usFormatSurvey(), Config::secondsSurvey(), Config::minutesSurvey(), $inlineclass, $inlinestyle, $inlinejavascript, $this->engine->replaceFills($var->getDateFormat()), $this->getErrorTextString($varname), $dkrfna, $variable, $linkedto) . '
                                </label>
                                </div>
                                </div>';
                break;
            case ANSWER_TYPE_TIME: //time
                $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">
                                <div class="uscic-time' . $inlineclass . ' ' . $qa . '">
                                <label>
                                ' . $this->displayDateTimePicker($varname, $id, $previousdata, getSurveyLanguagePostFix(getSurveyLanguage()), "false", "true", Config::usFormatSurvey(), Config::secondsSurvey(), Config::minutesSurvey(), $inlineclass, $inlinestyle, $inlinejavascript, $this->engine->replaceFills($var->getTimeFormat()), $this->getErrorTextString($varname), $dkrfna, $variable, $linkedto) . '
                                </label>
                                </div>
                                </div>';
                break;
            case ANSWER_TYPE_DATETIME: //date/time     
                $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">
                                <div class="uscic-datetime' . $inlineclass . ' ' . $qa . '">
                                <label>
                                ' . $this->displayDateTimePicker($varname, $id, $previousdata, getSurveyLanguagePostFix(getSurveyLanguage()), "true", "true", Config::usFormatSurvey(), Config::secondsSurvey(), Config::minutesSurvey(), $inlineclass, $inlinestyle, $inlinejavascript, $this->engine->replaceFills($var->getDateTimeFormat()), $this->getErrorTextString($varname), $dkrfna, $variable, $linkedto) . '
                                </label>
                                </div>
                                </div>';
                break;
            case ANSWER_TYPE_OPEN: //open
                $minimumlength = $this->engine->getFill($variable, $var, SETTING_MINIMUM_LENGTH);
                $maximumlength = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_LENGTH);
                if ($minimumlength > 0) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MINLENGTH, $minimumlength), replacePlaceHolders(array(PLACEHOLDER_MINIMUM_LENGTH => $minimumlength), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MINIMUM_LENGTH)));
                }
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MAXLENGTH, $maximumlength), replacePlaceHolders(array(PLACEHOLDER_MAXIMUM_LENGTH => $maximumlength), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MAXIMUM_LENGTH)));

                $minwords = $this->engine->getFill($variable, $var, SETTING_MINIMUM_WORDS);
                $maxwords = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_WORDS);
                if ($minwords > 0) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MINWORDS, $minwords), replacePlaceHolders(array(PLACEHOLDER_MINIMUM_WORDS => $minimumwords), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MINIMUM_WORDS)));
                }
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_MAXWORDS, $maxwords), replacePlaceHolders(array(PLACEHOLDER_MAXIMUM_WORDS => $maximumwords), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MAXIMUM_WORDS)));

                $pattern = $this->engine->getFill($variable, $var, SETTING_PATTERN);
                if (trim($pattern) != "") {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_PATTERN, $pattern), replacePlaceHolders(array(PLACEHOLDER_PATTERN => $pattern), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_PATTERN)));
                }

                if ($inline) {
                    $returnStr .= '<div class="form-group uscic-formgroup-inline"><label>
                                <textarea spellcheck="false" autocorrect="off" autocapitalize="off" ' . $placeholder . $linkedto . $inlinestyle . ' ' . $inlinejavascript . ' ' . $qa . ' ' . $this->getErrorTextString($varname) . ' id=' . $id . ' class="uscic-open-inline' . $inlineclass . ' ' . $dkrfnaclass . '" name=' . $varname . '>' . convertHTLMEntities($previousdata, ENT_QUOTES) . '</textarea>' . $dkrfna . '
                                </label></div>';
                } else {
                    $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">
                                <label>
                                <div class="uscic-open">
                                <textarea spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off" ' . $placeholder . $inlinestyle . ' ' . $inlinejavascript . ' ' . $qa . ' ' . $this->getErrorTextString($varname) . ' id=' . $id . ' class="form-control uscic-form-control ' . $dkrfnaclass . '" name=' . $varname . '>' . convertHTLMEntities($previousdata, ENT_QUOTES) . '</textarea>
                                    </div>' . $dkrfna . '
                                </label>                                
                                </div>';
                }
                break;
            case ANSWER_TYPE_CALENDAR: //calendar
                $maximum = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_CALENDAR);
                $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">
                            <div class="uscic-calendar' . $inlineclass . '">' . $this->showCalendar($varname, $id, $previousdata, $maximum, "en", true) . '
                            <p style="display:none" id="' . $id . '_help" class="help-block">You can only select a maximum of ' . $maximum . ' days.</p>
                             </div>
                             </div>';

                break;
            case ANSWER_TYPE_CUSTOM: //custom   
                /* input masking */
                $textmask = $this->addInputMasking($varname, $variable, $var);
                $minimum = $this->engine->getFill($variable, $var, SETTING_MINIMUM_RANGE);
                if ($minimum == "" || !is_numeric($minimum)) {
                    $minimum = ANSWER_RANGE_MINIMUM;
                }
                $maximum = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_RANGE);
                if ($maximum == "" || !is_numeric($maximum)) {
                    $maximum = ANSWER_RANGE_MAXIMUM;
                }
                $others = $this->engine->getFill($variable, $var, SETTING_OTHER_RANGE);
                if (trim($others) == "") {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_RANGE, "[" . $minimum . "," . $maximum . "]"), replacePlaceHolders(array(PLACEHOLDER_MINIMUM => $minimum, PLACEHOLDER_MAXIMUM => $maximum), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_RANGE)));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_RANGE_CUSTOM, "'" . $minimum . "," . $maximum . ";" . $others . "'"), replacePlaceHolders(array(PLACEHOLDER_MINIMUM => $minimum, PLACEHOLDER_MAXIMUM => $maximum), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_RANGE)));
                }
                if (!(contains($minimum, ".") || contains($maximum, "."))) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_INTEGER, "true"), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INTEGER));
                }
                $tocall = $this->engine->getFill($variable, $var, SETTING_ANSWERTYPE_CUSTOM);
                $parameters = array();
                if (stripos($tocall, '(') !== false) {
                    $parameters = rtrim(substr($tocall, stripos($tocall, '(') + 1), ')');
                    $parameters = preg_split("/[\s,]+/", $parameters);
                    $tocall = substr($tocall, 0, stripos($tocall, '('));
                }

                // add error string as parameter if we need it
                $parameters[] = $this->getErrorTextString($varname);

                //echo $tocall . '----';
                if (function_exists($tocall)) {
                    try {
                        $f = new ReflectionFunction($tocall);
                        $returnStr .= $f->invoke($variable, $parameters);
                    } catch (Exception $e) {
                        
                    }
                }
        }        
        if (!$inline) {
            $returnStr .= "</div>";
        }
        return $returnStr;
    }

    function addInputMasking($name, $variable, $var) {
        $textmask = "";
        if ($var->isInputMaskEnabled()) {
            $mask = $this->engine->getFill($variable, $var, SETTING_INPUT_MASK);
            $placeholder = "";
            if (trim($mask) == "") {
                switch ($var->getAnswerType()) {
                    case ANSWER_TYPE_INTEGER:
                        $mask = INPUTMASK_INTEGER;
                        break;
                    case ANSWER_TYPE_DOUBLE:
                        $mask = INPUTMASK_DOUBLE;
                        break;
                    case ANSWER_TYPE_RANGE:
                        $minimum = $this->engine->getFill($variable, $var, SETTING_MINIMUM_RANGE);
                        $maximum = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_RANGE);
                        if (!(contains($minimum, ".") || contains($maximum, "."))) {
                            $mask = INPUTMASK_INTEGER;
                        } else {
                            $mask = INPUTMASK_DOUBLE;
                        }
                    default:
                        break;
                }
            }

            $unmask = "";
            if ($var->isDataInputMask() == false) {
                $unmask = "data-inputmask-unmask='true'";
            }

            if ($mask == INPUTMASK_CUSTOM) {
                $m = "\"'mask': '" . $this->engine->getFill($variable, $var, SETTING_INPUT_MASK_CUSTOM) . "' , 'autoUnmask': 'true', 'removeMaskOnSubmit': 'true'\"";
                $placeholder = $this->engine->getFill($variable, $var, SETTING_INPUT_MASK_PLACEHOLDER);
                $textmask = "$unmask data-inputmask=" . $m . " data-inputmask-placeholder='" . $placeholder . "'";
            } else {
                $m = "\"'alias': '" . $mask . "', 'autoUnmask': 'true', 'removeMaskOnSubmit': 'true'\"";
                //$m = "\"'alias': '" . $mask . "'\"";
                $placeholder = $this->engine->getFill($variable, $var, SETTING_INPUT_MASK_PLACEHOLDER);
                $textmask = "$unmask data-inputmask=" . $m . " data-inputmask-placeholder='" . $placeholder . "'";
            }
            //data-inputmask-autounmask='true' data-inputmask-removemaskonsubmit='true' 
        }
        return $textmask;
    }

    function addInlineFieldChecks($name, $variable, $var, $ids) {
        if (sizeof($this->queryvariables) == 1) {
            return; // we don't need to do anything if we only have one question to be displayed
        }
        $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INLINE_ANSWERED, "['#" . implode("','#", $ids) . "']"), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INLINE_ANSWERED));
        if ($var->isInlineInclusive()) {
            $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INLINE_INCLUSIVE, "['#" . implode("','#", $ids) . "']"), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INLINE_INCLUSIVE));
        }
        if ($var->isInlineExclusive()) {
            $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INLINE_EXCLUSIVE, "['#" . implode("','#", $ids) . "']"), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INLINE_EXCLUSIVE));
        }
        $min = $this->engine->getFill($variable, $var, SETTING_INLINE_MINIMUM_REQUIRED);
        if ($min != "") {
            $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INLINE_MINREQUIRED, $min), replacePlaceHolders(array(PLACEHOLDER_INLINE_MINIMUM_REQUIRED => $min), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INLINE_MINIMUM_REQUIRED)));
        }
        $max = $this->engine->getFill($variable, $var, SETTING_INLINE_MAXIMUM_REQUIRED);
        if ($max != "") {
            $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INLINE_MAXREQUIRED, $max), replacePlaceHolders(array(PLACEHOLDER_INLINE_MAXIMUM_REQUIRED => $max), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INLINE_MAXIMUM_REQUIRED)));
        }
        $exact = $this->engine->getFill($variable, $var, SETTING_INLINE_EXACT_REQUIRED);
        if ($exact != "") {
            $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INLINE_EXACTREQUIRED, $exact), replacePlaceHolders(array(PLACEHOLDER_INLINE_EXACT_REQUIRED => $exact), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INLINE_EXACT_REQUIRED)));
        }
    }

    function getAnswerList($list) {

        $answeridlist = array();
        $arr = explode(SEPARATOR_COMPARISON, $list);
        $displaynumbers = $this->engine->getDisplayNumbers();
        //print_r($displaynumbers);
        foreach ($arr as $variable) {
            if (is_numeric($variable)) {
                $answeridlist[] = $variable;
            } else {
                $variable = str_replace(" ", "", $variable);

                // variable that is also shown on screen
                if (isset($displaynumbers[strtoupper($variable)])) {
                    //echo $variable . '---';
                    $var = $this->engine->getVariableDescriptive($variable);
                    if (!inArray($var->getAnswerType(), array(ANSWER_TYPE_NONE, ANSWER_TYPE_SECTION))) {
                        $name = SESSION_PARAMS_ANSWER . $displaynumbers[strtoupper($variable)];
                        $answeridlist[] = $name;
                    }
                } else {
                    // we treat it as a literal value
                    $answeridlist[] = $variable;
                }
            }
        }

        //print_r($answeridlist);

        return "['" . implode("','", $answeridlist) . "']";
    }

    function addComparisonChecks($var, $variable, $varname) {

        /* error checks numeric comparison */
        $at = $var->getAnswerType();

        if (inArray($at, array(ANSWER_TYPE_SETOFENUMERATED, ANSWER_TYPE_MULTIDROPDOWN))) {
            if (inArray($at, array(ANSWER_TYPE_SETOFENUMERATED))) {
                $varname = str_replace("[]", "", $varname) . "_name[]";
            }
            $eq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_EQUAL_TO));
            if ($eq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUM_COMPARISON_EQUAL_TO, $this->getAnswerList($eq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_EQUAL_TO));
            }
            $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO));
            if ($neq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUM_COMPARISON_NOT_EQUAL_TO, $this->getAnswerList($neq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_NOT_EQUAL_TO));
            }
            $geq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER_EQUAL_TO));
            if ($geq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUM_COMPARISON_GREATER_EQUAL_TO, $this->getAnswerList($geq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER_EQUAL_TO));
            }
            $gr = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER));
            if ($gr != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUM_COMPARISON_GREATER, $this->getAnswerList($gr)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER));
            }
            $seq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER_EQUAL_TO));
            if ($seq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUM_COMPARISON_SMALLER_EQUAL_TO, $this->getAnswerList($seq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER_EQUAL_TO));
            }
            $sm = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER));
            if ($sm != "") {
                //echo '!!!!!!!!!!!!!!!';
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_SETOFENUM_COMPARISON_SMALLER, $this->getAnswerList($sm)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER));
            }
        } else if (inArray($at, array(ANSWER_TYPE_CUSTOM, ANSWER_TYPE_RANGE, ANSWER_TYPE_DOUBLE, ANSWER_TYPE_INTEGER, ANSWER_TYPE_ENUMERATED, ANSWER_TYPE_DROPDOWN, ANSWER_TYPE_SLIDER))) {
            $eq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_EQUAL_TO));
            if ($eq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_EQUAL_TO, $this->getAnswerList($eq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_EQUAL_TO));
            }
            $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO));
            if ($neq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_NOT_EQUAL_TO, $this->getAnswerList($neq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_NOT_EQUAL_TO));
            }
            $geq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER_EQUAL_TO));
            if ($geq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_GREATER_EQUAL_TO, $this->getAnswerList($geq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER_EQUAL_TO));
            }
            $gr = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER));
            if ($gr != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_GREATER, $this->getAnswerList($gr)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER));
            }
            $seq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER_EQUAL_TO));
            if ($seq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_SMALLER_EQUAL_TO, $this->getAnswerList($seq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER_EQUAL_TO));
            }
            $sm = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER));
            if ($sm != "") {
                //echo '!!!!!!!!!!!!!!!';
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_SMALLER, $this->getAnswerList($sm)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER));
            }
        }
        // string comparison
        else if (inArray($at, array(ANSWER_TYPE_CUSTOM, ANSWER_TYPE_STRING, ANSWER_TYPE_OPEN))) {
            $eq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_EQUAL_TO));
            if ($eq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_EQUAL_TO_STRING, $this->getAnswerList($eq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_EQUAL_TO));
            }
            $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO));
            if ($neq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_NOT_EQUAL_TO_STRING, $this->getAnswerList($neq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_NOT_EQUAL_TO));
            }
            $eq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_EQUAL_TO_IGNORE_CASE));
            if ($eq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_EQUAL_TO_STRING_IGNORE_CASE, $this->getAnswerList($eq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_EQUAL_TO_IGNORE_CASE));
            }
            $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO_IGNORE_CASE));
            if ($neq != "") {
                $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_NOT_EQUAL_TO_STRING_IGNORE_CASE, $this->getAnswerList($neq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_NOT_EQUAL_TO_IGNORE_CASE));
            }
        }
        // error checking date/time
        else if (inArray($at, array(ANSWER_TYPE_DATE, ANSWER_TYPE_DATETIME, ANSWER_TYPE_TIME))) {
            $answertype = $at;
            $eq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_EQUAL_TO));
            if ($eq != "") {
                if ($answertype == ANSWER_TYPE_TIME) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_EQUAL_TO_TIME, $this->getAnswerList($eq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_EQUAL_TO));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_EQUAL_TO_DATETIME, $this->getAnswerList($eq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_EQUAL_TO));
                }
            }
            $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO));
            if ($neq != "") {
                if ($answertype == ANSWER_TYPE_TIME) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_NOT_EQUAL_TO_TIME, $this->getAnswerList($neq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_NOT_EQUAL_TO));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_NOT_EQUAL_TO_DATETIME, $this->getAnswerList($neq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_NOT_EQUAL_TO));
                }
            }
            $geq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER_EQUAL_TO));
            if ($geq != "") {
                if ($answertype == ANSWER_TYPE_TIME) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_GREATER_EQUAL_TO_TIME, $this->getAnswerList($geq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER_EQUAL_TO));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_GREATER_EQUAL_TO_DATETIME, $this->getAnswerList($geq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER_EQUAL_TO));
                }
            }
            //echo 'yyyyy';
            $gr = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER));
            //echo "<hr>" . $var->getComparisonGreater() . '----' . $gr;
            if ($gr != "") {
                if ($answertype == ANSWER_TYPE_TIME) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_GREATER_TIME, $this->getAnswerList($gr)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_GREATER_DATETIME, $this->getAnswerList($gr)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_GREATER));
                }
            }
            $seq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER_EQUAL_TO));
            if ($seq != "") {
                if ($answertype == ANSWER_TYPE_TIME) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_SMALLER_EQUAL_TO_TIME, $this->getAnswerList($seq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER_EQUAL_TO));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_SMALLER_EQUAL_TO_DATETIME, $this->getAnswerList($seq)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER_EQUAL_TO));
                }
            }
            $sm = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER));
            if ($sm != "") {
                if ($answertype == ANSWER_TYPE_TIME) {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_SMALLER_TIME, $this->getAnswerList($sm)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER));
                } else {
                    $this->addErrorCheck($varname, new ErrorCheck(ERROR_CHECK_COMPARISON_SMALLER_DATETIME, $this->getAnswerList($sm)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_COMPARISON_SMALLER));
                }
            }
        }
    }

    function addSetOfEnumeratedChecks($name, $variable, $var, $type) {
        $min = $this->engine->getFill($variable, $var, SETTING_MINIMUM_SELECTED);
        if ($min != "") {
            if ($type == ANSWER_TYPE_SETOFENUMERATED) {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_MINSELECTED, $min), replacePlaceHolders(array(PLACEHOLDER_MINIMUM_SELECTED => $min), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MINIMUM_SELECT)));
            } else {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_MINSELECTEDDROPDOWN, $min), replacePlaceHolders(array(PLACEHOLDER_MINIMUM_SELECTED => $min), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MINIMUM_SELECT)));
            }
        }
        $max = $this->engine->getFill($variable, $var, SETTING_MAXIMUM_SELECTED);
        if ($max != "") {
            if ($type == ANSWER_TYPE_SETOFENUMERATED) {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_MAXSELECTED, $max), replacePlaceHolders(array(PLACEHOLDER_MAXIMUM_SELECTED => $max), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MAXIMUM_SELECT)));
            } else {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_MAXSELECTEDDROPDOWN, $max), replacePlaceHolders(array(PLACEHOLDER_MAXIMUM_SELECTED => $max), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_MAXIMUM_SELECT)));
            }
        }
        $exact = $this->engine->getFill($variable, $var, SETTING_EXACT_SELECTED);
        if ($exact != "") {
            if ($type == ANSWER_TYPE_SETOFENUMERATED) {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_EXACTSELECTED, $exact), replacePlaceHolders(array(PLACEHOLDER_EXACT_SELECTED => $exact), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_EXACT_SELECT)));
            } else {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_EXACTSELECTEDDROPDOWN, $exact), replacePlaceHolders(array(PLACEHOLDER_EXACT_SELECTED => $exact), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_EXACT_SELECT)));
            }
        }
        $invalidsub = $this->engine->getFill($variable, $var, SETTING_INVALIDSUB_SELECTED);
        if ($invalidsub != "") {
            if ($type == ANSWER_TYPE_SETOFENUMERATED) {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INVALIDSUBSELECTED, "'" . $invalidsub . "'"), replacePlaceHolders(array(PLACEHOLDER_INVALIDSUBSET_SELECTED => getInvalidSubsetString($var, $invalidsub)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INVALID_SUB_SELECT)));
            } else {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INVALIDSUBSELECTEDDROPDOWN, "'" . $invalidsub . "'"), replacePlaceHolders(array(PLACEHOLDER_INVALIDSUBSET_SELECTED => getInvalidSubsetString($var, $invalidsub)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INVALID_SUB_SELECT)));
            }
        }
        $invalid = $this->engine->getFill($variable, $var, SETTING_INVALID_SELECTED);
        if ($invalid != "") {
            if ($type == ANSWER_TYPE_SETOFENUMERATED) {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INVALIDSELECTED, "'" . $invalid . "'"), replacePlaceHolders(array(PLACEHOLDER_INVALIDSET_SELECTED => getInvalidSetString($var, $invalid)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INVALID_SELECT)));
            } else {
                $this->addErrorCheck($name, new ErrorCheck(ERROR_CHECK_INVALIDSELECTEDDROPDOWN, "'" . $invalid . "'"), replacePlaceHolders(array(PLACEHOLDER_INVALIDSET_SELECTED => getInvalidSetString($var, $invalid)), $this->engine->getFill($variable, $var, SETTING_ERROR_MESSAGE_INVALID_SELECT)));
            }
        }
    }

    function calculateProgress($queryobject) {

        $current = "";
        $value = "";
        if ($value != "") {
            $current = $value;
        } else {
            //echo $this->progressbarwidth * $this->engine->getProgress();
            global $survey;
            $current = floor($this->progressbarwidth * $this->engine->getProgress($survey->getProgressBarType()));
            //echo floor(($current / $this->progressbarwidth) * 100);
        }
        $progress = round(($current / $this->progressbarwidth) * 100);

        // if so many screens that progress is non existent, set to 1%
        if ($progress == 0) {
            $progress = 1;
            $current = $this->progressbarwidth * ($progress / 100);
        }
        return array("value" => $current, "percent" => $progress);
    }

    function showProgress($rgid, $queryobject) {

        if ($queryobject) {
            $fillcolor = $this->engine->replaceFills($queryobject->getProgressBarFillColor());
            $width = $this->engine->replaceFills($queryobject->getProgressBarWidth());
            $value = $this->engine->replaceFills($queryobject->getProgressBarValue());
        } else {
            $fillcolor = PROGRESSBAR_FILLED_COLOR;
            $width = PROGRESSBAR_WIDTH;
        }
        $this->progressbarwidth = $width;

        $pro = $this->calculateProgress($queryobject);
        $current = $pro["value"];
        $progress = $pro["percent"];

        $showprogress = $queryobject->getShowProgressbar();
        if ($showprogress == PROGRESSBAR_NO) {
            return "";
        }

        if ($showprogress == PROGRESSBAR_PERCENT || $showprogress == PROGRESSBAR_ALL) {
            $progress = '<span style="padding-left: 5px;">' . $progress . '%</span>';
        } else {
            $progress = '';
        }

        if ($showprogress == PROGRESSBAR_BAR || $showprogress == PROGRESSBAR_ALL) {
            $returnStr = '<div class="uscic-progressbar">
                      <div class="progress" style="margin-left: auto; margin-right: auto; width: 30%;">
                        <div id="progressbar" class="progress-bar" role="progressbar" aria-valuenow="' . $current . '" aria-valuemin="0" aria-valuemax="' . $this->progressbarwidth . '" style="text-align: center; width: ' . floor(($current / $this->progressbarwidth) * 100) . '%;">
                          ' . $progress . '
                        </div>
                        </div>
                      </div>';
        } else {
            $returnStr = '<div style="margin-left: auto; margin-right: auto; width: 15%;">
                    
                ' . $progress . '<div>';
        }


        if ($fillcolor != "") {
            $returnStr .= '<script>$("#progressbar").css({"background-color": "' . $fillcolor . '"})</script>';
        }
        return $returnStr;
    }

    function showComboBox($variable, $var, $name, $id, $options, $previousdata, $inline, $multiple = "", $linkedto = "") {
        $this->combobox = true;
        $empty = '';
        $previous = explode(SEPARATOR_SETOFENUMERATED, $previousdata);
        if ($inline == false) {
            $returnStr = '<div class="form-group uscic-formgroup">'; // using <label> here destroys bootstrap-select mobile device popup, we're not using that for now
        } else {
            $returnStr = '<div class="form-group uscic-formgroup-inline">'; // using <label> here destroys bootstrap-select mobile device popup, we're not using that for now
        }

        $multipleheader = "";
        $singleheader = '';

        $align = $var->getAnswerAlignment();
        $qa = "";
        switch ($align) {
            case ALIGN_LEFT:
                $qa = "text-left";
                break;
            case ALIGN_RIGHT:
                $qa = "text-right";
                break;
            case ALIGN_JUSTIFIED:
                $qa = "text-justify";
                break;
            case ALIGN_CENTER:
                $qa = "text-center";
                break;
            default:
                break;
        }
        $hovertext = $this->engine->getFill($variable, $var, SETTING_HOVERTEXT);
        if ($hovertext != "") {
            $hovertext = "title='" . str_replace("'", "", $hovertext) . "'";
        }
        if ($multiple == "") {
            $selected = "";
            if ($previousdata == "") {
                $selected = "SELECTED";
            }
            $empty = '<option value=""></option>';
            if ($inline == false) {
                $returnStr .= "<div " . $hovertext . " class='uscic-singledropdown " . $qa . "'>";
            }
        } else {
            $multipleheader = 'title="Nothing selected"';
            if ($inline == false) {
                $returnStr .= "<div " . $hovertext . " class='uscic-multidropdown " . $qa . "'>";
            }
        }

        $pretext = $this->engine->getFill($variable, $var, SETTING_PRETEXT);
        $posttext = $this->engine->getFill($variable, $var, SETTING_POSTTEXT);
        if ($pretext != "") {
            $pretext = '<span class="input-group-addon uscic-inputaddon-pretext">' . $this->applyFormatting($pretext, $var->getAnswerFormatting()) . '</span>';
            $inputgroupstart = '<div class="input-group uscic-inputgroup-pretext">';
            $inputgroupend = "</div>";
        }
        if ($posttext != "") {
            $posttext = '<span class="input-group-addon uscic-inputaddon-posttext">' . $this->applyFormatting($posttext, $var->getAnswerFormatting()) . '</span>';
            $inputgroupstart = '<div class="input-group uscic-inputgroup-posttext">';
            $inputgroupend = "</div>";
        }
        $inlinejavascript = $this->engine->getFill($variable, $var, SETTING_JAVASCRIPT_WITHIN_ELEMENT);
        $inlinestyle = $this->engine->getFill($variable, $var, SETTING_STYLE_WITHIN_ELEMENT);

        if ($var->getAnswerType() == ANSWER_TYPE_MULTIDROPDOWN) {
            $dkrfna = $this->addDKRFNAButton(str_replace("[]", "", $name), $var, $variable);
        } else {
            $dkrfna = $this->addDKRFNAButton($name, $var, $variable);
        }
        $dkrfnaclass = "";
        if ($dkrfna != "") {
            if ($this->engine->isDKAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isRFAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            } else if ($this->engine->isNAAnswer($variable)) {
                $dkrfnaclass = "dkrfna";
            }
        }

        if ($inline == false) {
            if ($multiple == "") {
                $returnStr .= $inputgroupstart . $pretext . '<select ' . $linkedto . ' data-size="' . (sizeof($options) + 1) . '" ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $multipleheader . $this->getErrorTextString($name) . ' id="' . $id . '"' . ' name="' . $name . '"' . ' ' . $multiple . ' class="selectpicker show-tick ' . $dkrfnaclass . '">';
            }
            else {
                $returnStr .= $inputgroupstart . $pretext . '<select ' . $linkedto . ' data-size="' . sizeof($options) . '" ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $multipleheader . $this->getErrorTextString($name) . ' name="' . $name . '"' . ' id="' . $id . '"' . ' ' . $multiple . ' class="selectpicker show-tick ' . $dkrfnaclass . '">';
            }
        } else {
            if ($multiple == "") {
                $returnStr .= '<select ' . $linkedto . ' data-size="' . (sizeof($options)+1) . '" ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $multipleheader . $this->getErrorTextString($name) . ' id="' . $id . '"' . ' name="' . $name . '"' . ' ' . $multiple . ' class="selectpicker show-tick uscic-singledropdown-inline ' . $dkrfnaclass . '">';
            } else {
                $returnStr .= '<select ' . $linkedto . '  data-size="' . (sizeof($options)+1) . '" ' . $inlinestyle . ' ' . $inlinejavascript . ' ' . $multipleheader . $this->getErrorTextString($name) . ' id="' . $id . '"' . ' name="' . $name . '"' . ' ' . $multiple . ' class="selectpicker show-tick uscic-multipledropdown-inline ' . $dkrfnaclass . '">';
            }
        }
        $returnStr .= $empty;
        foreach ($options as $option) {
            if (trim($option["label"] != "")) {
                $selected = '';
                if (inArray($option["code"], $previous)) {
                    $selected = ' SELECTED';
                }

                $disabled = '';
                if ($this->isEnumeratedActive($variable, $var, $option["code"]) == false) {
                    $disabled = ' disabled ';
                }

                $returnStr .= '<option ' . $disabled . $selected . ' value="' . $option["code"] . '">' . $this->applyFormatting($option["label"], $var->getAnswerFormatting()) . '</option>';
            }
        }
        if ($inline == false) {
            $returnStr .= '</select>' . $posttext . $inputgroupend . '                                               
                        </div>' . $dkrfna . '                  
                        </div>
                        ';
        } else {
            $returnStr .= '</select></div>' . $dkrfna . ''; // using </select></label> here destroys bootstrap-select mobile device popup
        }
        return $returnStr;
    }

    function showCalendar($name, $id, $value, $max = "", $language = "en") {
        if ($max == "") {
            $max = ANSWER_CALENDAR_MAXSELECTED; // allow up to 10000 if no maximum specified
        }
        $returnStr = '
<input type=hidden ' . $this->getErrorTextString($name) . ' id="calendardiv" name="' . $name . '" value="' . $value . '" />
<script type=text/javascript>' . minifyScript('

function addEventHandlers(calendar, include) {
//alert($("span[data-cal-date]").length);
//class="cal-month-day cal-day-inmonth"
$("span[data-cal-date][data-cal-view=\'day\']").each(function() {
		var $this = $(this);
                $this.off(\'click\'); // remove any other click handlers
		$this.on(\'click\' , function(event) {
                    var start = new Date($(this).attr("data-cal-date"));
                    var milli = Date.parse(start);
                    start.setDate(start.getDate() + 1); // add 1 so we get the correct day
                    addEvent(Date.parse(start), Date.parse(start));
                    calendar.view(); 
                    addEventHandlers(calendar);
		});
	});

$("div[class=\'cal-cell1 cal-cell\']").each(function() {
		var $this = $(this);
                $this.off("dblclick"); // remove any other click handlers
	});
        
  $("div[class=\'span3 col-md-3 cal-cell\']").each(function() {
		var $this = $(this);
                $this.off("dblclick"); // remove any other click handlers
	});
  
if (include != 1) {
  $("span[data-cal-view=\'month\']").each(function() {
		var $this = $(this);
                $this.off("dblclick"); // remove any other click handlers
                $this.on(\'click\' , function(event) {
                    calendar.view($this.data(\'calendar-view\')); 
                    addEventHandlers(calendar, 1);
                });
	});
  }
  
$("a[data-event-class]").each(function() {
		var $this = $(this);
                $this.off(\'click\'); // remove any other click handlers
		$this.on(\'click\' , function(event) {
                    var id = $(this).attr("data-event-id");                    
                    removeEvent(id);
                    calendar.view(); 
                    addEventHandlers(calendar);
		});
	});

}


function addEvent(start,end) {
 var entries;
 var current = $("#calendardiv").attr("value");
 if (current) {
   entries = current.split("~");
 }
 else {
   entries = new Array();
  }
  
 // maximum number of entries allowed
 if (entries.length == ' . $max . ') {
     $("#' . $id . '_help").css("display","block"); 
     return;
 }
 else {
     $("#' . $id . '_help").css("display","none");
     entries.push(start+"-"+end);
 }
 
// update
 $("#calendardiv").attr("value", entries.join("~"));
}

function removeEvent(id) {
  var current = $("#calendardiv").attr("value");
  if (!current) {
    return;
  }
  entries = current.split("~");
  entries.splice(id-1, 1);
  
// update
 $("#calendardiv").attr("value", entries.join("~"));
}

function getEvents() {
  var current = $("#calendardiv").attr("value");
  if (!current) {
    return [];
  }
  else {

    var entries = current.split("~");
    var out = new Array();
    for (var i=0; i < entries.length; i++) {
       var entry = entries[i].split("-");
       var temp = new Array();
       temp["id"] = i+1;
       temp["class"] = "event-important";
       temp["start"] = entry[0];
       temp["end"] = entry[1];
       out.push(temp);
    }
    return out;
   }
}') . '</script>';
        $returnStr .= '<link rel="stylesheet" href="css/calendar.css">' . $this->displayCalendar($id, USCIC_SURVEY);
        return $returnStr;
    }

    function showLanguage() {
        global $survey;
        $allowed = explode("~", $survey->getAllowedLanguages(getSurveyMode()));
        if (sizeof($allowed) == 1) {
            return "";
        }
        $rgid = $this->engine->getRgid();
        $variablenames = $this->getRealVariables(explode("~", $this->engine->getDisplayed()));
        $template = $this->engine->getTemplate();
        $click = "";
        if ($template != "") {
            $group = $this->engine->getGroup($template);
            $click = $this->engine->replaceFills($group->getClickLanguageChange());
        } else {
            $vars = explode("~", $variablenames);
            $var = $this->engine->getVariableDescriptive($vars[0]);
            $click = $this->engine->replaceFills($var->getClickLanguageChange());
        }
        $click = str_replace("'", "", $click);
        $returnStr = '<div class="btn-group">
  <button type="button" class="btn btn-default dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
    ' . Language::surveyChangeLanguage() . ' <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">';
        $current = getSurveyLanguage();
        $langs = Language::getLanguagesArray();
        foreach ($langs as $key => $lang) {
            if (inArray($lang["value"], $allowed)) {
                $check = '';
                if ($lang["value"] == $current) {
                    $check = ' <span class="glyphicon glyphicon-ok"></span>';
                }
                $returnStr .= '<li><a href=# onclick=\'document.getElementById("r").value="' . setSessionsParamString(array_merge(array(SESSION_PARAM_LASTACTION => $this->engine->getLastSurveyAction(), SESSION_PARAM_SURVEY => $survey->getSuid(), SESSION_PARAM_PRIMKEY => $this->primkey, SESSION_PARAM_RGID => $rgid, SESSION_PARAM_VARIABLES => $variablenames, SESSION_PARAM_GROUP => $template, SESSION_PARAM_MODE => getSurveyMode(), SESSION_PARAM_TEMPLATE => getSurveyTemplate(), SESSION_PARAM_VERSION => getSurveyVersion(), SESSION_PARAM_LANGUAGE => $current, SESSION_PARAM_TIMESTAMP => time(), SESSION_PARAM_SEID => $this->engine->getSeid(), SESSION_PARAM_MAINSEID => $this->engine->getMainSeid()), array(SESSION_PARAM_NEWLANGUAGE => $lang["value"]))) . '"; document.getElementById("navigation").value="' . NAVIGATION_LANGUAGE_CHANGE . '"; ' . $click . ' document.getElementById("form").submit(); \'>' . $lang["name"] . $check . '</a></li>';
            }
        }
        $returnStr .= '
  </ul>
</div>';
        return $returnStr;
    }

    function showMode() {
        global $survey;
        $allowed = explode("~", $survey->getAllowedModes());
        if (sizeof($allowed) == 1) {
            return "";
        }
        $rgid = $this->engine->getRgid();
        $variablenames = $this->getRealVariables(explode("~", $this->engine->getDisplayed()));
        $template = $this->engine->getTemplate();
        $click = "";
        if ($template != "") {
            $group = $this->engine->getGroup($template);
            $click = $this->engine->replaceFills($group->getClickModeChange());
        } else {
            $vars = explode("~", $variablenames);
            $var = $this->engine->getVariableDescriptive($vars[0]);
            $click = $this->engine->replaceFills($var->getClickModeChange());
        }
        $click = str_replace("'", "", $click);

        $returnStr = '<div class="btn-group">
  <button type="button" class="btn btn-default dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
    ' . Language::surveyChangeMode() . ' <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">';
        $current = getSurveyMode();
        $modes = Common::surveyModes();
        foreach ($modes as $key => $mode) {
            if (inArray($key, $allowed)) {
                $check = '';
                if ($key == $current) {
                    $check = ' <span class="glyphicon glyphicon-ok"></span>';
                }
                $returnStr .= '<li><a href=# onclick=\'document.getElementById("r").value="' . setSessionsParamString(array_merge(array(SESSION_PARAM_LASTACTION => $this->engine->getLastSurveyAction(), SESSION_PARAM_SURVEY => $survey->getSuid(), SESSION_PARAM_PRIMKEY => $this->primkey, SESSION_PARAM_RGID => $rgid, SESSION_PARAM_VARIABLES => $variablenames, SESSION_PARAM_GROUP => $template, SESSION_PARAM_MODE => $current, SESSION_PARAM_VERSION => getSurveyVersion(), SESSION_PARAM_LANGUAGE => getSurveyLanguage(), SESSION_PARAM_TIMESTAMP => time(), SESSION_PARAM_SEID => $this->engine->getSeid(), SESSION_PARAM_MAINSEID => $this->engine->getMainSeid()), array(SESSION_PARAM_NEWMODE => $key))) . '"; document.getElementById("navigation").value="' . NAVIGATION_MODE_CHANGE . '"; ' . $click . ' document.getElementById("form").submit(); \'>' . $mode . $check . '</a></li>';
            }
        }
        $returnStr .= '
  </ul>
</div>';
        return $returnStr;
    }

    function showLoginSurvey() {
        $str = $this->showQuestiontext(VARIABLE_LOGIN, $this->engine->getVariableDescriptive(VARIABLE_LOGIN));
        $str .= $this->showAnswer(1, VARIABLE_LOGIN, $this->engine->getVariableDescriptive(VARIABLE_LOGIN), '');
        $str = str_replace('answer1', 'primkey', $str);
        if ($str == "") {
            $str = Language::labelLoginCode() . '<br/><br/><input style="max-width: 200px;" type="text" name="primkey" class="form-control">';
        }
        return $str;
    }

    function showWelcomeSurvey() {
        $str = $this->showQuestiontext(VARIABLE_INTRODUCTION, $this->engine->getVariableDescriptive(VARIABLE_INTRODUCTION));
        if ($str == "") {
            return Language::messageWelcome();
        }
        return $str;
    }

    function showCompletedSurvey() {
        $str = $this->showQuestion(VARIABLE_COMPLETED, 0);
        if ($str == "") {
            return Language::errorCompleted();
        }
        return $str;
    }

    function showEndSurvey() {
        $str = $this->showQuestion(VARIABLE_THANKS, 0);
        if ($str == "") {
            return Language::messageThanks();
        }
        return $str;
    }

    function showLockedSurvey() {
        $str = $this->showQuestion(VARIABLE_LOCKED, 0);
        if ($str == "") {
            return Language::errorLocked();
        }
        return $str;
    }
    
    function showInProgressSurvey() {
        $str = $this->showQuestion(VARIABLE_IN_PROGRESS, 0);
        if ($str == "") {
            return Language::errorInProgress();
        }
        return $str;
    }

    function showDirectAccessOnlySurvey() {
        $str = $this->showQuestion(VARIABLE_DIRECT, 0);
        if ($str == "") {
            return Language::errorDirectLogin();
        }
        return $str;
    }

    function showClosedSurvey() {
        $str = $this->showQuestiontext(VARIABLE_CLOSED, $this->engine->getVariableDescriptive(VARIABLE_CLOSED));
        if ($str == "") {
            return Language::messageSurveyClosed();
        }
        return $str;
    }

    function setLastParse($parse) {
        $this->lastparse = $parse;
    }

    function setShowHeader($header) {
        $this->showheader = $header;
    }

    function setShowFooter($footer) {
        $this->showfooter = $footer;
    }

}

?>
