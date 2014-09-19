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
$dataPerc = array();

$hits = $res["hits"]["hits"];
foreach ($hits as $hit ) {
    $ls = $hit["_source"]["ls"];
    $processed = $hit["_source"]["processed"];
    $pathAccepted = $hit["_source"]["path-accepted"];
    
    $i=0;
    foreach ($pathAccepted as $accepted) {
        $name = $pathNames[$i];
        $i++;
        if (!array_key_exists($name, $dataAccepted)){
            $dataAccepted[$name] = array();
            $dataProcessed[$name] = array();
            $dataPerc[$name] = array();
        }
        if (!array_key_exists($ls, $dataAccepted[$name])){
            $dataAccepted[$name][$ls] = 0;
            $dataProcessed[$name][$ls] = 0;

        }
        $dataAccepted[$name][$ls] += $accepted;
        $dataProcessed[$name][$ls] += $processed;
        $dataPerc[$name][$ls] = $dataAccepted[$name][$ls]/$dataProcessed[$name][$ls];
    }

}

//echo json_encode($dataProcessed);
//echo json_encode($dataAccepted);
//echo json_encode($dataPerc);

$out = array();
foreach ( $dataPerc as $pathName => $data ) {
    $out[$pathName] = array();
    foreach ( $data as $ls => $value ) {
        $out[$pathName][] = array("name"=> $ls,"y"=>$value);
    }
}

if ($format=="json"){ echo json_encode($out); }

?>

