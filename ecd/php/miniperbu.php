<?php 
include 'config.php';

if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["runNumber"])) $runNumber = 390008;
    else $runNumber = $_GET["runNumber"];
if(!isset($_GET["from"])) $from = 1000;
    else $from = $_GET["from"];
if(!isset($_GET["to"])) $to = 2000;
    else $to = $_GET["to"];     
if(!isset($_GET["stream"])) $stream = "A";
    else $stream = $_GET["stream"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];
if(!isset($_GET["streamList"])) $streamList = array("a","b");
    else $streamList = $_GET["streamList"]; 


//GET TOTAL
$index = "runindex_".$sysName."_read/eols"; 
$query = "teols.json";

$stringQuery = file_get_contents("../json/".$query);

$jsonQuery = json_decode($stringQuery,true);

//remove aggs
unset($jsonQuery["aggregations"]);
unset($jsonQuery["fields"]);
$jsonQuery["size"] = 2000000 ;
$jsonQuery["query"]["filtered"]["filter"]["prefix"]["_id"] = "run".$runNumber;
$jsonQuery["query"]["filtered"]["query"]["range"]["ls"]["from"]= $from;
$jsonQuery["query"]["filtered"]["query"]["range"]["ls"]["to"]= $to;

$stringQuery = json_encode($jsonQuery);
//var_dump($stringQuery);

$res=json_decode(esQuery($stringQuery,$index), true);
//die(var_dump($res));

$totals = array();

$docs = $res["hits"]["hits"];
foreach($docs as $doc) {
    $id =$doc["_source"]["id"];
    $total = $doc["_source"]["NEvents"];
    $bu = substr($id, strpos($id, "bu"));
    $totals[$bu] += $total ; 
}
//die(var_dump($totals));


//GET MINIMERGE

$index = "runindex_".$sysName."_read/minimerge"; 
$query = "miniperbu.json";

$stringQuery = file_get_contents("../json/".$query);

$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["query"]["bool"]["must"][1]["prefix"]["_id"] = "run".$runNumber;
$jsonQuery["query"]["bool"]["must"][0]["range"]["ls"]["from"]= $from;
$jsonQuery["query"]["bool"]["must"][0]["range"]["ls"]["to"]= $to;
$jsonQuery["query"]["bool"]["must"][2]["term"]["stream"]["value"] = $stream;

$stringQuery = json_encode($jsonQuery);
//var_dump($stringQuery);

$res=json_decode(esQuery($stringQuery,$index), true);



$totalProc = array();

$docs = $res["hits"]["hits"];
foreach($docs as $doc) {
    $id =$doc["_source"]["id"];
    $bu = substr($id, strpos($id, "bu"));
    $processed = $doc["_source"]["processed"];

    
    $totalProc[$bu] += $processed;
}

//echo json_encode($totals);
//echo json_encode($totalProc);

$out = array("percents"=>array());

foreach ($totals as $bu => $total){
    $processed = $totalProc[$bu];
    //CALC MINIMERGE PERCENTS        
    
    if ($total == 0){ $percent = 100;} 
        else{ $percent = round($processed/$total*100,2);  }
    $color = percColor($percent);

    $out["percents"][] = array("name"=>$bu,"y"=>$percent,"color"=>$color);
}

if ($format=="json"){ echo json_encode($out); }

?>
