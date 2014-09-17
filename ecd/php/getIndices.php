<?php
include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];


$index = "_alias/runindex*read";
$stringQuery = NULL;

$res=json_decode(esQuery($stringQuery,$index), true);


$ret = array();

foreach ($res as $key => $value){
    $index = strtolower(key($value["aliases"]));
    $system = explode("_",$index);
    $system = strtolower($system[1]);
    //array_push($ret, array($index,$system));
    $ret[$system] = $index;
}

if ($format=="json"){ echo json_encode($ret); }

?>
