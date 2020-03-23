<?php 

require __DIR__ . '/vendor/autoload.php';

$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
$url = $base_url . $_SERVER["REQUEST_URI"];
$url_components = parse_url($url); 

parse_str($url_components['query'], $params); 

if (!$params['SpreadSheetId'] || $params['SpreadSheetId'] == ""){
    echo "You should input SpreadSheetId";
    exit;
}
if (!$params['textbelt_apikey'] || $params['textbelt_apikey'] == ""){
    echo "You should input textbelt_apikey";
    exit;
}
if (!$params['sheetTabName'] || $params['sheetTabName'] == ""){
    echo "You should input sheetTabName";
    exit;
}
if (!$params['messageContent'] || $params['messageContent'] == ""){
    echo "You should input messageContent";
    exit;
}



$client = new \Google_Client();
$client->setApplicationName('Google Sheets and PHP');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__.'/credentials.json');
$service = new Google_Service_Sheets($client);
$spreadsheetId = $params['SpreadSheetId'];


$range = $params['sheetTabName'];
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

echo "now sending sms..  it will take some time... wait...\n\r";

foreach ($values as $cols){
    foreach($cols as $phone){
        $ch = curl_init('https://textbelt.com/text');
        $data = array(
            'phone' => $phone,
            'message' => $params['messageContent'],
            'key' => $params['textbelt_apikey'],
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $response = json_decode($response, true);
        
        if ($response['success'] == false){
            echo 'send sms failed to '.$phone."\n\r";
        }
        curl_close($ch);
    }
}
echo "send finished.  thanks.  \n\r";