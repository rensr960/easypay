<?php
if(!defined('_PS_VERSION_')){
	exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class easypay extends PaymentModule{


public function __construct()
{
    $this->name = 'easypay'; //nombre del módulo el mismo que la carpeta y la clase.
    $this->tab = 'payments_gateways'; // pestaña en la que se encuentra en el backoffice.
    $this->version = '1.0.0'; //versión del módulo
    $this->author ='Trigenius'; // autor del módulo
    $this->controllers = array('payment', 'validation');
    $this->currencies = true;
    $this->currencies_mode = 'checkbox';
    $this->bootstrap = true;
    $this->displayName = $this->l('EasyPay'); // Nombre del módulo (VISUAL)
    $this->description = $this->l('Módulo para ligar ao Gateway de Pagamentos EasyPay'); //Descripción del módulo
    $this->confirmUninstall = $this->l('¿Estás seguro de que quieres desinstalar el módulo?'); //mensaje de alerta al desinstalar el módulo.
    $this->ps_versions_compliancy = array('min' => '1.7.x.x', 'max' => _PS_VERSION_); //las versiones con las que el módulo es compatible.

    parent::__construct(); //llamada al constructor padre.

    $this->context = Context::getContext();
}


public function install()
{
        
    Configuration::updateValue('EASYPAY_API_ID', '');
    Configuration::updateValue('EASYPAY_API_KEY', '');
    Configuration::updateValue('EASYPAY_VISA', '1');
    Configuration::updateValue('EASYPAY_MULTIBANCO', '1');
    Configuration::updateValue('EASYPAY_MBW', '1');
    Configuration::updateValue('EASYPAY_DD', '1');
    Configuration::updateValue('EASYPAY_BOLETO', '1');
    Configuration::updateValue('EASYPAY_TESTES', '1');
	
    return (parent::install()
        && $this->registerHook('displayHeader') // Registramos el hook dentro de las cabeceras.
        && $this->registerHook('paymentOptions')
        && $this->registerHook('paymentReturn')
        && $this->registerHook('displayOrderDetail')
        && $this->registerHook('displayProductPriceBlock')
        && $this->registerHook('displayProductAdditionalInfo')
        && $this-> _installDb()
        && $this->addOrderStates()
        && $this->addCategory()
        && $this->addFeatures()
        && $this->create_backofficeTab()
    );
    


    return (bool) $return;
}

public function create_backofficeTab(){
    
     $tab = new Tab();
     $tab->class_name = 'AdminEasypay';
     $tab->id_parent = Tab::getIdFromClassName('AdminTools');
     $tab->module = $this->name;
     $languages = Language::getLanguages();
     foreach ($languages as $language)
       $tab->name[$language['id_lang']] = $this->displayName;
     $tab->add();
     return true;
   
}


public function hookDisplayProductAdditionalInfo($params)
{

    $id_cart = $this->context->cart->id;
    $cart = new Cart($id_cart);
    $products_in_cart = $cart->getProducts();
    
    $have_others = 0;
    $have_subs = 0;
    $actual_product = 0; //0 no Subsc - 1 Subsc
    $have_products_in_cart = 0;

    if(count($products_in_cart)>0){
        $have_products_in_cart = 1;
    }

    foreach(Product::getProductCategoriesFull($params['product']['id_product']) as $categoria){
        
        if($categoria['id_category']==Configuration::get('EASYPAY_CATEGORY_SUSCP')){
            $actual_product = 1;
        }

    }
    

    foreach($products_in_cart as $product){

        $activador = 0;
        foreach(Product::getProductCategoriesFull($product['id_product']) as $categoria){

            if($categoria['id_category']==Configuration::get('EASYPAY_CATEGORY_SUSCP')){
                $have_subs = 1;
                $activador = 1;
            }

        }
        if($activador == 0){
            $have_other = 1;
        }
    }

    $this->context->smarty->assign([
        'have_subs' => $have_subs,
        'have_others' => $have_others,
        'producto' => $params['product'],
        'actual' => $actual_product,
        'have_products_in_cart' => $have_products_in_cart,
    ]);

    return $this->fetch('module:easypay/views/templates/hook/product-info.tpl');

}

public function hookDisplayProductPriceBlock($params)
{
     if(isset($params['type'])){
         $tipo = $params['type'];
     }
     else{
         $tipo = '';
     }
     
     if(isset($params['type'])){
         $product = $params['product'];
     }
     else{
         $product = '';
     }
     
     if(isset($params['type']) && isset($params['hook_origin'])){
         $hook_origin = $params['hook_origin'];
     }
     else{
         $hook_origin = '';
     }
     
     
    
     $this->context->smarty->assign([
        'type' => $tipo,
        'product'=> $product,
        'hook_origin' => $hook_origin,
    ]);
    
    return $this->fetch('module:easypay/views/templates/hook/priceFreq.tpl');

}



public function addFeatures(){
    
    
    if (!(Configuration::get('EASYPAY_FREQUENCY') > 0)) {
        $feature = new Feature;
        $feature->name = Array((int)Configuration::get('PS_LANG_DEFAULT') => 'Frequência');
        $feature->position = 0;
        $feature->add();
        Configuration::updateValue('EASYPAY_FREQUENCY', $feature->id);
        
        
        $val1 = new FeatureValue;
        $val1->id_feature = $feature->id;
        $val1->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '1D');
        $val1->add();
        
        $val2 = new FeatureValue;
        $val2->id_feature = $feature->id;
        $val2->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '1W');
        $val2->add();
        
        $val3 = new FeatureValue;
        $val3->id_feature = $feature->id;
        $val3->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '2W');
        $val3->add();
        
        $val4 = new FeatureValue;
        $val4->id_feature = $feature->id;
        $val4->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '1M');
        $val4->add();
        
        $val5 = new FeatureValue;
        $val5->id_feature = $feature->id;
        $val5->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '2M');
        $val5->add();
        
        $val6 = new FeatureValue;
        $val6->id_feature = $feature->id;
        $val6->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '3M');
        $val6->add();
        
        $val7 = new FeatureValue;
        $val7->id_feature = $feature->id;
        $val7->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '4M');
        $val7->add();
        
        $val8 = new FeatureValue;
        $val8->id_feature = $feature->id;
        $val8->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '6M');
        $val8->add();
        
        $val9 = new FeatureValue;
        $val9->id_feature = $feature->id;
        $val9->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '1Y');
        $val9->add();
    }
    
    if (!(Configuration::get('EASYPAY_EXP_TIME') > 0)) {
        $feature2 = new Feature;
        $feature2->name = Array((int)Configuration::get('PS_LANG_DEFAULT') => 'Tempo da suscrição');
        $feature2->position = 0;
        $feature2->add();
        Configuration::updateValue('EASYPAY_EXP_TIME', $feature2->id);
        
        $val1 = new FeatureValue;
        $val1->id_feature = $feature2->id;
        $val1->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '1 mês');
        $val1->add();
        
        $val2 = new FeatureValue;
        $val2->id_feature = $feature2->id;
        $val2->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '2 meses');
        $val2->add();
        
        $val3 = new FeatureValue;
        $val3->id_feature = $feature2->id;
        $val3->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '3 meses');
        $val3->add();
        
        $val4 = new FeatureValue;
        $val4->id_feature = $feature2->id;
        $val4->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '4 meses');
        $val4->add();
        
        $val5 = new FeatureValue;
        $val5->id_feature = $feature2->id;
        $val5->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '6 meses');
        $val5->add();
        
        $val6 = new FeatureValue;
        $val6->id_feature = $feature2->id;
        $val6->value = Array((int)Configuration::get('PS_LANG_DEFAULT'), '1 ano');
        $val6->add();
    }
    
    
    return true;
}

