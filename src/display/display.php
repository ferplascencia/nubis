<?php

/*
  ------------------------------------------------------------------------
  Copyright (C) 2014 Bart Orriens, Albert Weerman

  This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
  ------------------------------------------------------------------------
 */

class Display {

    var $config;

    function __construct() {
        
    }

    function defaultDisplayOverviewAddressColumns() {
        return array('address1_dec' => Language::labelDwelling(), 'city_dec' => Language::labelVillage());
    }

    function defaultDisplayInfoAddressColumns() {
        return array('address1_dec' => Language::labelDwelling(), 'city_dec' => Language::labelVillage());
    }

    function defaultDisplayInfo2AddressColumns() {
        return array('telephone1_dec' => Language::labelTelephone());
    }

    function showHeader($title, $style = '', $fastload = false) {
        /* FOR NO CACHING
         * <meta http-equiv="cache-control" content="max-age=0" />
          <meta http-equiv="cache-control" content="no-cache" />
          <meta http-equiv="expires" content="0" />
          <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
          <meta http-equiv="pragma" content="no-cache" />
         */
        $returnStr = ' 
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="shortcut icon" href="images/favicon.ico">

    <title>' . $title . '</title>';

        if (determinedJavascriptEnabled() == false) {
            $returnStr .= '<noscript><meta http-equiv="refresh" content="0; URL=' . getURL() . '/nojavascript.php"></noscript>';
        }
        $returnStr .= '
    <!-- Bootstrap core CSS -->
		<link rel="stylesheet" type="text/css" href="bootstrap/dist/css/bootstrap.min.css">

    <!-- Custom scripts and styles for this template -->';
        if ($fastload == false) {
            $returnStr .= '<script type="text/javascript" charset="utf-8" language="javascript" src="bootstrap/assets/js/jquery.js"></script>';
        }
        $returnStr .= '
    ' . $style . '

<script type="text/javascript">
    if(typeof window.history.pushState == \'function\') {
        window.history.pushState({}, "Hide", "index.php");
    }    
</script>
      
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="bootstrap/assets/js/html5shiv.js"></script>
      <script src="bootstrap/assets/js/respond.min.js"></script>
    <![endif]-->
    
    <script src="js/hover-dropdown.js"></script>
    <script type="text/javascript" src="js/tooltip.js"></script>
    <script type="text/javascript" src="js/popover.js"></script>    
    <script type="text/javascript" src="js/modal.js"></script>
    ';

        $returnStr .= '</head>
                    <body>
                    ';
        return $returnStr;
    }

    function showSurveyHeader($title, $style = '') {
        /* FOR NO CACHING
         * <meta http-equiv="cache-control" content="max-age=0" />
          <meta http-equiv="cache-control" content="no-cache" />
          <meta http-equiv="expires" content="0" />
          <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
          <meta http-equiv="pragma" content="no-cache" />
         */
        $returnStr = ' 
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="shortcut icon" href="images/favicon.ico">

    <title>' . $title . '</title>';

        if (determinedJavascriptEnabled() == false) {
            $returnStr .= '<noscript><meta http-equiv="refresh" content="0; URL=' . getURL() . '/nojavascript.php"></noscript>';
        }
        $returnStr .= '<script type="text/javascript" charset="utf-8" language="javascript" src="bootstrap/assets/js/jquery.js"></script>';
        $returnStr .= '<link href="css/uscic.css" type="text/css" rel="stylesheet">';
        $returnStr .= '
    <!-- Bootstrap core CSS -->
		<link rel="stylesheet" type="text/css" href="bootstrap/dist/css/bootstrap.min.css">

    <!-- Custom scripts and styles for this template -->';
        $returnStr .= '
    ' . $style . '

<script type="text/javascript">' . minifyScript('
    if(typeof window.history.pushState == \'function\') {
        window.history.pushState({}, "Hide", "index.php");
    }') . '    
</script>
      
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="bootstrap/assets/js/html5shiv.js"></script>
      <script src="bootstrap/assets/js/respond.min.js"></script>
    <![endif]-->
    ';
        $returnStr .= '</head>
                    <body>
                    ';
        /*
         * 
         */
        return $returnStr;
    }

    function showSurveyFooter($extra = '') {
        if (loadvar(POST_PARAM_AJAX_LOAD) == AJAX_LOAD) {
            return;
        }
        $returnStr = '
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->';
        /*$returnStr .= ' <script src="js/hover-dropdown.js"></script>
                            <script type="text/javascript" src="js/tooltip.js"></script>
                            <script type="text/javascript" src="js/popover.js"></script>    
                            <script type="text/javascript" src="js/modal.js"></script>';
                            */
        $returnStr .= '<script src="bootstrap/dist/js/bootstrap.min.js"></script>'; // needed for bootstrap-select
        $returnStr .= $extra;
        if (dbConfig::defaultDevice() == DEVICE_TABLET) {

            $returnStr .= '<script type="text/javascript">';

            $str = 'if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {';


            $str .= '$( ".btn" ).removeClass("btn").addClass("btn-lg");';
            $str .= '$( "#searchbutton" ).removeClass("btn-lg").addClass("btn");';

            $str .= '$("input[type=radio]").addClass("form-control");';
            $str .= '$("input[type=radio]").css("width", "50px");';
            $str .= '$( ".uscic-radio" ).css("font-size", "26px");';
            $str .= '$( ".uscic-radio" ).css("border", "1px dotted gray");';

            $str .= '$("input[type=checkbox]").addClass("form-control");';
            $str .= '$("input[type=checkbox]").css("width", "50px");';
            $str .= '$( ".uscic-checkbox" ).css("font-size", "26px");';
            $str .= '$( ".uscic-checkbox" ).css("border", "1px dotted gray");';

            $str .= '}';
            $returnStr .= minifyScript($str);
            $returnStr .= '</script>';
        }

        $returnStr .= '</body></html>';
        return $returnStr;
    }

//remove the get parameters: only for html 5 (http://stackoverflow.com/questions/13789231/remove-get-parameter-in-url-after-processing-is-finishednot-using-post-php)



    function showHeaderNoJavascript($title, $style = '') {
        $returnStr = ' 
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.ico">

    <title>' . $title . '</title>';

        $returnStr .= '
    <!-- Bootstrap core CSS -->
		<link rel="stylesheet" type="text/css" href="bootstrap/dist/css/bootstrap.min.css">

    <!-- Custom styles for this template -->
    ' . $style . '
  </head>
  <body>
';
        return $returnStr;
    }

    function displayNoJavascript() {
        $returnStr = $this->showHeaderNoJavascript("Attention", '<link type="text/css" href="bootstrap/css/sticky-footer-navbar.min.css" rel="stylesheet">');
        $returnStr .= '<div id="wrap">';
        $returnStr .= '<div class="container">';
        $returnStr .= '<div class="panel panel-default">
                        <div class="panel-body">';

        $returnStr .= '<noscript>This site is optimized to work with JavaScript! If you wish to have the best available site experience, please enable JavaScript.</noscript>';
        $returnStr .= ' Please just click \'Next\' to continue.<br/><br/>';
        $returnStr .= '<form id="form" role="form" method="post" action="index.php">       

                <noscript>
                <input type="hidden" name="js_enabled" value="1" />
                </noscript>
                <input type="hidden" name="js_chosen" value="1" />';

        $returnStr .= '<div class="panel-footer text-center">';
        $returnStr .= '<input type="submit" class="btn btn-default" value="' . Language::buttonJavascriptContinue() . '">';
        $returnSt .= '</div>';
        $returnStr .= '</form>';


        $returnSt .= '</div></div></div>';
        $returnStr .= "</body></html>";
        echo $returnStr;
    }

    function showFooter($fastLoad = true, $extra = '') {
        if (loadvar(POST_PARAM_AJAX_LOAD) == AJAX_LOAD) {
            return;
        }
        $returnStr = '
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->';
        if ($fastLoad) {
            $returnStr .= '<script src="bootstrap/assets/js/jquery.js"></script>';
        }
        $returnStr .= '<script src="bootstrap/dist/js/bootstrap.min.js"></script>';
        $returnStr .= $extra;
        if (dbConfig::defaultDevice() == DEVICE_TABLET) {
            //WOULD NEED A dbConfig check here!!

            $returnStr .= '<script type="text/javascript">';

            $str = 'if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {';


            $str .= '$( ".btn" ).removeClass("btn").addClass("btn-lg");';
            $str .= '$( "#searchbutton" ).removeClass("btn-lg").addClass("btn");';

            $str .= '$("input[type=radio]").addClass("form-control");';
            $str .= '$("input[type=radio]").css("width", "50px");';
            $str .= '$( ".uscic-radio" ).css("font-size", "26px");';
            $str .= '$( ".uscic-radio" ).css("border", "1px dotted gray");';

            $str .= '$("input[type=checkbox]").addClass("form-control");';
            $str .= '$("input[type=checkbox]").css("width", "50px");';
            $str .= '$( ".uscic-checkbox" ).css("font-size", "26px");';
            $str .= '$( ".uscic-checkbox" ).css("border", "1px dotted gray");';

            $str .= '}';
            $returnStr .= minifyScript($str);
            $returnStr .= '</script>';
        }

        $returnStr .= '</body></html>';
        return $returnStr;
    }

