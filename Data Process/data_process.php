<?php
include('database_connection.php');
ob_start();
session_start();

//Fetch Ticker value to be converted from URL
$ticker = $_GET['ticker'];
$market = '';

// Get UNIX timestamp of last execution
$query_ts   = "SELECT max(UNIX_TIMESTAMP(TIMESTAMP)) FROM assgn WHERE ticker=" . $ticker;
$time_stamp = mysqli_query($dbc, $query_ts);
//echo var_export($time_stamp);
$ts         = mysqli_fetch_array($time_stamp, MYSQLI_ASSOC);
//echo var_export($ts);

if (isset($ts['max(UNIX_TIMESTAMP(TIMESTAMP))'])) {
    echo "Last execute Time is " . $ts['max(UNIX_TIMESTAMP(TIMESTAMP))'];
    $query = "SELECT * from assgn where ticker=1 and UNIX_TIMESTAMP(TIMESTAMP)>" . $ts['max(UNIX_TIMESTAMP(TIMESTAMP))'] . " order by market ";
} else
    $query = "SELECT * from assgn where ticker=1 order by market ";


// Get Data
$result = mysqli_query($dbc, $query);

$query = "INSERT INTO `assgn`(`MARKET`,`TIMESTAMP`, `OPEN`, `CLOSE`, `HIGH`, `LOW`, `VOLUME`,`TICKER`) VALUES ";


function convert_frame($input, $period)
{
    
    $inputSize = sizeof($input);
    
    $intervalCount = floor($inputSize / $period);
    
    
    for ($i = 0; $i < $intervalCount; $i++) {
        $opens   = array();
        $highs   = array();
        $lows    = array();
        $closes  = array();
        $volumes = array();
        
        for ($j = 0; $j < $period; $j++) {
            $elem = $input[($period * i) + j];
            
            $o = $elem['OPEN'];
            $h = $elem['HIGH'];
            $l = $elem['LOW'];
            $c = $elem['CLOSE'];
            $v = $elem['VOLUME'];
            
            
            if ($o > 0 && $o != '') {
                $opens[] = $o;
            }
            if ($h > 0 && $h != '') {
                $highs[] = $h;
            }
            if ($l > 0 && $l != '') {
                $lows[] = $l;
            }
            if ($c > 0 && $c != '') {
                $closes[] = $c;
            }
            if ($v > 0 && $v != '') {
                $volumes[] = $v;
            }
        }
        
        $lastElem = $input[$period * $i + ($period - 1)];
        
        $output = array(
            "MARKET" => $elem['MARKET'],
            "TIME" => $elem['TIMESTAMP'],
            "OPEN" => $opens[0],
            "LOW" => min($lows),
            "HIGH" => max($highs),
            "CLOSE" => $closes[0],
            "VOLUME" => array_sum($volumes)
        );
        
        $GLOBALS['query'] = $GLOBALS['query'] . "('" . $output['MARKET'] . "','" . $output['TIME'] . "'," . $output['OPEN'] . "," . $output['LOW'] . "," . $output['HIGH'] . "," . $output['CLOSE'] . "," . $output['VOLUME'] . "," . $period . "), ";
    }
}

while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $all_market_data[] = $row;
}


$data      = array();
$market    = $flag[0]['MARKET'];
$i         = 0;
$data_size = sizeof($all_market_data);

// For Each market convert the 1m tick data to $ticker data
foreach ($all_market_data as $line) {
    $i++;
    $data[] = $line;
    if (($data_size == $i) || ($line['MARKET'] != $all_market_data[$i]['MARKET'])) {
        $market = $line['MARKET'];
        convert_frame($data, $ticker);
        unset($data);
        $data = array();
    }
}
$query = substr($query, 0, -2);

// Store the data
if (mysqli_query($dbc, $query))
    echo " Database Updated Successfully";
else
    echo "Error Occured";

mysqli_close($dbc);
?>