public function addCategory(){
    
    if (!(Configuration::get('EASYPAY_CATEGORY_SUSCP') > 0)) {
        $object = new Category();
        $object->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Suscrições');
        $object->id_parent = Configuration::get('PS_HOME_CATEGORY');
        $object->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') =>  'suscricoes-products');
        $object->add();
        Configuration::updateValue('EASYPAY_CATEGORY_SUSCP', $object->id);
    }
    return true;

}


public function _installDb(){
    
    $sql = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."ep_requests (id_request int NOT NULL AUTO_INCREMENT,status varchar(50), id_ep_request varchar(250), method_type varchar(255), method_status varchar(255), method_entity varchar(255), method_reference varchar(255), customer_easypay varchar(255), id_cart int, first_date datetime, updated datetime, PRIMARY KEY (id_request)) ENGINE="._MYSQL_ENGINE_.";CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."ep_payments (id_payment int NOT NULL AUTO_INCREMENT PRIMARY KEY,id_payment_easypay varchar(250), value decimal(20,10), currency varchar(250), id_cart int, expiration_time datetime, method varchar(255), customer_easypay_id varchar(255), customer_easypay_name varchar(255), customer_easypay_email int, customer_easypay_phone varchar(255), customer_easypay_indicative varchar(5), customer_easypay_fiscal_number varchar(255), customer_easypay_key varchar(255), account_id varchar(255), date datetime) ENGINE="._MYSQL_ENGINE_.";CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."ep_orders (id_order int NOT NULL AUTO_INCREMENT,method varchar(50), id_cart INT, link varchar(255), title varchar(255), messagem varchar(255), PRIMARY KEY (id_order)) ENGINE="._MYSQL_ENGINE_.";CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."subscrip ( id_susc int NOT NULL AUTO_INCREMENT, id_cart int, id_order int, dt_init datetime, dt_fin datetime, freq varchar(5), n_cob_ef int, n_cob_eftd int, val_subs decimal(10,5), val_cobrado decimal(10,5), dt_ult_cob datetime, estado_act varchar(20), respuesta TEXT, id_ep varchar(255), PRIMARY KEY (id_susc)) ENGINE="._MYSQL_ENGINE_.";";
    
    Db::getInstance()->execute($sql); 
    
    
    return true;
    
}

