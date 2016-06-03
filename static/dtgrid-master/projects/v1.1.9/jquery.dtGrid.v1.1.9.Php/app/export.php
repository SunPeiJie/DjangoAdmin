<?php

	require("../config/config.inc.php");

	require("../utils/DtGridUtils.class.php");
	require("../utils/ExportUtils.class.php");
	require("../utils/QueryUtils.class.php");
	
	require("../lib/pdf/fpdf.php");
	require("../lib/pdf/chinese.php");
	
	require("../lib/excel/PHPExcel.php");
	require('../lib/excel/PHPExcel/IOFactory.php');  
	require('../lib/excel/PHPExcel/Writer/Excel5.php'); 
	
	$dtGridPager = $_POST["dtGridPager"];
	$pager = json_decode($dtGridPager, true);
	ExportUtils::export($pager);

?>