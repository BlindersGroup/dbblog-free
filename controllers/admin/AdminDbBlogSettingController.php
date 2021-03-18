<?php

class AdminDbBlogSettingController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=6&configure=dbblog&tab_module=dbblog&module_name=dbblog');
    }

}