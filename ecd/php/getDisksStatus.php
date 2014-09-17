<?php 
include 'config.php';



if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["query"])) $query = "disks";
    else $query = $_GET["query"];
if(!isset($_GET["runNumber"])) $runNumber = 36;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "boxinfo_".$sysName."_read/boxinfo";    

$stringQuery = file_get_contents("../json/".$query.".json");

$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["query"]["bool"]["must"][1]["wildcard"]["activeRuns"]["value"] = "*".$runNumber."*";

$stringQuery = json_encode($jsonQuery);

//var_dump($stringQuery);


$res=json_decode(esQuery($stringQuery,$index), true);

//var_dump($res);

$ret = $res["aggregations"];

if ($format=="json"){ echo json_encode($ret); }

?>
