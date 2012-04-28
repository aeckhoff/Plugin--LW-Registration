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

class lwr_base
{
    public function setDataHandler($datahandler)
    {
        $this->dh = $datahandler;
    }
    
    public function setRequest($request)
    {
        $this->request = $request;
    }
    
    public function setResponse($response)
    {
        $this->response = $response;
    }
    
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }
    
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
    
    public function setDB($db)
    {
        $this->db = $db;
    }
    
    protected function pageReload($url)
    {
        $url = str_replace("&amp;", "&", $url);
        echo '<html>'.PHP_EOL;
        echo '    <head><meta http-equiv="Refresh" content="0;url='.$url.'" /></head>'.PHP_EOL;
        echo '    <body onload="try {self.location.href='."'".$url."'".' } catch(e) {}"><a href="'.$url.'">Redirect </a></body>'.PHP_EOL;
        echo '</html>'.PHP_EOL;
        exit();
    }    
    
    protected function loadPluginData()
    {
        $sql = "SELECT * FROM ".$this->configuration['dbt']['plugins']." WHERE plugin = '".$this->pluginname."' AND container_id ='".$this->oid."'";
        $erg = $this->db->select1($sql);
        if (!$erg['id']) {
            $sql = "INSERT INTO ".$this->configuration['dbt']['plugins']." (plugin, container_id) VALUES ('".$this->pluginname."', '".$this->oid."')";
            $ok = $this->db->dbquery($sql);
        }
        if ($erg['parameter']) {
            $data['parameter'] = unserialize(stripslashes($erg['parameter']));
        }
        else {
            $data['parameter'] = array();
        }
        $data['content'] = stripslashes($erg['content']);
        return $data;
    }    
    
    protected function savePluginData($parameter=false, $content=false)
    {
        $parameter = addslashes(serialize($parameter));
        $content = addslashes($content);
        $sql = "UPDATE ".$this->configuration['dbt']['plugins']." set parameter = '".$parameter."', content='".$content."' WHERE plugin = '".$this->pluginname."' AND container_id = '".$this->oid."'";
        return $this->db->dbquery($sql);
    }    
    
    protected function deletePlugindata()
    {
        $sql = "DELETE FROM ".$this->configuration['dbt']['plugins']." WHERE plugin = '".$this->pluginname."' AND container_id = '".$this->oid."'";
        return $this->db->dbquery($sql);
    }     
}
