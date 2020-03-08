<?php
include(_PS_MODULE_DIR_.'easypay/easypay.php');


class AdminEasyPayController extends ModuleAdminController
{


	public function __construct()
	{
	    $this->bootstrap = true;
	    $this->context = Context::getContext();


	    parent::__construct();
	}


	public function initContent()
	{
	     parent::initContent();

	}

	 

	public function renderList()

	{
	        $sql = "SELECT * FROM "._DB_PREFIX_."subscrip ORDER BY id_susc DESC";
	        $subs = Db::getInstance()->executeS($sql);
	        $this->context->smarty->assign(
                array(
                    'subs' => $subs
                    )

            );
	        return $this->module->display(_PS_MODULE_DIR_.'easypay', 'views/templates/admin/teste.tpl');
	}

}

?>