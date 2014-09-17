<?php 
include 'jsonDecode.php';
$run = $_GET["run"];
$crl = curl_init();
$timeout = 5;
$hostname = php_uname('n');
$url = 'http://'.$hostname.':9200/runindex_cdaq_read/stream-hist/_search?size=0';
$data = '{"query":{"term":{"_parent":'.$run.'}},"sort":{"_timestamp":"desc"},"facets":{"streams":{"terms":{"field":"stream","order":"term"}}}}';
$crl = curl_init();
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
$ret = curl_exec($crl);
curl_close($crl);
$res=jsonDecode($ret);

foreach ($res["facets"]["streams"]["terms"] as $hit){  
  echo strtoupper($hit["term"]).' ';
}

?>
