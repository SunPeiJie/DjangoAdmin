<?php

class ExportUtils {
	
	/**
	 * 导出
	 * @param request 请求对象
	 * @param response 响应对象
	 * @param pager dtGrid对象
	 * @param exportDatas 导出的数据
	 * @param fileName 文件名
	 * @throws Exception
	 */
	static function export($pager){
//		处理导出
		if($pager["isExport"]){
//			获取文件名
			$fileName = "datas";
			$fileName = $pager["exportFileName"];
//			如果是excel
			if(strcasecmp("excel", $pager["exportType"])==0){
				ExportUtils::exportExcel($pager, $pager["exportDatas"], $fileName);
				return;
			}
//			如果是cvs
			if(strcasecmp("csv", $pager["exportType"])==0){
				ExportUtils::exportCsv($pager, $pager["exportDatas"], $fileName);
				return;
			}
//			如果是txt
			if(strcasecmp("txt", $pager["exportType"])==0){
				ExportUtils::exportTxt($pager, $pager["exportDatas"], $fileName);
				return;
			}
//			如果是pdf
			if(strcasecmp("pdf", $pager["exportType"])==0){
				ExportUtils::exportPdf($pager, $pager["exportDatas"], $fileName);
				return;
			}
		}
	}
	
	/**
	 * 导出Excel
	 * @param request 请求对象
	 * @param response 响应对象
	 * @param pager dtGrid对象
	 * @param exportDatas 导出的数据
	 * @param fileName 文件名
	 * @throws Exception
	 */
	static function exportExcel($pager, $exportDatas, $fileName){
//		设置响应头
		header("Content-Type: application/vnd.ms-execl");
		header("Content-Disposition: attachment; filename=".$fileName.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
//		创建Excel对象
		$resultPHPExcel = new PHPExcel();
//		设置当前Sheet名称
		$resultPHPExcel->setActiveSheetIndex(0);
		$resultPHPExcel->getActiveSheet()->setTitle($fileName);
//		定义边框样式
		$borderStyleArray = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF333333'),
				),
			),
		);
//		定义最大宽度
		$widths = array();
//		判断一下表头数组是否有数据
		if ($pager["exportColumns"] != null && count($pager["exportColumns"]) > 0) {
//			循环写入表头
			$i = 0;
			$j = 1;
			foreach ($pager["exportColumns"] as $column) {
				$resultPHPExcel->getActiveSheet()->setCellValue(ExportUtils::dectoletter($i).$j, $column["title"]);
				array_push($widths, strlen($column["title"]));
				$i++;
			}
			$j++;
//			设置表头样式
			$headerStyle = $resultPHPExcel->getActiveSheet()->getStyle("A1:".ExportUtils::dectoletter(count($pager["exportColumns"])-1)."1");
			$headerStyle->getFont()->setName("微软雅黑");
			$headerStyle->getFont()->setSize(9);
			$headerStyle->getFont()->setBold(true);
			$headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');
			$headerStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$headerStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$headerStyle->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$headerStyle->getFill()->getStartColor()->setARGB('FF1B9ECE');
			$headerStyle->applyFromArray($borderStyleArray);
//			判断表中是否有数据
			if ($exportDatas != null && count($exportDatas) > 0) {
//				循环写入表中数据
				foreach ($exportDatas as $record) {
					$i = 0;
					foreach ($pager["exportColumns"] as $column) {
						$content = $record[$column["id"]];;
//						如果内容未被处理则进行格式化
						if(!$pager["exportDataIsProcessed"]){
							$content = DtGridUtils::formatContent($column, $content);
						}
						$resultPHPExcel->getActiveSheet()->setCellValue(ExportUtils::dectoletter($i).$j, $content);
						if($widths[$i]<strlen($content)){
							$widths[$i] = strlen($content);
						}
						$i++;
					}
					$j++;
				}
//				设置内容样式
				$contentStyle = $resultPHPExcel->getActiveSheet()->getStyle("A2:".ExportUtils::dectoletter(count($pager["exportColumns"])-1).(count($exportDatas)+1));
				$contentStyle->getFont()->setName("微软雅黑");
				$contentStyle->getFont()->setSize(9);
				$contentStyle->getFont()->getColor()->setARGB('FF333333');
				$contentStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$contentStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$contentStyle->applyFromArray($borderStyleArray);
			}
//			冻结首行
			$resultPHPExcel->getActiveSheet()->freezePane("A2");
//			自适应宽度
			$i = 0;
			foreach ($pager["exportColumns"] as $column) {
				$resultPHPExcel->getActiveSheet()->getColumnDimension(ExportUtils::dectoletter($i))->setWidth($widths[$i]);
				$i++;
			}
		}