    function displayOptionsSidebar($selector, $name, $page = "sysadmin.search") {
        $returnStr = '';
        if (!isRegisteredScript("js/sidr/jquery.sidr.js")) {
            registerScript('js/sidr/jquery.sidr.js');
            $returnStr .= getScript('js/sidr/jquery.sidr.js');
        }
        if (!isRegisteredScript("js/sidr/jquery.sidr.light.css")) {
            registerScript('js/sidr/jquery.sidr.light.css');
            $returnStr .= getCSS('js/sidr/jquery.sidr.light.css');
        }

        $returnStr .= '<script type="text/javascript" >
                $(document).ready(function() {
                $(\'#' . $selector . '\').sidr( {
                 displace: false,
                 name: \'' . $name . '\'
                });
                ';

        if ($_SESSION['SEARCH'] == SEARCH_OPEN_YES) {
            $returnStr .= " var term = '" . $_SESSION['SEARCHTERM'] . "';
                            var r = '" . setSessionsParamString(array("page" => $page)) . "';
                            var url = '';

                            // Send the data using post
                            var posting = $.post( url, { r: r, search: term, updatesessionpage: 2 } );

                            // Put the results in a div
                            posting.done(function( data ) {
                            $( '#optionssidebar' ).empty().append( $( data ));
                            });";
            $returnStr .= '$.sidr(\'open\', \'optionssidebar\');';
        }
        $returnStr .= "});</script>";
        return $returnStr;
    }

    function displayComboBox($css = true) {
        $str = '';
        if (!isRegisteredScript("js/bootstrap-select/bootstrap-select-min.js")) {
            registerScript('js/bootstrap-select/bootstrap-select-min.js');
            $str .= getScript("js/bootstrap-select/bootstrap-select-min.js");
        }
        if ($css && !isRegisteredScript("css/bootstrap-select.min.css")) {
            registerScript('css/bootstrap-select.min.css');
            $str .= getCSS("css/bootstrap-select.min.css");
        }
        
        $str .= minifyScript('<script type="text/javascript">
                    $(document).ready(function(){
                    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
                      $(".selectpicker").selectpicker({
                            mobile: true,
                            noneSelectedText : \'' . Language::labelDropdownNothing() . '\'}
                        );                      
                      }
                    else {
                      $(".selectpicker").selectpicker({
                            noneSelectedText : \'' . Language::labelDropdownNothing() . '\'}
                        );
                    }
                  });
                  </script>');
        return $str;
    }
    
    function displayComboBoxCSS() {
        $str = '';        
        if (!isRegisteredScript("css/bootstrap-select.min.css")) {
            registerScript('css/bootstrap-select.min.css');
            $str .= getCSS("css/bootstrap-select.min.css");
        }        
        return $str;
    }

    function displayColorPicker() {
        $str = '';
        if (!isRegisteredScript("js/colorpicker/bootstrap-colorpicker.js")) {
            registerScript('js/colorpicker/bootstrap-colorpicker.js');
            $str .= getScript("js/colorpicker/bootstrap-colorpicker.js");
        }
        if (!isRegisteredScript("js/colorpicker/bootstrap-colorpicker.css")) {
            registerScript('js/colorpicker/bootstrap-colorpicker.css');
            $str .= getCSS("js/colorpicker/bootstrap-colorpicker.css");
        }
        $str .= '<script>
                    $(document).ready(function(){
                        $(".colorpicker").colorpicker();
                    });
                </script>';
        return $str;
    }

    function displayPopoverScript() {
        $returnStr = '<script type=text/javascript>
            
        function showPopover() {
            var $this = $(this);

            // Currently hovering popover
            $this.data("hoveringPopover", true);

            // If its still waiting to determine if it can be hovered, dont allow other handlers
            if ($this.data("waitingForPopoverTO")) {
                e.stopImmediatePropagation();
            }
         }
         
        function hidePopover() {
   
            var $this = $(this);

            // If timeout was reached, allow hide to occur
            if ($this.data("forceHidePopover")) {
                $this.data("forceHidePopover", false);
                return true;
            }

            // Prevent other `hide` handlers from executing
            e.stopImmediatePropagation();

            // Reset timeout checker
            clearTimeout($this.data("popoverTO"));

            // No longer hovering popover
            $this.data("hoveringPopover", false);

            // Flag for `show` event
            $this.data("waitingForPopoverTO", true);

            // In 500ms, check to see if the popover is still not being hovered
            $this.data("popoverTO", setTimeout(function () {
                // If not being hovered, force the hide
                if (!$this.data("hoveringPopover")) {
                    $this.data("forceHidePopover", true);
                    $this.data("waitingForPopoverTO", false);
                    $this.popover("hide");
                }
            }, 500));

            // Stop default behavior
            return false;
        }
        </script>';

        return $returnStr;
    }

    function displayPopover($selector, $content) {
        $returnStr = "<script type=text/javascript>$('" . $selector . "').popover({
                container: '" . $selector . "',
                animation: false,
                content: '" . str_replace("'", "\'", $content) . "'
                }).on({
                    show: showPopover,
                    hide: hidePopover
                    });
                    </script>";
        return $returnStr;
    }

    function displayValidation($paradata, $errors = array(), $checkerror = true, $checkempty = true, $placement = ERROR_PLACEMENT_WITH_QUESTION) {

        $rulestringerror = "";
        $rulestringempty = "";
        if ($paradata == true) {
            $errormapping = "";
            $errorcodes = Common::errorCodes();
        }

        /* error checking */
        if ($checkerror) {
            if (sizeof($errors) > 0) {
                if ($paradata == true) {
                    $errormapping = "var mapping = { errors: [] };\r\n";
                }
                $rulestringerror .= "rules: {\r\n";
                foreach ($errors as $error) {
                    $name = $error->getVariableName();

                    if (contains($name, "[")) {
                        $name = "'" . $name . "'";
                    }
                    $rulestringerror .= $name . ": {\r\n";
                    $local = "";
                    $err = $error->getErrorChecks();
                    foreach ($err as $e) {

                        // not empty check
                        if ($e->getType() != ERROR_CHECK_REQUIRED) {

                            /* not pattern check */
                            if ($e->getType() != ERROR_CHECK_PATTERN) {
                                $local .= $e->getType() . ": " . $e->getValue() . ",\r\n";
                            } else {
                                $value = $e->getValue();

                                /* not pattern given, then assume validator function */
                                if (!startsWith($value, '/')) {
                                    $local .= $e->getValue() . ": true,\r\n";
                                } else {
                                    $local .= $e->getType() . ": " . $e->getValue() . ",\r\n";
                                }
                            }

                            // add to mapping
                            if ($paradata == true) {
                                $code = 999;
                                if (isset($errorcodes[$e->getType()])) {
                                    $code = $errorcodes[$e->getType()];
                                }
                                $errormapping .= "mapping.errors.push( {name: '" . $error->getVariableName() . "-" . str_replace("'", "\'", $e->getMessage()) . "', value: '" . $code . "'});\r\n";
                            }
                        }
                    }
                    $local = substr(trim($local), 0, strlen(trim($local)) - 1) . "\r\n}, \r\n";
                    $rulestringerror .= $local;

                    // add to overal mapping
                }
                $rulestringerror = substr(trim($rulestringerror), 0, strlen(trim($rulestringerror)) - 1) . "\r\n}, \r\n";
            }
        }

        /* empty checking */
        if ($checkempty) {
            if (sizeof($errors) > 0) {
                if ($paradata == true) {
                    if ($errormapping == "") {
                        $errormapping = "var mapping = { errors: []};\r\n";
                    }
                }
                $rulestringempty .= "rules: {\r\n";
                foreach ($errors as $error) {
                    $name = $error->getVariableName();
                    if (contains($name, "[")) {
                        $name = "'" . $name . "'";
                    }
                    $rulestringempty .= $name . ": {\r\n";
                    $local = "";
                    $err = $error->getErrorChecks();
                    foreach ($err as $e) {

                        // empty check
                        if ($e->getType() == ERROR_CHECK_REQUIRED) {
                            $local .= $e->getType() . ": " . $e->getValue() . ",\r\n";

                            // add to mapping
                            if ($paradata == true) {
                                $code = 999;
                                if (isset($errorcodes[$e->getType()])) {
                                    $code = $errorcodes[$e->getType()];
                                }
                                $errormapping .= "mapping.errors.push( {name: '" . $error->getVariableName() . "-" . str_replace("'", "\'", $e->getMessage()) . "', value: '" . $code . "'});\r\n";
                            }
                        }
                    }
                    $local = substr(trim($local), 0, strlen(trim($local)) - 1) . "\r\n}, \r\n";
                    $rulestringempty .= $local;
                }
                $rulestringempty = substr(trim($rulestringempty), 0, strlen(trim($rulestringempty)) - 1) . "\r\n}, \r\n";
            }
        }
        $finalstr = "";
        if (!isRegisteredScript("js/validation/jquery.validate-min.js")) {
            $finalstr .= getScript("js/validation/jquery.validate-min.js");
            $finalstr .= getScript("js/validation/jquery.validate.additional.js");
        }
        $str = '<script type="text/javascript">';

        // add error mapping if logging paradata
        if ($paradata == true) {
            $str .= $errormapping;
            if ($errormapping != "") {
                $str .= "function lookupCode(name, search) {
                            $.each(mapping.errors, function(i, v) {
                                if (v.name == search) {
                                    logParadata(v.value + ':' + name);
                                }
                            });
                        }";
            }
        }
        $str .= '
                 
             function clearForm() {';

        if ($placement == ERROR_PLACEMENT_AT_TOP || $placement == ERROR_PLACEMENT_AT_BOTTOM) {
            $str .= '$(\'#uscic-errors\').empty();';
        } else {
            $str .= '$(\'div.form-group\').removeClass(\'has-errors\');
                $(\'div.form-group\').removeClass(\'has-warning\'); 
                $(\'tr.has-warning\').removeClass(\'has-warning\');
                $(\'div.form-group\').removeAttr(\'style\');                
                $(\'tr.form-group\').removeAttr(\'style\');  
                $(\':input\').removeClass(\'empty-error\');
                $(\':input\').removeClass(\'error-error\');';
        }

        $str .= '}
             
             var validator;    
';

        /* define error placement */
        $errorplacement = '';
        $errorplacement1 = '';
        if ($placement == ERROR_PLACEMENT_WITH_QUESTION) {
            $errorplacement .= 'showErrors: function(errorMap, errorList) {';
            $errorplacement .= '$.each(errorList, function (index, error) {';
            $errorplacement .= 'if ($(error.element).attr("data-validation-empty") == 3) {';
            $errorplacement .= 'var name = $(error.element).attr("name");';
            $errorplacement .= '$("[name=\'" + name + "\']").addClass("ignore-empty")';
            $errorplacement .= '}';
            if ($paradata == true) {
                $errorplacement .= 'lookupCode($(error.element).attr("name"), $(error.element).attr("name") + "-" + error.message);';
            }
            $errorplacement .= '});';
            $errorplacement .= 'this.defaultShowErrors(); },';

            $errorplacement .= 'errorElement: \'p\',
                            errorClass: \'help-block uscic-help-block\',
                            errorPlacement: function(error, element) {  ';
            $errorplacement .= '
                                if ($(element).closest(\'div.form-group\').hasClass(\'has-errors\') === false) {
                                   $(element).closest(\'div.form-group\').addClass(\'has-errors\');
                                }
                                if ($(element).hasClass(\'uscic-radio-table\') === true) {
                                     error.insertAfter($(element).closest(\'tr\').first().children(\'td\').children(\'div\').first());
                                }
                                else if ($(element).hasClass(\'uscic-checkbox-table\') === true) {
                                     error.insertAfter($(element).closest(\'tr\').first().children(\'td\').children(\'div\').first());
                                }
                                else if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for text fields in a table row
                                    error.insertAfter($(element).closest(\'tr\').first().children(\'td\').children(\'div\').first());
                                }
                                else {                                
                                   error.insertAfter($(element).closest(\'div.form-group\').children().last());            
                                }
                           }';
            $errorplacement1 = str_replace("ignore-empty", "ignore-error", str_replace("data-validation-empty", "data-validation-error", $errorplacement));
        } else if ($placement == ERROR_PLACEMENT_AT_TOP || $placement == ERROR_PLACEMENT_AT_BOTTOM) {
            $errorplacement .= 'showErrors: function(errorMap, errorList) {
                        $(\'#uscic-errors\').empty();
                        $(\'#uscic-errors\').addClass(\'has-warning has-errors\');
                        var str = "";
                        $.each(errorList, function (index, error) {
                            //var $element = $(error.element);
                            if ($(error.element).attr("data-validation-empty") == 3) {
                                var name = $(error.element).attr("name");
                                $("[name=\'" + name + "\']").addClass("ignore-empty");
                            }
                            str = str + "<p class=\'help-block uscic-help-block\'>" + error.message + "</p>";';
            if ($paradata == true) {
                $errorplacement .= 'lookupCode($(error.element).attr("name"), $(error.element).attr("name") + "-" + error.message);';
            }
            $errorplacement .= '
                        });
                        str = str;
                        $(\'#uscic-errors\').append(str);
                    }';
            $errorplacement1 .= 'showErrors: function(errorMap, errorList) {
                        $(\'#uscic-errors\').addClass(\'has-warning has-errors\');
                        var str = "";
                        $.each(errorList, function (index, error) {
                            if ($(error.element).attr("data-validation-error") == 3) {
                                var name = $(error.element).attr("name");
                                $("[name=\'" + name + "\']").addClass("ignore-error");
                            }
                            var $element = $(error.element);
                            str = str + "<p class=\'help-block uscic-help-block\'>" + error.message + "</p>";';
            if ($paradata == true) {
                $errorplacement1 .= 'lookupCode($(error.element).attr("name"), $(error.element).attr("name") + "-" + error.message);';
            }
            $errorplacement1 .= '
                                });
                        str = str;
                        $(\'#uscic-errors\').append(str);
                    }';
        } else if ($placement == ERROR_PLACEMENT_WITH_QUESTION_TOOLTIP) {

            // TODO: IF WE EVER FINISH THE BELOW, NEED TO CREATE ERRORPLACEMENT AND ERRORPLACEMENT1 INSTEAD
            // MAKE SURE THEN TO ADD THE CODE NEEDED TO ADD IGNORE-EMPTY/IGNORE-ERROR
            /* $str .= 'showErrors: function(errorMap, errorList) {

              // Clean up any tooltips for valid elements
              $.each(this.validElements(), function (index, element) {
              var $element = $(element);

              $element.data("title", "") // Clear the title - there is no error associated anymore
              .removeClass("error")
              .tooltip("destroy");
              });

              // Create new tooltips for invalid elements
              $.each(errorList, function (index, error) {
              var $element = $(error.element);
              $element.tooltip("destroy") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
              .data("title", error.message)
              .addClass("error")
              .tooltip(); // Create a new tooltip based on the error messsage we just set in the title';
              if ($paradata == true) {
              $str .= 'lookupCode($(error.element).attr("name"), $(error.element).attr("name") + "-" + error.message);';
              }
              $str .= '
              });
              }'; */
        }

        /* add empty checking function */
        if ($checkempty) {
            $str .= 'function validateFormEmpty() {   
                $(\'form\').removeData(\'validator\');                
                    
    $(\'form\').validate({  ' . $rulestringempty . ' 
        ignore: ".ignore-empty, .dkrfna, :hidden:not(.selectpicker, .bootstrapslider, #calendardiv)", // for selectpicker bootstrap plugin and bootstrap-slider plugin
            onkeyup: false,
            onclick: false,
            onfocusout: false,
            highlight: function(element) {                            
                $(element).addClass(\'empty-error\');
                $(element).closest(\'div.form-group\').addClass(\'has-warning\');
                if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for multi column setup in a table row                
                    $(element).closest(\'tr\').addClass(\'has-warning\');
                    $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                }
                else if ($(element).is(\':checkbox\')) { // for checkboxes to add the highlighting                
                    if ($(element).hasClass(\'uscic-checkbox-table\') === true) {
                        $(element).closest(\'tr\').addClass(\'has-warning\');
                        $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                    else {
                        $(element).closest(\'div.form-group\').attr(\'style\', \'padding: 0.5em; border: 1px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }    
                }                
                else if ($(element).is(\':radio\')) { // for radio buttons to add the highlighting
                    if ($(element).hasClass(\'uscic-radio-table\') === true) {
			$(element).closest(\'tr\').addClass(\'has-warning\');
                        $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                    else {
                        $(element).closest(\'div.form-group\').attr(\'style\', \'padding: 0.5em; border: 1px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                }
                else if ($(element).is(\'select\')) { // for select picker to add the highlighting                
                    $(element).next().children().first().attr(\'style\', \'border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                }
                else if ($(element).is(\':text\')) { // for checkboxes to add the highlighting
                    if ($(element).hasClass(\'bootstrapslider\') === true) {                        
                        $(element).closest(\'div.form-group\').attr(\'style\', \'padding: 0.5em; border: 1px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    } 
                    else if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for text fields in a table row
			$(element).closest(\'tr\').addClass(\'has-warning\');
                        $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                }
            },
            unhighlight: function(element) {
                $(element).removeClass(\'empty-error\');                
                if ($(element).closest(\'div .form-group\').hasClass(\'has-errors\') === false) {
                    $(element).closest(\'div .form-group\').removeClass(\'has-warning\');
                    
                    if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for text fields in a table row
                        $(element).closest(\'tr\').removeAttr(\'style\');
                    }
                    else if ($(element).is(\':checkbox\')) { // for checkboxes to remove the highlighting                
                        if ($(element).hasClass(\'uscic-checkbox-table\') === true) {
                            if ($(element).closest(\'tr\').hasClass(\'has-warning\') === false) {
                                $(element).closest(\'tr\').removeAttr(\'style\');
                            }  
                        }
                        else {
                            $(element).closest(\'div.form-group\').removeAttr(\'style\');
                        }
                    }
                    else if ($(element).is(\':radio\')) { // for checkboxes to remove the highlighting                
                        if ($(element).hasClass(\'uscic-radio-table\') === true) {
                            if ($(element).closest(\'tr\').hasClass(\'has-warning\') === false) {
                                $(element).closest(\'tr\').removeAttr(\'style\');
                            }    
                        }
                        else {
                            $(element).closest(\'div.form-group\').removeAttr(\'style\');
                        }    
                    }
                    else if ($(element).is(\'select\')) { // for select picker to remove the highlighting                
                        $(element).next().children().first().removeAttr(\'style\');
                    }
                    else if ($(element).is(\':text\')) { // for checkboxes to add the highlighting                                    
                        if ($(element).hasClass(\'bootstrapslider\') === true) {                        
                            $(element).closest(\'div.form-group\').removeAttr(\'style\');
                        }    
                        else if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for text fields in a table row
                            if ($(element).closest(\'tr\').hasClass(\'has-warning\') === false) {;
                                $(element).closest(\'tr\').removeAttr(\'style\');
                            }    
                        }
                    }
                }
            },';

            $str .= $errorplacement;


            $str .= '});    
        return $(\'form\').valid();
        }';
        }

        /* add error checking function */
        if ($checkerror) {

            $str .= 'function validateFormError() {                
    $(\'form\').removeData(\'validator\');    
    $(\'form\').validate({  ' . $rulestringerror . ' 
        ignore: ":hidden:not(.selectpicker, .bootstrapslider), #calendardiv, .empty-error, .ignore-error", // for selectpicker bootstrap plugin; we dont ignore dkrfna here so we check dk/rf/naed answers
            onkeyup: false,
            onclick: false,
            onfocusout: false,
            highlight: function(element) {
                $(element).addClass(\'error-error\');
                $(element).closest(\'div.form-group\').addClass(\'has-warning\');
                
                if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for multi column setup in a table row                
                    $(element).closest(\'tr\').addClass(\'has-warning\');
                    $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                }
                else if ($(element).is(\':checkbox\')) { // for checkboxes to add the highlighting                
                    if ($(element).hasClass(\'uscic-checkbox-table\') === true) {
                        $(element).closest(\'tr\').addClass(\'has-warning\');
                        $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                    else {
                        $(element).closest(\'div.form-group\').attr(\'style\', \'padding: 0.5em; border: 1px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }    
                }
                else if ($(element).is(\':radio\')) { // for radio buttons to add the highlighting
                    if ($(element).hasClass(\'uscic-radio-table\') === true) {
			$(element).closest(\'tr\').addClass(\'has-warning\');
                        $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                    else {
                        $(element).closest(\'div.form-group\').attr(\'style\', \'padding: 0.5em; border: 1px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                }
                else if ($(element).is(\'select\')) { // for select picker to add the highlighting                
                    $(element).next().children().first().attr(\'style\', \'border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                } 
                else if ($(element).is(\':text\')) { // for checkboxes to add the highlighting
                    if ($(element).hasClass(\'bootstrapslider\') === true) {                        
                        $(element).closest(\'div.form-group\').attr(\'style\', \'padding: 0.5em; border: 1px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    } 
                    else if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for text fields in a table row
			$(element).closest(\'tr\').addClass(\'has-warning\');
                        $(element).closest(\'tr\').attr(\'style\', \'padding: 0.5em; border: 3px solid; border-color: #C09853; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;\');
                    }
                }
            },
            unhighlight: function(element) {
                $(element).removeClass(\'error-error\');
                if ($(element).closest(\'div .form-group\').hasClass(\'has-errors\') === false) {
                    $(element).closest(\'div .form-group\').removeClass(\'has-warning\');
                    
                    if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for text fields in a table row
                        if ($(element).closest(\'tr\').hasClass(\'has-warning\') === false) {;
                            $(element).closest(\'tr\').removeAttr(\'style\');
                        }    
                    }
                    else if ($(element).is(\':checkbox\')) { // for checkboxes to remove the highlighting                
                        if ($(element).hasClass(\'uscic-checkbox-table\') === true) {
                            if ($(element).closest(\'tr\').hasClass(\'has-warning\') === false) {;
                                $(element).closest(\'tr\').removeAttr(\'style\');
                            }    
                        }
                        else {
                            $(element).closest(\'div.form-group\').removeAttr(\'style\');
                        }
                    }
                    else if ($(element).is(\':radio\')) { // for checkboxes to remove the highlighting                
                        if ($(element).hasClass(\'uscic-radio-table\') === true) {
                            if ($(element).closest(\'tr\').hasClass(\'has-warning\') === false) {
                                $(element).closest(\'tr\').removeAttr(\'style\');
                            }    
                        }
                        else {
                            $(element).closest(\'div.form-group\').removeAttr(\'style\');
                        }    
                    }
                    else if ($(element).is(\'select\')) { // for select picker to remove the highlighting                
                        $(element).next().children().first().removeAttr(\'style\');
                    }
                    else if ($(element).is(\':text\')) { // for checkboxes to add the highlighting                                    
                        if ($(element).hasClass(\'bootstrapslider\') === true) {                        
                            $(element).closest(\'div.form-group\').removeAttr(\'style\');
                        }
                        else if ($(element).closest(\'td\') && $(element).closest(\'td\').hasClass(\'uscic-table-row-cell-multicolumn\')) { // for text fields in a table row
                            $(element).closest(\'tr\').removeAttr(\'style\');
                        }
                    }
                }
            },';

            $str .= $errorplacement1;


            $str .= '});    
        
            // validate for errors            
            var result = $(\'form\').valid();
        
            // force showing of all error blocks since jquery validator hides the empty ones!
            // find all inputs with empty-error class, get name and show p block that matches in its for attr
            $(document).find(\'.empty-error\').each(function(element) {
                var name = $(this).attr(\'name\');
                if($(this).is("select") && $(this).is("[multiple]")) {
                   name = $(this).attr(\'name\').replace("[","").replace("]",""); 
                }
                $(\'p[for="\' + name + \'"]\').css(\'display\', \'block\');
            });

            return result;
        }
        
';
        }

        $str .= '</script>';
        return $finalstr . minifyScript($str);
    }

    function displaySlider($variable, $var, $name, $id, $value, $minimum, $maximum, $errors, $qa, $inlineclass, $step = 1, $tooltip = "show", $orientation = "horizontal", $dkrfna = "", $linkedto = "", $width = "400px", $height = "40px", $formater = 'value') {

        $returnStr = '';
        if (!isRegisteredScript("js/modernizr.js")) {
            registerScript('js/modernizr.js');
            $returnStr = getScript("js/modernizr.js");
        }

        if (!isRegisteredScript("js/bootstrap-slider/bootstrap-slider.min.js")) {
            registerScript('js/bootstrap-slider/bootstrap-slider.min.js');
            $returnStr .= getScript("js/bootstrap-slider/bootstrap-slider.min.js");
        }

        if (!isRegisteredScript("js/bootstrap-slider/bootstrap-slider.min.css")) {
            registerScript('js/bootstrap-slider/css/bootstrap-slider.min.css');
            $returnStr .= getCSS("js/bootstrap-slider/bootstrap-slider.min.css");
        }

        $str = '<script type="text/javascript">
                        $( document ).ready(function() {
                            $(\'#' . $id . '\').slider({ 
                          formatter: function(value) {return ' . $formater . ';} });              
                        ';

        if ($value == "") {
            $str .= "$('#" . $id . "').val(''); document.getElementById('" . $id . "').value='';";
        }

        $str .= '});
                       </script>';
        $returnStr .= minifyScript($str);
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

        if ($orientation == "horizontal") {
            $returnStr .= '<div id="' . $id . '_slid" class="form-group uscic-formgroup' . $inlineclass . '">';


            // add labeling
            $labels = $this->engine->replaceFills($var->getSliderLabels());
            $labelStr = '';
            if ($labels != "") {
                global $survey;
                $labelvar = $survey->getVariableDescriptiveByName($labels);
                $labels = $labelvar->getOptions();
                if (sizeof($labels) > 0) {

                    // NEW WAY TO ADD:
                    // <input id="ex13" type="text" data-slider-ticks="[0, 100, 200, 300, 400]" data-slider-ticks-snap-bounds="30" data-slider-ticks-labels='["$0", "$100", "$200", "$300", "$400"]'/>

                    $labelStr .= '
                                <table id="' . $id . '_labels" class="slider_labels">
                                    <tr>';
                    for ($i = 0; $i < sizeof($labels); $i++) {
                        $option = $labels[$i];
                        if ($i == 0) {
                            $labelStr .= '<td style="width: 20%; text-align: left;">' . $this->engine->replaceFills($option["label"]) . '</td>';
                        } else if (($i + 1) == sizeof($labels)) {
                            $labelStr .= '<td style="width: 20%; text-align: right;">' . $this->engine->replaceFills($option["label"]) . '</td>';
                        } else {
                            $labelStr .= '<td style="width: 20%; text-align: center;">' . $this->engine->replaceFills($option["label"]) . '</td>';
                        }
                    }
                    $labelStr .= '</tr>       
                                </table>
                           </div>' . minifyScript(
                           '<script type="text/javascript">
                                        $(document).ready(function() {                                        
                                            $(".slider").css("width", $("#' . $id . '").width());    
                                            $("#' . $id . '_labels").css("width", $("#' . $id . '").width());
                                            $(".slider-track").css("width", $("#' . $id . '").width());    
                                         });
                                                
                                         $( window ).resize(function() {
                                            $(".slider").css("width", $("#' . $id . '").width());  
                                            $("#' . $id . '_labels").css("width", $("#' . $id . '").width());
                                            $(".slider-track").css("width", $("#' . $id . '").width());                
                                         });
                             </script>');
                }
            }

            if ($var->getSliderLabelPlacement() == SLIDER_LABEL_PLACEMENT_TOP) {
                $returnStr .= $labelStr;
            }

            $returnStr .= '<div class="uscic-' . $orientation . '-slider' . $inlineclass . ' ' . $qa . '">
                                <label>
                                <span style="font-weight: bold; padding-right: 10px;">' . $minimum . '</span><input ' . $linkedto . ' id="' . $id . '" ' . $errors . ' class="bootstrapslider ' . $dkrfnaclass . '" type="text" name=' . $name . ' value="' . addslashes($value) . '" data-slider-min="' . $minimum . '" data-slider-max="' . $maximum . '" data-slider-step="' . $step . '" data-slider-value="' . addslashes($value) . '" data-slider-orientation="' . $orientation . '" data-slider-selection="after" data-slider-tooltip="' . $tooltip . '"><span style="font-weight: bold; padding-left: 10px;">' . $maximum . '</span><br/>
                                    </label>';

            if ($var->getSliderLabelPlacement() == SLIDER_LABEL_PLACEMENT_BOTTOM) {
                $returnStr .= $labelStr;
            }

            $returnStr .= '</div>'; // end class=uscic div

            if ($var->isTextbox()) {
                $pretext = $this->engine->getFill($variable, $var, SETTING_SLIDER_TEXTBOX_LABEL);
                $pretext = '<span class="input-group-addon uscic-inputaddon-pretext">' . $this->applyFormatting($pretext, $var->getAnswerFormatting()) . '</span>';
                $inputgroupstart = '<div class="input-group uscic-inputgroup-pretext">';
                $inputgroupend = "</div>";
                $style = "";
                if ($qa == "text-center") {
                    $style = "style='display: block; margin-left: 40%; margin-right: 40%;'";
                } else if ($qa == "text-right") {
                    $style = "style='display: block; margin-left: 80%; margin-right: 0%;'";
                }
                $mask = "integer";
                $m = "\"'alias': '" . $mask . "'\"";
                $placeholder = $this->engine->getFill($variable, $var, SETTING_INPUT_MASK_PLACEHOLDER);
                $textmask = "data-inputmask=" . $m . " data-inputmask-placeholder='" . $placeholder . "'";
                $returnStr .= '<div ' . $style . ' class="uscic-horizontal-slider-textbox ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input id="' . $id . '_textbox" autocomplete="off" ' . $textmask . ' class="form-control uscic-form-control uscic-slider-' . $orientation . '" type=text value="' . addslashes($value) . '">                                    
                                    ' . $inputgroupend . '</div>
                                </div>
                                ';
                $str = "<script type=text/javascript>";
                $str .= '$("#' . $id . '_textbox").keyup(
                                    function(event) {
                                        var x = $("#' . $id . '").slider();
                                        x.slider(\'setValue\', parseFloat($(this).val()));                                        
                                        if ($(this).val() == "") {
                                            $("#' . $id . '").val(""); document.getElementById("' . $id . '").value="";
                                        }
                                        $("#' . $id . '_textbox").trigger("slideStopCustom");
                                    });
                                    
                                $("#' . $id . '_textbox").change(
                                    function(event) {
                                        var x = parseFloat($("#' . $id . '").slider("getAttribute", "max"));                                        
					var y = parseFloat($("#' . $id . '").slider("getAttribute", "min"));                                        
                                        if ($(this).val() == "") {
                                            $("#' . $id . '").val(""); document.getElementById("' . $id . '").value="";
                                        }
					else if (parseFloat($(this).val()) > x) {
                                            $("#' . $id . '").val(x); 
                                            document.getElementById("' . $id . '").value=x;
                                            $(this).val(x);
					}
					else if (parseFloat($(this).val()) < y) {
                                            $("#' . $id . '").val(y);
                                            document.getElementById("' . $id . '").value=y;
                                            $(this).val(y);
					}
					$("#' . $id . '_textbox").trigger("slideStopCustom"); 	
                                    });
        

                                $("#' . $id . '").on(\'slideStop\', function(slideEvt) {
                                        $("#' . $id . '_textbox").val(slideEvt.value);
                                            $("#' . $id . '_textbox").val(slideEvt.value);
                                            $("#' . $id . '_textbox").trigger("slideStopCustom");
                                });    
                                ';
                $str .= "</script>";
                $returnStr .= minifyScript($str);
            }
            $returnStr .= $dkrfna;
        } else {
            $returnStr .= '<div class="form-group uscic-formgroup' . $inlineclass . '">';
            $returnStr .= '<table class="uscic-' . $orientation . '-slider' . $inlineclass . ' ' . $qa . '"><tr><td align=middle>' . $minimum . '</td></tr><tr><td align=middle>
                <input ' . $linkedto . ' class="bootstrapslider ' . $dkrfnaclass . '" id="' . $id . '" ' . $errors . ' style="width: ' . $width . '; height: ' . $height . ';" type="text" name=' . $name . ' value="' . addslashes($value) . '" data-slider-min="' . $minimum . '" data-slider-max="' . $maximum . '" data-slider-step="' . $step . '" data-slider-value="' . addslashes($value) . '" data-slider-orientation="' . $orientation . '" data-slider-selection="after" data-slider-tooltip="' . $tooltip . '"></td></tr><tr><td align=middle><b>' . $maximum . '</b></td></tr></table>';

            if ($var->isTextbox()) {
                $pretext = $this->engine->getFill($variable, $var, SETTING_SLIDER_TEXTBOX_LABEL);
                $pretext = '<span class="input-group-addon uscic-inputaddon-pretext">' . $this->applyFormatting($pretext, $var->getAnswerFormatting()) . '</span>';
                $inputgroupstart = '<div class="input-group uscic-inputgroup-pretext">';
                $inputgroupend = "</div>";

                $style = "";
                if ($qa == "text-center") {
                    $style = "style='display: block; margin-left: 40%; margin-right: 40%;'";
                } else if ($qa == "text-right") {
                    $style = "style='display: block; margin-left: 80%; margin-right: 0%;'";
                }

                $mask = "integer";
                $m = "\"'alias': '" . $mask . "'\"";
                $placeholder = $this->engine->getFill($variable, $var, SETTING_INPUT_MASK_PLACEHOLDER);
                $textmask = "data-inputmask=" . $m . " data-inputmask-placeholder='" . $placeholder . "'";
                $returnStr .= '<div ' . $style . ' class="uscic-vertical-slider-textbox ' . $qa . '">' . $inputgroupstart . $pretext . '
                                <input id="' . $id . '_textbox" autocomplete="off" ' . $textmask . ' class="form-control uscic-form-control" type=text value="' . addslashes($value) . '">                                    
                                    ' . $inputgroupend . '</div>
                                </div>
                                ';
                $str = "<script type=text/javascript>";
                $str .= '$("#' . $id . '_textbox").keyup(
                                    function(event) {
                                        var x = $("#' . $id . '").slider();
                                        x.slider(\'setValue\', parseFloat($(this).val()));
                                        
                                        if ($(this).val() == "") {
                                            $("#' . $id . '").val(""); document.getElementById("' . $id . '").value="";                                                
                                        }
                                    });
                                    
                                $("#' . $id . '_textbox").change(
                                    function(event) {
                                        var x = parseFloat($("#' . $id . '").slider("getAttribute", "max"));                                        
					var y = parseFloat($("#' . $id . '").slider("getAttribute", "min"));                                        
                                        if ($(this).val() == "") {
                                            $("#' . $id . '").val(""); 
                                            document.getElementById("' . $id . '").value="";
                                        }
					else if (parseFloat($(this).val()) > x) {
                                            $("#' . $id . '").val(x); 
                                            document.getElementById("' . $id . '").value=x;
                                            $(this).val(x);
					}
					else if (parseFloat($(this).val()) < y) {
                                            $("#' . $id . '").val(y);
                                            document.getElementById("' . $id . '").value=y;
                                            $(this).val(y);
					}
					$("#' . $id . '_textbox").trigger("slideStopCustom"); 	
                                    });
                                    
                                $("#' . $id . '").on(\'slideStop\', function(slideEvt) {                                    
                                        $("#' . $id . '_textbox").val(slideEvt.value);
                                        $("#' . $id . '_textbox").trigger("slideStopCustom");
                                });    
                                ';
                $str .= "</script>";
                $returnStr .= minifyScript($str);
            }
            $returnStr .= $dkrfna;
        }
        return $returnStr;
    }

    function displayDateTimePicker($name, $id, $default = '', $language = "en", $pickdate = 'true', $picktime = 'true', $ushourformat = "true", $seconds = "true", $minutes = "true", $inlineclass = "", $inlinestyle = "", $inlinejavascript = "", $customformat = "", $errorstring = "", $dkrfna = "", $variable = "", $linkedto = "") {

        if ($language != "en") {
            $language = "en"; // TODO: FIGURE OUT WHICH OTHER ONES ARE SUPPORTED AND HOW TO CALL THEM
        }
        $icon = 'glyphicon-calendar';
        $class = "uscic-datetime";
        if ($pickdate == "true" && $picktime == "true") {
            if ($ushourformat == "true") {
                if ($seconds == "true") {
                    $format = "YYYY-MM-DD hh:mm:ss A";
                } else {
                    $format = "YYYY-MM-DD hh:mm A";
                }
            } else {
                $format = "YYYY-MM-DD HH:mm:ss";
            }
        } else if ($pickdate == "true") {
            $class = "uscic-date";
            $format = "YYYY-MM-DD";
        } else if ($picktime == "true") {
            $class = "uscic-time";
            $icon = 'glyphicon-time';
            if ($ushourformat == "true") {
                if ($seconds == "true") {
                    $format = "hh:mm:ss A";
                } else {
                    $format = "hh:mm A";
                }
            } else {
                if ($seconds == "true") {
                    $format = "HH:mm:ss";
                } else {
                    $format = "HH:mm";
                }
            }
        }
        $sec = '';
        $min = '';
        if ($seconds == "true") {
            //$sec = 'useSeconds: \'true\',';
        }
        if ($minutes == "true") {
            //$min = 'useMinutes: \'true\',';
        }

        if ($customformat != "") {
            $format = $customformat;
            if (contains($customformat, "ss") == false) {
                $sec = "";
            }
            if (contains($customformat, "mm") == false) {
                $min = "";
            }
        }

        if ($_SESSION['SYSTEM_ENTRY'] == USCIC_SURVEY) {
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
        }

        $returnStr = '';
        if (!isRegisteredScript("js/datetimepicker/moment-min.js")) {
            registerScript('js/datetimepicker/moment-min.js');
            $returnStr .= getScript("js/datetimepicker/moment-min.js");
        }
        if (!isRegisteredScript("js/datetimepicker/bootstrap-datetimepicker-min.js")) {
            registerScript('js/datetimepicker/bootstrap-datetimepicker-min.js');
            $returnStr .= getScript("js/datetimepicker/bootstrap-datetimepicker-min.js");
        }
        if (!isRegisteredScript("css/bootstrap-datetimepicker.min.css")) {
            registerScript('css/bootstrap-datetimepicker.min.css');
            $returnStr .= getCSS("css/bootstrap-datetimepicker.min.css");
        }

        /* in survey, then check for input masking */
        $inputmasking = '';
        if ($_SESSION['SYSTEM_ENTRY'] == USCIC_SURVEY) {
            global $survey;
            $var = $survey->getVariableDescriptiveByName($variable);
            if ($var->isInputMaskEnabled()) {
                $inputmasking = $this->getDateTimePickerInputMasking($variable, $var);
            }
        }

        // bootstrap date/time picker version 3
        /* $returnStr .= '<div class=\'input-group date ' . $class . '\' id=\'' . $id . 'div\'>
          <div class="input-group uscic-inputgroup-posttext">
          <input ' . $errorstring . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' autocomplete="off" type=\'text\' class="form-control uscic-form-control ' . $dkrfnaclass . ' ' . $inlineclass . '" value="' . $default . '" id="' . $id . '" name="' . $name . '"/>
          <div class="input-group-addon uscic-inputaddon-posttext"><span class="glyphicon ' . $icon . '"></span>
          </div></div>' . $dkrfna . '
          </div>
          <script type="text/javascript">
          $(function () {
          $(\'#' . $id . '\').datetimepicker({' . $sec . $min . 'format: \'' . $format . '\', language: \'' . $language . '\', pickDate: ' . $pickdate . ', pickTime: ' . $picktime . $inputmasking . '});
          $(\'#' . $id . '\').attr("readonly","true");
          });
          </script>'; */

        // bootstrap date/time picker version 4
        $returnStr .= '<div class=\'input-group date ' . $class . '\' id=\'' . $id . 'div\'>
            <div class="input-group uscic-inputgroup-posttext">
            <input ' . $linkedto . ' ' . $errorstring . ' ' . $inlinestyle . ' ' . $inlinejavascript . ' autocomplete="off" type=\'text\' class="form-control uscic-form-control ' . $dkrfnaclass . ' ' . $inlineclass . '" value="' . $default . '" id="' . $id . '" name="' . $name . '"/>
            <div class="input-group-addon uscic-inputaddon-posttext"><span class="glyphicon ' . $icon . '"></span>
            </div></div>' . $dkrfna . '
        </div>';

        if ($_SESSION['SYSTEM_ENTRY'] == USCIC_SURVEY) {
            $returnStr .= '<script type="text/javascript">' . minifyScript('
                $(function () {
                    $(\'#' . $id . '\').datetimepicker({locale: \'' . $language . '\', ' . 'format: \'' . $format . '\'' . $inputmasking . '});                                    
                    $(\'#' . $id . '\').attr("readonly","true");
                });') . '                    
            </script>';
        } else {
            $returnStr .= '<script type="text/javascript">
                $(function () {
                    $(\'#' . $id . '\').datetimepicker(locale: \'' . $language . '\', ' . 'format: \'' . $format . '\'' . $inputmasking . '});                
                });        
            </script>';
            
            
        }
        return $returnStr;
    }

    function getDateTimePickerInputMasking($variable, $var) {

        $inputmasking = '';
        $eq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_EQUAL_TO));
        $enableddates = '';
        $at = $var->getAnswerType();

        // date/datetime picker
        if (inArray($at, array(ANSWER_TYPE_DATE, ANSWER_TYPE_DATETIME))) {

            if ($eq != "") {
                $dates = explode(SEPARATOR_COMPARISON, $eq);
                foreach ($dates as $d) {
                    if (strtotime($d) != false) { // date string
                        if ($enableddates == '') {
                            $enableddates = '"' . date("Y-m-d H:m:s", strtotime($d)) . '"';
                        } else {
                            $enableddates .= ',"' . date("Y-m-d H:m:s", strtotime($d)) . '"';
                        }
                    }
                }
                if ($enableddates != "") {
                    $inputmasking .= ', enabledDates: [' . $enableddates . ']';
                }
            }

            $disableddates = '';
            $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO));
            if ($neq != "") {
                $dates = explode(SEPARATOR_COMPARISON, $neq);
                foreach ($dates as $d) {
                    if (strtotime($d) != false) { // date string
                        if ($disableddates == '') {
                            $disableddates = '"' . date("Y-m-d H:m:s", strtotime($d)) . '"';
                        } else {
                            $disableddates .= ',"' . date("Y-m-d H:m:s", strtotime($d)) . '"';
                        }
                    }
                }
                if ($disableddates != "") {
                    $inputmasking .= ', disabledDates: [' . $disableddates . ']';
                }
            }

            // check for minimum dates
            $mindate = "";
            $gr = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER));
            if ($gr != "") {
                $dates = explode(SEPARATOR_COMPARISON, $gr);
                foreach ($dates as $d) {
                    if (strtotime($d) != false) { // date string
                        if ($mindate == '') {
                            $mindate = date("Y-m-d H:i:s", strtotime($d . "+1 day"));
                        } else {
                            if (strtotime($d) > strtotime($mindate)) {
                                $mindate = date("Y-m-d H:i:s", strtotime($d . "+1 day"));
                            }
                        }
                    }
                }
            }
            $geq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER_EQUAL_TO));
            if ($geq != "") {
                $dates = explode(SEPARATOR_COMPARISON, $geq);
                foreach ($dates as $d) {
                    if (strtotime($d) != false) { // date string
                        if ($mindate == '') {
                            $mindate = date("Y-m-d H:i:s", strtotime($d));
                        } else {
                            if (strtotime($d) > strtotime($mindate)) {
                                $mindate = date("Y-m-d H:i:s", strtotime($d));
                            }
                        }
                    }
                }
            }
            if ($mindate != '') {
                $inputmasking .= ', minDate: moment("' . $mindate . '")';
            }

            // check for maximum dates
            $maxdate = '';
            $sm = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER));
            if ($sm != "") {
                $dates = explode(SEPARATOR_COMPARISON, $sm);
                foreach ($dates as $d) {
                    if (strtotime($d) != false) { // date string
                        if ($maxdate == '') {
                            $maxdate = date("Y-m-d H:i:s", strtotime($d . "-1 day"));
                        } else {
                            if (strtotime($d) < strtotime($mindate)) {
                                $maxdate = date("Y-m-d H:i:s", strtotime($d . "-1 day"));
                            }
                        }
                    }
                }
            }

            $seq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER_EQUAL_TO));
            if ($seq != "") {
                $dates = explode(SEPARATOR_COMPARISON, $seq);
                foreach ($dates as $d) {
                    if (strtotime($d) != false) { // date string
                        if ($maxdate == '') {
                            $maxdate = date("Y-m-d H:i:s", strtotime($d));
                        } else {
                            if (strtotime($d) < strtotime($mindate)) {
                                $maxdate = date("Y-m-d H:i:s", strtotime($d));
                            }
                        }
                    }
                }
            }
            if ($maxdate != '') {
                $inputmasking .= ', maxDate: moment("' . $maxdate . '")';
            }
        }
        // time picker
        else {

            // get all hours equal to
            $allhours = array();
            if ($eq != "") {
                $times = explode(SEPARATOR_COMPARISON, $eq);
                foreach ($times as $d) {
                    if (is_numeric($d)) {
                        $allhours[] = $d;
                    }
                }
            } else {
                // add all hours                
                for ($i = 1; $i < 25; $i++) {
                    $allhours[] = $i;
                }
            }

            // exclude hours not equal to
            $neq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_NOT_EQUAL_TO));
            if ($neq != "") {
                $times = explode(SEPARATOR_COMPARISON, $neq);
                foreach ($times as $d) {
                    if (is_numeric($d)) {
                        if (inArray($d, $allhours)) {
                            unset($allhours[array_search($d, $allhours)]);
                        }
                    }
                }
            }

            // check for minimum hours
            $gr = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER));
            if ($gr != "") {
                $times = explode(SEPARATOR_COMPARISON, $gr);
                foreach ($times as $d) {
                    if (is_numeric($d)) { // date string
                        $key = array_search($d + 1, $allhours);
                        if ($key) {
                            $allhours = array_splice($allhours, $key);
                        }
                    }
                }
            }
            $geq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_GREATER_EQUAL_TO));
            if ($geq != "") {
                $times = explode(SEPARATOR_COMPARISON, $geq);
                foreach ($times as $d) {
                    if (is_numeric($d)) { // date string
                        $key = array_search($d, $allhours);
                        if ($key) {
                            $allhours = array_splice($allhours, $key);
                        }
                    }
                }
            }

            // check for maximum hours
            $maxdate = '';
            $sm = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER));
            if ($sm != "") {
                $times = explode(SEPARATOR_COMPARISON, $sm);
                foreach ($times as $d) {
                    if (is_numeric($d)) { // date string
                        $key = array_search($d, $allhours);
                        if ($key) {
                            array_splice($allhours, $key);
                        }
                    }
                }
            }

            $seq = trim($this->engine->getFill($variable, $var, SETTING_COMPARISON_SMALLER_EQUAL_TO));
            if ($seq != "") {
                $times = explode(SEPARATOR_COMPARISON, $seq);
                foreach ($times as $d) {
                    if (is_numeric($d)) { // date string
                        $key = array_search($d + 1, $allhours);
                        if ($key) {
                            array_splice($allhours, $key);
                        }
                    }
                }
            }
            $inputmasking .= ', enabledHours: [' . implode(",", $allhours) . ']';
        }

        // return result
        return $inputmasking;
    }

    function displayCalendar($id = "calendar", $type = USCIC_SMS) {
        $returnStr = "";
        if ($type == USCIC_SMS) {
            $returnStr .= '<div class="page-header" style="padding-bottom: 1px; margin: 5px 0 20px;">';
            $returnStr .= '
		<div class="pull-right form-inline">
			<div class="btn-group">
				<button class="btn btn-primary" data-calendar-nav="prev"><< Prev</button>
				<button class="btn" data-calendar-nav="today">Today</button>
				<button class="btn btn-primary" data-calendar-nav="next">Next >></button>
			</div>
			<div class="btn-group">
				<button class="btn btn-default" data-calendar-view="year">Year</button>
				<button class="btn btn-default active" data-calendar-view="month">Month</button>
				<button class="btn btn-default" data-calendar-view="week">Week</button>
				<button class="btn btn-default" data-calendar-view="day">Day</button>
			</div>
		</div>	
                <h3></h3>
	</div>
        ';
        } else {
            $returnStr = '<div class="page-header" style="padding-bottom: 1px; margin: 5px 0 20px;">';

            $returnStr .= '<div class="pull-right form-inline">
			<div class="btn-group">
				<button type=button class="btn btn-primary" data-calendar-nav="prev"><< Prev</button>
				<button type=button class="btn" data-calendar-nav="today">Today</button>
				<button type=button class="btn btn-primary" data-calendar-nav="next">Next >></button>
			</div>
			<div class="btn-group">
				<button type=button class="btn btn-default" data-calendar-view="year">Year</button>
				<button type=button class="btn btn-default active" data-calendar-view="month">Month</button>
			</div>
		</div>	<h3></h3>	
	</div>
       
        ';
        }

        $returnStr .= '

<!--		<div class="col-md-9">-->
			<div id="' . $id . '"></div>
	<!--	</div>-->';

        if (!isRegisteredScript("js/underscore-min.js")) {
            registerScript('js/underscore-min.js');
            $returnStr .= getScript("js/underscore-min.js");
        }
        if (!isRegisteredScript("js/jstz.min.js")) {
            registerScript('js/jstz.min.js');
            $returnStr .= getScript("js/jstz.min.js");
        }

        // also in header, but has to be here, otherwise calendar.js fails
        if (!isRegisteredScript("js/tooltip.js")) {
            registerScript('js/tooltip.js');
            $returnStr .= getScript("js/tooltip.js");
        }

        if (!isRegisteredScript("js/calendar-min.js")) {
            registerScript('js/calendar-min.js');
            $returnStr .= getScript("js/calendar-min.js");
        }

        if ($type == USCIC_SMS) {
            if (!isRegisteredScript("js/app.js")) {
                registerScript('js/app.js');
                $returnStr .= getScript("js/app.js");
            }
        } else {
            if (!isRegisteredScript("js/appsurvey.js")) {
                registerScript('js/appsurvey.js');
                $returnStr .= getScript("js/appsurvey.js");
            }
        }
        return $returnStr;
    }

    function displayHeaderForTable($title, $message = '') {
        $extramin = 0;
        if ($message != '') {
            $extramin = 90;
        }
        $header = $this->displayDataTablesScripts();

        if (!isRegisteredScript("css/DT_bootstrap.min.css")) {
            registerScript('css/DT_bootstrap.min.css');
            $header .= getCSS("css/DT_bootstrap.min.css");
        }
        if (!isRegisteredScript("bootstrap/css/sticky-footer-navbar.min.css")) {
            registerScript('bootstrap/css/sticky-footer-navbar.min.css');
            $header .= getCSS("bootstrap/css/sticky-footer-navbar.min.css");
        }
        if (!isRegisteredScript("js/DT_bootstrap.min.js")) {
            registerScript('js/DT_bootstrap.min.js');
            $header .= getScript("js/DT_bootstrap.min.js");
        }

        // $.fn.dataTable.moment( "MMM DD, YYYY - HH:mm:ss" ); this line can be used to hook in ordering of date/time columns using momnet.js...
        // for formatting see http://momentjs.com
        $header .= '<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				$(\'#example\').dataTable({"bFilter": false, "bLengthChange": false, "iDisplayLength": Math.round(($(window).height() - 60 - 51 - 36 - 160 - ' . $extramin . ')/40)});
			} );
		  </script>';
        return $this->showHeader($title, $header);
    }

    function displayHeaderForTableAndSideBar($title, $message = '') {
        $extramin = 0;
        if ($message != '') {
            $extramin = 90;
        }
        $header = $this->displayDataTablesScripts();

        if (!isRegisteredScript("css/DT_bootstrap.min.css")) {
            registerScript('css/DT_bootstrap.min.css');
            $header .= getScript("css/DT_bootstrap.min.css");
        }
        if (!isRegisteredScript("css/uscicadmin.css")) {
            registerScript('css/uscicadmin.css');
            $header .= getCSS("css/uscicadmin.css");
        }
        if (!isRegisteredScript("bootstrap/css/sticky-footer-navbar.min.css")) {
            registerScript('bootstrap/css/sticky-footer-navbar.min.css');
            $header .= getCSS("bootstrap/css/sticky-footer-navbar.min.css");
        }
        $header .= '<script type="text/javascript" charset="utf-8">
			$(document).ready(function () {
					if ($("[rel=tooltip]").length) {
						  $("[rel=tooltip]").tooltip();
					}
			});
			</script>';
        if (!isRegisteredScript("js/DT_bootstrap.min.js")) {
            registerScript('js/DT_bootstrap.min.js');
            $header .= getScript("js/DT_bootstrap.min.js");
        }
        $header .= '<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				$(\'#example\').dataTable({"bFilter": false, "bLengthChange": false, "iDisplayLength": Math.round(($(window).height() - 60 - 51 - 36 - 160 - ' . $extramin . ')/40)});
			} );
      </script>
';
        return $this->showHeader($title, $header);
    }

    function showRespondentsTable($respondents, $refpage = 'interviewer') {
        $returnStr = '

<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="example">
	<thead>
		<tr>
			<th>Id</th>
			<th>Name</th>';
        $columns = Language::defaultDisplayOverviewAddressColumns();

        foreach ($columns as $column) {
            $returnStr .= '<th>' . $column . '</th>';
        }
        $returnStr .= '<th>Last contact</th>
			<th>Status</th>
			<th>Refusal</th>
		</tr>
	</thead>
	<tbody>';
        foreach ($respondents as $respondent) {
            $returnStr .= '<tr>';
            $returnStr .= '<td>' . setSessionParamsHref(array('page' => $refpage . '.info', 'primkey' => $respondent->getPrimkey()), $respondent->getPrimkey()) . '</td>';
            $returnStr .= '<td>' . $respondent->getFirstname() . ' ' . $respondent->getLastname() . '</td>';

            foreach ($columns as $key => $column) {
                $returnStr .= '<td>' . $respondent->getDataByField($key) . '</td>';
            }
            $returnStr .= '<td>' . $this->displayLastContact($respondent) . '</td>';
            $returnStr .= '<td>' . $this->displayStatus($respondent) . '</td>';
            $returnStr .= '<td>' . $this->displayRefusal($respondent) . '</td>';
            $returnStr .= '</tr>';
        }

        $returnStr .= '</tbody></table>';
        return $returnStr;
    }

    function showHouseholdsTable($households, $refpage = '') {
        $returnStr = '<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="example">
	<thead>
		<tr>
			<th>Household id</th>
			<th>Name</th>';

        $columns = Language::defaultDisplayOverviewAddressColumns();
        foreach ($columns as $column) {
            $returnStr .= '<th>' . $column . '</th>';
        }
        $returnStr .= '          <th>Last contact</th>
			<th>Status</th>
			<th>Refusal</th>
		</tr>
	</thead>
	<tbody>';

        foreach ($households as $household) {
            $returnStr .= '<tr>';
            $returnStr .= '<td>' . setSessionParamsHref(array('page' => $refpage . 'interviewer.household.info', 'primkey' => $household->getPrimkey()), $household->getPrimkey()) . '</td>';
            $returnStr .= '<td>' . $household->getName() . '</td>';
            foreach ($columns as $key => $column) {
                $returnStr .= '<td>' . $household->getDataByField($key) . '</td>';
            }
            $returnStr .= '<td><div data-toggle="tooltip" data-placement="top" title="' . $this->displayLastContactText($household) . '">' . $this->displayLastContact($household) . '</div></td>';
            $returnStr .= '<td>' . $this->displayStatus($household) . '</td>';
            $returnStr .= '<td>' . $this->displayRefusal($household) . '</td>';
            $returnStr .= '</tr>';
            $respondents = $household->getSelectedRespondentsWithFinFamR();
            foreach ($respondents as $respondent) {
                $bgcolor = 'style="background: #ecf4ff;"';
                $returnStr .= '<tr>';
                $returnStr .= '<td ' . $bgcolor . ' align=right>' . setSessionParamsHref(array('page' => $refpage . 'interviewer.respondent.info', 'primkey' => $respondent->getPrimkey()), $respondent->getPrimkey()) . '</td>';
                $returnStr .= '<td ' . $bgcolor . ' align=right><b>' . $respondent->getName() . '<b></td>';
                foreach ($columns as $key => $column) {
                    $returnStr .= '<td ' . $bgcolor . '></td>'; //don't diplay for members in hh
                    // $returnStr .= '<td>' . $household->getDataByField($key) . '</td>';
                }
                $returnStr .= '<td ' . $bgcolor . '><div data-toggle="tooltip" data-placement="top" title="' . $this->displayLastContactText($respondent) . '">' . $this->displayLastContact($respondent) . '</div></td>';
                $returnStr .= '<td ' . $bgcolor . '>' . $this->displayStatus($respondent) . '</td>';
                $returnStr .= '<td ' . $bgcolor . '>' . $this->displayRefusal($respondent) . '</td>';
                $returnStr .= '</tr>';
            }
        }

        $returnStr .= '</tbody></table>';
        return $returnStr;
    }

    function displayLastContact($respondent) {
        $contact = $respondent->getLastContact();
        if ($contact == null) {
            return Language::labelNone();
        } else {
            return $contact->getCode();
        }
    }

    function displayLastContactText($respondent) {
        $contact = $respondent->getLastContact();
        if ($contact == null) {
            return Language::labelNone();
        } else {
            return $contact->getText();
        }
    }

    function displayStatus($respondent) {
        $statusCodes = Language::labelStatus();
        if (isset($statusCodes[$respondent->getStatus()])) {
            return $statusCodes[$respondent->getStatus()];
        }
        return '-';
    }

    function displayRefusal($respondent) {
        if ($respondent->isRefusal()) {
            return Language::labelYes();
        }
        return Language::labelNo();
    }

    function displayWarning($message, $id = "") {
        $idtext = "";
        if ($id != "") {
            $idtext = "id=" . $id;
        }
        return '<div ' . $idtext . ' class="alert alert-warning">' . $message . '</div>';
    }

    function displayError($message, $id = "") {
        $idtext = "";
        if ($id != "") {
            $idtext = "id=" . $id;
        }
        return '<div class="alert alert-danger">' . $message . '</div>';
    }

    function displaySuccess($message, $id = "") {
        $idtext = "";
        if ($id != "") {
            $idtext = "id=" . $id;
        }
        return '<div class="alert alert-success">' . $message . '</div>';
    }

    function displayInfo($message, $id = "") {
        $idtext = "";
        if ($id != "") {
            $idtext = "id=" . $id;
        }
        return '<div class="alert alert-info">' . $message . '</div>';
    }

    function displayModesAdmin($name, $id, $value, $multiple = "", $list = "", $onchange = "") {
        $returnStr = $this->displayComboBox();
        $tag = "";
        if ($multiple != "") {
            $tag = "[]";
        }
        $returnStr .= '<select ' . $onchange . ' ' . $multiple . ' id="' . $id . '" name="' . $name . $tag . '" class="form-control selectpicker show-tick">';
        $modes = Common::surveyModes();
        ksort($modes);
        $values = explode("~", $value);
        $modelist = explode("~", $list);
        $icons = array(MODE_CAPI => "data-icon='glyphicon glyphicon-user'", MODE_CATI => "data-icon='glyphicon glyphicon-earphone'", MODE_CASI => "data-icon='glyphicon glyphicon-globe'", MODE_CADI => "data-icon='glyphicon glyphicon-pencil'");
        foreach ($modes as $k => $mode) {
            if (trim($list) == "" || inArray($k, $modelist)) {
                $selected = "";
                if (inArray($k, $values)) {
                    $selected = "SELECTED";
                }
                $icon = $icons[$k];
                $returnStr .= "<option $icon $selected value=" . $k . ">" . $mode . "</option>";
            }
        }
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayModesChange($current = "") {
        $returnStr = "<select class='form-control selectpicker show-tick' name=" . SETTING_CHANGE_MODE . ">";
        $selected = array(MODE_CHANGE_PROGRAMMATIC_ALLOWED => "", MODE_CHANGE_NOTALLOWED => "", MODE_CHANGE_RESPONDENT_ALLOWED => "");
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[MODE_CHANGE_NOTALLOWED] . " value=" . MODE_CHANGE_NOTALLOWED . ">" . Language::optionsModeChangeNotAllowed() . "</option>";
        $returnStr .= "<option " . $selected[MODE_CHANGE_PROGRAMMATIC_ALLOWED] . " value=" . MODE_CHANGE_PROGRAMMATIC_ALLOWED . ">" . Language::optionsModeChangeProgrammaticAllowed() . "</option>";
        $returnStr .= "<option " . $selected[MODE_CHANGE_RESPONDENT_ALLOWED] . " value=" . MODE_CHANGE_RESPONDENT_ALLOWED . ">" . Language::optionsModeChangeRespondentAllowed() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayModeReentry($current = "") {
        $returnStr = "<select class='form-control selectpicker show-tick' name=" . SETTING_REENTRY_MODE . ">";
        $selected = array(MODE_REENTRY_YES => "", MODE_REENTRY_NO => "");
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[MODE_REENTRY_NO] . " value=" . MODE_REENTRY_NO . ">" . Language::optionsModeReentryNo() . "</option>";
        $returnStr .= "<option " . $selected[MODE_REENTRY_YES] . " value=" . MODE_REENTRY_YES . ">" . Language::optionsModeReentryYes() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayModeBack($current = "") {
        $returnStr = "<select class='form-control selectpicker show-tick' name=" . SETTING_BACK_MODE . ">";
        $selected = array(MODE_BACK_YES => "", MODE_BACK_NO => "");
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[MODE_BACK_NO] . " value=" . MODE_BACK_NO . ">" . Language::optionsModeBackNo() . "</option>";
        $returnStr .= "<option " . $selected[MODE_BACK_YES] . " value=" . MODE_BACK_YES . ">" . Language::optionsModeBackYes() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayLanguagesAdmin($name, $id, $value, $flags = true, $country = true, $showdefault = true, $multiple = "", $list = "") {
        $languages = Language::getLanguagesArray();
        $returnStr = $this->displayComboBox();
        $values = explode("~", $value);
        $tag = "";
        if ($multiple != "") {
            $tag = "[]";
        }
        $returnStr .= '<select ' . $multiple . ' id="' . $id . '" name="' . $name . $tag . '" class="form-control selectpicker show-tick">';
        $languagelist = explode("~", $list);
        foreach ($languages as $lang) {
            if (trim($list) == "" || inArray($lang["value"], $languagelist)) {
                $text = $lang["name"];
                if ($country) {
                    if ($lang["countryfull"] != "") {
                        $text .= "(" . $lang["countryfull"] . ")";
                    }
                }
                $flagtext = "";
                if ($flags) {
                    $flagtext = 'data-icon="bfh-flag-' . $lang["country"] . '"';
                }
                $selected = "";
                if (inArray($lang["value"], $values)) {
                    $selected = "SELECTED";
                }
                $default = "";
                if ($showdefault == true && $lang["value"] == getDefaultSurveyLanguage()) {
                    $default = " (default)";
                }
                $returnStr .= '<option ' . $selected . ' value="' . $lang["value"] . '" ' . $flagtext . '>' . $text . $default . '</option>';
            }
        }
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayLanguagesChange($current = "") {
        $returnStr = "<select class='form-control selectpicker show-tick' name=" . SETTING_CHANGE_LANGUAGE . ">";
        $selected = array(LANGUAGE_CHANGE_PROGRAMMATIC_ALLOWED => "", LANGUAGE_CHANGE_NOTALLOWED => "", LANGUAGE_CHANGE_RESPONDENT_ALLOWED => "");
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[LANGUAGE_CHANGE_NOTALLOWED] . " value=" . LANGUAGE_CHANGE_NOTALLOWED . ">" . Language::optionsLanguageChangeNotAllowed() . "</option>";
        $returnStr .= "<option " . $selected[LANGUAGE_CHANGE_PROGRAMMATIC_ALLOWED] . " value=" . LANGUAGE_CHANGE_PROGRAMMATIC_ALLOWED . ">" . Language::optionsLanguageChangeProgrammaticAllowed() . "</option>";
        $returnStr .= "<option " . $selected[LANGUAGE_CHANGE_RESPONDENT_ALLOWED] . " value=" . LANGUAGE_CHANGE_RESPONDENT_ALLOWED . ">" . Language::optionsLanguageChangeRespondentAllowed() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayLanguageReentry($current = "") {
        $returnStr = "<select class='form-control selectpicker show-tick' name=" . SETTING_REENTRY_LANGUAGE . ">";
        $selected = array(LANGUAGE_REENTRY_YES => "", LANGUAGE_REENTRY_NO => "");
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[LANGUAGE_REENTRY_NO] . " value=" . LANGUAGE_REENTRY_NO . ">" . Language::optionsLanguageReentryNo() . "</option>";
        $returnStr .= "<option " . $selected[LANGUAGE_REENTRY_YES] . " value=" . LANGUAGE_REENTRY_YES . ">" . Language::optionsLanguageReentryYes() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayLanguageBack($current = "") {
        $returnStr = "<select class='form-control selectpicker show-tick' name=" . SETTING_BACK_LANGUAGE . ">";
        $selected = array(LANGUAGE_BACK_YES => "", LANGUAGE_BACK_NO => "");
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[LANGUAGE_BACK_NO] . " value=" . LANGUAGE_BACK_NO . ">" . Language::optionsLanguageBackNo() . "</option>";
        $returnStr .= "<option " . $selected[LANGUAGE_BACK_YES] . " value=" . LANGUAGE_BACK_YES . ">" . Language::optionsLanguageBackYes() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayIsArray($current = "", $type = -1) {
        $returnStr = "<select id=arraydrop class='form-control selectpicker show-tick' name='" . SETTING_ARRAY . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ARRAY_ANSWER_YES => "", ARRAY_ANSWER_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $returnStr .= "<option " . $selected[ARRAY_ANSWER_NO] . " value=" . ARRAY_ANSWER_NO . ">" . Language::optionsArrayNo() . "</option>";
        $returnStr .= "<option " . $selected[ARRAY_ANSWER_YES] . " value=" . ARRAY_ANSWER_YES . ">" . Language::optionsArrayYes() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayIsKeep($current = "", $type = -1) {
        $returnStr = "<select id=keepdrop class='form-control selectpicker show-tick' name='" . SETTING_KEEP . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", KEEP_ANSWER_YES => "", KEEP_ANSWER_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $returnStr .= "<option " . $selected[KEEP_ANSWER_NO] . " value=" . KEEP_ANSWER_NO . ">" . Language::optionsKeepNo() . "</option>";
        $returnStr .= "<option " . $selected[KEEP_ANSWER_YES] . " value=" . KEEP_ANSWER_YES . ">" . Language::optionsKeepYes() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayHidden($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", HIDDEN_YES => "", HIDDEN_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[HIDDEN_NO] . " value=" . HIDDEN_NO . ">" . Language::optionsHiddenNo() . "</option>";
        $returnStr .= "<option " . $selected[HIDDEN_YES] . " value=" . HIDDEN_YES . ">" . Language::optionsHiddenYes() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayScreendumps($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", SCREENDUMPS_YES => "", SCREENDUMPS_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[SCREENDUMPS_YES] . " value=" . SCREENDUMPS_YES . ">" . Language::optionsScreendumpsYes() . "</option>";
        $returnStr .= "<option " . $selected[SCREENDUMPS_NO] . " value=" . SCREENDUMPS_NO . ">" . Language::optionsScreendumpsNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayDataInputMask($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", DATA_INPUTMASK_YES => "", DATA_INPUTMASK_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[DATA_INPUTMASK_YES] . " value=" . DATA_INPUTMASK_YES . ">" . Language::optionsDataInputMaskYes() . "</option>";
        $returnStr .= "<option " . $selected[DATA_INPUTMASK_NO] . " value=" . DATA_INPUTMASK_NO . ">" . Language::optionsDataInputMaskNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayDataKeepOnly($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", DATA_KEEP_ONLY_YES => "", DATA_KEEP_ONLY_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[DATA_KEEP_ONLY_YES] . " value=" . DATA_KEEP_ONLY_YES . ">" . Language::optionsDataKeepOnlyYes() . "</option>";
        $returnStr .= "<option " . $selected[DATA_KEEP_ONLY_NO] . " value=" . DATA_KEEP_ONLY_NO . ">" . Language::optionsDataKeepOnlyNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayDataKeep($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", DATA_KEEP_YES => "", DATA_KEEP_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[DATA_KEEP_YES] . " value=" . DATA_KEEP_YES . ">" . Language::optionsDataKeepYes() . "</option>";
        $returnStr .= "<option " . $selected[DATA_KEEP_NO] . " value=" . DATA_KEEP_NO . ">" . Language::optionsDataKeepNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayDataSkip($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", DATA_SKIP_YES => "", DATA_SKIP_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[DATA_SKIP_YES] . " value=" . DATA_SKIP_YES . ">" . Language::optionsDataSkipYes() . "</option>";
        $returnStr .= "<option " . $selected[DATA_SKIP_NO] . " value=" . DATA_SKIP_NO . ">" . Language::optionsDataSkipNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displaySetOfEnumeratedOutput($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", DATA_KEEP_YES => "", DATA_KEEP_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[SETOFENUMERATED_DEFAULT] . " value=" . SETOFENUMERATED_DEFAULT . ">" . Language::optionsDataSetOfEnumeratedDefault() . "</option>";
        $returnStr .= "<option " . $selected[SETOFENUMERATED_BINARY] . " value=" . SETOFENUMERATED_BINARY . ">" . Language::optionsDataSetOfEnumeratedBinary() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayValueLabelWidth($name, $current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . $name . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", DATA_KEEP_YES => "", DATA_KEEP_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[VALUELABEL_WIDTH_SHORT] . " value=" . VALUELABEL_WIDTH_SHORT . ">" . Language::optionsValueLabelWidthShort() . "</option>";
        $returnStr .= "<option " . $selected[VALUELABEL_WIDTH_FULL] . " value=" . VALUELABEL_WIDTH_FULL . ">" . Language::optionsValueLabelWidthFull() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displaySections($name, $current, $suid, $ignore = "", $multiple = '') {
        $survey = new Survey($suid);
        $sections = $survey->getSections();
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        foreach ($sections as $section) {
            $selected = "";
            if ($current == $section->getSeid()) {
                $selected = "SELECTED";
            }
            if ($section->getSeid() != $ignore) {
                $returnStr .= "<option " . $selected . " value=" . $section->getSeid() . ">" . $section->getName() . "</option>";
            }
        }
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displaySurveys($name, $id, $current, $ignore = "", $multiple = "", $onchange = "") {
        $surveys = new Surveys($suid);
        $surveys = $surveys->getSurveys(false);
        $returnStr = "<select $onchange $multiple class='selectpicker show-tick' name=$name id=$id>";
        $current = explode("~", $current);
        foreach ($surveys as $survey) {
            if ($survey->getSuid() != $ignore) {
                $selected = "";
                if (inArray($survey->getSuid(), $current)) {
                    $selected = "SELECTED";
                }
                $returnStr .= "<option " . $selected . " value=" . $survey->getSuid() . ">" . $survey->getName() . "</option>";
            }
        }
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayIfEmpty($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", IF_EMPTY_ALLOW => "", IF_EMPTY_NOTALLOW => "", IF_EMPTY_WARN => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[IF_EMPTY_ALLOW] . " value=" . IF_EMPTY_ALLOW . ">" . Language::optionsIfEmptyAllow() . "</option>";
        $returnStr .= "<option " . $selected[IF_EMPTY_NOTALLOW] . " value=" . IF_EMPTY_NOTALLOW . ">" . Language::optionsIfEmptyNotAllow() . "</option>";
        $returnStr .= "<option " . $selected[IF_EMPTY_WARN] . " value=" . IF_EMPTY_WARN . ">" . Language::optionsIfEmptyWarn() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayIfError($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", IF_ERROR_ALLOW => "", IF_ERROR_NOTALLOW => "", IF_ERROR_WARN => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[IF_ERROR_ALLOW] . " value=" . IF_ERROR_ALLOW . ">" . Language::optionsIfErrorAllow() . "</option>";
        $returnStr .= "<option " . $selected[IF_ERROR_NOTALLOW] . " value=" . IF_ERROR_NOTALLOW . ">" . Language::optionsIfErrorNotAllow() . "</option>";
        $returnStr .= "<option " . $selected[IF_ERROR_WARN] . " value=" . IF_ERROR_WARN . ">" . Language::optionsIfErrorWarn() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayInputMasks($name, $current = "") {
        $returnStr = "<select id=$name class='selectpicker show-tick' name=" . $name . ">";
        $returnStr .= "<option value=''>" . Language::optionsInputMaskNone() . "</option>";
        $selected = array();
        $inputmasks = Common::surveyInputMasks();
        foreach ($inputmasks as $k => $v) {
            $selected[$k] = "";
            if ($current == $k) {
                $selected[$k] = "selected";
            }
            $returnStr .= "<option " . $selected[$k] . " value=" . $k . ">" . $v . "</option>";
        }
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayInputMaskEnabled($current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . SETTING_INPUT_MASK_ENABLED . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", INPUT_MASK_YES => "", INPUT_MASK_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[INPUT_MASK_YES] . " value=" . INPUT_MASK_YES . ">" . Language::optionsInputMaskYes() . "</option>";
        $returnStr .= "<option " . $selected[INPUT_MASK_NO] . " value=" . INPUT_MASK_NO . ">" . Language::optionsInputMaskNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayHeaderFixed($current = "", $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=" . SETTING_HEADER_FIXED . ">";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", TABLE_YES => "", TABLE_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[TABLE_YES] . " value=" . TABLE_YES . ">" . Language::optionsHeaderFixedYes() . "</option>";
        $returnStr .= "<option " . $selected[TABLE_NO] . " value=" . TABLE_NO . ">" . Language::optionsHeaderFixedNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayAlignment($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ALIGN_LEFT => "", ALIGN_RIGHT => "", ALIGN_JUSTIFIED => "", ALIGN_CENTER => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[ALIGN_LEFT] . " value=" . ALIGN_LEFT . ">" . Language::optionsAlignmentLeft() . "</option>";
        $returnStr .= "<option " . $selected[ALIGN_RIGHT] . " value=" . ALIGN_RIGHT . ">" . Language::optionsAlignmentRight() . "</option>";
        $returnStr .= "<option " . $selected[ALIGN_JUSTIFIED] . " value=" . ALIGN_JUSTIFIED . ">" . Language::optionsAlignmentJustified() . "</option>";
        $returnStr .= "<option " . $selected[ALIGN_CENTER] . " value=" . ALIGN_CENTER . ">" . Language::optionsAlignmentCenter() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayFormatting($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select multiple class='selectpicker show-tick' name='" . $name . "[]'>";
        $current = explode("~", $current);

        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", FORMATTING_BOLD => "", FORMATTING_ITALIC => "", FORMATTING_UNDERLINED => "");
        foreach ($current as $c) {
            $selected[$c] = "selected";
        }

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[FORMATTING_BOLD] . " value=" . FORMATTING_BOLD . ">" . Language::optionsFormattingBold() . "</option>";
        $returnStr .= "<option " . $selected[FORMATTING_ITALIC] . " value=" . FORMATTING_ITALIC . ">" . Language::optionsFormattingItalic() . "</option>";
        $returnStr .= "<option " . $selected[FORMATTING_UNDERLINED] . " value=" . FORMATTING_UNDERLINED . ">" . Language::optionsFormattingUnderlined() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayErrorPlacement($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ERROR_PLACEMENT_AT_BOTTOM => "", ERROR_PLACEMENT_AT_TOP => "", ERROR_PLACEMENT_WITH_QUESTION => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[ERROR_PLACEMENT_WITH_QUESTION] . " value=" . ERROR_PLACEMENT_WITH_QUESTION . ">" . Language::optionsErrorPlacementWithQuestion() . "</option>";
        $returnStr .= "<option " . $selected[ERROR_PLACEMENT_AT_BOTTOM] . " value=" . ERROR_PLACEMENT_AT_BOTTOM . ">" . Language::optionsErrorPlacementAtBottom() . "</option>";
        $returnStr .= "<option " . $selected[ERROR_PLACEMENT_AT_TOP] . " value=" . ERROR_PLACEMENT_AT_TOP . ">" . Language::optionsErrorPlacementAtTop() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayButton($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", BUTTON_YES => "", BUTTON_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[BUTTON_YES] . " value=" . BUTTON_YES . ">" . Language::optionsButtonYes() . "</option>";
        $returnStr .= "<option " . $selected[BUTTON_NO] . " value=" . BUTTON_NO . ">" . Language::optionsButtonNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayButtonLabel($name, $current, $readonly = '') {
        if ($readonly != "") {
            $name .= "_ignore";
        }
        return "<input type=text $readonly class='form-control autocompletebasic' name='" . $name . "' value='" . $this->displayTextSettingValue(convertHTLMEntities($current, ENT_QUOTES)) . "'>";
    }

    function displayProgressbar($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", PROGRESSBAR_NO => "", PROGRESSBAR_PERCENT => "", PROGRESS_BAR_BAR => "", PROGRESS_BAR_ALL => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[PROGRESSBAR_NO] . " value=" . PROGRESSBAR_NO . ">" . Language::optionsProgressBarNo() . "</option>";
        $returnStr .= "<option " . $selected[PROGRESSBAR_PERCENT] . " value=" . PROGRESSBAR_PERCENT . ">" . Language::optionsProgressBarPercent() . "</option>";
        $returnStr .= "<option " . $selected[PROGRESSBAR_BAR] . " value=" . PROGRESSBAR_BAR . ">" . Language::optionsProgressBarBar() . "</option>";
        $returnStr .= "<option " . $selected[PROGRESSBAR_ALL] . " value=" . PROGRESSBAR_ALL . ">" . Language::optionsProgressBarAll() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayProgressbarType($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", PROGRESSBAR_NO => "", PROGRESSBAR_PERCENT => "", PROGRESS_BAR_BAR => "", PROGRESS_BAR_ALL => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[PROGRESSBAR_WHOLE] . " value=" . PROGRESSBAR_WHOLE . ">" . Language::optionsProgressBarWhole() . "</option>";
        $returnStr .= "<option " . $selected[PROGRESSBAR_SECTION] . " value=" . PROGRESSBAR_SECTION . ">" . Language::optionsProgressBarSection() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayAccessTypes($current) {
        $returnStr = "<select class='selectpicker show-tick' name=" . SETTING_ACCESS_TYPE . ">";
        $selected = array(LOGIN_ANONYMOUS => "", LOGIN_DIRECT => "", LOGIN_LOGINCODE);
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[LOGIN_ANONYMOUS] . " value=" . LOGIN_ANONYMOUS . ">" . Language::optionsAccessTypeAnonymous() . "</option>";
        $returnStr .= "<option " . $selected[LOGIN_DIRECT] . " value=" . LOGIN_DIRECT . ">" . Language::optionsAccessTypeDirect() . "</option>";
        $returnStr .= "<option " . $selected[LOGIN_LOGINCODE] . " value=" . LOGIN_LOGINCODE . ">" . Language::optionsAccessTypeLogincode() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayAccessReturn($current) {
        $returnStr = "<select class='selectpicker show-tick' name='" . SETTING_ACCESS_RETURN . "'>";
        $selected = array(ACCESS_RETURN_YES => "", ACCESS_RETURN_NO => "");
        $selected[$current] = "selected";
        $returnStr .= "<option " . $selected[ACCESS_RETURN_YES] . " value=" . ACCESS_RETURN_YES . ">" . Language::optionsAccessReturnYes() . "</option>";
        $returnStr .= "<option " . $selected[ACCESS_RETURN_NO] . " value=" . ACCESS_RETURN_NO . ">" . Language::optionsAccessReturnNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayValidateAssignment($name, $current, $generic = false) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", VALIDATE_ASSIGNMENT_YES => "", VALIDATE_ASSIGNMENT_NO => "");
        $selected[$current] = "selected";
        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $returnStr .= "<option " . $selected[VALIDATE_ASSIGNMENT_YES] . " value=" . VALIDATE_ASSIGNMENT_YES . ">" . Language::optionsValidateYes() . "</option>";
        $returnStr .= "<option " . $selected[VALIDATE_ASSIGNMENT_NO] . " value=" . VALIDATE_ASSIGNMENT_NO . ">" . Language::optionsValidateNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayApplyChecks($name, $current, $generic = false) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", APPLY_CHECKS_YES => "", APPLY_CHECKS_NO => "");
        $selected[$current] = "selected";        
        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $returnStr .= "<option " . $selected[APPLY_CHECKS_YES] . " value=" . APPLY_CHECKS_YES . ">" . Language::optionsApplyChecksYes() . "</option>";
        $returnStr .= "<option " . $selected[APPLY_CHECKS_NO] . " value=" . APPLY_CHECKS_NO . ">" . Language::optionsApplyChecksNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }
    
    function displayExclusive($name, $current, $generic = false) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", GROUP_YES => "", GROUP_NO => "");
        $selected[$current] = "selected";
        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $returnStr .= "<option " . $selected[GROUP_YES] . " value=" . GROUP_YES . ">" . Language::optionsGroupYes() . "</option>";
        $returnStr .= "<option " . $selected[GROUP_NO] . " value=" . GROUP_NO . ">" . Language::optionsGroupNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayInclusive($name, $current, $generic = false) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $selected = array(SETTING_FOLLOW_GENERIC => "", GROUP_YES => "", GROUP_NO => "");
        $selected[$current] = "selected";
        //$returnStr .= "<option></option>";
        $returnStr .= "<option " . $selected[GROUP_YES] . " value=" . GROUP_YES . ">" . Language::optionsGroupYes() . "</option>";
        $returnStr .= "<option " . $selected[GROUP_NO] . " value=" . GROUP_NO . ">" . Language::optionsGroupNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayStriped($name, $current, $generic = false) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", GROUP_YES => "", GROUP_NO => "");
        $selected[$current] = "selected";
        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $returnStr .= "<option " . $selected[GROUP_YES] . " value=" . GROUP_YES . ">" . Language::optionsGroupYes() . "</option>";
        $returnStr .= "<option " . $selected[GROUP_NO] . " value=" . GROUP_NO . ">" . Language::optionsGroupNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayMobileLabels($name, $current, $generic = false) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", GROUP_YES => "", GROUP_NO => "");
        $selected[$current] = "selected";
        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        $returnStr .= "<option " . $selected[GROUP_YES] . " value=" . GROUP_YES . ">" . Language::optionsGroupYes() . "</option>";
        $returnStr .= "<option " . $selected[GROUP_NO] . " value=" . GROUP_NO . ">" . Language::optionsGroupNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayOrientation($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ORIENTATION_HORIZONTAL => "", ORIENTATION_VERTICAL => "");
        $selected[$current] = "selected";

        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        }
        $returnStr .= "<option " . $selected[ORIENTATION_HORIZONTAL] . " value=" . ORIENTATION_HORIZONTAL . ">" . Language::optionsSliderOrientationHorizontal() . "</option>";
        $returnStr .= "<option " . $selected[ORIENTATION_VERTICAL] . " value=" . ORIENTATION_VERTICAL . ">" . Language::optionsSliderOrientationVertical() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayEnumeratedTemplate($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select id=$name class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ORIENTATION_HORIZONTAL => "", ORIENTATION_VERTICAL => "");
        $selected[$current] = "selected";

        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        }
        $returnStr .= "<option " . $selected[ORIENTATION_HORIZONTAL] . " value=" . ORIENTATION_HORIZONTAL . ">" . Language::optionsOrientationHorizontal() . "</option>";
        $returnStr .= "<option " . $selected[ORIENTATION_VERTICAL] . " value=" . ORIENTATION_VERTICAL . ">" . Language::optionsOrientationVertical() . "</option>";

        if ($generic || $type > 0) {
            $returnStr .= "<option " . $selected[ORIENTATION_CUSTOM] . " value=" . ORIENTATION_CUSTOM . ">" . Language::optionsOrientationCustom() . "</option>";
        }
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayEnumeratedOrder($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ORDER_LABEL_FIRST => "", ORDER_OPTION_FIRST => "");
        $selected[$current] = "selected";

        if ($generic) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
        }
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        }
        $returnStr .= "<option " . $selected[ORDER_OPTION_FIRST] . " value=" . ORDER_OPTION_FIRST . ">" . Language::optionsEnumeratedOrderOptionFirst() . "</option>";
        $returnStr .= "<option " . $selected[ORDER_LABEL_FIRST] . " value=" . ORDER_LABEL_FIRST . ">" . Language::optionsEnumeratedOrderLabelFirst() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayTooltip($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", TOOLTIP_YES => "", TOOLTIP_NO => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[TOOLTIP_YES] . " value=" . TOOLTIP_YES . ">" . Language::optionsSliderTooltipYes() . "</option>";
        $returnStr .= "<option " . $selected[TOOLTIP_NO] . " value=" . TOOLTIP_NO . ">" . Language::optionsSliderTooltipNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayEnumeratedLabel($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='$name'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ENUMERATED_LABEL_LABEL_ONLY => "", ENUMERATED_LABEL_LABEL_CODE => "", ENUMERATED_LABEL_LABEL_CODE_VALUELABEL => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }

        $returnStr .= "<option " . $selected[ENUMERATED_LABEL_INPUT_ONLY] . " value=" . ENUMERATED_LABEL_INPUT_ONLY . ">" . Language::optionsEnumeratedInputOnly() . "</option>";
        $returnStr .= "<option " . $selected[ENUMERATED_LABEL_LABEL_ONLY] . " value=" . ENUMERATED_LABEL_LABEL_ONLY . ">" . Language::optionsEnumeratedLabelOnly() . "</option>";
        $returnStr .= "<option " . $selected[ENUMERATED_LABEL_LABEL_CODE] . " value=" . ENUMERATED_LABEL_LABEL_CODE . ">" . Language::optionsEnumeratedLabelCode() . "</option>";
        $returnStr .= "<option " . $selected[ENUMERATED_LABEL_LABEL_CODE_VALUELABEL] . " value=" . ENUMERATED_LABEL_LABEL_CODE_VALUELABEL . ">" . Language::optionsEnumeratedLabelAll() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayEnumeratedTextBox($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='$name'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", TEXTBOX_YES => "", TEXTBOX_NO => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[TEXTBOX_YES] . " value=" . TEXTBOX_YES . ">" . Language::optionsEnumeratedTextboxYes() . "</option>";
        $returnStr .= "<option " . $selected[TEXTBOX_NO] . " value=" . TEXTBOX_NO . ">" . Language::optionsEnumeratedTextboxNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayTextBox($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", TEXTBOX_YES => "", TEXTBOX_NO => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[TEXTBOX_YES] . " value=" . TEXTBOX_YES . ">" . Language::optionsSliderTextboxYes() . "</option>";
        $returnStr .= "<option " . $selected[TEXTBOX_NO] . " value=" . TEXTBOX_NO . ">" . Language::optionsSliderTextboxNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displaySliderPlacement($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", TEXTBOX_YES => "", TEXTBOX_NO => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[SLIDER_LABEL_PLACEMENT_TOP] . " value=" . SLIDER_LABEL_PLACEMENT_TOP . ">" . Language::optionsSliderLabelsTop() . "</option>";
        $returnStr .= "<option " . $selected[SLIDER_LABEL_PLACEMENT_BOTTOM] . " value=" . SLIDER_LABEL_PLACEMENT_BOTTOM . ">" . Language::optionsSliderLabelsBottom() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayEnumeratedSplit($name, $current, $generic = false) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", ENUMERATED_YES => "", ENUMERATED_NO => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[ENUMERATED_YES] . " value=" . ENUMERATED_YES . ">" . Language::optionsEnumeratedYes() . "</option>";
        $returnStr .= "<option " . $selected[ENUMERATED_NO] . " value=" . ENUMERATED_NO . ">" . Language::optionsEnumeratedNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayTextSettingValue($text) {
        if ($text == SETTING_FOLLOW_GENERIC) {
            return "";
        }
        if ($text == SETTING_FOLLOW_TYPE) {
            return "";
        }
        return $text;
    }

    function displaySectionHeader($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", SECTIONHEADER_YES => "", SECTIONHEADER_NO => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[SECTIONHEADER_YES] . " value=" . SECTIONHEADER_YES . ">" . Language::optionsSectionHeaderYes() . "</option>";
        $returnStr .= "<option " . $selected[SECTIONHEADER_NO] . " value=" . SECTIONHEADER_NO . ">" . Language::optionsSectionHeaderNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displaySectionFooter($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name=$name>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", SECTIONFOOTER_YES => "", SECTIONFOOTER_NO => "");
        $selected[$current] = "selected";

        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[SECTIONFOOTER_YES] . " value=" . SECTIONFOOTER_YES . ">" . Language::optionsSectionFooterYes() . "</option>";
        $returnStr .= "<option " . $selected[SECTIONFOOTER_NO] . " value=" . SECTIONFOOTER_NO . ">" . Language::optionsSectionFooterNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    /* RADIO BUTTON/CHECK BOX SCRIPTS */

    function displayRadioSwitch($css = true) {
        $returnStr = "";
        if (!isRegisteredScript("js/switch/bootstrap-switch-min.js")) {
            registerScript('js/switch/bootstrap-switch-min.js');
            $returnStr .= getScript("js/switch/bootstrap-switch-min.js");
        }
        if ($css && !isRegisteredScript("js/switch/bootstrap-switch.min.css")) {
            registerScript('js/switch/bootstrap-switch.min.css');
            $returnStr .= getCSS("js/switch/bootstrap-switch.min.css");
        }
        $returnStr .= minifyScript("<script type='text/javascript'>
                            $( document ).ready(function() {
                                $('input.bootstrapswitch').bootstrapSwitch();
                            });
                          </script>");
        return $returnStr;
    }
    
    function displayRadioSwitchCSS() {
        $returnStr = "";        
        if (!isRegisteredScript("js/switch/bootstrap-switch.css")) {
            registerScript('js/switch/bootstrap-switch.css');
            $returnStr .= getCSS("js/switch/bootstrap-switch.css");
        }        
        return $returnStr;
    }

    function displayRadioButtonScript($target, $tablecell = false) {

        $returnStr = '<script type="text/javascript">$( document ).ready(function() {';

        // not allowing deselect
        if (Config::allowRadioButtonUnselect() == false) {
            if ($tablecell) {
                $returnStr .= '$("#cell' . $target . '").mousedown(function (e){                
                                                        $("#' . $target . '").prop("checked", true);
                                                        $("#' . $target . '").change();
                                                        return false;
                                                    });';
            }
        }
        // allowing deselect
        else {
            $returnStr .= 'var radioChecked' . $target . ';
                                                    
                                                    $("#' . $target . '").mousedown(function (e) {                                                        
                                                            if ($(this).prop("checked") == true) {
                                                                radioChecked' . $target . ' = true;                                                              
                                                            }  
                                                            else {
                                                                radioChecked' . $target . ' = false;
                                                            }
                                                            return true;
                                                     });
                                                        
                                                    $("#' . $target . '").click(function (e) {                                                         
                                                            if (radioChecked' . $target . ') {                                                                
                                                                $(this).prop("checked", false);
                                                             } else {
                                                                $(this).prop("checked", true);
                                                             }                                                             
                                                     });
                                                     
                                                   $("label[for=\'' . $target . '\']").mousedown(function (e){
                                                       
                                                            if ($(e.target).hasClass("uscic-radiobutton")) {                                                            
                                                                
                                                            }
                                                            else {
                                                                return true; // inline field OR individual dk/rf/na, so prevent click
                                                            }
                                                        
                                                        
                                                        radioChecked' . $target . ' = $(\'#' . $target . '\').prop(\'checked\');
                                                        if (radioChecked' . $target . ') {                                                                
                                                                $("#' . $target . '").prop("checked", false);
                                                             } else {
                                                                $("#' . $target . '").prop("checked", true);
                                                             }
                                                             $("#' . $target . '").change();
                                                             return false; // prevent bubbling to table cell level
                                                        });

                                                    ';

            // if in table
            if ($tablecell) {
                $returnStr .= '$("#cell' . $target . '").mousedown(function (e){                
                                                            radioChecked' . $target . ' = $(\'#' . $target . '\').prop(\'checked\');                                               
                                                                if (radioChecked' . $target . ') {                                                                
                                                                    $("#' . $target . '").prop("checked", false);
                                                                 } else {
                                                                    $("#' . $target . '").prop("checked", true);
                                                                 }
                                                                 $("#' . $target . '").change();
                                                                     return false;
                                                            });';
            }
        }
        $returnStr .= '});</script>';

        return minifyScript($returnStr);
    }

    function displayCheckBoxUnchecking($mainid, $invalidsub) {
        if ($invalidsub == "") {
            return "";
        }
        $returnStr = "";
        $uncheck = array();

        // determine incompatible sets
        $sets = explode(SEPARATOR_COMPARISON, $invalidsub);
        foreach ($sets as $set) {
            $setarray = explode(",", $set);

            // skip if for example only '1': no counterpart specified
            if (sizeof($setarray) != 2) {
                continue;
            } else {
                $first = $setarray[0];
                $second = $setarray[1];
                $uncheck[$first] = $second;
                $uncheck[$second] = $first;
            }
        }

        // process
        $returnStr .= "<script type='text/javascript'>
                        $(document).ready(function(){                        
                        ";
        foreach ($uncheck as $k => $v) {
            $karray = explode("-", $k); // in case of range
            if (sizeof($karray) == 1) {
                $karray[1] = $karray[0];
            }
            $varray = explode("-", $v); // in case of range
            if (sizeof($varray) == 1) {
                $varray[1] = $varray[0];
            }

            /*
             * function checkBoxes(obj) {if (obj.value != 5) { var checked = false; $('input[type=checkbox]').each(function () { if (this.value != 5 && this.checked) { checked = true; } });  if (checked == true) {$('input[type=checkbox][value="5"]').prop('checked', false); $('input[type=checkbox][value="5"]').change(); }  }  else { $('input[type=checkbox]').each(function () { if (this.value != 5) {this.checked = false; $(this).change();}});  }} 
             * 
             * 
             */
            $uncheckcode = "";
            for ($i = trim($varray[0]); $i <= trim($varray[1]); $i++) {
                $uncheckcode .= "$('#" . $mainid . "_" . $i . "').prop('checked', false);\r\n";
                $uncheckcode .= "$('#" . $mainid . "_" . $i . "').change();\r\n";
            }


            for ($i = trim($karray[0]); $i <= trim($karray[1]); $i++) {
                $returnStr .= "$('#" . $mainid . "_" . $i . "').change( function(e) {\r\n
                                    if ($(this).prop('checked') == true ";

                // handle range
                for ($j = trim($karray[0]); $j <= trim($karray[1]); $j++) {
                    if ($i != $j) {
                        $returnStr .= " && $('#" . $mainid . "_" . $j . "').prop('checked') == true";
                    }
                }
                $returnStr .= ") {\r\n " . $uncheckcode . " }
                                });\r\n";
            }

            // reverse
            $uncheckcode = "";
            for ($i = trim($karray[0]); $i <= trim($karray[1]); $i++) {
                $uncheckcode .= "$('#" . $mainid . "_" . $i . "').prop('checked', false);\r\n";
                $uncheckcode .= "$('#" . $mainid . "_" . $i . "').change();\r\n";
            }


            for ($i = trim($varray[0]); $i <= trim($varray[1]); $i++) {
                $returnStr .= "$('#" . $mainid . "_" . $i . "').change( function(e) {\r\n
                                    if ($(this).prop('checked') == true ";

                // handle range
                for ($j = trim($varray[0]); $j <= trim($varray[1]); $j++) {
                    if ($i != $j) {
                        $returnStr .= " && $('#" . $mainid . "_" . $j . "').prop('checked') == true";
                    }
                }

                $returnStr .= ") {\r\n " . $uncheckcode . " }
                                });\r\n";
            }
        }
        $returnStr .= "});
            </script>";
        //echo "<textarea rows=10 cols=200>" . $returnStr . "</textarea>";
        return minifyScript($returnStr);
    }

    /* INLINE SCRIPT FUNCTIONS */

    function displayAutoFocusScript($id) {
        return '<script type=text/javascript>' . minifyScript('$( document ).ready(function() {$(\'#' . $id . '\').click(function(event) { $(\'#' . $id . '\').trigger("dblclick"); event.preventDefault(); return false;});});') . '</script>';
    }

    function displayAutoSelectScript($id, $variable, $targetid, $inputtype, $value, $inlineanswertype) {
        if (!inArray($inputtype, array(ANSWER_TYPE_ENUMERATED, ANSWER_TYPE_SETOFENUMERATED))) {
            return "";
        }
        $type = "radio";
        if ($inputtype == ANSWER_TYPE_SETOFENUMERATED) {
            $type = "checkbox";
        }
        $returnStr = "";
        
        if (inArray($inlineanswertype, array(ANSWER_TYPE_OPEN, ANSWER_TYPE_STRING, ANSWER_TYPE_RANGE, ANSWER_TYPE_INTEGER, ANSWER_TYPE_DOUBLE))) {
            $returnStr .= "$('#" . $id . "').keyup(function(){if (this.value != '')";
        }
        else if (inArray($inlineanswertype, array(ANSWER_TYPE_DATE, ANSWER_TYPE_DATETIME, ANSWER_TYPE_TIME))) {
            $returnStr .= "$('#" . $id . "').on(\"dp.change\", function(e) {if (this.value != '')";
        }
        else if (inArray($inlineanswertype, array(ANSWER_TYPE_ENUMERATED, ANSWER_TYPE_SETOFENUMERATED))) {
            // TODO: $returnStr .= "$('#" . $id . "').change(function(){if (this.value != '')";
        }
        else if (inArray($inlineanswertype, array(ANSWER_TYPE_SLIDER))) {
            $returnStr .= "$('#" . $id . "').change(function(){if (this.value != '')";
        }
        else {
            $returnStr .= "$('#" . $id . "').change(function(){if (this.value != '')";
        }
        $returnStr .= '{$(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"][value="' . $value . '"]\').prop("checked", true);} else { $(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"][value="' . $value . '"]\').prop("checked", false);} $(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"]\').change(); });';

        $returnStr .= "$('input[name=\"" . $variable . "_dkrfna\"]').on('switchChange.bootstrapSwitch', function(event, state) {";
        $returnStr .= 'if ($("input[name=\'' . $variable . '_dkrfna\']:checked").val()) {                        
                        $(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"][value="' . $value . '"]\').prop("checked", true);                        
                            $(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"]\').change();
                    }
                    else {
                    
                        // currently selected, then deselect
                       if ($(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"][value="' . $value . '"]\').prop("checked") == true) {
                            $(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"][value="' . $value . '"]\').prop("checked", false);
                            $(\'input[type="' . $type . '"][id="' . $targetid . '_' . $value . '"]\').change();
                            // no change event --> if dk/rf/na was selected, then 
                       }
                    }                    
                    
                    });';

        return "<script type='text/javascript'>" . minifyScript($returnStr) . "</script>";
    }

    /* PARADATA HANDLING */

    function displayParadataScripts($paradata) {

        //only for surveys
        if ($_SESSION['SYSTEM_ENTRY'] != USCIC_SURVEY) {
            return;
        }

        // check config
        if ($paradata == false) {
            return;
        }

        // http://greensock.com/forums/topic/9059-cross-browser-to-detect-tab-or-window-is-active-so-animations-stay-in-sync-using-html5-visibility-api/
        if (!isRegisteredScript("js/TabWindowVisibilityManager.min.js")) {
            registerScript('js/TabWindowVisibilityManager.min.js');
            $returnStr = getScript('js/TabWindowVisibilityManager.min.js');
        }
        if (!isRegisteredScript("js/datetimepicker/moment-min.js")) {
            registerScript('js/datetimepicker/moment-min.js');
            $returnStr .= getScript("js/datetimepicker/moment-min.js");
        }
        //if (!isRegisteredScript("js/zip/lzstring.js")) {
        //    registerScript('js/zip/lzstring.js');
        //    $returnStr .= '<script type=text/javascript src="js/zip/lzstring.js"></script>';
        //}        
        $params = array(POST_PARAM_DEFAULT_LANGUAGE => getDefaultSurveyLanguage(), POST_PARAM_DEFAULT_MODE => getDefaultSurveyMode(), POST_PARAM_RGID => $this->engine->getRgid(), POST_PARAM_LANGUAGE => getSurveyLanguage(), POST_PARAM_MODE => getSurveyMode(), SESSION_PARAM_TEMPLATE => getSurveyTemplate(), POST_PARAM_VERSION => getSurveyVersion(), POST_PARAM_STATEID => $this->engine->getStateId(), POST_PARAM_DISPLAYED => urlencode(serialize($this->engine->getDisplayNumbers())), POST_PARAM_PRIMKEY => $this->engine->getPrimaryKey(), POST_PARAM_SUID => $this->engine->getSuid());
        $r = setSessionsParamString($params);
        $returnStr .= '<script type="text/javascript">';
        $str = '
            // bind listeners
            $(document).ready(function(){  ';
        if (Config::logParadataMouseMovement()) {

            $str .= '$("html").mousemove(function(event) {
                    window.mousex = event.pageX;
                    window.mousey = event.pageY;
                });';
        }
        $str .= '
                $("html").click(function(event){
                    var name = "";
                    if (event.target.name) {
                        name = event.target.name;
                    }
                    logParadata("MC:"+event.pageX+":"+event.pageY+":"+event.which+":"+name);
                });
                $("html").keyup(function(event){
                    var name = "";
                    if (event.target.name) {
                        name = event.target.name;
                    }
                    logParadata("KE:"+event.keyCode+":"+name);
                });
            });';

        if (Config::logParadataMouseMovement()) {

            $str .= '
                window.mousex = 0;
                window.mousey = 0;
                window.lastx = window.mousex;
                window.lasty = window.mousey;
                function mousemov() {
                    if (window.lastx != window.mousex || window.lasty != window.mousey) {
                        logParadata("MM:"+window.mousex+":"+window.mousey);
                        window.lastx = window.mousex;
                        window.lasty = window.mousey;
                    }

                }
                window.onload=setInterval(mousemov, ' . Config::logParadataMouseMovementInterval() . '); // capture mouse movement every 5 seconds
                ';
        }

        $str .= '
            // compress function
            function compress(string) {
                return string;
                //return LZString.compressToUTF16(string);
            }
            
            // function to log paradata
            function logParadata(para) {
                $("#pid").val($("#pid").val() + "||" + compress(para + "=" + moment()));
                //alert($("#pid").val().length);
                // if length exceeds limit
                //if ($("#pid").val().length > 1024) {
                    //alert($("#pid").val().length);
                    //sendParadata($("#pid").val()); // send to server
                    //$("#pid").val(""); // reset
                //}
            }

            // function to send paradata to the server
            function sendParadata(paradata) {
                $.ajax({
                    type: "POST",
                    url: "ajax/index.php",
                    data: {ajaxr: "' . $r . '", p: "storeparadata", ' . POST_PARAM_PARADATA . ': paradata},
                    async: true
                });
            }
                 
            var firedin = false;                
            var firedout = false; 
            $(window).TabWindowVisibilityManager({
                onFocusCallback: function(){
                        if (firedin == false) {
                            //document.title="visible";
                            logParadata("FI:");	                
                        }
                        firedin = true;
                        firedout = false;
                },
                onBlurCallback: function(){
                    if (firedout == false) {
                        //document.title="invisible";
                        logParadata("FO:");
                    }
                    firedout = true;    
                    firedin = false;
                }
            });';
        $returnStr .= minifyScript($str);
        $returnStr .= '</script>';
        return $returnStr;
    }

    /* COOKIE HANDLING */

    function displayCookieScripts() {
        if (!isRegisteredScript("js/cookie/jquery.cookie.js")) {
            registerScript('js/cookie/jquery.cookie.js');
            $returnStr = getScript("js/cookie/jquery.cookie.js");
        }
        if (!isRegisteredScript("js/cookie/uscic.cookie.js")) {
            registerScript('js/cookie/uscic.cookie.js');
            $returnStr .= getScript("js/cookie/uscic.cookie.js");
        }
        return $returnStr;
    }

    /*  TABLE MOBILE HANDLING */

    function displayTableSaw() {
        $returnStr = "";
        if (!isRegisteredScript("js/tablesaw/stackonly/tablesaw.stackonly.nubis.min.js")) {
            $returnStr .= getScript('js/tablesaw/stackonly/tablesaw.stackonly.nubis.min.js');
        }
        if (!isRegisteredScript("js/tablesaw/tablesaw-init.min.js")) {
            $returnStr .= getScript('js/tablesaw/tablesaw-init.min.js');
        }
        if (!isRegisteredScript("js/tablesaw/stackonly/tablesaw.stackonly.min.css")) {
            $returnStr .= getCSS("js/tablesaw/stackonly/tablesaw.stackonly.min.css");
        }
        return $returnStr;
    }

    /* DRAGGABLE */

    function displayDraggable() {
        $returnStr = "<script type='text/javascript'>(function($) {
            $.fn.drags = function(opt) {

                opt = $.extend({handle:\"\",cursor:\"move\"}, opt);

                if(opt.handle === \"\") {
                    var \$el = this;
                } else {
                    var \$el = this.find(opt.handle);
                }

                return \$el.css('cursor', opt.cursor).on(\"mousedown\", function(e) {
                    if(opt.handle === \"\") {
                        var \$drag = $(this).addClass('draggable');
                    } else {
                        var \$drag = $(this).addClass('active-handle').parent().addClass('draggable');
                    }
                    var z_idx = \$drag.css('z-index'),
                        drg_h = \$drag.outerHeight(),
                        drg_w = \$drag.outerWidth(),
                        pos_y = \$drag.offset().top + drg_h - e.pageY,
                        pos_x = \$drag.offset().left + drg_w - e.pageX;
                    \$drag.css('z-index', 1000).parents().on(\"mousemove\", function(e) {
                        $('.draggable').offset({
                            top:e.pageY + pos_y - drg_h,
                            left:e.pageX + pos_x - drg_w
                        }).on(\"mouseup\", function() {
                            $(this).removeClass('draggable').css('z-index', z_idx);
                        });
                    });
                    e.preventDefault(); // disable selection
                }).on(\"mouseup\", function() {
                    if(opt.handle === \"\") {
                        $(this).removeClass('draggable');
                    } else {
                        $(this).removeClass('active-handle').parent().removeClass('draggable');
                    }
                });

            }
        })(jQuery);</script>";
        return $returnStr;
    }

    /* AUTO COMPLETE */

    function displayAutoCompleteScripts($delimiters = array()) {
        if (!isRegisteredScript("js/jquery-textcomplete/jquery.textcomplete.min.css")) {
            registerScript('js/jquery-textcomplete/jquery.textcomplete.min.css');
            $str .= getCSS("js/jquery-textcomplete/jquery.textcomplete.min.css");
        }
        if (!isRegisteredScript("js/jquery-textcomplete/jquery.textcomplete-min.js")) {
            registerScript('js/jquery-textcomplete/jquery.textcomplete-min.js');
            $returnStr = getScript("js/jquery-textcomplete/jquery.textcomplete-min.js");
        }

        $returnStr .= "<script type=text/javascript>" . minifyScript("
                        var delimiter = '';
                        $(document).ready(function() {
                            var variables = [];
                            $.getJSON('index.php?p=sysadmin.autocomplete&" . POST_PARAM_SMS_AJAX . "=" . SMS_AJAX_CALL . "', function( data ) {
                                $.each( data, function( key, val ) {
                                  variables.push(val);
                                });
                            });
                            
                            // record delimiter
                            $('.autocomplete').keypress(function(event) {                             
                                if (event.which == 94 || event.which == 126 || event.which == 42 || event.which == 118) {
                                    delimiter = event.which;
                                }    
                                return true;
                            });
                            
                            $('.autocomplete').textcomplete([
                                { 
                                    match: /[\^~\*`](\w*)$/,
                                    search: function (term, callback) {                                                                               
                                        term = term.toLowerCase();
                                        callback($.map(variables, function (word) {
                                            return word.toLowerCase().indexOf(term) === 0 ? word : null;
                                        }));                                        
                                    },
                                    index: 1,
                                    replace: function (element) {                                         
                                        var delim = String.fromCharCode(delimiter);
                                        return delim + element;
                                    },
                                    cache: false,
                                    maxCount: 20
                                }
                            ]);
                            
                            // record delimiter
                            $('.autocompletebasic').keypress(function(event) {  
                                if (event.which == 94 || event.which == 42 || event.which == 118) {
                                    delimiter = event.which;
                                }    
                                return true;
                            });
                            
                            $('.autocompletebasic').textcomplete([
                                { 
                                    match: /[\^\*`](\w*)$/,
                                    search: function (term, callback) {                                                                               
                                        term = term.toLowerCase();
                                        callback($.map(variables, function (word) {
                                            return word.toLowerCase().indexOf(term) === 0 ? word : null;
                                        }));                                        
                                    },
                                    index: 1,
                                    replace: function (element) {  
                                        var delim = String.fromCharCode(delimiter);
                                        return delim + element;
                                    },
                                    cache: false,
                                    maxCount: 20
                                }
                            ]);
                        });") . "    
                    </script>";
        return $returnStr;
    }

    /* ZIP */

    function displayZipScripts() {
        return;
        if (!isRegisteredScript("js/zip/lzstring.min.js")) {
            registerScript('js/zip/lzstring.min.js');
            $returnStr = getScript("js/zip/lzstring.min.js");
        }
        $returnStr .= '<script type="text/javascript">' . minifyScript('$(document).ready(function(){
                           unzip();
                        }); 
            
            function unzip() {                            
                $("*[data-zip]").each(function() {
                    var v = $(this).val();
                    var out = LZString.decompressFromBase64(v);
                    $(this).val(out);
                    document.getElementById($(this).attr("id")).value=out;
                });
            }            

            function zip() {                            
                $("*[data-zip]").each(function() {
                    var v = $(this).val();
                    var out = LZString.compressToBase64(v);
                    $(this).val(out);
                    document.getElementById($(this).attr("id")).value=out;
                });
            }') . '</script>';
        return $returnStr;
    }

    /* SESSION TIMEOUT */

    function displayTimeoutScripts() {

        global $survey, $engine;
        $returnStr = "";
        if (!isRegisteredScript("js/session/timeout-min.js")) {
            registerScript('js/session/timeout-min.js');
            $returnStr .= getScript("js/session/timeout-min.js");
        }

        $logouturl = $engine->replaceFills($survey->getTimeoutLogoutURL());
        if ($logouturl == "") {
            $logouturl = Config::sessionLogoutURL();
        }
        $logout = "";
        if ($logouturl != "") {
            $logout = "logoutUrl: '" . $logouturl . "',";
        }
        $aliveurl = Config::sessionAliveURL();
        $alive = "";
        if ($aliveurl != "") {
            $alive = "keepAliveUrl: '" . $aliveurl . "',";
        }
        $redirurl = $engine->replaceFills($survey->getTimeoutRedirectURL());
        if ($redirurl == "") {
            $redirurl = Config::sessionRedirectURL();
        }
        $redir = "";
        $length = $engine->replaceFills($survey->getTimeoutLength());
        if ($length == "") {
            $length = Config::sessionTimeout();
        }
        if ($redirurl != "") {
            $redir = "redirUrl: '" . $redirurl . "',";
            $redirafter = "redirAfter: " . $length * 1000;
        }
        $warnafter = ($length * 1000) * Config::sessionExpiredWarnPoint(); // warn after 60% of the time has passed
        $timeleft = ($length - ($length * Config::sessionExpiredWarnPoint())) / 60; // in minutes
        $message = Language::sessionExpiredMessage(round($timeleft));
        $alivebutton = $engine->replaceFills($survey->getTimeoutAliveButton());
        if ($alivebutton == "") {
            $alivebutton = Language::sessionExpiredKeepAliveButton();
        }
        $logoutbutton = $engine->replaceFills($survey->getTimeoutLogoutButton());
        if ($logoutbutton == "") {
            $logoutbutton = Language::sessionExpiredLogoutButton();
        }
        $title = $engine->replaceFills($survey->getTimeoutTitle());
        if ($title == "") {
            $title = Language::sessionExpiredTitle();
        }
        $ping = Config::sessionExpiredPingInterval();

        $returnStr .= "<script type='text/javascript'>" . minifyScript("
            $(document).ready(function(){
                $.sessionTimeout({
                    title: '$title',
                    keepAliveButton: '$alivebutton',
                    keepAliveInterval: $ping,    
                    logoutButton: '$logoutbutton',
                    message: '$message',
                    $alive
                    $logout
                    $redir                    
                    warnAfter: $warnafter,
                    $redirafter
                });
              });") . "  
            </script>";

        return $returnStr;
    }

    /* INPUT MASKING SCRIPT FUNCTIONS */

    function displayMaskingScripts() {
        if (!isRegisteredScript("js/inputmasking/inputmask-min.js")) {
            registerScript('js/inputmasking/inputmask-min.js');
            $returnStr = getScript("js/inputmasking/inputmask-min.js");
        }
        if (!isRegisteredScript("js/inputmasking/numeric-min.js")) {
            registerScript('js/inputmasking/numeric-min.js');
            $returnStr .= getScript("js/inputmasking/numeric-min.js");
        }
        if (!isRegisteredScript("js/inputmasking/date-min.js")) {
            registerScript('js/inputmasking/date-min.js');
            $returnStr .= getScript("js/inputmasking/date-min.js");
        }

        if (!isRegisteredScript("js/inputmasking/regex-min.js")) {
            registerScript('js/inputmasking/regex-min.js');
            $returnStr .= getScript("js/inputmasking/regex-min.js");
        }
        if (!isRegisteredScript("js/inputmasking/uscic-min.js")) {
            registerScript('js/inputmasking/uscic-min.js');
            $returnStr .= getScript("js/inputmasking/uscic-min.js");
        }
        if (!isRegisteredScript("js/inputmasking/web-min.js")) {
            registerScript('js/inputmasking/web.js');
            $returnStr .= getScript("js/inputmasking/web-min.js");
        }

        /* NOTE: DISABLED FOR NOW FOR ANDROID Chrome and Firefox UNTIL RELEASE 38 COMES OUT. THEN ENABLE FOR RELEASE 38 IF WORKING THERE */
        // (user agent example for android: Mozilla/5.0 (Linux; Android 4.4.4; Nexus 5 Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.131 Mobile Safari/537.36 =>)
        $returnStr .= minifyScript('<script type="text/javascript">$(document).ready(function(){
                          if(inputMaskingSupported() === true){
                            $(":input").inputmask();                 
                          }  
                        }); 
                        
function unmaskForm() {
    $("*[data-inputmask-unmask]").each(function() {
        var v = $(this).val();
        document.getElementById($(this).attr("id")).value = v;
    });
}

function inputMaskingSupported() {
    var ua = navigator.userAgent;
    //alert(ua);
    //var androidchrome = ua.match(new RegExp("android.*chrome.*", "i")) !== null;
    var androidchrome = ua.match(new RegExp("android.*chrome.*", "i")) !== null;    
    if (androidchrome) {    
        var bs = ua.match(/Chrome\/(\d+)/);        
        if (bs[1] < 38) { 
            return false;
        }
    }
    var androidfirefox = ua.match(new RegExp("android.*firefox.*", "i")) !== null;
    if (androidfirefox) {    
        return false;
    }
    var kindle = /Kindle/i.test(ua) || /Silk/i.test(ua) || /KFTT/i.test(ua) || /KFOT/i.test(ua) || /KFJWA/i.test(ua) || /KFJWI/i.test(ua) || /KFSOWI/i.test(ua) || /KFTHWA/i.test(ua) || /KFTHWI/i.test(ua) || /KFAPWA/i.test(ua) || /KFAPWI/i.test(ua);
    if(kindle) {
        var match = ua.match(/\bSilk\/([0-9]+)\b/);
        if (match[1] < 47) { // works silk 47 and higher 
            return false;
        }
    }
    return true;
}
                        </script>
                        ');
        return $returnStr;
    }

    /* DATA TABLES SCRIPT FUNCTIONS */
    
    function displayDataTablesScripts($extensions = array(), $css = true) {

        $returnStr = "";
        if (!isRegisteredScript("js/datatables/datatables.js")) {
            registerScript('js/datatables/datatables.js');
            $returnStr .= getScript("js/datatables/datatables.js");
        }

        if (!isRegisteredScript("js/datatables/extensions/date_sorting.js")) {
            registerScript('js/datatables/extensions/date_sorting.js');
            $returnStr .= getScript("js/datatables/extensions/date_sorting.js");
        }

        if (!isRegisteredScript("js/datetimepicker/moment-min.js")) {
            registerScript('js/datetimepicker/moment-min.js');
            $returnStr .= getScript("js/datetimepicker/moment-min.js");
        }

        if ($css && !isRegisteredScript("js/datatables/datatables.css")) {
            registerScript('js/datatables/datatables.css');
            $returnStr .= getCSS("js/datatables/datatables.css");
        }
        foreach ($extensions as $ext) {
            if (!isRegisteredScript("js/datatables/extensions/' . $ext . '.js")) {
                registerScript('js/datatables/extensions/' . $ext . '.js');
                $returnStr .= getScript("js/datatables/extensions/" . $ext . ".js");
            }
            if (strtoupper($ext) != strtoupper('rowreorder')) { // reorder has no associated css
                if ($css && !isRegisteredScript("js/datatables/extensions/' . $ext . '.css")) {
                    registerScript('js/datatables/extensions/' . $ext . '.css');
                    $returnStr .= getCSS("js/datatables/extensions/" . $ext . ".css");
                }
            } else {
                if (!isRegisteredScript("js/jqueryui/sortable.js")) {
                    registerScript('js/jqueryui/sortable.js');
                    $returnStr .= getScript("js/jqueryui/sortable.js");
                }
            }
        }

        /* https://datatables.net/forums/discussion/10437/fixedheader-column-headers-not-changing-on-window-resize/p1 */
        /* resize of header on window resize/empty/error */
        $returnStr .= '<script type="text/javascript">' . minifyScript('            
                        function resizeDataTables() {
                        $(\'div.dataTables_scrollBody table.dataTable\').each( function( index ) {
                        $(this).dataTable().fnAdjustColumnSizing();
                        });
                        }

                        $(window).on(\'resize\', function () {
                        resizeDataTables();
                        } );') . '
                        </script>';
        return $returnStr;
    }
    
    function displayDataTablesCSS($extensions = array()) {

        $returnStr = "";
        if (!isRegisteredScript("js/datatables/datatables.css")) {
            registerScript('js/datatables/datatables.css');
            $returnStr .= getCSS("js/datatables/datatables.css");
        }
        foreach ($extensions as $ext) {
            if (strtoupper($ext) != strtoupper('rowreorder')) { // reorder has no associated css
                if (!isRegisteredScript("js/datatables/extensions/' . $ext . '.css")) {
                    registerScript('js/datatables/extensions/' . $ext . '.css');
                    $returnStr .= getCSS("js/datatables/extensions/" . $ext . ".css");
                }
            }
        }
        return $returnStr;
    }

    /* KEYBOARD BINDING FUNCTIONS */

    function displayKeyBoardBinding($engine, $queryobject, $back) {
        $returnStr = "";
        if (!isRegisteredScript("js/hotkeys.js")) {
            registerScript('js/hotkeys.js');
            $returnStr = getScript("js/hotkeys.js");
        }

        $returnStr .= '<script type="text/javascript">';

        if ($back == true) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingBack()) . "', function(event){ $('#uscic-backbutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        if ($queryobject->getShowNextButton() == BUTTON_YES) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingNext()) . "', function(event){ $('#uscic-nextbutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        if ($queryobject->getShowDKButton() == BUTTON_YES) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingDK()) . "', function(){ $('#uscic-dkbutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        if ($queryobject->getShowRFButton() == BUTTON_YES) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingRF()) . "', function(){ $('#uscic-rfbutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        if ($queryobject->getShowNAButton() == BUTTON_YES) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingNA()) . "', function(){ $('#uscic-nabutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        if ($queryobject->getShowUpdateButton() == BUTTON_YES) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingUpdate()) . "', function(){ $('#uscic-updatebutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        if ($queryobject->getShowRemarkButton() == BUTTON_YES) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingRemark()) . "', function(){ $('#uscic-remarkbutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        if ($queryobject->getShowCloseButton() == BUTTON_YES) {
            $returnStr .= "$(document).bind('keypress', '" . $engine->replaceFills($queryobject->getKeyboardBindingClose()) . "', function(){ $('#uscic-closebutton').click(); event.preventDefault(); event.stopPropagation(); return false;} );";
        }

        $returnStr .= "</script>";
        return $returnStr;
    }

    function displayKeyBoardBindingDropdown($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", KEYBOARD_BINDING_YES => "", KEYBOARD_BINDING_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[KEYBOARD_BINDING_YES] . " value=" . KEYBOARD_BINDING_YES . ">" . Language::optionsKeyboardBindingYes() . "</option>";
        $returnStr .= "<option " . $selected[KEYBOARD_BINDING_NO] . " value=" . KEYBOARD_BINDING_NO . ">" . Language::optionsKeyboardBindingNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayTimeout($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' id='" . $name . "' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", TIMEOUT_YES => "", TIMEOUT_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[TIMEOUT_YES] . " value=" . TIMEOUT_YES . ">" . Language::optionsTimeoutYes() . "</option>";
        $returnStr .= "<option " . $selected[TIMEOUT_NO] . " value=" . TIMEOUT_NO . ">" . Language::optionsTimeoutNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayIndividualDKRFNA($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", INDIVIDUAL_DKRFNA_YES => "", INDIVIDUAL_DKRFNA_NO => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[INDIVIDUAL_DKRFNA_YES] . " value=" . INDIVIDUAL_DKRFNA_YES . ">" . Language::optionsIndividualDKRFNAYes() . "</option>";
        $returnStr .= "<option " . $selected[INDIVIDUAL_DKRFNA_NO] . " value=" . INDIVIDUAL_DKRFNA_NO . ">" . Language::optionsIndividualDKRFNANo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayAccessAfterCompletionReturn($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", AFTER_COMPLETION_NO_REENTRY => "", AFTER_COMPLETION_FIRST_SCREEN => "", AFTER_COMPLETION_LAST_SCREEN => "", AFTER_COMPLETION_LAST_SCREEN_REDO => "", AFTER_COMPLETION_FROM_START);
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }

        $returnStr .= "<option " . $selected[AFTER_COMPLETION_NO_REENTRY] . " value=" . AFTER_COMPLETION_NO_REENTRY . ">" . Language::optionsAccessReTurnAfterCompletionNo() . "</option>";
        $returnStr .= "<option " . $selected[AFTER_COMPLETION_FROM_START] . " value=" . AFTER_COMPLETION_FROM_START . ">" . Language::optionsAccessReTurnAfterCompletionFromStart() . "</option>";
        $returnStr .= "<option " . $selected[AFTER_COMPLETION_FIRST_SCREEN] . " value=" . AFTER_COMPLETION_FIRST_SCREEN . ">" . Language::optionsAccessReTurnAfterCompletionFirst() . "</option>";
        $returnStr .= "<option " . $selected[AFTER_COMPLETION_LAST_SCREEN] . " value=" . AFTER_COMPLETION_LAST_SCREEN . ">" . Language::optionsAccessReTurnAfterCompletionLast() . "</option>";
        $returnStr .= "<option " . $selected[AFTER_COMPLETION_LAST_SCREEN_REDO] . " value=" . AFTER_COMPLETION_LAST_SCREEN_REDO . ">" . Language::optionsAccessReTurnAfterCompletionLastRedo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayAccessAfterCompletionPreload($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", PRELOAD_REDO_NO => "", PRELOAD_REDO_YES => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[PRELOAD_REDO_YES] . " value=" . PRELOAD_REDO_YES . ">" . Language::optionsPreloadRedoYes() . "</option>";
        $returnStr .= "<option " . $selected[PRELOAD_REDO_NO] . " value=" . PRELOAD_REDO_NO . ">" . Language::optionsPreloadRedoNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayAccessReentryPreload($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", PRELOAD_REDO_NO => "", PRELOAD_REDO_YES => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[PRELOAD_REDO_YES] . " value=" . PRELOAD_REDO_YES . ">" . Language::optionsPreloadRedoYes() . "</option>";
        $returnStr .= "<option " . $selected[PRELOAD_REDO_NO] . " value=" . PRELOAD_REDO_NO . ">" . Language::optionsPreloadRedoNo() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayUsers($users, $key, $name = 'uridsel', $none = false) {
        $returnStr = "<select style='width:300px' class='form-control selectpicker show-tick' name='" . $name . "'>";
        if ($none) {
            $returnStr .= "<option value=-1>" . Language::labelNone() . "</option>";
        }

        foreach ($users as $user) {
            $selected = '';
            if ($key == $user->getUrid()) {
                $selected = "selected";
            }
            $returnStr .= "<option " . $selected . " value=" . $user->getUrid() . ">" . $user->getName() . "</option>";
        }
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayUsersUpdate($users, $name = 'uridsel') {
        $returnStr = "<select style='width:300px' class='form-control selectpicker show-tick' multiple name='" . $name . "[]'>";
        $returnStr .= "<option selected value=-1>" . Language::labelAll() . "</option>";
        foreach ($users as $user) {
            $returnStr .= "<option value=" . $user->getUrid() . ">" . $user->getName() . "</option>";
        }
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayAccessReentryAction($name, $current, $generic = false, $type = -1) {
        $returnStr = "<select class='selectpicker show-tick' name='" . $name . "'>";
        $selected = array(SETTING_FOLLOW_GENERIC => "", SETTING_FOLLOW_TYPE => "", REENTRY_FIRST_SCREEN => "", REENTRY_SAME_SCREEN => "", REENTRY_SAME_SCREEN_REDO_ACTION => "", REENTRY_NEXT_SCREEN => "", REENTRY_FROM_START => "", REENTRY_NO_REENTRY => "");
        $selected[$current] = "selected";
        if ($type > 0) {
            $returnStr .= "<option " . $selected[SETTING_FOLLOW_TYPE] . " value=" . SETTING_FOLLOW_TYPE . ">" . Language::optionsFollowType() . "</option>";
        } else {
            if ($generic) {
                $returnStr .= "<option " . $selected[SETTING_FOLLOW_GENERIC] . " value=" . SETTING_FOLLOW_GENERIC . ">" . Language::optionsFollowGeneric() . "</option>";
            }
        }
        $returnStr .= "<option " . $selected[REENTRY_NO_REENTRY] . " value=" . REENTRY_NO_REENTRY . ">" . Language::optionsAccessReentryActionNotAllowed() . "</option>";
        $returnStr .= "<option " . $selected[REENTRY_FROM_START] . " value=" . REENTRY_FROM_START . ">" . Language::optionsAccessReentryActionStart() . "</option>";
        $returnStr .= "<option " . $selected[REENTRY_FIRST_SCREEN] . " value=" . REENTRY_FIRST_SCREEN . ">" . Language::optionsAccessReentryActionFirst() . "</option>";
        $returnStr .= "<option " . $selected[REENTRY_SAME_SCREEN] . " value=" . REENTRY_SAME_SCREEN . ">" . Language::optionsAccessReentryActionSame() . "</option>";
        $returnStr .= "<option " . $selected[REENTRY_SAME_SCREEN_REDO_ACTION] . " value=" . REENTRY_SAME_SCREEN_REDO_ACTION . ">" . Language::optionsAccessReentryActionSameRedo() . "</option>";
        $returnStr .= "<option " . $selected[REENTRY_NEXT_SCREEN] . " value=" . REENTRY_NEXT_SCREEN . ">" . Language::optionsAccessReentryActionNext() . "</option>";
        $returnStr .= "</select>";
        return $returnStr;
    }

    function displayButtonBinding($name, $current = '') {
        return "<input type=text class='form-control autocompletebasic' name='" . $name . "' value='" . $this->displayTextSettingValue(convertHTLMEntities($current, ENT_QUOTES)) . "'>";
    }

    function displayOnSubmit($name, $current = '') {
        return "<input type=text class='form-control autocompletebasic' name='" . $name . "' value='" . $this->displayTextSettingValue(convertHTLMEntities($current, ENT_QUOTES)) . "'>";
    }

    function disableForm() {
        $returnStr = "<script type='text/javascript'>" . minifyScript("
                            function checkForm() {
                                $('#sectiondiv :input').attr('disabled', true);
                                $('input[type=\"submit\"]').attr('disabled',true);                                
                             }  
                             $(document).ready(function(){
                                checkForm();
                             });") . "
                          </script>";
        return $returnStr;
    }

    function displayButtonToggling() {
        $returnStr = minifyScript("<script type='text/javascript'>
                            function disableButtons() {
                                $('button').attr('disabled', 'disabled');
                            }
                            function enableButtons() {
                                $('button').removeAttr('disabled');
                            }
                          </script>");
        return $returnStr;
    }

    function enableForm() {
        $returnStr = "<script type='text/javascript'>
                          function checkForm() {
                                                               
                           } 
                      </script>";
        return $returnStr;
    }

    function checkForm() {
        $returnStr = "";
        $active = checkUserAccess();
        if ($active) {
            $returnStr .= $this->disableForm();
        } else {
            $returnStr = $this->enableForm();
        }
        return $returnStr;
    }

    function bindAjax() {
        return "";
        $returnStr = "<script type='text/javascript'>
                        $(document).ready(function(){
                            
                            // http://stackoverflow.com/questions/1964839/jquery-please-wait-loading-animation
                            \$body = $('body');
                            $(document).on({
                                ajaxStart: function() { \$body.addClass('loading');  },
                                ajaxStop: function() { \$body.removeClass('loading'); }    
                            });
                            
                            $('#wrap').on('click', '#mainnavbar a',function(event){                            
                                  if (event.which != 1) {                                  
                                    return;
                                  }
                                  
                                  if ($(this).attr('target') == '_blank' || $(this).attr('" . POST_PARAM_NOAJAX . "') == " . NOAJAX . ") {
                                    return;
                                  }
                                  
                                  event.preventDefault();
                                  var url= this.href;
                                  if (url) {
                                    url = url + \"&" . POST_PARAM_AJAX_LOAD . "=" . AJAX_LOAD . "\";
                                  }
                                  else {
                                    url = 'index.php' + \"?" . POST_PARAM_AJAX_LOAD . "=" . AJAX_LOAD . "\";
                                  }
                                  $.get(url,{},function(response){ 
                                     $('#content').html($(response).contents());
                                     $('[data-hover=\"dropdown\"]').dropdownHover();  
                                  });
                                  return false;
                            });
                            
                            $('#wrap').on('click', '#content a[href]',function(event){
                                  
                                  if (event.which != 1) {                                  
                                    return;
                                  }   
                                  
                                  // http://stackoverflow.com/questions/1318076/jquery-hasattr-checking-to-see-if-there-is-an-attribute-on-an-element
                                  var oc = $(this).attr('onclick');
                                  if (typeof oc !== 'undefined' && oc !== false) {                                    
                                    return;
                                  }  
                                  event.preventDefault();
                                  var url= this.href;
                                  if (url) {
                                    url = url + \"&" . POST_PARAM_AJAX_LOAD . "=" . AJAX_LOAD . "\";
                                  }
                                  else {
                                    url = 'index.php' + \"?" . POST_PARAM_AJAX_LOAD . "=" . AJAX_LOAD . "\";
                                  }
                                  $.get(url,{},function(response){ 
                                     $('#content').html($(response).contents());
                                     $('[data-hover=\"dropdown\"]').dropdownHover();  
                                  });
                                  return false;
                            });
                            
                            $('#wrap').on('submit', '#content form' ,function(event){
                                  //  return;
                                  if ($(this).attr('target') == '_blank' || $(this).attr('" . POST_PARAM_NOAJAX . "') == " . NOAJAX . ") {
                                      return;
                                  }   
                                  event.preventDefault();
                                  
                                  var values = $(this).serialize();
                                  values += '&" . POST_PARAM_AJAX_LOAD . "=" . AJAX_LOAD . "';
                                  //alert(values);
                                  // Send the data using post
                                  var posting = $.post( $(this).attr('action'), values );
                                  
                                  posting.done(function( data ) {       
                                    $('#content').html( $( data ).html());
                                    $('[data-hover=\"dropdown\"]').dropdownHover();  
                                  }); 
                                  return false;
                                });	
                          });</script>
                        ";
        return $returnStr;
    }

    function displayRoutingErrorModal($section, $text) {
        $returnStr = "<script type='text/javascript' src='js/jqueryui/jquery-ui.js'></script>";
        $returnStr .= "<script type='text/javascript'>" . minifyScript("
                        $(document).ready(function() {
                            $('#errorsModal').drags({ 
                                handle: '.modal-header' 
                            });
                        });") . "   
                        </script>";
        $returnStr .= '<div class="modal fade" id="errorsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">' . Language::labelErrorsIn() . '\'' . $section->getName() . '\'</h4>
      </div>
      <div class="modal-body">';
        $returnStr .= $text;
        $returnStr .= '</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
';
        return $returnStr;
    }

    function showInputBox($name, $value, $edit) {

        if ($edit) {

            return '<input type="text" name="' . $name . '" class="form-control" value="' . convertHTLMEntities($value, ENT_QUOTES) . '" />';
        } else {

            return $value;
        }
    }

    function showActionBar($title, $input, $buttontext, $sessionparams, $javascript = '') {

        $content .= '<nav class="navbar navbar-default" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand">' . $title . '</a>
   </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">';


        $content .= '<form method="post" class="navbar-form navbar-left">';
        $content .= $sessionparams;
        //$content .= setSessionParamsPost(array('page' => 'supervisor.interviewer.respondent.reassign', 'primkey' => $respondentOrHousehold->getPrimkey()));
//          <form class="navbar-form navbar-left" role="search">
        $content .= '<div class="form-group">';
        //<input type="text" class="form-control" placeholder="Search">
        //$content .= '<select class="form-control"><option value=1>test</option></select>';

        $content .= $input; //$this->displayUsers($users, $respondentOrHousehold->getUrid());
        $content .= '</div>';
        $content .= '<button type="submit" class="btn btn-default"' . $javascript . '>' . $buttontext . '</button>';
        $content .= '</form>
        </div>
      </div>
</nav>';



        return $content;
    }

    function displayInterviewerDropDown($page, $urid = 1) {
        $returnStr = '';
        $returnStr .= '<div style="width:250px"><form method=post>';
        $returnStr .= setSessionParamsPost(array('page' => $page));

        $returnStr .= '<div class="input-group">';

        $returnStr .= $this->displayInterviewerSelect($urid);

        $returnStr .= '<span class="input-group-btn">';
        $returnStr .= '<input type=submit class="form-control" value="Go">';
        $returnStr .= '</span></div>';
        $returnStr .= '</form></div>';

        return $returnStr;
    }

    function displaySupervisorSelect($urid) {
        $returnStr = '<select name=selurid class="form-control" style="width:200px">';
        $selected = '';
        if (0 == $urid) {
            $selected = ' SELECTED';
        }
        $returnStr .= '<option value="' . 0 . '"' . $selected . '>' . 'Select supervisors' . '</option>';
        $users = new Users();
        $users = $users->getUsersByType(USER_SUPERVISOR);
        foreach ($users as $user) {
            $selected = '';
            if ($user->getUrid() == $urid) {
                $selected = ' SELECTED';
            }
            $returnStr .= '<option value="' . $user->getUrid() . '"' . $selected . '>' . $user->getUsername() . ': ' . $user->getName() . '</option>';
        }
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayInterviewerSelect($urid) {
        $returnStr = '<select name=selurid class="form-control" style="width:200px">';
        $selected = '';
        if (0 == $urid) {
            $selected = ' SELECTED';
        }
        $returnStr .= '<option value="' . 0 . '"' . $selected . '>' . 'Select interviewer' . '</option>';
        $users = new Users();
        $user = new User($_SESSION['URID']);
        if ($user->getUserType() == USER_SUPERVISOR) {
            $users = $users->getUsersBySupervisor($user->getUrid());
        } elseif ($user->getUserType() == USER_SYSADMIN || $user->getUserType() == USER_RESEARCHER) {
            $users = $users->getUsersByType(USER_INTERVIEWER);
        } else {
            $users = array();
        }
        foreach ($users as $user) {
            $selected = '';
            if ($user->getUrid() == $urid) {
                $selected = ' SELECTED';
            }
            $returnStr .= '<option value="' . $user->getUrid() . '"' . $selected . '>' . $user->getUsername() . ': ' . $user->getName() . '</option>';
        }
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayRespondentOrHousehold($rorh) {
        $returnStr = '<select name=rorh class="form-control" style="width:200px">';
        $selected = array('', '', '');
        $selected[$rorh] = ' SELECTED';
        $returnStr .= '<option value="1"' . $selected[1] . '>' . 'Household level' . '</option>';
        $returnStr .= '<option value="2"' . $selected[2] . '>' . 'Respondent level' . '</option>';

        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayContactType($ceid) {
        $returnStr = '<select name=ceid class="form-control" style="width:200px">';
        $selected = array('', '', '');
        $selected[$ceid] = ' SELECTED';
        $returnStr .= '<option value="1"' . $selected[1] . '>' . 'Interviewer codes' . '</option>';
        $returnStr .= '<option value="2"' . $selected[2] . '>' . 'Final codes' . '</option>';
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayPsus($puid, $showAll = false) {
        $returnStr = '<select class="form-control" name="puid">';
        $psus = new Psus();
        $psus = $psus->getPsus();
        $selected = array_fill(0, 500, '');
        $selected[$puid] = ' SELECTED';

        if ($showAll) {
            $returnStr .= '<option value="0"' . $selected[0] . '>' . 'All psus' . '</option>';
        }
        foreach ($psus as $psu) {
            $returnStr .= '<option value="' . $psu->getPuid() . '"' . $selected[$psu->getPuid()] . '>' . $psu->getName() . '</option>';
        }
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displayCommunicationSelect($comm) {
        $selected = array_fill(0, 10, '');
        $selected[$comm] = ' SELECTED';
        $returnStr = '<select class="form-control" name="communication">';
        $returnStr .= '<option value=' . SEND_RECEIVE_USB . $selected[SEND_RECEIVE_USB] . '>' . Language::labelUSB() . '</option>';
        $returnStr .= '<option value=' . SEND_RECEIVE_INTERNET . $selected[SEND_RECEIVE_INTERNET] . '>' . Language::labelInternet() . '</option>';
        $returnStr .= '<option value=' . SEND_RECEIVE_EXPORTSQL . $selected[SEND_RECEIVE_EXPORTSQL] . '>' . Language::labelExportAsSql() . '</option>';
        $returnStr .= '<option value=' . SEND_RECEIVE_WORKONSERVER . $selected[SEND_RECEIVE_WORKONSERVER] . '>' . Language::labelWorkOnServer() . '</option>';
        $returnStr .= '</select>';
        return $returnStr;
    }

    function displaySelectFromArray($inputArray, $inputSel, $name = 'arrayinput') {
        $selected = array_fill(0, 50, '');
        $selected[$inputSel] = ' SELECTED';
        $returnStr = $this->displayComboBox();
        $returnStr .= '<select class="selectpicker show-tick" id="' . $name . '" name="' . $name . '" style="width:300px" >';
        foreach ($inputArray as $key => $input) {
            $returnStr .= '<option value=' . $key . $selected[$key] . '>' . $input . '</option>';
        }
        $returnStr .= '</select>';
        return $returnStr;
    }

    function showModalForm($id, $text) {
        $returnStr .= '<div class="modal fade bs-example-modal-lg" id="' . $id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width:1000px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
      </div>
      <div class="modal-body">
         <table width=100%" style="height:500px" ><tr><td valign=top>' . $text . ' 
         </td></tr></table>
      </div>
    </div>
  </div>
</div>        ';

        return $returnStr;
    }

    function displayPanelTypeFilter($paneltype = 0) {
        $returnStr = '';
        $active = array('', '', '', '', '', '', '', '');
        $active[$paneltype] = ' active';

        $returnStr .= '<input type="hidden" name="paneltype" id="paneltype" value="' . $paneltype . '">';

        $returnStr .= '<div id="filterselector" class="btn-group">
	  <button type="button" class="btn btn-default' . $active[1] . '" value=1>Households</button>
	  <button type="button" class="btn btn-default' . $active[2] . '" value=2>Respondents</button>';
        $returnStr .= '</div>';

        $returnStr .= '<script>';
        $returnStr .= '$(\'#filterselector button\').click(function() {
		  $(\'#filterselector button\').addClass(\'active\').not(this).removeClass(\'active\');
		  $(\'#paneltype\').val("0");
		  if ($(this).val() == "1") {
		    $(\'#paneltype\').val("1");
		  }
 	  });';
        $returnStr .= '</script>';


        return $returnStr;
    }

    function ShowCommunicationServerOptions() {
        if (is_array(dbConfig::defaultCommunicationServer())) {
            $returnStr = '';
            $active = array('', '', '', '', '', '', '', '');
            //if ($_SESSION['COMMSERVER'] == ''){ //init in index.php
            //    $_SESSION['COMMSERVER'] = 0;
            //}
            if (loadvar('commserver') != '') {
                $_SESSION['COMMSERVER'] = loadvar('commserver');
            }
            $active[$_SESSION['COMMSERVER']] = ' active';

            $returnStr .= '<form method="post" id="hiddenform">';
            $returnStr .= setSessionParamsPost(array('page' => 'interviewer.sendreceive'));

            $returnStr .= '<input type="hidden" name="commserver" id="commserver" value="' . $paneltype . '">';

            $returnStr .= '<div id="commserverselector" class="btn-group">
  	    <button type="button" class="btn btn-default' . $active[0] . '" value=0>' . Language::labelCommServerLocal() . '</button>
	    <button type="button" class="btn btn-default' . $active[1] . '" value=1>' . Language::labelCommServerOutside() . '</button>';
            $returnStr .= '</div>';



            $returnStr .= '</form>';

            $returnStr .= '<br/>';


            $returnStr .= '<script>';
            $returnStr .= '$(\'#commserverselector button\').click(function() {
		  $(\'#commserverselector button\').addClass(\'active\').not(this).removeClass(\'active\');
		  $(\'#commserver\').val("0");
		  if ($(this).val() == "1") {
		    $(\'#commserver\').val("1");
		  }
                  $("#hiddenform").submit();            

 	  });';
            $returnStr .= '</script>';
        }

        return $returnStr;
    }

    function getTinyMCE($selector = "textarea.tinymce", $inline = 1, $editicon = '') {

        $returnStr = '';
        if (!isRegisteredScript("js/tinymce/tinymce.min.js")) {
            registerScript('js/tinymce/tinymce.min.js');
            $returnStr .= getScript("js/tinymce/tinymce.min.js");
        }
        if (!isRegisteredScript("js/tinymce/jquery.tinymce.min.js")) {
            registerScript('js/tinymce/jquery.tinymce.min.js');
            $returnStr .= getScript("js/tinymce/jquery.tinymce.min.js");
        }
        $returnStr .= '
            <script type="text/javascript">';

        // inline survey editing, then define load text function
        if ($inline > 1) {
            $returnStr .= 'function loadRealText() {
                var realtext = "";
                var ed = tinyMCE.activeEditor;
                var id = ed.id;
                var target = $("#" + id).attr("uscic-target");
                var texttype = $("#" + id).attr("uscic-texttype");
                var answercode = $("#" + id).attr("uscic-answercode"); 
                $.ajax({
                        type: "POST",
                        url: "' . setSessionParams(getSessionParams()) . '",
                        data: { ' . POST_PARAM_SMS_AJAX . ': "' . SMS_AJAX_CALL . '", p: "sysadmin.inline.getcontent", texttype: texttype, answercode: answercode, target: target },    
                        success: function(response){
                            ed.setContent(response + "' . $editicon . '");
                        }
                    });     
            }';
        }

        if ($inline == 1) {
            $returnStr .= 'var old = "";
               $( document ).ready(function() {
                
                /*$("textarea.tinymce").focusin(function() {
                    $(this).click();
                });*/
                                
                tinymce.init({  
                    valid_elements : "*[*]",
                    mode : "textareas",
                    selector: "' . $selector . '",    
                    menubar: "insert edit table format view tools",
                    setup: function(editor) {
                                editor.on("blur", function(e) {
                                    return;
                                });
                                editor.on("init", function(e) {
                                    tinyMCE.activeEditor.focus(); // does not work first time round
                                });
                
                                
                            },';
        }

        // editor
        // inline survey editing
        if ($inline > 1) {
            $returnStr .= '
                tinymce.init({
                mode : "textareas",
                selector: "' . $selector . '",    
                menubar: "insert edit table format view tools",';

            if ($inline == 2) {
                $returnStr .= '
                    valid_elements : "*[*]",';
            }
            $save = '';
            $contextmenu = 'contextmenu';
            $save = 'save';
            $contextmenu = '';
            $returnStr .= 'inline: true,
                            save_enablewhendirty: true,
                            save_onsavecallback: function() { ajaxSave(this);},
                            setup: function(editor) {
                                editor.on("focus", function(e) {
                                    loadRealText();
                                });                                
                            },
                        ';
        }

        $returnStr .= '    
        content_css : "css/tinymce.css",
        theme_advanced_font_sizes: "10px,12px,13px,14px,16px,18px,20px",
        font_size_style_values : "10px,12px,13px,14px,16px,18px,20px",
        force_br_newlines : false,
        force_p_newlines : false,
        forced_root_block: \'\',
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace code ' . $save . '",
            "insertdatetime media table ' . $contextmenu . ' paste"
        ],
        toolbar1: "insertfile save undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
        toolbar2: "preview media | forecolor backcolor emoticons"
});';

        // inline survey editing
        if ($inline > 1) {
            $returnStr .= '
                function ajaxSave(ed) {
        ed.setProgressState(1); // Show progress 
        var id = ed.id;
        var texttype = $("#" + id).attr("uscic-texttype");
        var answercode = $("#" + id).attr("uscic-answercode");
        var target = $("#" + id).attr("uscic-target");
        $.ajax({
            type: "POST",
            url: "' . setSessionParams(getSessionParams()) . '",
            data: { ' . POST_PARAM_SMS_AJAX . ': "' . SMS_AJAX_CALL . '", p: "sysadmin.inline.editcontent", target: target, texttype: texttype, answercode: answercode, text: ed.getContent() },    
            success: function(msg){
                document.getElementById("navigation").value="' . PROGRAMMATIC_UPDATE . '"; 
                document.getElementById("form").submit();
            }
        });
        ed.setProgressState(0); // Show progress            
}
';
        }
        if ($inline == 1) {
            $returnStr .= '
                });';
        }

        $returnStr .= '</script>';
        return $returnStr;
    }

    function getCodeMirror($style = '') {

        if (!isRegisteredScript("js/codemirror/lib/codemirror.css")) {
            registerScript('js/codemirror/lib/codemirror.css');
            $returnStr = getCSS("js/codemirror/lib/codemirror.css");
        }
        if (!isRegisteredScript("js/codemirror/addon/dialog/dialog.css")) {
            registerScript('js/codemirror/addon/dialog/dialog.css');
            $returnStr .= getCSS("js/codemirror/addon/dialog/dialog.css");
        }
        if (!isRegisteredScript("js/codemirror/lib/codemirror.js")) {
            registerScript('js/codemirror/lib/codemirror.js');
            $returnStr .= getScript("js/codemirror/lib/codemirror.js");
        }
        if (!isRegisteredScript("js/codemirror/mode/xml/xml.js")) {
            registerScript('js/codemirror/mode/xml/xml.js');
            $returnStr .= getScript("js/codemirror/mode/xml/xml.js");
        }
        if (!isRegisteredScript("js/codemirror/addon/dialog/dialog.js")) {
            registerScript('js/codemirror/addon/dialog/dialog.js');
            $returnStr .= getScript("js/codemirror/addon/dialog/dialog.js");
        }
        if (!isRegisteredScript("js/codemirror/addon/search/searchcursor.js")) {
            registerScript('js/codemirror/addon/search/searchcursor.js');
            $returnStr .= getScript("js/codemirror/addon/search/searchcursor.js");
        }
        if (!isRegisteredScript("js/codemirror/addon/search/search.js")) {
            registerScript('js/codemirror/addon/search/search.js');
            $returnStr .= getScript("js/codemirror/addon/search/search.js");
        }
        if (!isRegisteredScript("js/codemirror/mode/nubis/nubis.js")) {
            registerScript('js/codemirror/mode/nubis/nubis.js');
            $returnStr .= getScript("js/codemirror/mode/nubis/nubis.js");
        }
        $returnStr .= '<style type="text/css">';
        $returnStr .= '    .CodeMirror {' . $style . ' border-top: 1px solid black; border-bottom: 1px solid black;}
                        dt {font-family: monospace; color: #666;}
                      </style>';
        return $returnStr;
    }

    function getDirtyForms() {

        $returnStr = '';
        if (!isRegisteredScript("js/dirtyform/lib/jquery.dirtyform.min.js")) {
            registerScript('js/dirtyform/jquery.dirtyform.min.js');
            $returnStr .= getScript("js/dirtyform/jquery.dirtyform.min.js");
            ;
        }

        if (!isRegisteredScript("js/dirtyform/lib/jquery.dirtyform.bootstrap.js")) {
            registerScript('js/dirtyform/jquery.dirtyform.bootstrap.js');
            $returnStr .= getScript("js/dirtyform/jquery.dirtyform.bootstrap.js");
        }

        if (isRegisteredScript("js/tinymce/tinymce.min.js")) {
            if (!isRegisteredScript("js/dirtyform/tinymce/jquery.dirtyforms.helpers.tinymce.min.js")) {
                registerScript('js/dirtyform/tinymce/jquery.dirtyforms.helpers.tinymce.min.js');
                $returnStr .= getScript("js/dirtyform/tinymce/jquery.dirtyforms.helpers.tinymce.min.js");
            }
        }

        $returnStr .= "<script type='text/javascript'>
                        $(document).ready(function() {
                            $.DirtyForms.ignoreClass = 'dirtyignore';
                            $.DirtyForms.dialog.dialogID = 'uscic-dialog';
                            //$.DirtyForms.dialog.titleID = 'uscic-title';
                            $.DirtyForms.dialog.continueButtonClass = 'uscic-continue';
                            $.DirtyForms.dialog.cancelButtonClass = 'uscic-cancel';
                            $.DirtyForms.dialog.continueButtonText = '" . Language::buttonContinue() . "';
                            $.DirtyForms.dialog.cancelButtonText = '" . Language::buttonCancel() . "';
                            $('#editform').dirtyForms({});
                        });                       
                        </script>";

        $returnStr .= '<div id="uscic-dialog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="dirty-title">' .
                '<div class="modal-dialog" role="document">' .
                '<div class="modal-content panel-danger">' .
                '<div class="modal-header panel-heading">' .
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' .
                '<h3 class="modal-title" id="uscic-title"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ' . Language::labelUnsavedChanges() . '</h3>' .
                '</div>' .
                '<div class="modal-body panel-body">' . Language::labelUnsavedChangesMessage() . '</div>' .
                '<div class="modal-footer panel-footer">' .
                '<button type="button" class="uscic-continue btn btn-danger" data-dismiss="modal"></button>' .
                '<button type="button" class="uscic-cancel btn btn-default" data-dismiss="modal"></button>' .
                '</div>' .
                '</div>' .
                '</div>' .
                '</div>';
        return $returnStr;
    }

}