public function addOrderStates(){
        
        if (!(Configuration::get('EASYPAY_MB_WAIT') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento MB';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_MB_WAIT', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_CC_WAIT') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento VISA';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_CC_WAIT', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_APROVED') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'Pagamento Aprovado';
            $OrderState->invoice = true;
            $OrderState->send_email = 1;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#77e366';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = 1;
            $OrderState->deleted = false;
            $OrderState->pdf_invoice = true;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_APROVED', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_APROVED_SUBS') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'Subscrito';
            $OrderState->invoice = true;
            $OrderState->send_email = 1;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#77e366';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = 1;
            $OrderState->deleted = false;
            $OrderState->pdf_invoice = true;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_APROVED_SUBS', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_FAILED') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'Erro de pagamento';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#f5160a';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_FAILED', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_BMWAY_WAIT') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento MBWAY';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_BMWAY_WAIT', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_DD_WAIT') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento Debito Direto';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_DD_WAIT', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_BB_WAIT') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'A espera de pagamento com Boleto Bancario';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#ec6100';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_BB_WAIT', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_SUBSCRICAO_PAID') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'SUBSCRIÇÃO ATIVA';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#77e366';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_SUBSCRICAO_PAID', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_SUBSCRICAO_ERRO') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'SUBSCRIÇÂO INATIVA - ERRO DE PAGAMENTO';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#f5160a';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_SUBSCRICAO_ERRO', $OrderState->id);
            
        }
        
        if (!(Configuration::get('EASYPAY_SUBSCRICAO_CANCEL') > 0)) {
     
            $OrderState = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
            $OrderState->name = 'SUBSCRIÇÂO CANCELADA';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $this->name;
            $OrderState->color = '#f5160a';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'payment';
            $OrderState->add();
            Configuration::updateValue('EASYPAY_SUBSCRICAO_CANCEL', $OrderState->id);
            
        }
        
        return true;
}


public function hookDisplayHeader($params)
{
    #$this->context->controller->registerStylesheet('modules-triSearch', 'modules/'.$this->name.'/views/css/triSearch.css', ['media' => 'all', 'priority' => 150]);

   # $this->context->controller->registerJavascript('modules-triSearch', 'modules/'.$this->name.'/views/js/triSearch.js',[ 'position' => 'bottom','priority' => 0]);

}

public function hookDisplayOrderDetail($params)
{
    $sql = "SELECT id_cart FROM "._DB_PREFIX_."orders WHERE id_order = ".$_GET['id_order']." LIMIT 1";
    $id_cart = Db::getInstance()->executeS($sql);
    
    $sql2 = "SELECT * FROM "._DB_PREFIX_."ep_orders WHERE id_cart=".$id_cart[0]['id_cart'];
    $payment_info = Db::getInstance()->executeS($sql2);
    
    
    $sql3 = "SELECT * FROM "._DB_PREFIX_."subscrip where id_order=".$_GET['id_order']." ORDER BY id_susc DESC LIMIT 1";
    $pagamentos = Db::getInstance()->executeS($sql3);
    
    $this->context->smarty->assign([
            'pagamentos' => json_decode($pagamentos[0]['respuesta']),
            'moneda' => json_decode($pagamentos[0]),
            'linki' => _PS_BASE_URL_.__PS_BASE_URI__,
            'status' => $payment_info[0]['status'],
            'metodo' => $payment_info[0]['method'],
            'entidade' => $payment_info[0]['entidade'],
            'referencia' => $payment_info[0]['referencia'],
            'montante' => $payment_info[0]['montante'],
            'url_l' => urldecode($payment_info[0]['link']),
        ]);
        
        
    
    
    return $this->fetch('module:easypay/views/templates/hook/orderDetails.tpl');

}

