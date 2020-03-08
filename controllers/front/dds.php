<?php
ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayDdsModuleFrontController extends ModuleFrontController
{
    
    
    
    private function create_pago_simple(){
        $cart = $this->context->cart;
        $address = new Address(intval($cart->id_address_invoice));
        $currency = new CurrencyCore($cart->id_currency);
        
        $exp_module = date('Y-m-d', strtotime('2020-3-25'));
        $now_date = date('Y-m-d');
        if($now_date>$exp_module){
            echo '<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: A fase BETA do modulo EASYPAY expirou, deve contactar o administrador da loja.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>';
            die();
        }
        
        
        if(empty($_POST['account_holder']) or empty($_POST['iban']) or empty($_POST['telephone'])){
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Você deve preencher todos os campos.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                die();
            
        }


    

    
    
        
    //Validar si todos los articulos son de suscripcion    
    $productos_actuales = Context::getContext()->cart->getProducts();
    $cat_valido = 1;
    $productos_in = 0;
    foreach($productos_actuales as $product_act){
        if((int)$product_act['id_category_default'] != (int)Configuration::get('EASYPAY_CATEGORY_SUSCP')){
            $cat_valido = 0;
        }
        $productos_in = $productos_in + 1;
    }
    
    if($cat_valido!=1 && $productos_in!=1){
        
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Para pagar com este metodo, só deves adicionar produtos de suscrição ao carrinho.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                die();
            
    }
    
    

    
    
    
    
    //Comprovar si tiene FREQUENCY Y EXP TIME
    $features = $productos_actuales[0]['features'];
    $tiene_la_feature = 0;
    
    foreach($features as $feature){
        if((int)$feature['id_feature']==(int)Configuration::get('EASYPAY_FREQUENCY')){
            
            $expiration = $feature['id_feature_value'];
            $tiene_la_feature = 1;
            
        }
        
        
        
        $tiene_exp_time = 0;
        $exp_time = 0;
        if((int)$feature['id_feature']==(int)Configuration::get('EASYPAY_EXP_TIME')){
            
            $exp_time = $feature['id_feature_value'];
            $tiene_exp_time = 1;
            
        }
        
    }
    
    if($tiene_la_feature!=1){
        
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Para comprar este artigo deves contatar ao administrador. (Deve ser definido FREQUENCY no produto)</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                die();
            
    }
    
    if($tiene_exp_time!=1){
        
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Para comprar este artigo deves contatar ao administrador. (Deve ser definido EXP_TIME no produto)</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                die();
            
    }
    
    
    //GET FREQUENCY NAME
    $feature_value = new FeatureValue($expiration);
    $expiration_final =$feature_value->value[(int)Configuration::get('PS_LANG_DEFAULT')];
    $this->freq = $expiration_final;
    
    if($expiration_final=='1D' or $expiration_final=='1W'){
        
        $retries = 0;
        $this->retr = $retries;
    }
    else if($expiration_final=='2W'){
        $retries = 1;
        $this->retr = $retries;
    }
    else if($expiration_final=='1M'){
        $retries = 2;
        $this->retr = $retries;
    }
    else if($expiration_final=='2M'){
        $retries = 3;
        $this->retr = $retries;
    }
    else if($expiration_final=='3M'){
        $retries = 4;
        $this->retr = $retries;
    }
    else if($expiration_final=='4M'){
        $retries = 5;
        $this->retr = $retries;
    }
    else if($expiration_final=='6M'){
        $retries = 6;
        $this->retr = $retries;
    }
    else if($expiration_final=='1Y'){
        $retries = 10;
        $this->retr = $retries;
    }
    else{
        print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Para comprar este artigo deves contatar ao administrador. (Deve escrever um valor valido para FREQUENCY no produto)</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
            die();
    }
    
 
    
    
    //GET EXP NAME
    $feature_value = new FeatureValue($exp_time);
    $expiration_final2 =$feature_value->value[(int)Configuration::get('PS_LANG_DEFAULT')];
    

    if($expiration_final2=='1 mês'){
        
        $fecha = date('Y-m-d H:i');
        $nuevafecha = strtotime ( '+1 month' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        
        $this->exptime = $nuevafecha;
        $final_expdate = $nuevafecha;
    }
    else if($expiration_final2=='2 meses'){
        $fecha = date('Y-m-d H:i');
        $nuevafecha = strtotime ( '+2 month' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        
        $this->exptime = $nuevafecha;
        $final_expdate = $nuevafecha;
    }
    else if($expiration_final2=='3 meses'){
        $fecha = date('Y-m-d H:i');
        $nuevafecha = strtotime ( '+3 month' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        
        $this->exptime = $nuevafecha;
        $final_expdate = $nuevafecha;
    }
    else if($expiration_final2=='4 meses'){
        $fecha = date('Y-m-d H:i');
        $nuevafecha = strtotime ( '+4 month' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        
        $this->exptime = $nuevafecha;
        $final_expdate = $nuevafecha;
    }
    else if($expiration_final2=='5 meses'){
        $fecha = date('Y-m-d H:i');
        $nuevafecha = strtotime ( '+5 month' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        
        $this->exptime = $nuevafecha;
        $final_expdate = $nuevafecha;
    }
    else if($expiration_final2=='6 meses'){
        $fecha = date('Y-m-d H:i');
        $nuevafecha = strtotime ( '+6 month' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        
        $this->exptime = $nuevafecha;
        $final_expdate = $nuevafecha;
    }
    else if($expiration_final2=='1 ano'){
        $fecha = date('Y-m-d H:i');
        $nuevafecha = strtotime ( '+1 year' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        
        $this->exptime = $nuevafecha;
        $final_expdate = $nuevafecha;
    }
    else{
        print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Para comprar este artigo deves contatar ao administrador. (Deve escrever um valor valido para EXP_TIME no produto)</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
            die();
    }
    

    


$body = [
    "key" => ''.$cart->id.'',
    "retries" =>$retries,
    "capture_now" => true,
    "method" => "dd", //solo acepta dd y cc
    "type"	=> "sale",
    "value"	=> round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2), //precio, requerido
	"frequency"=> $expiration_final, //requerido, frequencia con la que se realizara el pago, los valores son "1D" "1W" "2W" "1M" "2M" "3M" "4M" "6M" "1Y", D significa dias, W semanas, M meses
    "currency"	=> $currency->iso_code,
	"start_time"	=> gmdate('Y-m-d H:i', strtotime("+5 min")),  //documentacion dice que es opcional y required al mismo tiempo, marca cuando empieza a cobrar
    "expiration_time" =>$final_expdate, // es opcional, indica cuando expira el pago, osea, cuando se acaba
    "capture" => [
        "transaction_key" => ''.$cart->id.'',
        "descriptive" => Configuration::get('PS_SHOP_NAME'), // esto es requerido aqui
       //"capture_date" => "2018-12-31",
		
    ],
    "customer" => [
        "name" => $address->firstname.' '.$address->lastname,
        "email" => $this->context->customer->email,
        "key" => ''.$cart->id.'',
        //"phone_indicative" => "+351",
        "phone" => $_POST['telephone'],
        //"fiscal_number" =>"PT123456789",
    ],
    "sdd_mandate" => [
    "name" => $address->firstname.' '.$address->lastname,
    "email" => $this->context->customer->email,
    "account_holder" => $_POST['account_holder'],
    "key" => ''.$cart->id.'',
    "iban" => $_POST['iban'],
    "phone" => $_POST['telephone'],
    ],
];




$headers = [
    "AccountId: ".Configuration::get('EASYPAY_API_ID'),
    "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
    'Content-Type: application/json',
];
if(Configuration::get('EASYPAY_TESTES')==1){
    $URL_EP = "https://api.test.easypay.pt/2.0/subscription";
}else{
    $URL_EP = "https://api.prod.easypay.pt/2.0/subscription";
}
$curlOpts = [
    CURLOPT_URL => $URL_EP,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => 1,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers,
];



$curl = curl_init();
curl_setopt_array($curl, $curlOpts);
$response_body = curl_exec($curl);
curl_close($curl);
$response = json_decode($response_body, true);



$sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated) VALUES ('".$response['status']."', '".$response['id']."', '".$response['method']['type']."', '".$response['method']['status']."', '".$response['method']['entity']."', '".$response['method']['reference']."', '".$response['customer']['id']."', ".$cart->id.", NOW(), NOW())";

Db::getInstance()->execute($sql);
$response['exp_time_trig'] = $final_expdate;
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
        
        
        
        
        
        
        
        
        
        $suplant = array("2", "5", "7", "0");
        Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'dds', // email template file to be use
            'Pagamento de subscrição com Debito Direto - EASYPAY', // email subject
            array(
                '{titular}' => $_POST['account_holder'],
                '{iban}' => str_replace($suplant, "X", $_POST['iban']),
                '{telemovel}' => $_POST['telephone'],
                '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
                '{EXPTIME}'=> date("d-m-Y H:i:s", strtotime($multibanco['exp_time_trig'])),
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
        $new_order = $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('EASYPAY_DD_WAIT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'Debito Direto - EasyPay',
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );
        
        
        $sql = "SELECT * FROM "._DB_PREFIX_."orders WHERE id_cart=".(int) $this->context->cart->id;
        $orderp = Db::getInstance()->executeS($sql);

        
        $sql = "INSERT INTO "._DB_PREFIX_."subscrip (
            id_susc,
            id_cart, 
            id_order, 
            dt_init, 
            dt_fin, 
            freq, 
            n_cob_ef, 
            n_cob_eftd, 
            val_subs, 
            val_cobrado, 
            dt_ult_cob, 
            estado_act, 
            respuesta, 
            id_ep
            ) VALUES (
            NULL, 
            ".(int) $this->context->cart->id.", 
            ".$orderp[0]['id_order'].", 
            NOW(), 
            '".$this->exptime."', 
            '".$this->freq."', 
            '".$this->retr."', 
            0, 
            ".$this->context->cart->getOrderTotal(true, Cart::BOTH).", 
            '0', 
            NOW(), 
            'ACTIVO', 
            '".json_encode($multibanco)."', 
            '".$multibanco['id']."');";
	    $subs = Db::getInstance()->execute($sql);

        /**
         * Redirect the customer to the order confirmation page
         */
        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key.'&qtt='.$_POST['susc'].'&method=dds&monto='.' '.(float) $this->context->cart->getOrderTotal(true, Cart::BOTH).'&url='.urlencode($multibanco['method']['url']));
    }


}