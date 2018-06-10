<?php

include('database_connection.php');
include('./httpful.phar');

error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

set_time_limit(40);

$query1 = "INSERT INTO `assgn`(`MARKET`, `OPEN`, `CLOSE`, `HIGH`, `LOW`, `VOLUME`) VALUES ";


function call_api($market)
{
    $url_get_tick       = "https://bittrex.com/Api/v2.0/pub/market/GetLatestTick";
    $url_get_tick_param = "marketName=%s&tickInterval=oneMin&_=%s";
    
    $url_get_ticker_fm = sprintf($url_get_tick_param, $market, '1528303047000');
    $url_tick          = $url_get_tick . '?' . $url_get_ticker_fm;
    $resp_http         = \Httpful\Request::get($url_tick)->send();
    
    $ticker = json_decode($resp_http, true);
    
    $GLOBALS['query1'] = $GLOBALS['query1'] . "('" . $market . "'," . $ticker['result'][0]['O'] . "," . $ticker['result'][0]['C'] . "," . $ticker['result'][0]['H'] . "," . $ticker['result'][0]['L'] . "," . $ticker['result'][0]['V'] . "), ";
    
}


$url_all_market     = "https://bittrex.com/api/v1.1/public/getmarkets";
$url_get_tick       = "https://bittrex.com/Api/v2.0/pub/market/GetLatestTick";
$url_get_tick_param = "marketName=%s&tickInterval=oneMin&_=%s";

$curl = curl_init();


curl_setopt_array($curl, array(
    CURLOPT_URL => $url_all_market,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
    )
));


$response = curl_exec($curl);

$err = curl_error($curl);

$market_list = json_decode($response, true);

$resp_http = \Httpful\Request::get($url_all_market)->send();

$i = 0;

foreach ($market_list['result'] as $market) {
    
    $url_get_ticker_fm = sprintf($url_get_tick_param, $market['MarketName'], time());
    {
        $ticker_api = $url_get_tick . '?' . $url_get_ticker_fm;
        
        try {
            call_api($market['MarketName']);
        }
        catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
        
        $key = $market['MarketName'];
        
        $i = $i + 1;
        
        if ($i == 80)
            break;
    }
    
}

$query1 = substr($query1, 0, -2);

$result = mysqli_query($dbc, $query1);

if (!$result) {
    //If the QUery Failed 
    echo "Error Occured";
} else {
    //Updated Successfully
    $arr = array(
        'code' => 200
    );
    echo "Database updated successfully";
}

mysqli_close($dbc);

?>