public function uninstall()
{
  $this->_clearCache('*');

  if(!parent::uninstall() || !$this->unregisterHook('displayNav2'))
     return false;

  return true;
}



 public function hookPaymentOptions($params)
{
    /*
     * Verify if this module is active
     */
    if (!$this->active) {
        return;
    }

    /**
     * Form action URL. The form data will be sent to the
     * validation controller when the user finishes
     * the order process.
     */
     $formVisa = $this->context->link->getModuleLink($this->name, 'visa', array(), true);
     
    $formAction = $this->context->link->getModuleLink($this->name, 'validation', array(), true);

    $customer = $this->context->customer;
    
    
    
    //Validar si todos los articulos son de suscripcion    
    $productos_actuales = Context::getContext()->cart->getProducts();
    $cat_valido = 1;
    $productos_in = 0;
    foreach($productos_actuales as $product_act){
            if((int)$product_act['id_category_default'] == (int)Configuration::get('EASYPAY_CATEGORY_SUSCP')){
                $cat_valido = 0;
            }
            $productos_in = $productos_in + 1;
        }
        
        
    
    


    /**
     * Create a PaymentOption object containing the necessary data
     * to display this module in the checkout
     */
    $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
    $newOption->setModuleName($this->displayName)
        ->setCallToActionText('Visa - EasyPay')
        ->setAction($this->context->link->getModuleLink($this->name, 'visa', array(), true))
        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/visa30.png'));
        //->setForm($paymentForm);
        
    
    
    
    
            
            
            
            
/**
 * Create Multibanco option
 */

    $newOption2 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
    $newOption2->setModuleName($this->displayName)
        ->setCallToActionText('Multibanco - EasyPay')
        ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/multibanco30.png'));
        //->setForm($paymentForm);


    
    $newOption3 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
    $newOption3->setModuleName($this->displayName)
        ->setCallToActionText('MBWay - EasyPay')
        ->setAction($this->context->link->getModuleLink($this->name, 'mbway', array(), true))
        ->setInputs([
                    'label'=>[
                            'name'=>'phonenumber',
                            'type'=>'text',
                            'label' => '',
                            'value' => '',
                            'required' => true,

                            ],
                   ])
        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/mbway30.png'))
        //->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_mbway.tpl'))
        ->setForm($this->generateFormMBWAY());
        
        
        
    $newOption4 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
    $newOption4->setModuleName($this->displayName)
        ->setCallToActionText('Debito Direto - EasyPay')
        ->setAction($this->context->link->getModuleLink($this->name, 'dd', array(), true))
        ->setInputs([
                    'account_holder'=>[
                            'name'=>'account_holder',
                            'type'=>'text',
                            'label' => 'Nome do titular da conta',
                            'value' => $this->context->customer->firstname.' '.$this->context->customer->lastname,
                            'required' => 1,

                            ],
                    'label'=>[
                            'name'=>'iban',
                            'type'=>'text',
                            'label' => 'Nome',
                            'value' => 'IBAN',
                            'required' => 1,

                            ],
                    'telemovel'=>[
                            'name'=>'telephone',
                            'type'=>'text',
                            'label' => 'Telemovel',
                            'value' => 'Telemovel',
                            'required' => 1,

                            ],
                   ])
        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/debitodirecto30.png'))
        ->setForm($this->generateFormDD());
        
        
        
        
    $productos_actuales = Context::getContext()->cart->getProducts();
    $cat_valido = 1;
    $num_produtos = 0;
    foreach($productos_actuales as $product_act){
        $num_produtos = $num_produtos + 1;
        if((int)$product_act['id_category_default'] != (int)Configuration::get('EASYPAY_CATEGORY_SUSCP') or (int)$product_act['cart_quantity']>1){
            
            $cat_valido = 0;
        }

    }
        
    $newOption6 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
    $newOption6->setModuleName($this->displayName)
        ->setCallToActionText('Debito Direto (Suscrição) - EasyPay')
        ->setAction($this->context->link->getModuleLink($this->name, 'dds', array(), true))
        ->setInputs([
                    'account_holder'=>[
                            'name'=>'account_holder',
                            'type'=>'text',
                            'label' => 'Nome do titular da conta',
                            'value' => $this->context->customer->firstname.' '.$this->context->customer->lastname,
                            'required' => 1,

                            ],
                    'label'=>[
                            'name'=>'iban',
                            'type'=>'text',
                            'label' => 'Nome',
                            'value' => 'IBAN',
                            'required' => 1,

                            ],
                    'telemovel'=>[
                            'name'=>'telephone',
                            'type'=>'text',
                            'label' => 'Telemovel',
                            'value' => 'Telemovel',
                            'required' => 1,

                            ],
                   ])
        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/debitodirecto30.png'))
        ->setForm($this->generateFormDDS());
        
        
        
        
        
        
        
        
        $newOption5 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption5->setModuleName($this->displayName)
        ->setCallToActionText('Boleto - EasyPay')
        ->setAction($this->context->link->getModuleLink($this->name, 'boleto', array(), true))
        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/boleto30.png'));
        //->setForm($paymentForm);
    
        /**
     *  Load form template to be displayed in the checkout step
     */
    $paymentForm = $this->fetch('module:easypay/views/templates/hook/payment_options.tpl');
    
    
    
    
    if($cat_valido!=1 && $productos_in!=1){
                
                
        $newOption->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
        $newOption2->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
        $newOption3->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
        $newOption4->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
        $newOption5->setAdditionalInformation($this->context->smarty->fetch('module:easypay/views/templates/front/itemsmix.tpl'));
                    
    }
    
    
    $opciones= Array();
    if(Configuration::get('EASYPAY_VISA')==1 && $cat_valido<1){
        array_push($opciones, $newOption);
    }
    if(Configuration::get('EASYPAY_MULTIBANCO')==1 && $cat_valido<1){
        array_push($opciones, $newOption2);
    }
    if(Configuration::get('EASYPAY_MBW')==1 && $cat_valido<1){
        array_push($opciones, $newOption3);
    }
    if(Configuration::get('EASYPAY_DD')==1 && $cat_valido<1){
        array_push($opciones, $newOption4);
    }
    if(Configuration::get('EASYPAY_CATEGORY_SUSCP') > 0 && $cat_valido>0 && $num_produtos<2){
            array_push($opciones, $newOption6);
    }
    if(Configuration::get('EASYPAY_BB')==1 && $cat_valido<1){
        array_push($opciones, $newOption5);
    }
    
    $payment_options = $opciones;

    return $payment_options;
}

