<?php 
include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["query"])) $query = "riverstatus";
    else $query = $_GET["query"];
if(!isset($_GET["size"])) $size = 100;
    else $size = $_GET["size"];  
 $index = '_river';

$stringQuery = file_get_contents("../json/".$query.".json");
$jsonQuery = json_decode($stringQuery,true);
$jsonQuery["size"] = $size;



$statusList = array();

//get status
$jsonQuery["filter"]["term"]["_id"] = "_status";
$stringQuery = json_encode($jsonQuery);
$res=json_decode(esQuery($stringQuery,$index), true);
$hits = $res["hits"]["hits"];
foreach ($hits as $hit){
    $ip = substr($hit["_source"]["node"]["transport_address"], 6, -6);
    $statusList[$hit["_type"]] = $ip;
}
//var_dump($statusList);


$out = array(
    "systems" => array(),
    "runs"    => array()
    );

//get meta
$jsonQuery["filter"]["term"]["_id"] = "_meta";
$stringQuery = json_encode($jsonQuery);
$res=json_decode(esQuery($stringQuery,$index), true);
$hits = $res["hits"]["hits"];
foreach ($hits as $hit){
    $type = $hit["_type"];
    $source = $hit["_source"];
    
    $system = substr($source["runIndex_read"], 9,strpos($source["runIndex_read"], "_read")-9);


    if (array_key_exists($type, $statusList)){
        $status = true;
        $ip = $statusList[$type];
        $host = gethostbyaddr($ip);
    }else{
        $status = false;
        $host = "";
    }



    if(array_key_exists("role", $source) && $source["role"]=="collector") { 
        $out["runs"][$source["runNumber"]] = array(
            "status" => $status,
            "host"   => $host,
            "system" => $system       
            );  
    }else{
        $out["systems"][$system] = array(
            "status" => $status,
            "host"   => $host,       
            );  
    }
}

if ($format=="json"){ echo json_encode($out); }

?>
