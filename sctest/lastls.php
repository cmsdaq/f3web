<?php 

include 'urunfunc.php';
$run=urunfunc();

$crl = curl_init();
$timeout = 5;
$hostname = php_uname('n');
$url = 'http://'.$hostname.':9200/runindex_cdaq_read/eols/_search?size=10';
$data = '{"sort":{"_timestamp":"desc"},"query":{"term":{"_parent":"'.$run.'"}}}';
$crl = curl_init();
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
$ret = curl_exec($crl);
curl_close($crl);
$res=jsonDecode($ret);
$max = 0;
foreach ($res["hits"]["hits"] as $hit){  
  if($max < $hit["_source"]["ls"]){
    $max = $hit["_source"]["ls"];
  }
}
echo $max
?>
