<?php 
include 'jsonDecode.php';
$crl = curl_init();
$hostname = 'es-tribe';
$url = 'http://'.$hostname.':9200/_nodes/bu*/_none';
$data = '';
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
$ret = curl_exec($crl);

curl_close($crl);

$res=jsonDecode($ret);
$what=$res["nodes"];
$hosts=array();
foreach ($what as $key => $value){
  $hosts[]=$value["host"];
}
$crl = curl_init();
$hostname = php_uname('n');
$url = 'http://'.$hostname.':9200/runindex_prod/boxinfo/_search';
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
$summary=array();

foreach ($hosts as $i){
  $stats=array();  
  $data='{"query":{"query_string":{"query":"_id:'.$i.'*"}},"sort":{"_timestamp":"desc"},"size":1}';
  curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
  $ret = curl_exec($crl);
  $res=jsonDecode($ret);
  //  echo $ret;
  $stats['rdisk']=$res["hits"]["hits"][0]["_source"]["usedRamdisk"]/$res["hits"]["hits"][0]["_source"]["totalRamdisk"];
  $stats['odisk']=$res["hits"]["hits"][0]["_source"]["usedOutput"]/$res["hits"]["hits"][0]["_source"]["totalOutput"];
  $summary[$i]=$stats;
}
curl_close($crl);
foreach ($summary as $key => $value){
  echo $key.",".$value["rdisk"].",".$value["odisk"]." ";
}


?>
