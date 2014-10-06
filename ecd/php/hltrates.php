<?php 
include 'configTribe.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["runNumber"])) $runNumber = 790014;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["numVal"])) $numVal = 50;
    else $numVal = $_GET["numVal"];
if(!isset($_GET["timePerLs"])) $timePerLs = 24.3;
    else $timePerLs = $_GET["timePerLs"];     



//get legend
$index = "run".$runNumber."*/hltrates-legend"; 
$stringQuery = '{"size":1}';
$res=json_decode(esQuery($stringQuery,$index), true);
$pathNames = $res["hits"]["hits"][0]["_source"]["path-names"];


//get last ls
$index = "run".$runNumber."*/hltrates"; 
$stringQuery = '{"size":1,"sort":[{"ls":{"order":"desc"}}]}';
$res=json_decode(esQuery($stringQuery,$index), true);
$lastLs = $res["hits"]["hits"][0]["_source"]["ls"];
$from = $lastLs - $numVal;

//get rates
$query = "hltrates.json";
$index = "run".$runNumber."*/hltrates"; 
$stringQuery = file_get_contents("../json/".$query);
$jsonQuery = json_decode($stringQuery,true);
$jsonQuery["query"]["range"]["ls"]["from"]= $from;
$stringQuery = json_encode($jsonQuery);
$res=json_decode(esQuery($stringQuery,$index), true);



$dataAccepted = array();
$dataProcessed = array();


$hits = &$res["hits"]["hits"];

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
unset($res);
unset($hits);

$out = array();
foreach ( $dataProcessed as $ls => $value ) {
    $rate = round($value/$timePerLs,2);
    $outData[] = array($ls,$rate);
}
$out[] = array("name"=>"processed","data"=> $outData);

foreach ( $dataAccepted as $pathName => $data ) {
    $outData = array();
    foreach ( $data as $ls => $value ) {
        $rate = round($value/$timePerLs,2);
        $outData[] = array($ls,$rate);
    }
    $out[] = array("name"=>$pathName,"data"=> $outData);
}

if ($format=="json"){ echo json_encode($out); }

?>

