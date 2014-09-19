<?php 
include 'configTribe.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["runNumber"])) $runNumber = 390008;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["from"])) $from = 1000;
    else $from = $_GET["from"];
if(!isset($_GET["to"])) $to = 2000;
    else $to = $_GET["to"];     

$index = "run".$runNumber."*/hltdrates"; 
$query = "hltrates.json";

$stringQuery = file_get_contents("../json/".$query);
$jsonQuery = json_decode($stringQuery,true);

$res=json_decode(esQuery($stringQuery,$index), true);



?>
