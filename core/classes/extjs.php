<?php
/**
 * @package Simple Groupware
 * @link http://www.simple-groupware.de
 * @copyright Simple Groupware Solutions Thomas Bley 2002-2012
 * @license GPLv2
 */

class extjs extends ajax {

static function get_table($folder, $view, $fields_hidden, $filter, $groupby, $orderby, $limit, $title) {
  $url = http_build_query(array(
    "folder" => $folder,
	"view" => $view,
	"fields_hidden" => $fields_hidden,
	"filter" => $filter,
	"groupby" => $groupby,
	"orderby" => $orderby,
	"limit" => $limit,
	"title" => $title
  ));
  return '
	<iframe src="ext/lib/extjs/index.php?'.$url.'" style="width:100%; border:0px;"></iframe>
  ';
}

static function ajax_get_tree() {
  $id = $_REQUEST["node"];
  
  $sel_folder = folder_build_selfolder($id, "");
  $children = db_get_children($sel_folder);

  foreach ($children as $key=>$folder) {
    $children[$key]["text"] = $folder["ftitle"];
	if ($folder["fcount"]!=0) $children[$key]["text"] .= " (".$children[$key]["fcount"].")";
	$children[$key]["qtip"] = $folder["fdescription"];
	
	$children[$key]["cls"] = "folder";
	$children[$key]["href"] = "#".$folder["id"];
	if ($folder["icon"]!="") $children[$key]["icon"] = "ext/modules/".$folder["icon"];
  }
  return $children;
}

static function ajax_get_model($folder, $view) {
  self::_require_access($folder, "read", $view);
  
  $sgsml = new sgsml($folder, $view, array(), false);
  $view = $sgsml->view;
  
  $model = array(
	'groupby' => $sgsml->schema["views"][$view]["GROUPBY"],
    'start' => 0,
    'limit' => $sgsml->schema["views"][$view]["LIMIT"],
	'sort' => array("field"=>$sgsml->schema["views"][$view]["ORDERBY"], "direction"=>$sgsml->schema["views"][$view]["ORDER"]),
	'fields' => array(),
	'folder' => $folder,
	'view' => $view,
	'writable' => db_get_right($sgsml->folder, "write")
  );

  foreach ($sgsml->current_fields as $name=>$field) {
	$model["fields"][$name] = array(
	  "name" => $name,
	  "header" => empty($field["DISPLAYNAME"]) ? ucfirst($name) : $field["DISPLAYNAME"],
	  "type" => "text",
	  "hidden" => isset($field["HIDDENIN"][$view]) or isset($field["HIDDENIN"]["all"])
	);
  }
  return $model;
}

static function ajax_get_rows($folder,$view="display",$fields="*") {
  $order = "";
  if (!empty($_REQUEST["groupBy"])) {
    $order = $_REQUEST["groupBy"]." ".strtolower($_REQUEST["groupDir"]);
  }
  if (!empty($_REQUEST["sort"])) {
    if ($order!="") $order .= ",";
    $order .= $_REQUEST["sort"]." ".strtolower($_REQUEST["dir"]);
  }
  $limit = $_REQUEST["start"].",".$_REQUEST["limit"];
  
  $filter = "";
  if (!empty($_REQUEST["filter"])) $filter = $_REQUEST["filter"];
  
  return self::asset_get_rows($folder,$view,$fields,$order,$limit,array(),$filter,true,true);
}

}