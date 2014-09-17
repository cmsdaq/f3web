<?php 

include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["query"])) $query = "streamsinrun";
    else $query = $_GET["query"];
if(!isset($_GET["runNumber"])) $runNumber = 36;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "runindex_".$sysName."_read/stream-hist"; 


$stringQuery = file_get_contents("../json/".$query.".json");
$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["query"]["term"]["_parent"] = $runNumber;

$stringQuery = json_encode($jsonQuery);

$res=json_decode(esQuery($stringQuery,$index), true);

//var_dump($res);

$terms = $res["facets"]["streams"]["terms"];

foreach ($terms as &$term) {
    unset($term["count"]);
    //$term["term"]=strtoupper(substr($term["term"],6));
}

if ($format=="json"){ echo json_encode($terms); }

?>