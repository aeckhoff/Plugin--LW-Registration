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

require_once dirname(__FILE__).'/lwr_base.php';

class lwr_backend extends lwr_base
{
    public function __construct($oid)
    {
        $this->pluginname = 'lw_registration';
        $this->oid = $oid;
    }
    
    public function execute() 
    {
        if ($this->request->getAlnum("pcmd") == "save") {
            $parameter = false;
            $parameter['intranet_id'] = $this->request->getInt("intranet_id");
            $parameter['from_address'] = $this->request->getEmail("from_address");
            $parameter['debug'] = $this->request->getInt("debug");
            $parameter['admin'] = $this->request->getInt("admin");
            $this->savePluginData($parameter, $content);
            $this->pageReload(lw_object::buildURL(array("saved" => 1), array("pcmd")));
        }        
        $this->showPluginForm();
    }
    
    private function showPluginForm()
    {
        $view = new lw_view(dirname(__FILE__).'/../templates/backend.phtml');
        $data = $this->loadPluginData();
        $view->data = $data['parameter'];
        $view->intranetOptions = $this->buildIntranetOptions($data['parameter']['intranet_id']);
        $view->actionurl = lw_object::buildUrl(array("pcmd" => "save"));
        $view->backurl = lw_object::buildUrl(false, array('oid', 'cmd', 'save'));
        $this->output = $view->render();        
    }   
    
    function buildIntranetOptions($id) 
    {
        $intranets = $this->repository->intranetadmin()->getAllIntranets();
        foreach($intranets as $intranet) {
            $out.='<option value="'.$intranet['id'].'"';
            if ($intranet['id'] == $id) {
                $out.=' selected="selected"';
            }
            $out.='>'.$intranet['name'].'&nbsp;&nbsp;</option>'.PHP_EOL;
        }
        return $out;
    }
    
    function getOutput() 
    {
        return $this->output;
    }    
}
