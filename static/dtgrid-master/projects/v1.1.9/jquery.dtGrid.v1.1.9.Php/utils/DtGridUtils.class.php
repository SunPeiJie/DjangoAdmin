<?php

class DtGridUtils {
	
	/**
	 * DTGrid的查询方法
	 * @param sql 查询SQL
	 * @param pager 传递的Pager参数对象
	 * @return 包含查询结果集的Pager
	 * @throws Exception
	 */
	static function queryForDTGrid($sql, $pager, $db_config, &$args) {
		if(!$args){
			$args = array();
		}
		try{
			$mysqli = new mysqli($db_config['dbhost'], $db_config['dbuser'], $db_config['dbpass'], $db_config['dbname']);
			mysqli_query($mysqli, "SET NAMES utf8");
//			检查连接是否被创建
			if (mysqli_connect_errno()) {
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
//			处理导出
			if($pager["isExport"]){
//				如果是全部导出数据
				if($pager["exportAllData"]){
//					获取快速查询条件SQL、高级查询条件SQL
					$fastQuerySql = QueryUtils::getFastQuerySql($pager["fastQueryParameters"], $args);
					$advanceQueryConditionSql = QueryUtils::getAdvanceQueryConditionSql($pager["advanceQueryConditions"], $args);
//					获取排序SQL
					$advanceQuerySortSql = QueryUtils::getAdvanceQuerySortSql($pager["advanceQuerySorts"]);
//					查询结果集放到信息中
					$resultSql = "select * from (".$sql.") t where 1=1 ".$fastQuerySql.$advanceQueryConditionSql.$advanceQuerySortSql;
					if ($stmt = $mysqli->prepare($resultSql)){
						$types = "";
						foreach ($args as $arg) {
							if (is_numeric($arg)) {
								$types.="i";
							}else{
								$types.="s";
							}
						}
						$execute_args = $args;
						array_splice($execute_args, 0, 0, $types);
						if(count($args)!=0){
							call_user_func_array(array($stmt, "bind_param"), ParamsUtils::refValues($execute_args));
						}
						$stmt->execute();
						$result = array();
						$stmt->bind_result($result);
						$exportDatas = array();
						while($row=mysql_fetch_array($result))
							array_push($exportDatas, $row);
						$pager["exportDatas"] = $exportDatas;
						$stmt->close();
					}
				}
				ExportUtils::export($pager);
				return;
			}
//			映射为int型
			$pageSize = $pager["pageSize"];
			$startRecord = $pager["startRecord"];
			$recordCount = $pager["recordCount"];
			$pageCount = $pager["pageCount"];
//			获取快速查询条件SQL、高级查询条件SQL
			$fastQuerySql = QueryUtils::getFastQuerySql($pager["fastQueryParameters"], $args);
			$advanceQueryConditionSql = QueryUtils::getAdvanceQueryConditionSql($pager["advanceQueryConditions"], $args);
//			获取排序SQL
			$advanceQuerySortSql = QueryUtils::getAdvanceQuerySortSql($pager["advanceQuerySorts"]);
//			获取总记录条数、总页数可能没有
			$countSql = "select count(*) from (".$sql.") t where 1=1 ".$fastQuerySql.$advanceQueryConditionSql.$advanceQuerySortSql;
			$recordCount = 0;
			if ($stmt = $mysqli->prepare($countSql)){
				$types = "";
				foreach ($args as $arg) {
					if (is_numeric($arg)) {
						$types.="i";
					}else{
						$types.="s";
					}
				}
				$execute_args = $args;
				array_splice($execute_args, 0, 0, $types);
				if(count($args)!=0){
					call_user_func_array(array($stmt, "bind_param"), ParamsUtils::refValues($execute_args));
				}
				$stmt->execute();
				$stmt->bind_result($result);
				while($stmt->fetch()){
					$recordCount = $result;
				}
				$stmt->close();
			}
			$pager["recordCount"] = $recordCount;
			$pageCount = $recordCount/$pageSize+($recordCount%$pageSize>0?1:0);
			$pager["pageCount"] = $pageCount;
//			查询结果集放到信息中
			$resultSql = "";
			$resultSql.="select * from (".$sql.") t where 1=1 ".$fastQuerySql.$advanceQueryConditionSql.$advanceQuerySortSql." limit ?, ?";
			array_push($args, $startRecord);
			array_push($args, $pageSize);
			$dataList = array();
			if ($stmt = $mysqli->prepare($resultSql)){
				$types = "";
				foreach ($args as $arg) {
					if (is_numeric($arg)) {
						$types.="i";
					}else{
						$types.="s";
					}
				}
				$execute_args = $args;
				array_splice($execute_args, 0, 0, $types);
				if(count($args)!=0){
					call_user_func_array(array($stmt, "bind_param"), ParamsUtils::refValues($execute_args));
				}
				$stmt->execute();
				$dataList = DtGridUtils::fetch($stmt);
				$stmt->close();
			}
			$pager["exhibitDatas"] = $dataList;
//			设置查询成功
			$pager["isSuccess"] = true;
		}catch(Exception $e){
//			设置查询失败
			$pager["isSuccess"] = false;
		}
		echo json_encode($pager);
	}
	
	/**
	 * 格式化日期
	 * @param column
	 * @param content
	 * @return
	 * @throws Exception
	 */
	static function formatContent($column, $content){
		try{
//			处理码表
			if($column["codeTable"]!=null){
				if($column["codeTable"][$content]){
					return $column["codeTable"][$content];
				}
			}
//			处理日期、数字的默认情况
			if(strcasecmp("date", $column["type"])==0&&$column["format"]!=null&&!strcasecmp("", $column["format"])){
				if($column["otype"]!=null&&!strcasecmp("", $column["otype"])){
//					if(strcasecmp("time_stamp_s", $column["otype"])){
//						SimpleDateFormat sdf = new SimpleDateFormat(column.getFormat());
//						Date date = new Date(Integer.parseInt(content)*1000);
//						return sdf.format(date);
//					}else if(strcasecmp("time_stamp_ms", $column["otype"])){
//						SimpleDateFormat sdf = new SimpleDateFormat(column.getFormat());
//						Date date = new Date(Integer.parseInt(content));
//						return sdf.format(date);
//					}else if(strcasecmp("string", $column["otype"])){
//						if($column["oformat"]!=null&&!strcasecmp("", $column["oformat"])){
//							SimpleDateFormat osdf = new SimpleDateFormat($column["oformat"]);
//							SimpleDateFormat sdf = new SimpleDateFormat(column.getFormat());
//							Date date = osdf.parse(content);
//							return sdf.format(date);
//						}
//					}
				}
			}else if(strcasecmp("number", $column["type"])&&!strcasecmp("", $column["format"])){
//				DecimalFormat df = new DecimalFormat(column.getFormat());
//				content = df.format(Double.parseDouble(content));
			}
		}catch(Exception $e){}
		return $content;
	}
	
	/**
	 * 获取数据
	 */
	static function fetch($result) {
		$array = array();
		if($result instanceof mysqli_stmt) {
			$result->store_result();
			$variables = array();
			$data = array();
			$meta = $result->result_metadata();
			while($field = $meta->fetch_field())
				$variables[] = &$data[$field->name];
			call_user_func_array(array($result, 'bind_result'), $variables);
			$i=0;
			while($result->fetch()) {
				$array[$i] = array();
				foreach($data as $k=>$v)
					$array[$i][$k] = $v;
				$i++;
			}
		} else if($result instanceof mysqli_result) {
			while($row = $result->fetch_assoc())
				$array[] = $row;
		}
		return $array;
	}
	
}
?>