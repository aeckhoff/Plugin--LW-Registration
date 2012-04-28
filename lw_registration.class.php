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

class lw_registration extends lw_pluginbase
{
    public function __construct()
    {
        parent::__construct();
        require_once(dirname(__FILE__).'/classes/lwr_classDefinitions.php');
        $this->dic = new lw_dic();
        $this->dic->setService('lw_registration', $this);
    }
    
    public function buildPageOutput()
    {
        $this->setOid($this->params['oid']);
        lwr_classDefinitions::registerServiceDefinitions($this->dic);
        $data = $this->loadPluginData();
        if ($data['parameter']['admin'] == 1) {
            $administration = $this->dic->get('lwr_administration');
            $administration->execute($this->request->getAlnum("cmd"));
            return $administration->getOutput();
        }
        else {
            $registration = $this->dic->get('lwr_registration');
            $registration->execute($this->request->getAlnum("cmd"));
            return $registration->getOutput();
        }
    }
    
    public function getOutput()
    {
        $this->setOid($this->request->getInt('oid'));
        lwr_classDefinitions::registerServiceDefinitions($this->dic);
        $backend = $this->dic->get('lwr_backend');
        $backend->execute($this->request->getAlnum("cmd"));
        return $backend->getOutput();
    }
}
