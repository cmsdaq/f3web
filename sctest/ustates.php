<?php 
include 'jsonDecode.php';
$crl = curl_init();
$timeout = 5;
$hostname = php_uname('n');
$url = 'http://'.$hostname.':9200/runindex_cdaq_read/state-hist/_search?size=1';
$data = '{"sort":{"_timestamp":"desc"}}';
$crl = curl_init();
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
$ret = curl_exec($crl);
curl_close($crl);
$res=jsonDecode($ret);
$time=$res["hits"]["hits"][0]["sort"][0];
$what=$res["hits"]["hits"][0]["_source"]["hmicro"]["entries"];
$time=gmdate("Y-m-d\TH:i:s\Z", $time/1000);
$s=$time." ";
ksort($what);
foreach ($what as $key => $value){
  $s .= " ".$value['key'].",".$value['count'];
}
echo $s;
echo "\n";
?>
