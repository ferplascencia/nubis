<?php



error_reporting(E_ALL);

ini_set("display_errors", 1);

define('directLoginKey', '43admafeifaqgfasdFDedfq34qfa#1sa');

require_once("../surveys/constants.php");

require_once("../surveys/functions.php");
require_once("../surveys/dbConfig.php");
require_once("../surveys/config.php");

echo '<html><head><title>UCNets Face-to-Face test site</title></head><body>';

$p = loadvar('p');
if ($p == 'login' && loadvar('primkey') != ''){
  echo startSurvey(loadvar('primkey'));
}
else {
  echo enterId();
}

echo '</body></html>';


function enterId(){

  $content = '<center><h2>UCNets Face-to-Face test site</h2><div style="margin-top: 100px;">Please enter an id to login: <br/><br/>';

  $content .= '<form method="post" action="index.php">';
  $content .= '<input type=hidden name="p" value="login">';
  $content .= '<input type=text name="primkey"><br/><br/>';
  $content .= '<button type="submit" class="btn btn-default">Start</button>';
  $content .= '</form></div></center>';

  return $content;

}


function gen_password($length = 8) {
    mt_srand((double)microtime()*1000000);
    $password = "";
    $chars = "abcdefghijkmnpqrstuvwxyz123456";
    for($i = 0; $i < $length; $i++) {
        $x = mt_rand(0, strlen($chars) -1);
        $password .= $chars{$x};
    }
    $chars = "23456789";
    for($i = 0; $i < 2; $i++) {
        $x = mt_rand(0, strlen($chars) -1);
        $password .= $chars{$x};
    }
    return $password;
}

function startSurvey($primkey){
  $content = '<center><h2>UCNets Face-to-Face test site</h2><div style="margin-top: 100px;">Please click next >> to start the survey. ';

  $content .= '<form method="post" action="../surveys/index.php">';

	$content .= '<input type=hidden name=' . POST_PARAM_SE . ' value="' . addslashes(USCIC_SURVEY) . '">';
	$content .= '<input type=hidden name=' . POST_PARAM_PRIMKEY . ' value="' . addslashes(encryptC($primkey, directLoginKey)) . '">';
	$content .= '<input type=hidden name=' . POST_PARAM_LANGUAGE . ' value="1">';
        $content .= '<input type=hidden name=' . POST_PARAM_MODE . ' value="' . MODE_CAPI . '">';
        $content .= '<input type=hidden name=ms value=1>';
//	$content .= '<input type=hidden name=' . POST_PARAM_PRELOAD . ' value="' .  encodeSession($member->getPreload()) . '">';

  $content .= '<br/><br/><button type="submit" class="btn btn-default">Next >></button>';
  $content .= '</form></div></center>';
  return $content;
}



?>
