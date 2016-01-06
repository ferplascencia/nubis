<?php

/*
  ------------------------------------------------------------------------
  Copyright (C) 2014 Bart Orriens, Albert Weerman

  This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
  ------------------------------------------------------------------------
 */
/*
  class Surveys {

  function Surveys() {

  }

  function getSurveys() {
  global $db;
  $surveys = array();
  $result = $db->selectQuery('select * from ' . Config::dbSurvey() . '_surveys');
  while ($row = $db->getRow($result)) {
  $surveys[] = new Survey($row);
  }
  return $surveys;
  }

  function getNumberOfSurveys() {
  global $db;
  $surveys = array();
  $result = $db->selectQuery('select * from ' . Config::dbSurvey() . '_surveys');
  return $db->getNumberOfRows($result);
  }

  function getSurveyIdentifiers() {
  global $db;
  $surveys = array();
  $result = $db->selectQuery('select suid from ' . Config::dbSurvey() . '_surveys');
  while ($row = $db->getRow($result)) {
  $surveys[] = $row["suid"];
  }
  return $surveys;
  }

  function getMaximumSuid() {
  global $db;
  $surveys = array();
  $result = $db->selectQuery('select max(suid) as max from ' . Config::dbSurvey() . '_surveys');
  if ($result) {
  $row = $db->getRow($result);
  return $row["max"];
  }
  }

  function getFirstSurvey() {
  global $db;
  $surveys = array();
  $result = $db->selectQuery('select suid from ' . Config::dbSurvey() . '_surveys order by suid asc');
  if ($result) {
  $row = $db->getRow($result);
  return $row["suid"];
  }
  return "";
  }
  } */


/*
 * NEW VERSION CHECKING FOR ACCESS, FINISH BY ALLOWING IT TO BE EDITED IN USER SCREEN
 * 
 * 
 */

class Surveys {

    private $surveycnt;
    private $usersurveys;
    private $identifiers;

    function Surveys() {
        
    }

    function getSurveys($all = false) {
        global $db;
        if (isset($this->usersurveys) && sizeof($this->usersurveys) > 0) {
            return $this->usersurveys;
        }
        $this->usersurveys = array();
        $this->identifiers = array();
        $result = $db->selectQuery('select * from ' . Config::dbSurvey() . '_surveys');
        $user = new User($_SESSION['URID']);
        $avsurveys = $user->getSurveysAccess();
        while ($row = $db->getRow($result)) {
            if (inArray($row["suid"], $avsurveys) || $all) {
                $this->usersurveys[] = new Survey($row);
                $this->identifiers[] = $row["suid"];
            }
        }
        return $this->usersurveys;
    }

    function getNumberOfSurveys($all = false) {

        if (isset($this->surveycnt) && $this->surveycnt > 0) {
            return $this->surveycnt;
        }
        global $db;
        $surveys = array();
        $result = $db->selectQuery('select * from ' . Config::dbSurvey() . '_surveys');
        $user = new User($_SESSION['URID']);
        $avsurveys = $user->getSurveysAccess();
        $this->surveycnt = 0;
        while ($row = $db->getRow($result)) {
            if (inArray($row["suid"], $avsurveys) || $all) {
                $this->surveycnt++;
            }
        }
        return $this->surveycnt;
    }

    function getSurveyIdentifiers() {
        global $db;
        if (isset($this->identifiers) && sizeof($this->identifiers) > 0) {
            return $this->identifiers;
        }
        $this->identifiers = array();
        $result = $db->selectQuery('select suid from ' . Config::dbSurvey() . '_surveys');
        while ($row = $db->getRow($result)) {
            $this->identifiers[] = $row["suid"];
        }
        return $this->identifiers;
    }

    function getMaximumSuid() {
        global $db;
        $surveys = array();
        $result = $db->selectQuery('select max(suid) as max from ' . Config::dbSurvey() . '_surveys');
        if ($result) {
            $row = $db->getRow($result);
            return $row["max"];
        }
    }

    function getFirstSurvey($all = false) {
        global $db;
        $surveys = array();
        $user = new User($_SESSION['URID']);
        $avsurveys = $user->getSurveysAccess();
        $result = $db->selectQuery('select suid from ' . Config::dbSurvey() . '_surveys order by suid asc');
        if ($result) {
            while ($row = $db->getRow($result)) {
                if (inArray($row["suid"], $avsurveys) || $all) {
                    return $row["suid"];
                }
            }
        }
        return "";
    }

}

?>
