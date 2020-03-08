<?
include_once('../../config/config.inc.php');
include_once('../../init.php');
$id = $_GET['id_sub']; //este es el id del pagamento, de acuerdo a docs de easypay, DEBES hacer este request despues de la notificacion generica para confirmar

$body = [
    "status" => "inactive",

];

$headers = [
    "AccountId: 56e7a6b4-41ba-4d3e-8578-2760563841bb",
    "ApiKey: 16d0899a-e055-496d-8d03-afe7139bff0a",
    'Content-Type: application/json',
];

    if(Configuration::get('EASYPAY_TESTES')==1){
        $url = "https://api.test.easypay.pt/2.0/subscription/" . $id;
    }else{
        $url = "https://api.prod.easypay.pt/2.0/subscription/" . $id;
    }
    

$curlOpts = [
    CURLOPT_URL => $url,
	CURLOPT_CUSTOMREQUEST => "PATCH",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers,
];

$curl = curl_init();
curl_setopt_array($curl, $curlOpts);
$response_body = curl_exec($curl);
curl_close($curl);
$response = json_decode($response_body, true);


$esql = "UPDATE "._DB_PREFIX_."subscrip
    SET estado_act='INACTIVE'
    WHERE id_ep = '".$id."'";
Db::getInstance()->execute($esql);

$eqql = "SELECT * FROM "._DB_PREFIX_."subscrip WHERE id_ep='".$id."'";
$registro = Db::getInstance()->executeS($eqql);


$objOrder = new Order(Order::getOrderByCartId((int)($registro[0]['id_cart']))); 
$history = new OrderHistory();
$history->id_order = (int)$objOrder->id;
$history->changeIdOrderState(Configuration::get('EASYPAY_SUBSCRICAO_CANCEL'), (int)$objOrder->id);
$history->add();

echo $_GET['url_v'];
die();

if(isset($_GET['url_v'])){
    header('Location: '.$_GET['url_v']);
}




    
?>