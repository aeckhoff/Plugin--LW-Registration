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

class lwr_administration extends lwr_base
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
            case "approve":
                $this->approveUserEntry($this->request->getInt("id"));
                $this->pageReload(lw_page::getInstance()->getUrl());
                break;
            
            case "delete":
                $this->deleteUserEntry($this->request->getInt("id"));
                $this->pageReload(lw_page::getInstance()->getUrl());
                break;
            
            default:
                $this->buildApprovalList();
                break;
        }
    }
    
    public function getOutput() 
    {
        return $this->output;
    }
    
    protected function buildApprovalList()
    {
        $this->response->useJQuery();
        $this->response->useJQueryUI();
        $this->response->addHeaderItems('jsfile', $this->configuration['url']['media'].'jquery/datatables/media/js/jquery.dataTables.js');
        $this->response->addHeaderItems('cssfile', $this->configuration['url']['media'].'jquery/datatables/media/css/demo_page.css');
	$this->response->addHeaderItems('cssfile', $this->configuration['url']['media'].'jquery/datatables/media/css/demo_table_jui.css');
        
        $template = $this->dh->getTemplate('approvalList');
        $tpl = new lw_te($template);        
        $list = $this->dh->loadApprovalListByIntranetId($this->intranetId);
        if (is_array($list) && count($list)>0) {
            $tpl->setIfVar('entries');
            $blocktemplate = $tpl->getBlock('entries');
            foreach($list as $entry) {
                $btpl = new lw_te($blocktemplate);
                $btpl->reg("id", $entry['id']);
                $btpl->reg("name", $entry['name']);
                $btpl->reg("firstdate", lw_object::formatDate($entry['lw_first_date']));
                $btpl->reg("deleteurl", lw_page::getInstance()->getUrl(array("cmd"=>"delete","id"=>$entry['id'])));
                if(!$entry['hash']) {
                    $btpl->setIfVar('nohash');
                    $btpl->reg("approveurl", lw_page::getInstance()->getUrl(array("cmd"=>"approve","id"=>$entry['id'])));
                }
                $block.=$btpl->parse();
            }
            $tpl->putBlock('entries', $block);
        }
        else {
            $tpl->setIfVar('noentries');
        }
        $this->output = $tpl->parse();
                
    }
    
    protected function approveUserEntry($id)
    {
        $this->dh->approveEntryById($id);
        $data = $this->dh->loadEntryById($id);
        $this->sendApprovalEmail($data['email']);
    }
    
    protected function deleteUserEntry($id)
    {
        $this->dh->deleteEntryById($id);
    }
    
    protected function sendApprovalEmail($email) 
    {
        $message = "You have been approved, now you can login!";
        $subject = "approval message!";

        if (!$this->debug) {
            $ok = mail($email, $subject, $message, 'From:'.$this->fromAddress);
        }
        else {
            $txt = '<h2>E-Mail Confirmation</h2>'.PHP_EOL;
            $txt.= 'To: '.$email.'<br>'.PHP_EOL;
            $txt.= 'from: '.$this->fromAddress.'<br>'.PHP_EOL;
            $txt.= 'Subject: '.$subject.'<br>'.PHP_EOL;
            $txt.= 'Message: '.$message.'<br>'.PHP_EOL;
            $txt.= '<a href="'.lw_page::getInstance()->getUrl().'">zur Liste</a>';
            die($txt);
        }
        return true;
    }    

}