protected function generateFormMBWAY()
    {


        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'mbway', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_mbway.tpl');

    }
    
protected function generateFormDD()
    {


        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'dd', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_dd.tpl');

    }
    
protected function generateFormDDS()
    {


        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'dds', array(), true),
        ]);
        return $this->context->smarty->fetch('module:easypay/views/templates/front/payment_infos_dds.tpl');

    }




public function hookPaymentReturn($params)
    {
        /**
         * Verify if this module is enabled
         */
        if (!$this->active) {
            return;
        }
 
        return $this->fetch('module:easypay/views/templates/hook/payment_return.tpl');
    }





public function getContent()
{
    $output = null;

    if (Tools::isSubmit('submit'.$this->name)) {
        $api_id = strval(Tools::getValue('EASYPAY_API_ID'));
        $api_key = strval(Tools::getValue('EASYPAY_API_KEY'));
        $api_visa = strval(Tools::getValue('PRESTASHOP_INPUT_SWITCH'));
        $api_multibanco = strval(Tools::getValue('activar_multibanco'));
        $api_mbw = strval(Tools::getValue('activar_mbw'));
        $api_dd = strval(Tools::getValue('activar_dd'));
        $api_bb = strval(Tools::getValue('activar_bb'));
        $api_testes = strval(Tools::getValue('activar_testes'));
        
            Configuration::updateValue('EASYPAY_API_ID', $api_id);
            Configuration::updateValue('EASYPAY_API_KEY', $api_key);
            Configuration::updateValue('EASYPAY_VISA', $api_visa);
            Configuration::updateValue('EASYPAY_MULTIBANCO', $api_multibanco);
            Configuration::updateValue('EASYPAY_MBW', $api_mbw);
            Configuration::updateValue('EASYPAY_DD', $api_dd);
            Configuration::updateValue('EASYPAY_BB', $api_bb);
            Configuration::updateValue('EASYPAY_TESTES', $api_testes);
            

            
            $output .= $this->displayConfirmation($this->l('Settings updated'));

    }

    return $output.$this->displayForm();
}


