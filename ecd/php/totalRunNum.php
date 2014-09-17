<?php 
include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "runindex_".$sysName."_read/run"; 

$stringQuery = "";

$res=json_decode(esQuery($stringQuery,$index."/_count"), true);

//var_dump($res);
$ret = $res["count"];

if ($format=="json"){ echo json_encode($ret); }

?>
