<?php 
include 'jsonDecode.php';
$response= array();
header("Content-Type: application/json");
$buhosts=array();
$fuhosts=array();

date_default_timezone_set("UTC");
$crl = curl_init();

/* get the health of the tribe server (this will be the one server that the request hits 
   so it does not give information about all other servers) */
$hostname = 'es-tribe';
$url = 'http://'.$hostname.':9200/_cluster/health'; 
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
$response["tribe_server"] = json_decode(curl_exec($crl));

$url = 'http://'.$hostname.':9200/_nodes/fu*/stats/jvm'; 
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
$res = jsonDecode(curl_exec($crl));
$fustats = $res["nodes"];

$fustat = array();
foreach ($fustats as $key => $value){
  $fustat[$value["host"]]=array();
  $fustat[$value["host"]]["heapu"] = 
    $value["jvm"]["mem"]["heap_used_percent"];
}
/* get the list of all bu nodes*/
$url = 'http://'.$hostname.':9200/_nodes/bu*/_none';
$data = '';
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
header('Content-Type: application/json');
$ret = curl_exec($crl);

/* is this necessary ? */
curl_close($crl);

$res=jsonDecode($ret);
$what=$res["nodes"];


$response["appliance_clusters"]=array();

$crl = curl_init();

/* loop on all bu hosts and fill information about appliance, then get the name of all fus in an appliance (from the ES point of view) */

foreach ($what as $key => $value){
  $buhosts[]=$value["host"];
  $fuhosts[$value["host"]]=array();
  $url = 'http://'.$value["host"].':9200/_cluster/health';
  curl_setopt ($crl, CURLOPT_URL,$url);
  curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
  $ret = curl_exec($crl);
  $res=jsonDecode($ret);
  $response["appliance_clusters"][$value["host"]]["status"]=$res["status"];
  $response["appliance_clusters"][$value["host"]]["active_primary_shards"]=$res["active_primary_shards"];
  $url = 'http://'.$value["host"].':9200/_nodes/fu*/_none';
  curl_setopt ($crl, CURLOPT_URL,$url);
  curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
  $ret = curl_exec($crl);
  $res=jsonDecode($ret);
  $fus=$res["nodes"];
  foreach ($fus as $fu){ 
    $fuhosts[$value["host"]][$fu["host"]]=array();
    $fuhosts[$value["host"]][$fu["host"]]["heap"]=$fustat[$fu["host"]]["heapu"];
  }
  $response["appliance_clusters"][$value["host"]]["fus"]=$fuhosts[$value["host"]];
}
/* this should sort the associative array to give nodes in lexicographic order*/
ksort($buhosts);

curl_close($crl);

$crl = curl_init();
$hostname = php_uname('n'); // set the host to local to get health of central server
$url = 'http://'.$hostname.':9200/_cluster/health';
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
$response["central_server"]=jsonDecode(curl_exec($crl));
$response["central_server"]["query_time"]=date_create()->format('D d M Y H:i:s e');
/*loop on all bu hosts and get the box info*/
$url = 'http://'.$hostname.':9200/runindex_prod/boxinfo/_search';
curl_setopt ($crl, CURLOPT_URL,$url);
curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
$summary=array();
foreach ($buhosts as $i){
  $stats=array();  
  $data='{"query":{"query_string":{"query":"_id:'.$i.'*"}},"sort":{"_timestamp":"desc"},"size":1}';
  curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
  $now = date_create();
  $ret = curl_exec($crl);
  $res=jsonDecode($ret);

  /*format the response*/
  $response["appliance_clusters"][$i]['rdisk']=$res["hits"]["hits"][0]["_source"]["usedRamdisk"]/$res["hits"]["hits"][0]["_source"]["totalRamdisk"];
  $response["appliance_clusters"][$i]['odisk']=$res["hits"]["hits"][0]["_source"]["usedOutput"]/$res["hits"]["hits"][0]["_source"]["totalOutput"];
  $response["appliance_clusters"][$i]['active_runs']=$res["hits"]["hits"][0]["_source"]["activeRuns"];
  $response["appliance_clusters"][$i]['idle']=0;
  $response["appliance_clusters"][$i]['online']=0;
  $response["appliance_clusters"][$i]['uldisk']=0;
  $response["appliance_clusters"][$i]['tldisk']=0;
  $response["appliance_clusters"][$i]['stale']=array();
  $response["appliance_clusters"][$i]['dead']=array();
  
  $last_updated = date_create($res["hits"]["hits"][0]["_source"]["fm_date"]);
  $age=$now->getTimestamp()-$last_updated->getTimestamp();
  $response["appliance_clusters"][$i]['age']=$age;
  $summary[$i]=$stats;
  foreach ($fuhosts[$i] as $j=>$irrelevant){
    $stats=array();  
    $data='{"query":{"query_string":{"query":"_id:'.$j.'*"}},"sort":{"_timestamp":"desc"},"size":1}';
    curl_setopt ($crl, CURLOPT_POSTFIELDS, $data);
    $now = date_create();
    $ret = curl_exec($crl);
    $res=jsonDecode($ret);
    $last_updated = date_create($res["hits"]["hits"][0]["_source"]["fm_date"]);
    $age=$now->getTimestamp()-$last_updated->getTimestamp();
    //    echo $j," ",$now->format('Y-m-d H:i:s')," ",$last_updated->format('Y-m-d H:i:s'),"\n";
    //    echo $j," ",$age,"\n";
    //  echo $ret;
    if($age<10){
      $response["appliance_clusters"][$i]['idle']+=$res["hits"]["hits"][0]["_source"]["idles"];
      $response["appliance_clusters"][$i]['online']+=$res["hits"]["hits"][0]["_source"]["used"];
      $response["appliance_clusters"][$i]['uldisk']+=$res["hits"]["hits"][0]["_source"]["usedDataDir"];
      $response["appliance_clusters"][$i]['tldisk']+=$res["hits"]["hits"][0]["_source"]["totalDataDir"];
    }
    else if($age<3600){
      $response["appliance_clusters"][$i]['stale'][]=$j;
    }
    else{
      $response["appliance_clusters"][$i]['dead'][]=$j;
    }
      $summary[$i]=$stats;
  }
}
curl_close($crl);
echo json_encode($response);
?>
