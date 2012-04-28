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

class lwr_registration extends lwr_base
{
    public function __construct($oid)
    {
        $this->oid = $oid;
        $this->pluginname = 'lw_registration';
    }
    
    public function init()
    {
        $data = $this->loadPluginData();
        $this->error = false;
        $this->fromAddress = $data['parameter']['from_address'];
        $this->intranetId = $data['parameter']['intranet_id'];
        $this->debug = $data['parameter']['debug'];
    }    
    
    public function execute($cmd)
    {
        switch($cmd)
        {
            case"validateRegistrationForm":
                $this->validateRegistrationForm();
                break;
            
            case"registrationFormDone":
                $this->output = $this->buildMessage('confirmationMessageSend');
                break;
            
            case"registrationConfirmed":
                $this->confirmRegistrationByHash($this->request->getRaw("hash"));
                break;
            
            case"registrationConfirmedMessage":
                $this->output = $this->buildMessage('registrationConfirmedMessage');
                break;
            
            default:
                $this->buildRegistrationForm();
                break;
        }
    }
    
    public function getOutput() 
    {
        return $this->output;
    }
    
    protected function validateRegistrationForm()
    {
        $data = $this->validate();
        if ($this->error) {
            $this->buildRegistrationForm($data);
            return;
        }
        $data['intranet_id'] = $this->intranetId;
        $hash = $this->buildConfirmationHash($data['email']);
        $this->dh->saveRegistrationToBeConfirmed($data, $hash);
        $this->sendConfirmationEmail($data['email'], $hash);
        $this->pageReload($this->buildURL(array("cmd" => "registrationFormDone")));        
    }
    
    protected function confirmRegistrationByHash($hash) 
    {
        $hashExists = $this->dh->valueExists('hash', $hash);
        if ($hashExists) {
            $this->dh->saveRegistrationConfirmationByHash($hash);
            $this->pageReload(lw_object::buildURL(array("cmd" => "registrationConfirmedMessage")));
        } 
        else {
            $this->output = $this->buildMessage('invalidHashMessage');
        }
    }
    
    protected function buildMessage($templateName)
    {
        $template = $this->dh->getTemplate($templateName);
        $tpl = new lw_te($template);
        $tpl->reg('backurl', lw_page::getInstance()->getUrl());
        return $tpl->parse();
    }

    protected function buildRegistrationForm($data=false)
    {
        $template = $this->dh->getTemplate('registrationForm');
        $tpl = new lw_te($template);
        $tpl->reg('action', lw_page::getInstance()->getUrl(array("cmd"=>"validateRegistrationForm")));
        $tpl->reg('email', $data['email']);

        if ($this->error === true) {
            $tpl->setIfVar('error');
            if (strstr($this->errorMessage, ':error1:')) $tpl->setIfVar('error1');
            if (strstr($this->errorMessage, ':error2:')) $tpl->setIfVar('error2');
            if (strstr($this->errorMessage, ':error3:')) $tpl->setIfVar('error3');
            if (strstr($this->errorMessage, ':error4:')) $tpl->setIfVar('error4');
        }
        $this->output = $tpl->parse();
    }
    
    protected function validate()
    {
        $data['email'] = trim($this->request->getRaw('email'));
        if (strlen($data['email'])<6 || !lw_validation::isEmail($data['email'])) {
            $this->error = true;
            $this->errorMessage.= ':error1:';
        }
        if ($this->dh->valueExists('name', $data['email'])) {
            $this->error = true;
            $this->errorMessage.= ':error2:';
        }
        $data['pw1'] = trim($this->request->getRaw('pw1'));
        if (strlen($data['pw1'])<5) {
            $this->error = true;
            $this->errorMessage.= ':error3:';
        }
        $data['pw2'] = trim($this->request->getRaw('pw2'));
        if ($data['pw1'] != $data['pw2']) {
            $this->error = true;
            $this->errorMessage.= ':error4:';
        }
        return $data;
    }
    
    protected function sendConfirmationEmail($email, $hash) 
    {
        $link = lw_page::getInstance()->getUrl(array("cmd"=>"registrationConfirmed", "hash"=>$hash));
        $message = "Hello, please confirm by click on link in email: ".PHP_EOL.$link;
        
        $subject = "confirm registration!";

        if (!$this->debug) {
            $ok = mail($email, $subject, $message, 'From:'.$this->fromAddress);
        }
        else {
            $txt = '<h2>E-Mail Confirmation</h2>'.PHP_EOL;
            $txt.= 'To: '.$email.'<br>'.PHP_EOL;
            $txt.= 'from: '.$this->fromAddress.'<br>'.PHP_EOL;
            $txt.= 'Subject: '.$subject.'<br>'.PHP_EOL;
            $txt.= 'Message: '.$message.'<br>'.PHP_EOL;
            $txt.= '<a href="'.lw_object::buildURL(array("cmd" => "registrationFormDone")).'">weiter</a>';
            die($txt);
        }
        return true;
    }
    
    protected function buildConfirmationHash($email) 
    {
        $string = $email.'_e_'.time();
        $hash = sha1($string);
        return $hash;
    }
}
