<?php

	require("../config/config.inc.php");
	
	require("../utils/DtGridUtils.class.php");
	require("../utils/ExportUtils.class.php");
	require("../utils/QueryUtils.class.php");
	require("../utils/ParamsUtils.class.php");
	
	require("../lib/pdf/fpdf.php");
	require("../lib/pdf/chinese.php");
	
	require("../lib/excel/PHPExcel.php");
	require('../lib/excel/PHPExcel/IOFactory.php');  
	require('../lib/excel/PHPExcel/Writer/Excel5.php'); 
	
	$dtGridPager = $_POST["dtGridPager"];
	$pager = json_decode($dtGridPager, true);
	$sql = "select u.* from user u where 1=1 ";
	$args = array();
//	判断是否包含自定义参数
	$parameters = $pager["parameters"];
	if($parameters&&$parameters["like_user_code_or_user_name"]){
		$like_user_code_or_user_name = $parameters["like_user_code_or_user_name"];
		if($like_user_code_or_user_name!=null&&trim($like_user_code_or_user_name)!=""){
			$sql.="and u.user_code like ? or u.user_name like ? ";
			array_push($args, "%".$like_user_code_or_user_name."%");
			array_push($args, "%".$like_user_code_or_user_name."%");
		}
	}
	DtGridUtils::queryForDTGrid($sql, $pager, $config, $args);

?>