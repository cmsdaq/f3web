<?php 

include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["query"])) $query = "ulegenda";
    else $query = $_GET["query"];
if(!isset($_GET["runNumber"])) $runNumber = 10;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "runindex_".$sysName."_read/microstatelegend";

$stringQuery = getStringQuery($query);

$jsonQuery = json_decode($stringQuery,true);
$jsonQuery["query"]["filtered"]["query"]["term"]["_parent"] = $runNumber;
$stringQuery = json_encode($jsonQuery);

//var_dump($stringQuery);
//var_dump($index);


$res=json_decode(esQuery($stringQuery,$index), true);


//var_dump($res);

$ret = $res["hits"]["hits"][0]["_source"]["names"];

if ($format=="json"){ echo json_encode($ret); }

?>

