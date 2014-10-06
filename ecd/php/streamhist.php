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
if(!isset($_GET["intervalNum"])) $intervalNum = 20;
    else $intervalNum = $_GET["intervalNum"];   
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];
if(!isset($_GET["streamList"])) $streamList = array("a","b");
    else $streamList = $_GET["streamList"]; 
if(!isset($_GET["timePerLs"])) $timePerLs = 24.3;
    else $timePerLs = $_GET["timePerLs"]; 


$interval = round((intval($to) - intval($from)) / intval($intervalNum),0 );
if ($interval == 0 ){ $interval = 1; };

//var_dump($interval);


//GET TOTALS
$index = "runindex_".$sysName."_read/eols"; 
$query = "teols.json";

$stringQuery = file_get_contents("../json/".$query);

$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["aggregations"]["ls"]["histogram"]["interval"] = intval($interval);
$jsonQuery["aggregations"]["ls"]["histogram"]["extended_bounds"]["min"]= $from;
$jsonQuery["aggregations"]["ls"]["histogram"]["extended_bounds"]["max"]= $to;
$jsonQuery["query"]["filtered"]["filter"]["prefix"]["_id"] = "run".$runNumber;
$jsonQuery["query"]["filtered"]["query"]["range"]["ls"]["from"]= $from;
$jsonQuery["query"]["filtered"]["query"]["range"]["ls"]["to"]= $to;

$stringQuery = json_encode($jsonQuery);

//var_dump($stringQuery);


$res=json_decode(esQuery($stringQuery,$index), true);

//var_dump($res);

$buckets = $res["aggregations"]["ls"]["buckets"];
$ret = array(
    "lsList" => array(),
    "events" => array(),
    "files" => array(),
    "doc_counts" => array(),
    );


$took = $res["took"];
foreach($buckets as $bucket){

    $ls = $bucket["key"];
    $events = $bucket["events"]["value"];
    $doc_count = $bucket["doc_count"];
    //$files[$ls] = array($ls,$bucket["files"]["value"]);
    

    $ret["events"][$ls] = $events;
    $ret["doc_counts"][$ls] = $doc_count;
    $ret["lsList"][] = $ls;

}   

$streamTotals = $ret;

//var_dump(json_encode($streamTotals));

//GET STREAM OUT

$index = "runindex_".$sysName."_read/stream-hist"; 
$query = "outls.json";

$stringQuery = file_get_contents("../json/".$query);

$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["query"]["filtered"]["filter"]["and"]["filters"][0]["prefix"]["_id"] = $runNumber;
$jsonQuery["aggs"]["stream"]["aggs"]["inrange"]["filter"]["range"]["ls"]["from"]= $from;
$jsonQuery["aggs"]["stream"]["aggs"]["inrange"]["filter"]["range"]["ls"]["to"]= $to;
$jsonQuery["aggs"]["stream"]["aggs"]["inrange"]["aggs"]["ls"]["histogram"]["extended_bounds"]["min"]= $from;
$jsonQuery["aggs"]["stream"]["aggs"]["inrange"]["aggs"]["ls"]["histogram"]["extended_bounds"]["max"]= $to;
$jsonQuery["aggs"]["stream"]["aggs"]["inrange"]["aggs"]["ls"]["histogram"]["interval"]= intval($interval);

$stringQuery = json_encode($jsonQuery);

//var_dump($stringQuery);


$res=json_decode(esQuery($stringQuery,$index), true);

//var_dump(json_encode($res));

$ret = array(
    "streamList" => array(),
    "streams" => array()
);

$took = $took + $res["took"];
$ret["took"] = $took;
$ret["lsList"] = $streamTotals["lsList"];

$streams = $res["aggregations"]["stream"]["buckets"];
foreach($streams as $stream) {    
    $stream["key"] = $stream["key"];
    if ($stream["key"] == '' || !in_array($stream["key"], $streamList)) { continue; };
    array_push($ret["streamList"], $stream["key"]);
    $lsList = $stream["inrange"]["ls"]["buckets"];
    foreach ($lsList as $item ) {

        $ls = $item["key"];

        $total = $streamTotals["events"][$ls];
        $doc_count = $streamTotals["doc_counts"][$ls];
        
        $in = round($item["in"]["value"],2);
        $out = round($item["out"]["value"],2);
        $filesize = round($item["filesize"]["value"],2);

//CALC STREAM PERCENTS        
        if ($total == 0){ 
            if ($doc_count == 0) {$percent = 0;} 
            else {$percent = 100; }
        }
        else{ $percent = round($in/$total*100,2);  }
        
//OUTPUT
        if($timePerLs>1){ 
            $out = round($out/$timePerLs,2); 
            $filesize = round($filesize/$timePerLs,2);
        }

        $ret["streams"][$stream["key"]]["dataOut"][] = array("name"=>$ls,"y"=>$out);
        $ret["streams"][$stream["key"]]["fileSize"][] = array("name"=>$ls,"y"=>$filesize);
        $ret["streams"][$stream["key"]]["percent"][] = array("name"=>$ls,"y"=>$percent);
    }
}
$streamOut = $ret;



//GET MINIMERGE

$index = "runindex_".$sysName."_read/minimerge"; 
$query = "minimerge.json";

$stringQuery = file_get_contents("../json/".$query);

$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["query"]["filtered"]["filter"]["and"]["filters"][0]["prefix"]["_id"] = "run".$runNumber;
$jsonQuery["aggs"]["inrange"]["filter"]["range"]["ls"]["from"]= $from;
$jsonQuery["aggs"]["inrange"]["filter"]["range"]["ls"]["to"]= $to;
$jsonQuery["aggs"]["inrange"]["aggs"]["ls"]["histogram"]["extended_bounds"]["min"]= $from;
$jsonQuery["aggs"]["inrange"]["aggs"]["ls"]["histogram"]["extended_bounds"]["max"]= $to;
$jsonQuery["aggs"]["inrange"]["aggs"]["ls"]["histogram"]["interval"]= intval($interval);

$stringQuery = json_encode($jsonQuery);

//var_dump($stringQuery);


$res=json_decode(esQuery($stringQuery,$index), true);


$minimerge = array(
    "percents" => array()
);

$took = $took + $res["took"];
$minimerge["took"] = $took;
$streamNum = count($streamList);

$lsList = $res["aggregations"]["inrange"]["ls"]["buckets"];
foreach ($lsList as $item ) {
    $ls = $item["key"];
    $processed = $item["processed"]["value"];
    $total = $streamTotals["events"][$ls] * $streamNum ;
    $doc_count = $streamTotals["doc_counts"][$ls];
    $mdoc_count = $item["doc_counts"];

//CALC MINIMERGE PERCENTS        
    if ($total == 0){ 
        if ($doc_count == 0 || $mdoc_count == 0) {$percent = 0;} 
        else {$percent = 100; }
    }
    else{ $percent = round($processed/$total*100,2);  }
    
    $color = percColor($percent);

    $minimerge["percents"][] =  array("name"=>$ls,"y"=>$percent,"color"=>$color);
}

$streamOut["minimerge"] = $minimerge;
$streamOut["took"] = $took;
$streamOut["interval"] = $interval;
if ($format=="json"){ echo json_encode($streamOut); }

?>
