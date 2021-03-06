<?php 
include 'configTribe.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["runNumber"])) $runNumber = 790014;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["from"])) $from = 0;
    else $from = $_GET["from"];
if(!isset($_GET["to"])) $to = 20;
    else $to = $_GET["to"];     
if(!isset($_GET["timePerLs"])) $timePerLs = 24.3;
    else $timePerLs = $_GET["timePerLs"];     



//get legend
$index = "run".$runNumber."*/hltrates-legend"; 
$stringQuery = '{"size":1}';
$res=json_decode(esQuery($stringQuery,$index), true);
$pathNames = $res["hits"]["hits"][0]["_source"]["path-names"];

//get rates
$query = "hltrates.json";
$index = "run".$runNumber."*/hltrates"; 
$stringQuery = file_get_contents("../json/".$query);
$jsonQuery = json_decode($stringQuery,true);
$jsonQuery["query"]["range"]["ls"]["from"]= $from;
$jsonQuery["query"]["range"]["ls"]["to"]= $to;
$stringQuery = json_encode($jsonQuery);
$res=json_decode(esQuery($stringQuery,$index), true);



$dataAccepted = array();
$dataProcessed = array();


$hits = $res["hits"]["hits"];
foreach ($hits as $hit ) {
    $ls = $hit["_source"]["ls"];
    $processed = $hit["_source"]["processed"];
    $pathAccepted = $hit["_source"]["path-accepted"];
    $dataProcessed[$ls] += $processed;
    
    $i=0;
    foreach ($pathAccepted as $accepted) {
        $name = $pathNames[$i];
        $i++;
        if (!array_key_exists($name, $dataAccepted)){
            $dataAccepted[$name] = array();
        }
        if (!array_key_exists($ls, $dataAccepted[$name])){
            $dataAccepted[$name][$ls] = 0;
        }
        $dataAccepted[$name][$ls] += $accepted;
    }
}

$out = array();
foreach ( $dataProcessed as $ls => $value ) {
    $rate = round($value/$timePerLs,2);
    $outData[] = array("name"=> $ls,"y"=>$rate);
}
$out[] = array("name"=>"processed","data"=> $outData);

foreach ( $dataAccepted as $pathName => $data ) {
    $outData = array();
    foreach ( $data as $ls => $value ) {
        $rate = round($value/$timePerLs,2);
        $outData[] = array("name"=> $ls,"y"=>$rate);
    }
    $out[] = array("name"=>$pathName,"data"=> $outData);
}

if ($format=="json"){ echo json_encode($out); }

?>

