<?php

$body = [
    "key" => "Key Example",
    "method" => "mb",
    "type"	=> "sale",
    "value"	=> floatval(17.50),
    "currency"	=> "EUR",
    "expiration_time" =>"2019-12-31 12:00",
    "capture" => [
        "transaction_key" => "Transaction Key Example",
        "descriptive" => "Descriptive Example",
        "capture_date" => "2018-12-31",
		
    ],
    "customer" => [
        "name" => "Customer Example",
        "email" => "jhon.sanchez@trigenius.pt",
        "key" => "333333333",
        "phone_indicative" => "+351",
        "phone" => "911234567",
        "fiscal_number" =>"PT123456789",
    ],
    "sdd_mandate" => [
    "name" => "Name Example",
    "email" => "sdd_email@example.com",
    "account_holder" => "Account Holder Example",
    "key" => "SDD Key Example",
    "iban" => "PT50002700000001234567833",
    "phone" => "911234567",
    "max_num_debits" =>"12",
    ],
];

$headers = [
    "AccountId: 48267de3-2f31-4c03-8336-515e03a66d27",
    "ApiKey: 3bbeeeb0-dbdc-484c-a5c5-c86b4251def9",
    'Content-Type: application/json',
];

$curlOpts = [
    CURLOPT_URL => "https://api.test.easypay.pt/2.0/single",
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
print_r($response);


 ?>