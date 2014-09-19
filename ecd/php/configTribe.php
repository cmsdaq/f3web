<?php
header("Content-Type: application/json");

//$runindex = "runindex_cdaq_read";
//$boxinfoindex = "boxinfo_cdaq_read";
$hostname = "tu-02.cern.ch";




function esPut ($document,$index) {
    $timeout = 5;
    //$hostname = php_uname('n');

    $url = 'http://'.$hostname.':9200/'.$index;

    $crl = curl_init();
    curl_setopt ($crl, CURLOPT_URL,$url);
    curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt ($crl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt ($crl, CURLOPT_POSTFIELDS, $document);
    $ret = curl_exec($crl);
    curl_close($crl);
    //var_dump($ret);
    return $ret;
};

function esDel($index)
{
    $timeout = 5;
    //$hostname = php_uname('n');

    $url = 'http://'.$hostname.':9200/'.$index;
    
    $crl = curl_init();
    curl_setopt($crl, CURLOPT_URL,$url);
    curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "DELETE");
    $ret = curl_exec($crl);
//    $httpCode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
    curl_close($crl);

    return $ret;
}

function esQuery ($stringQuery,$index) {
    $timeout = 5;
    $hostname = "tu-02.cern.ch";
    //$hostname = php_uname('n');
    //var_dump($stringQuery);
    //var_dump($index);

    if ($stringQuery != NULL && end(explode( '_', $index )) != "count") 
        { $index = $index."/_search?preference=_replica_first"; };
    //var_dump($index);

    $url = 'http://'.$hostname.':9200/'.$index;

    //echo $url;

    $crl = curl_init();
    curl_setopt ($crl, CURLOPT_URL,$url);
    curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);

    if ($stringQuery != NULL){ curl_setopt ($crl, CURLOPT_POSTFIELDS, $stringQuery); };
    
    $ret = curl_exec($crl);
    curl_close($crl);
    //var_dump($ret);
    return $ret;
};



function getStringQuery($query){
    $stringQuery = file_get_contents("../json/".$query.".json");
    return $stringQuery;
};

function percColor($percent){
    $color = "";
    if($percent == 100){$color = "green";}
        else if ($percent >50) {$color = "orange";}
            else if ($percent < 50) {$color = "red";}
    return $color;
}

function search($array, $key, $value)
{
    $results = array();

    if (is_array($array)) {
        if (isset($array[$key]) && $array[$key] == $value) {
            $results[] = $array;
        }

        foreach ($array as $subarray) {
            $results = array_merge($results, search($subarray, $key, $value));
        }
    }

    return $results;
}

?>