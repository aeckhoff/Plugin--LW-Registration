<?php

/**************************************************************************
*  Copyright notice
*
*  Copyright 2012 Logic Works GmbH
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*  
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*  
***************************************************************************/

class lwr_datahandler
{
    public function __construct($db) 
    {
        $this->db = $db;
    }
    
    public function saveRegistrationToBeConfirmed($data, $hash)
    {
        $sql = "INSERT INTO ".$this->db->gt('lw_in_user')." (intranet_id, name, password, email, lw_first_date, hash, locked) VALUES (".$data['intranet_id'].", '".$data['email']."', '".sha1($data['pw1'])."', '".$data['email']."', ".date("YmdHis").", '".$hash."', 1)";
        $ok = $this->db->dbquery($sql);
    }
    
    public function valueExists($field, $value)
    {
        $sql = "SELECT ".$field." FROM ".$this->db->gt('lw_in_user')." WHERE ".$field." = '".$this->db->quote($value)."' ";
        $result = $this->db->select1($sql);
        if ($result[$field] == $value && strlen($value)>0) {
            return true;
        }
        return false;        
    }

    public function saveRegistrationConfirmationByHash($hash) 
    {
        $sql = "UPDATE ".$this->db->gt('lw_in_user')." SET hash = NULL WHERE hash = '".$this->db->quote($hash)."' ";
        return $this->db->dbquery($sql);
    }
    
    public function approveEntryById($id)
    {
        $sql = "UPDATE ".$this->db->gt('lw_in_user')." SET locked = NULL WHERE id = '".intval($id)."' ";
        return $this->db->dbquery($sql);
    }
    
    public function deleteEntryById($id)
    {
        $sql = "DELETE FROM ".$this->db->gt('lw_in_user')." WHERE id = '".intval($id)."' ";
        return $this->db->dbquery($sql);
    }
    
    public function loadApprovalListByIntranetId($id) 
    {
        $sql = "SELECT id, name, lw_first_date, hash FROM ".$this->db->gt('lw_in_user')." WHERE intranet_id = ".intval($id)." AND locked = 1 ORDER BY lw_first_date DESC ";
        return $this->db->select($sql);
    }
    
    public function loadEntryById($id)
    {
        $sql = "SELECT * FROM ".$this->db->gt('lw_in_user')." WHERE id = '".intval($id)."' ";
        return $this->db->select1($sql);
    }
    
    public function getTemplate($name)
    {
        $sql = "SELECT opt1clob as template FROM ".$this->db->gt('lw_items')." WHERE page_id = ".lw_page::getInstance()->getId()." AND description = '".$name."' and opt1text = 'lw_registration'  ";
        $result = $this->db->select1($sql);
        if (!$result['template']) {
            return file_get_contents(dirname(__FILE__).'/../templates/'.$name.'.tpl.html');
        }
        else {
            return $result['template'];
        }
    }
}
