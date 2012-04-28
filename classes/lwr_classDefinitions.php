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

class lwr_classDefinitions
{
    static function registerServiceDefinitions($dic) 
    {

        $dic->register('lwr_datahandler', 'lwr_datahandler')
            ->setFile(dirname(__FILE__).'/lwr_datahandler.php')
            ->addArgument('registry:db');

        $dic->register('lwr_backend', 'lwr_backend')
            ->setFile(dirname(__FILE__).'/lwr_backend.php')
            ->addArgument('service:lw_registration:getOid')
            ->addMethodCall('setConfiguration', array('registry:config'))
            ->addMethodCall('setDB', array('registry:db'))
            ->addMethodCall('setRequest', array('registry:request'))
            ->addMethodCall('setResponse', array('registry:response'))
            ->addMethodCall('setRepository', array('registry:repository'));
        
        $dic->register('lwr_administration', 'lwr_administration')
            ->setFile(dirname(__FILE__).'/lwr_administration.php')
            ->addArgument('service:lw_registration:getOid')
            ->addMethodCall('setConfiguration', array('registry:config'))
            ->addMethodCall('setDB', array('registry:db'))
            ->addMethodCall('setRequest', array('registry:request'))
            ->addMethodCall('setResponse', array('registry:response'))
            ->addMethodCall('setDataHandler', array('reference:lwr_datahandler'))
            ->addMethodCall('init', array());    
        
        $dic->register('lwr_registration', 'lwr_registration')
            ->setFile(dirname(__FILE__).'/lwr_registration.php')
            ->addArgument('service:lw_registration:getOid')
            ->addMethodCall('setConfiguration', array('registry:config'))
            ->addMethodCall('setDB', array('registry:db'))
            ->addMethodCall('setRequest', array('registry:request'))
            ->addMethodCall('setResponse', array('registry:response'))
            ->addMethodCall('setDataHandler', array('reference:lwr_datahandler'))
            ->addMethodCall('init', array());    
        
    }
}