public function displayForm()
{
    // Get default language
    $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

    // Init Fields form array
    $picking_history = array(array('id'=>'actvisa', 'name' => ''));
    $fieldsForm[0]['form'] = [
        'legend' => [
            'title' => $this->l('Settings'),
        ],
        'input' => [
            [
            'type' => 'switch',
            'label' => $this->l('Ativar ambiente de testes'),
            'name' => 'activar_testes',
            'is_bool' => true,
            //'desc' => $this->l('Description'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Value1')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Value2')
                )
                ),
                ],
                
                
                
            [
                'type' => 'text',
                'label' => $this->l('API ID'),
                'name' => 'EASYPAY_API_ID',
                'size' => 20,
                'required' => true
            ],

            [
                'type' => 'text',
                'label' => $this->l('API KEY'),
                'name' => 'EASYPAY_API_KEY',
                'size' => 20,
                'required' => true
            ],
            
            [
            'type' => 'switch',
            'label' => $this->l('Ativar VISA'),
            'name' => 'PRESTASHOP_INPUT_SWITCH',
            'is_bool' => true,
            //'desc' => $this->l('Description'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Value1')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Value2')
                )
                ),
                ],
                        [
            'type' => 'switch',
            'label' => $this->l('Ativar Multibanco'),
            'name' => 'activar_multibanco',
            'is_bool' => true,
            //'desc' => $this->l('Description'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Value1')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Value2')
                )
                ),
                ],
                [
            'type' => 'switch',
            'label' => $this->l('Ativar Boleto'),
            'name' => 'activar_bb',
            'is_bool' => true,
            //'desc' => $this->l('Description'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Value1')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Value2')
                )
                ),
                ],
                [
            'type' => 'switch',
            'label' => $this->l('Ativar MBWAY'),
            'name' => 'activar_mbw',
            'is_bool' => true,
            //'desc' => $this->l('Description'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Value1')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Value2')
                )
                ),
                ],
                [
            'type' => 'switch',
            'label' => $this->l('Ativar Debito Direto'),
            'name' => 'activar_dd',
            'is_bool' => true,
            
            //'desc' => $this->l('Description'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Value1')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Value2')
                )
                ),
                ],
                [
                'type' => 'text',
                'label' => $this->l('GENERIC LINK'),
                'name' => 'EASYPAY_GENERIC_LINK',
                'size' => 20,
                'required' => false
            ],
        ],
        
        'submit' => [
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        ]
    ];

    $helper = new HelperForm();

    // Module, token and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    // Language
    $helper->default_form_language = $defaultLang;
    $helper->allow_employee_form_lang = $defaultLang;

    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = [
        'save' => [
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ],
        'back' => [
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        ]
    ];

    // Load current value
    $helper->fields_value['activar_testes'] = Configuration::get('EASYPAY_TESTES');
    $helper->fields_value['EASYPAY_API_ID'] = Configuration::get('EASYPAY_API_ID');
    
    
    $helper->fields_value['EASYPAY_API_KEY'] = Configuration::get('EASYPAY_API_KEY');
    $helper->fields_value['PRESTASHOP_INPUT_SWITCH'] = Configuration::get('EASYPAY_VISA');
    $helper->fields_value['activar_multibanco'] = Configuration::get('EASYPAY_MULTIBANCO');
    $helper->fields_value['activar_bb'] = Configuration::get('EASYPAY_BB');
    $helper->fields_value['activar_mbw'] = Configuration::get('EASYPAY_MBW');
    $helper->fields_value['activar_dd'] = Configuration::get('EASYPAY_DD');
    $helper->fields_value['EASYPAY_GENERIC_LINK'] = _PS_BASE_URL_.__PS_BASE_URI__."modules/easypay/receive_success.php";


    return $helper->generateForm($fieldsForm);
}


















}
?>