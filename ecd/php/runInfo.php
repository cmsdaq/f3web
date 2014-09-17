<?php 
include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["query"])) $query = "runinfo";
    else $query = $_GET["query"];
if(!isset($_GET["runNumber"])) $runNumber = 700032;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "runindex_".$sysName."_read/run";     

$stringQuery = file_get_contents("../json/".$query.".json");

$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["filter"]["term"]["_id"] = $runNumber;

$stringQuery = json_encode($jsonQuery);



$res=json_decode(esQuery($stringQuery,$index), true);

$ret = $res["hits"]["hits"][0]["_source"];

if ($format=="json"){ echo json_encode($ret); }

?>
