<?php 
include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["query"])) $query = "nstates";
    else $query = $_GET["query"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "runindex_".$sysName."_read/state-hist";     


$stringQuery = file_get_contents("../json/".$query.".json");

//var_dump($stringQuery);

$res=json_decode(esQuery($stringQuery,$index), true);

//var_dump($res);


$ret["time"]=$res["hits"]["hits"][0]["sort"][0];
$ret["entries"] = array();
$entries = $res["hits"]["hits"][0]["_source"]["hmicro"]["entries"];
//var_dump($entries);
foreach ($entries as $entry){
    
    //echo $entry["key"]."\n";
    $ret["entries"][$entry["key"]] = $entry["count"];

}

if ($format=="json"){ echo json_encode($ret); }


?>
