<?php



$body = [
    "key: TfuelJNDPAAGO19",
    "secret: c1a24CqsNe16hMhICne2F1Z5shsK5RIXZc7HQ1UZAwOtslRdmw7WE2AKtaFzXQNx",
];

$headers = [
    "Content-Type: application/json"
];

$curlOpts = [
    CURLOPT_URL => "https://janaodaparaabastecer.vost.pt/api/v1",
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
print json_encode($response);


 ?>