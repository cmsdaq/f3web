<?php 
//include 'jsonDecode.php';
$run = $_GET["run"];
$stream = strtolower($_GET["stream"]);

$crl = curl_init();
$timeout = 5;
$hostname = php_uname('n');
$url = 'http://'.$hostname.':9200/runindex_cdaq_read/stream-hist/_search?size=1';
$data = '{"query":{"filtered":{"query":{"term":{"stream":"'.$stream.'"}},"filter":{"has_parent":{"parent_type":"run","query":{"term":{"runNumber":'.$run.'}}}}}},"sort":{"ls":"desc"}}';

$crl = curl_init();
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
$ret = curl_exec($crl);

curl_close($crl);
//echo $ret;
$res=json_decode($ret,true);
echo $res["hits"]["hits"][0]["_source"]["ls"].','.$res["hits"]["hits"][0]["_source"]["out"]

?>
