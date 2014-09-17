<?php 
include 'jsonDecode.php';
$run = $_GET["run"];
$crl = curl_init();
$timeout = 5;
$hostname = php_uname('n');
$url = 'http://'.$hostname.':9200/runindex_cdaq_read/pathlegend/_search?size=1';
$data = '{"query":{"filtered":{"query":{"query_string" : {"query":"_parent:'.$run.'"}}}}}';
$crl = curl_init();
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
$ret = curl_exec($crl);
curl_close($crl);
$res=jsonDecode($ret);
echo $res["hits"]["hits"][0]["_source"]["names"];
?>