//		输出
		$xlsWriter = new PHPExcel_Writer_Excel5($resultPHPExcel); 
		$xlsWriter->save("php://output");
	}
	
	static $letterArr = array(0=>'A', 1=>'B', 2=>'C', 3=>'D', 4=>'E', 5=>'F', 6=>'G', 7=>'H', 8=>'I', 9=>'J', 'a'=>'K', 'b'=>'L', 'c'=>'M', 'd'=>'N', 'e'=>'O', 'f'=>'P', 'g'=>'Q', 'h'=>'R', 'i'=>'S', 'j'=>'T', 'k'=>'U', 'l'=>'V', 'm'=>'W', 'n'=>'X', 'o'=>'Y', 'p'=>'Z');
	
	/**
	 * 转换数字为26进制字母
	 */
	static function dectoletter($number){
	    $number = strval($number);
	    $l = strlen($number);
	    $retNum = '';
	    for($i = 0;$i < $l;$i++){
	        $retNum = $retNum.ExportUtils::$letterArr[substr($number,$i,1)];
	    }
	    return $retNum;
	}
	
	/**
	 * 导出Csv
	 * @param request 请求对象
	 * @param response 响应对象
	 * @param pager dtGrid对象
	 * @param exportDatas 导出的数据
	 * @param fileName 文件名
	 * @throws Exception
	 */
	static function exportCsv($pager, $exportDatas, $fileName) {
//		设置响应头
		header("Content-Type: application/CSV");
		header("Content-Disposition: attachment; filename=".$fileName.".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
//		判断一下表头数组是否有数据
		if ($pager["exportColumns"] != null && count($pager["exportColumns"]) > 0) {
//			循环写入表头
			foreach ($pager["exportColumns"] as $column) {
				echo iconv("UTF-8", "GBK", '"'.$column["title"].'"').",";
			}
			echo "\n";
//			判断表中是否有数据
			if ($exportDatas != null && count($exportDatas) > 0) {
//				循环写入表中数据
				foreach ($exportDatas as $record) {
					foreach ($pager["exportColumns"] as $column) {
						$content = $record[$column["id"]];;
//						如果内容未被处理则进行格式化
						if(!$pager["exportDataIsProcessed"]){
							$content = DtGridUtils::formatContent($column, $content);
						}
						echo iconv("UTF-8", "GBK", '"'.$content.'"').",";
					}
					echo "\n";
				}
			}
		}
	}

	/**
	 * 导出Txt
	 * @param request 请求对象
	 * @param response 响应对象
	 * @param pager dtGrid对象
	 * @param exportDatas 导出的数据
	 * @param fileName 文件名
	 * @throws Exception
	 */
	static function exportTxt($pager, $exportDatas, $fileName) {
//		设置响应头
		header("Content-Type: application/txt");
		header("Content-Disposition: attachment; filename=".$fileName.".txt");
		header("Pragma: no-cache");
		header("Expires: 0");
//		判断一下表头数组是否有数据
		if ($pager["exportColumns"] != null && count($pager["exportColumns"]) > 0) {
//			循环写入表头
			foreach ($pager["exportColumns"] as $column) {
				echo iconv("UTF-8", "GBK", $column["title"])."\t";
			}
			echo "\r\n";
//			判断表中是否有数据
			if ($exportDatas != null && count($exportDatas) > 0) {
//				循环写入表中数据
				foreach ($exportDatas as $record) {
					foreach ($pager["exportColumns"] as $column) {
						$content = $record[$column["id"]];;
//						如果内容未被处理则进行格式化
						if(!$pager["exportDataIsProcessed"]){
							$content = DtGridUtils::formatContent($column, $content);
						}
						echo iconv("UTF-8", "GBK", $content)."\t";
					}
					echo "\r\n";
				}
			}
		}
	}
	
	/**
	 * 导出Pdf
	 * @param request 请求对象
	 * @param response 响应对象
	 * @param pager dtGrid对象
	 * @param exportDatas 导出的数据
	 * @param fileName 文件名
	 * @throws Exception
	 */
	static function exportPdf($pager, $exportDatas, $fileName) {
//		定义PDF文件
		$pdf=new PDF_Chinese("L");
		$pdf->AddGBFont();
		$pdf->AddPage();
		$pdf->SetFont('GB','',9);
		$pdf->SetFillColor(47, 151, 210);
		$pdf->SetDrawColor(33, 33, 33);
		$pdf->SetTextColor(255, 255, 255);
//		循环一次获取出宽度
		$widths = array();
		if ($pager["exportColumns"] != null && count($pager["exportColumns"]) > 0) {
//			循环写入表头
			foreach ($pager["exportColumns"] as $column) {
				array_push($widths, $pdf->GetStringWidth(iconv("UTF-8", "GBK", $column["title"]))+6);
			}
//			判断表中是否有数据
			if ($exportDatas != null && count($exportDatas) > 0) {
//				循环写入表中数据
				foreach ($exportDatas as $record) {
					$i = 0;
					foreach ($pager["exportColumns"] as $column) {
						$content = $record[$column["id"]];;
//						如果内容未被处理则进行格式化
						if(!$pager["exportDataIsProcessed"]){
							$content = DtGridUtils::formatContent($column, $content);
						}
						$width = $pdf->GetStringWidth(iconv("UTF-8", "GBK", $content))+6;
						if($widths[$i]<$width){
							$widths[$i] = $width;
						}
						$i++;
					}
				}
			}
		}
//		判断一下表头数组是否有数据
		if ($pager["exportColumns"] != null && count($pager["exportColumns"]) > 0) {
//			循环写入表头
			$i=0;
			foreach ($pager["exportColumns"] as $column) {
				$pdf->Cell($widths[$i], 6, iconv("UTF-8", "GBK", $column["title"]), 1, 0, "C", 1);
				$i++;
			}
			$pdf->SetTextColor(44, 44, 44);
			$pdf->Ln();
//			判断表中是否有数据
			if ($exportDatas != null && count($exportDatas) > 0) {
//				循环写入表中数据
				foreach ($exportDatas as $record) {
					$i=0;
					foreach ($pager["exportColumns"] as $column) {
						$content = $record[$column["id"]];;
//						如果内容未被处理则进行格式化
						if(!$pager["exportDataIsProcessed"]){
							$content = DtGridUtils::formatContent($column, $content);
						}
						$pdf->Cell($widths[$i], 6, iconv("UTF-8", "GBK", $content), 1, 0, "C", 0);
						$i++;
					}
					$pdf->Ln();
				}
			}
		}
		$pdf->Output($fileName.".pdf", "D");
	}

}

?>