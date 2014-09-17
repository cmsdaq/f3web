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
if(!isset($_GET["search"])) $search = "";
    else $search = $_GET["search"];
if(!isset($_GET["sysName"])) $sysName = "cdaq";
    else $sysName = $_GET["sysName"];

$index = "runindex_".$sysName."_read/run"; 

$sort = array();
foreach($order as $item){
    $field = $_GET["columns"][ $item["column"] ][ "data" ];
    array_push( $sort, array( $field => array( "order" => $item["dir"])   ));
}

//get total run number
$res=json_decode(esQuery("",$index."/_count"), true);
$total = $res["count"];



//get runlist
$query = "rltable";

$stringQuery = file_get_contents("../json/".$query.".json");
$jsonQuery = json_decode($stringQuery,true);

//$jsonQuery["size"] = $size;
$jsonQuery["size"] = $total;
$jsonQuery["from"] = $from;
//$jsonQuery["sort"] = $sort;

$stringQuery = json_encode($jsonQuery);

$res=json_decode(esQuery($stringQuery,$index), true);


$ret = array();
foreach ($res["hits"]["hits"] as $item) {
    array_push($ret, $item["_source"]);
}

$filteredTotal = intval($total) - intval($size);

//Output
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $total,
    "iTotalDisplayRecords" => $filteredTotal,
    "aaData" => array()
);

$output['aaData'] = $ret;
echo json_encode( $output );


//echo json_encode($from);
//echo json_encode($size);
//echo json_encode($order);
//echo json_encode($search);
?>