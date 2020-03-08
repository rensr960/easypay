<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
$respuesta = file_get_contents('php://input');
$respuesta = json_decode($respuesta, true);

$sql = "INSERT INTO notifications (text) VALUES ('".$respuesta['id'].$respuesta['value'].json_encode($respuesta)."')";
$retornar = Db::getInstance()->execute($sql);   



if($respuesta['status']=='success' && $respuesta['type']!='subscription_capture'){
    /*CHANGE ORDER STATUS*/
    $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key']))); 
    $history = new OrderHistory();
    $history->id_order = (int)$objOrder->id;
    $history->changeIdOrderState(Configuration::get('EASYPAY_APROVED'), (int)$objOrder->id);
    $history->add(); 
    
    $sql = 'UPDATE '._DB_PREFIX_.'ep_orders SET status="ok", messagem="'.$respuesta['messages'][0].'" WHERE id_cart='.$respuesta['key'];
    $excel = Db::getInstance()->execute($sql);
    
    
    
    
    $sql2 = "SELECT "._DB_PREFIX_."ep_orders.*, "._DB_PREFIX_."orders.id_order idorder, "._DB_PREFIX_."orders.reference, "._DB_PREFIX_."orders.id_customer FROM "._DB_PREFIX_."ep_orders INNER JOIN "._DB_PREFIX_."orders ON pss_orders.id_cart = "._DB_PREFIX_."ep_orders.id_cart WHERE "._DB_PREFIX_."ep_orders.id_cart=".$respuesta['key']."";
    $rsp = Db::getInstance()->executeS($sql2);
    $customer = new Customer((int)$rsp[0]['id_customer']);
    
    
    
    $metodo_pagamento = $sql2;
    
    if($rsp[0]['method']=='cc'){
        $metodo_pagamento = "VISA";
    }
    else if($rsp[0]['method']=='mb'){
        $metodo_pagamento = "Multibanco";
    }
    else if($rsp[0]['method']=='bb'){
        $metodo_pagamento = "Boleto Bancario";
    }
    else if($rsp[0]['method']=='dd'){
        $metodo_pagamento = "Debito Direto";
    }
    else if($rsp[0]['method']=='mbw'){
        $metodo_pagamento = "MBWAY";
    }
    
    
    Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'pagamento', // email template file to be use
            'Pagamento com '.$metodo_pagamento.' - EASYPAY', // email subject
            array(
                '{id_order}' => $rsp[0]['idorder'],
                '{referencia}' => $rsp[0]['reference'],
                '{pagamento}' =>  $metodo_pagamento,
                '{order_details}' => _PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-detail&id_order='.$rsp[0]['idorder'],
                '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
            ),
            $customer->email, // receiver email address 
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL,
            NULL,
            _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
    );
    
    
    
    
    
    
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
    
    $url = $URL_EP . $respuesta['id'];
    $curlOpts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, $curlOpts);
    $response_body = curl_exec($curl);
    curl_close($curl);
    $response2 = json_decode($response_body, true);
    print_r($response2);
    



    
}
else if($respuesta['status']=='success' && $respuesta['type']=='subscription_capture'){
    
    
    
        /*CHANGE ORDER STATUS*/
    $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key']))); 
    $history = new OrderHistory();
    $history->id_order = (int)$objOrder->id;
    $history->changeIdOrderState(Configuration::get('EASYPAY_SUBSCRICAO_PAID'), (int)$objOrder->id);
    $history->add();
    
    $sql = 'UPDATE '._DB_PREFIX_.'ep_orders SET status="ok", messagem="'.$respuesta['messages'][0].'" WHERE id_cart='.$respuesta['key'];
    $excel = Db::getInstance()->execute($sql);
    
    
    
    
    $sql2 = "SELECT "._DB_PREFIX_."ep_orders.*, "._DB_PREFIX_."orders.id_order idorder, "._DB_PREFIX_."orders.reference, "._DB_PREFIX_."orders.id_customer FROM "._DB_PREFIX_."ep_orders INNER JOIN "._DB_PREFIX_."orders ON pss_orders.id_cart = "._DB_PREFIX_."ep_orders.id_cart WHERE "._DB_PREFIX_."ep_orders.id_cart=".$respuesta['key']."";
    $rsp = Db::getInstance()->executeS($sql2);
    $customer = new Customer((int)$rsp[0]['id_customer']);
    
    
    
    

    
    
    
    $metodo_pagamento = $sql2;
    
    if($rsp[0]['method']=='cc'){
        $metodo_pagamento = "VISA - Subscrição";
    }
    else if($rsp[0]['method']=='mb'){
        $metodo_pagamento = "Multibanco - Subscrição";
    }
    else if($rsp[0]['method']=='bb'){
        $metodo_pagamento = "Boleto Bancario - Subscrição";
    }
    else if($rsp[0]['method']=='dd'){
        $metodo_pagamento = "Debito Direto - Subscrição";
    }
    else if($rsp[0]['method']=='mbw'){
        $metodo_pagamento = "MBWAY - Subscrição";
    }
    
    
    /*Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'pagamento_sub', // email template file to be use
            'Pagamento com '.$metodo_pagamento.' - EASYPAY', // email subject
            array(
                '{id_order}' => $rsp[0]['idorder'],
                '{referencia}' => $rsp[0]['reference'],
                '{pagamento}' =>  $metodo_pagamento,
                '{order_details}' => _PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-detail&id_order='.$rsp[0]['idorder'],
                '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
            ),
            $customer->email, // receiver email address 
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL,
            NULL,
            _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
    );*/
    
    
    
    
    
    
    
    //CAPTURAR EL PAGO Y ENVIAR RESPUESTA
    $headers = [
        "AccountId: ".Configuration::get('EASYPAY_API_ID'),
        "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
        'Content-Type: application/json',
    ];
    
    if(Configuration::get('EASYPAY_TESTES')==1){
        $URL_EP = "https://api.test.easypay.pt/2.0/subscription/";
    }else{
        $URL_EP = "https://api.prod.easypay.pt/2.0/subscription/";
    }
    
    $url = $URL_EP . $respuesta['id'];
    $curlOpts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, $curlOpts);
    $response_body = curl_exec($curl);
    curl_close($curl);
    $response2 = json_decode($response_body, true);
    print_r($response2);
    

    $lista_de_pagamentos = '<table style="width:100%"><tr><th>Nº Fatura</th><th>VALOR</th><th>DATA</th></tr>';
    
    $n_contador = count($response2['transactions']);
    $contador = 1;
    $alumbrar = '';
    $valor_final = '';
    $data_final = '';
    foreach ($response2['transactions'] as $transaction){
        if($n_contador == $contador){
            $alumbrar = 'background-color: orange;';
        }
        $lista_de_pagamentos .= '<tr style="'.$alumbrar.'"><td style="text-align: center;">'.$transaction['document_number'].'</td><td style="text-align: center;">'.round($transaction['values']['paid'],2).' '.$response2['currency'].'</td><td style="text-align: center;">'.$transaction['transfer_date'].'</td></tr>';
        $valor_final = round($transaction['values']['paid'],2);
        $data_final = $transaction['transfer_date'];
        $contador = $contador + 1;
    }
    $lista_de_pagamentos .= '</table>';
    
    
    //GET PRODUCTS FROM ORDER
    $sql3 = "SELECT * FROM "._DB_PREFIX_."orders inner join "._DB_PREFIX_."order_detail ON "._DB_PREFIX_."orders.id_order = "._DB_PREFIX_."order_detail.id_order WHERE "._DB_PREFIX_."orders.reference='".$rsp[0]['reference']."'";
    $produtos_order = Db::getInstance()->executeS($sql3);
    
    $lista_de_produtos = '<table style="width:100%"><tr><th>PRODUTOS NESTA SUBSCRIÇÃO</th><th>VALOR</th></tr>';
    foreach($produtos_order as $mproduto){
        $lista_de_produtos .= '<tr><td style="text-align: center"><b>'.$mproduto['product_name'].'<b></td><td style="text-align: center">'.Tools::displayPrice(round($mproduto['total_price_tax_incl'],2)).'</td></tr>';
    }

    $lista_de_produtos .= '</table>';
    
    $get_id_order = "SELECT * FROM "._DB_PREFIX_."orders WHERE reference='".$rsp[0]['reference']."'";
    $orden_actual = Db::getInstance()->executeS($get_id_order);
    
    $actualizar_table = "UPDATE "._DB_PREFIX_."subscrip SET respuesta='".json_encode($response2)."',
    estado_act = 'OK',
    dt_ult_cob = NOW(),
    n_cob_eftd = n_cob_eftd + 1,
    val_cobrado = val_cobrado + ".$valor_final." 
    WHERE id_order=".$orden_actual[0]['id_order']."";
    $atualizar = Db::getInstance()->executeS($actualizar_table);
    

    

    Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'pagamentosub', // email template file to be use
            "Pagamento com ".$metodo_pagamento."", // email subject
            array(
                '{id_order}' => $rsp[0]['reference'],
                '{customer_name}' => $rsp[0]['reference'],
                '{lista_de_produtos}' => $lista_de_produtos,
                '{Transactions}' => $response2['transactions'],
                '{currency}' => $response2['currency'],
                '{tabla}' => $lista_de_pagamentos,
                '{precos}' => $valor_final,
                '{data_final}' => $data_final,
                '{transacciones}' => json_encode($response2['transactions']),
                '{respuesta}' => json_encode($response2['currency']),
                '{cobros}' => json_encode($response2['transactions']),
                '{order_details}' => _PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-detail&id_order='.$rsp[0]['idorder'],
                '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
            ),
            $customer->email, // receiver email address 
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL,
            NULL,
            _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
    );
    
    
    
}
else{
    
     /*CHANGE ORDER STATUS*/
    $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key']))); 
    $history = new OrderHistory();
    $history->id_order = (int)$objOrder->id;
    $history->changeIdOrderState(Configuration::get('EASYPAY_FAILED'), (int)$objOrder->id);
    $history->add();
    $sql = 'UPDATE '._DB_PREFIX_.'ep_orders SET status="error", messagem="'.$respuesta['messages'][0].'" WHERE id_cart='.$respuesta['key'];
    $exec = Db::getInstance()->execute($sql);
    
    $id = $respuesta['id']; //este es el id del pagamento, de acuerdo a docs de easypay.
    
    
    $sql2 = "SELECT "._DB_PREFIX_."ep_orders.*, "._DB_PREFIX_."orders.id_order idorder, "._DB_PREFIX_."orders.reference, "._DB_PREFIX_."orders.id_customer FROM "._DB_PREFIX_."ep_orders INNER JOIN "._DB_PREFIX_."orders ON pss_orders.id_cart = "._DB_PREFIX_."ep_orders.id_cart WHERE "._DB_PREFIX_."ep_orders.id_cart=".$respuesta['key']."";
    $rsp = Db::getInstance()->executeS($sql2);
    $customer = new Customer((int)$rsp[0]['id_customer']);
    
    $metodo_pagamento = '';
    
    if($rsp[0]['method']=='cc'){
        $metodo_pagamento = "VISA";
    }
    else if($rsp[0]['method']=='mb'){
        $metodo_pagamento = "Multibanco";
    }
    else if($rsp[0]['method']=='bb'){
        $metodo_pagamento = "Boleto Bancario";
    }
    else if($rsp[0]['method']=='dd'){
        $metodo_pagamento = "Debito Direto";
    }
    else if($rsp[0]['method']=='mbw'){
        $metodo_pagamento = "MBWAY";
    }
    
    
    Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'pagamentoerr', // email template file to be use
            'Pagamento com '.$metodo_pagamento.' - EASYPAY', // email subject
            array(
                '{message}' => $respuesta['messages'][0],
                '{id_order}' => $rsp[0]['idorder'],
                '{referencia}' => $rsp[0]['reference'],
                '{pagamento}' =>  $metodo_pagamento,
                '{order_details}' => _PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-detail&id_order='.$rsp[0]['idorder'],
                '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
            ),
            $customer->email, // receiver email address 
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL,
            NULL,
            _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
        );

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
    
    $url = $URL_EP . $id;
    $curlOpts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, $curlOpts);
    $response_body = curl_exec($curl);
    curl_close($curl);
    $response2 = json_decode($response_body, true);
    print_r($response2);
}
    








?>