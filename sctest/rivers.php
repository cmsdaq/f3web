<?php 
include 'jsonDecode.php';
$run = $_GET["run"];
$crl = curl_init();
$timeout = 5;
$hostname = php_uname('n');
$url = 'http://'.$hostname.':9200/_river/runriver_'.$run.'/_status';
$crl = curl_init();
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
$ret = curl_exec($crl);

curl_close($crl);
echo $ret;
?>
