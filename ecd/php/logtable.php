<?php
include 'config.php';
if(!isset($_GET["format"])) $format = "json";
    else $format = $_GET["format"];
if(!isset($_GET["start"])) $from = 0;
    else $from = $_GET["start"];
if(!isset($_GET["length"])) $size = 100;
    else $size = $_GET["length"];
if(!isset($_GET["order"])) $order = "";
    else $order = $_GET["order"];
if(!isset($_GET["search"])) $search = "*";
    else $search = $_GET["search"];
if(!isset($_GET["startTime"])) $startTime = "0";
    else $startTime = $_GET["startTime"];
if(!isset($_GET["endTime"])) $endTime = "now";
    else $endTime = $_GET["endTime"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "hltdlogs_".$sysName."/hltdlog"; 



$sort = array();
foreach($order as $item){
    $field = $_GET["columns"][ $item["column"] ][ "data" ];
    array_push( $sort, array( $field => array( "order" => $item["dir"])   ));
}

//get runlist
$query = "logmessages";

$stringQuery = file_get_contents("../json/".$query.".json");
$jsonQuery = json_decode($stringQuery,true);

$jsonQuery["size"] = $size;
$jsonQuery["from"] = $from;
$jsonQuery["sort"] = $sort;
$jsonQuery["query"]["filtered"]["filter"]["and"][0]["range"]["_timestamp"]["from"] = $startTime;
$jsonQuery["query"]["filtered"]["filter"]["and"][0]["range"]["_timestamp"]["to"] = $endTime ;

if ($search["value"] != "" ){
    $jsonQuery["query"]["filtered"]["query"]["bool"]["should"][0]["query_string"]["query"] = $search["value"];    
}


$stringQuery = json_encode($jsonQuery);

$res=json_decode(esQuery($stringQuery,$index), true);

$total = $res["hits"]["total"];
$ret = array();
foreach ($res["hits"]["hits"] as $item) {
    array_push($ret, $item["_source"]);
}


//Output
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $total,
    "iTotalDisplayRecords" => $total,
    "aaData" => array()
);

$output['aaData'] = $ret;
echo json_encode( $output );

//echo json_encode( $size );
//echo json_encode( $from );
//echo json_encode( $sort);
//echo json_encode( $search);
//echo json_encode( $startTime);
//echo json_encode( $endTime);


?>
