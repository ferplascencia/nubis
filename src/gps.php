<?php

/*
  ------------------------------------------------------------------------
  Copyright (C) 2014 Bart Orriens, Albert Weerman

  This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
  ------------------------------------------------------------------------
 */

class GPS {
    var $gps= null;

    function GPS($village, $dwellingid) {
        global $db;
        $query = 'select *,';
        $query .= 'aes_decrypt(latitude, "' . Config::smsPersonalInfoKey() . '") as latitude, ';
        $query .= 'aes_decrypt(longitude, "' . Config::smsPersonalInfoKey() . '") as longitude ';
        $query .= ' from ' . Config::dbSurvey() . '_gps where dwellingid = "' . prepareDatabaseString($dwellingid) . '" and code = "' . prepareDatabaseString($village) . '"';
        //echo '<br/><br/><br/>' . $query;
        $result = $db->selectQuery($query);
        $this->gps = $db->getRow($result);
    }

    function getLatitude(){
        return $this->gps['latitude'];
    }
    
    function getLongitude(){
        return $this->gps['longitude'];
    }
   
}

?>