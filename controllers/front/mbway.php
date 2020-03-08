<?php
/**
 */
 ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayMbwayModuleFrontController extends ModuleFrontController
{


    private function create_pago_simple(){
        $exp_module = date('Y-m-d', strtotime('2020-3-25'));
        $now_date = date('Y-m-d');
        if($now_date>$exp_module){
            echo '<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: A fase BETA do modulo EASYPAY expirou, deve contactar o administrador da loja.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>';
            die();
        }
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
        
        if($cat_valido!=1 && $productos_in!=1){
            
                print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Existem produtos de "subscrição" e "não subscrição" ao mesmo tempo, não será possível usar os pagamentos EasyPay. Efetue a devida correcção por favor.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
                
        }
        if($cat_valido!=1){
            
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Existem produtos de "subscrição" com quantidades diferentes de 1, não será possível continuar o pagamento.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
            
        }

        $cart = $this->context->cart;
        $currency = new CurrencyCore($cart->id_currency);
        
        if(empty($_POST['phonenumber'])){
            print($_POST['phonenumber']);
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Debe escrever o seu numero do telemovel.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                die();
            
        }
        
$body = [
    "key" => ''.$cart->id.'',
    "method" => "mbw",
    "type"	=> "sale",
    "value"	=> round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2),
    "currency"	=> $currency->iso_code,
    //"expiration_time" =>"2019-12-31 12:00",
    "capture" => [
        "transaction_key" => ''.$cart->id.'',
        "descriptive" => Configuration::get('PS_SHOP_NAME'),
        //"capture_date" => "2018-12-31",
		
    ],
    "customer" => [
        "name" => $this->context->customer->firstname.' '.$this->context->customer->lastname,
        "email" => $this->context->customer->email,
        "key" => ''.$cart->id.'',
        //"phone_indicative" => "+351",
        "phone" => $_POST['phonenumber'],
        //"fiscal_number" =>"PT123456789",
    ],
    /*"sdd_mandate" => [
    "name" => "Name Example",
    "email" => "sdd_email@example.com",
    "account_holder" => "Account Holder Example",
    "key" => ''.$cart->id.'',
    "iban" => "PT50002700000001234567833",
    "phone" => "911234567",
    "max_num_debits" =>"12",
    ],*/
];

$headers = [
    "AccountId: ".Configuration::get('EASYPAY_API_ID'),
    "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
    'Content-Type: application/json',
];
if(Configuration::get('EASYPAY_TESTES')==1){
    $URL_EP = "https://api.test.easypay.pt/2.0/single";
}else{
      $URL_EP = "https://api.prod.easypay.pt/2.0/single";
}
$curlOpts = [
    CURLOPT_URL => $URL_EP,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => 1,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers,
];

$curl = curl_init();
curl_setopt_array($curl, $curlOpts);
$response_body = curl_exec($curl);
curl_close($curl);
$response = json_decode($response_body, true);

$sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated) VALUES ('".$response['status']."', '".$response['id']."', '".$response['method']['type']."', '".$response['method']['status']."', '', '', '".$response['customer']['id']."', ".$cart->id.", NOW(), NOW())";

Db::getInstance()->execute($sql);


return $response;
    }
    

    /**
     * Processa os dados enviados pelo formulário de pagamento
     */
    public function postProcess()
    {
        /**
         * Get current cart object from session
         */
        $cart = $this->context->cart;
        $authorized = false;

        /**
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }
 
        /**
         * Verify if this payment module is authorized
         */

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'easypay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->l('This payment method is not available.'));
        }
 
        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);
 
        /**
         * Check if this is a valid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        /**
        *Validar pago com mutlibanco
        */
        $multibanco = $this->create_pago_simple();

        if($multibanco['status']!='ok'){

            if($multibanco['status']=='error'){
                print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>'.$multibanco['message'][0].'</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                die();
                header('Location: '.'/index.php?controller=Order'.urlencode($multibanco['message'][0]));
            }
        }
        
        $sql = "INSERT INTO "._DB_PREFIX_."ep_orders (method, id_cart, link, title) VALUES ('".$multibanco['method']['type']."', ".(int)$cart->id.", '', 'Pagar Agora: ')";
        Db::getInstance()->execute($sql);
        
        Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'mbway', // email template file to be use
            'Pagamento com MBWAY - EASYPAY', // email subject
            array(
                '{telemovel}' =>  number_format($_POST['phonenumber'], 0, ',', ' '),
                '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
            ),
            $this->context->customer->email, // receiver email address 
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL,
            NULL,
            _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
        );
        
  
 
        /**
         * Place the order
         */
        $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('EASYPAY_BMWAY_WAIT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'MBWAY - EasyPay',
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );

    print_r('easypay');
        /**
         * Redirect the customer to the order confirmation page
         */
        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key.'&method='.$multibanco['method']['type'].'&monto='.' '.(float) $this->context->cart->getOrderTotal(true, Cart::BOTH).'&url='.urlencode($multibanco['method']['url']));
    }


}