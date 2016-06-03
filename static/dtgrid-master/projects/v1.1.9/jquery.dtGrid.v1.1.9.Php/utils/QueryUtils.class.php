<?php

class QueryUtils {

	/**
	 * 获取快速查询的条件SQL
	 * @param params 快速查询参数
	 * @return 条件SQL
	 * @throws Exception
	 */
	static function getFastQuerySql($params, &$args){
//		如果传递的条件参数为空则返回空字符串
		if($params===null){
			return "";
		}
//		定义条件SQL
		$conditionSql = "";
//		遍历参数，拼接SQL
		foreach ($params as $key => $value) {
			if(trim($value)===""){
				continue;
			}
			if(strpos($key, "_")){
				$field = substr($key, strpos($key, "_")+1, strlen($key));
//				equal
				if(strpos($key, "eq_")===0){
					$conditionSql.=" and ".$field." = ? ";
					array_push($args, $value);
					continue;
				}
//				not equal
				if(strpos($key, "ne_")===0){
					$conditionSql.=" and ".$field." != ? ";
					array_push($args, $value);
					continue;
				}
//				like
				if(strpos($key, "lk_")===0){
					$conditionSql.=" and ".$field." like ? ";
					array_push($args, "%".$value."%");
					continue;
				}
//				right like
				if(strpos($key, "rl_")===0){
					$conditionSql.=" and ".$field." like ? ";
					array_push($args, "%".$value);
					continue;
				}
//				left like
				if(strpos($key, "ll_")===0){
					$conditionSql.=" and ".$field." like ? ";
					array_push($args, $value."%");
					continue;
				}
//				is null
				if(strpos($key, "in_")===0){
					$conditionSql.=" and ".$field." is null ";
					continue;
				}
//				is not null
				if(strpos($key, "inn_")===0){
					$conditionSql.=" and ".$field." is not null ";
					continue;
				}
//				great then
				if(strpos($key, "gt_")===0){
					$conditionSql.=" and ".$field." > ? ";
					array_push($args, $value);
					continue;
				}
//				great then and equal
				if(strpos($key, "ge_")===0){
					$conditionSql.=" and ".$field." >= ? ";
					array_push($args, $value);
					continue;
				}
//				less then
				if(strpos($key, "lt_")===0){
					$conditionSql.=" and ".$field." < ? ";
					array_push($args, $value);
					continue;
				}
//				less then and equal
				if(strpos($key, "le_")===0){
					$conditionSql.=" and ".$field." <= ? ";
					array_push($args, $value);
					continue;
				}
			}
		}
//		返回条件SQL
		return $conditionSql;
	}
	
	/**
	 * 获取高级查询的条件SQL
	 * @param advanceQueryConditions 查询条件列表
	 * @return 条件SQL
	 * @throws Exception
	 */
	static function getAdvanceQueryConditionSql($advanceQueryConditions, &$args){
//		定义条件SQL
		$conditionSql = "";
		if($advanceQueryConditions!=null&&count($advanceQueryConditions)>0){
//			加入前置的and参数
			$conditionSql.=" and ";
			foreach($advanceQueryConditions as $advanceQueryCondition){
//				获取参数：leftParentheses-左括号 field-字段名 condition-条件 value-值 rightParentheses-右括号 logic-逻辑符号
				$leftParentheses = $advanceQueryCondition["leftParentheses"];
				$field = $advanceQueryCondition["field"];
				$condition = $advanceQueryCondition["condition"];
				$value = $advanceQueryCondition["value"];
				$rightParentheses = $advanceQueryCondition["rightParentheses"];
				$logic = $advanceQueryCondition["logic"];
//				拼接SQL
				$conditionSql.=QueryUtils::getSingleAdvanceQueryConditionSql($leftParentheses, $field, $condition, $value, $rightParentheses, $logic, $args);
			}
		}
//		返回条件SQL
		return $conditionSql;
	}
	
	/**
	 * 拼接单条的高级查询SQL
	 * @param conditionSql 条件SQL
	 * @param leftParentheses 左括号 0-( 1-(( 2-((( 3-(((( 4-(((((
	 * @param field 字段信息
	 * @param condition 条件 0-= 1-!= 2-like 3-start with 4-end with 5-> 6->= 7-< 8-<= 9-is null 10-is not null
	 * @param value 值
	 * @param rightParentheses 右括号 0-) 1-)) 2-))) 3-)))) 4-)))))
	 * @param logic 逻辑符号 0-and 1-or
	 */
	static function getSingleAdvanceQueryConditionSql($leftParentheses, $field, $condition, $value, $rightParentheses, $logic, &$args){
		$conditionSql = "";
//		获取左括号内容、右括号内容、逻辑符号内容
		$logic = QueryUtils::getConditionLogicContent($logic);
//		根据条件类型拼接SQL
		$conditionSql.=" ".$leftParentheses." ";
		if($condition==="0"){
			$conditionSql.=" ".$field." = ? ";
			array_push($args, $value);
		}else if($condition==="1"){
			$conditionSql.=" ".$field." != ? ";
			array_push($args, $value);
		}else if($condition==="2"){
			$conditionSql.=" ".$field." like ? ";
			array_push($args, "%".$value."%");
		}else if($condition==="3"){
			$conditionSql.=" ".$field." like ? ";
			array_push($args, $value."%");
		}else if($condition==="4"){
			$conditionSql.=" ".$field." like ? ";
			array_push($args, "%".$value);
		}else if($condition==="5"){
			$conditionSql.=" ".$field." > ? ";
			array_push($args, $value);
		}else if($condition==="6"){
			$conditionSql.=" ".$field." >= ? ";
			array_push($args, $value);
		}else if($condition==="7"){
			$conditionSql.=" ".$field." < ? ";
			array_push($args, $value);
		}else if($condition==="8"){
			$conditionSql.=" ".$field." <= ? ";
			array_push($args, $value);
		}else if($condition==="9"){
			$conditionSql.=" ".$field." is null ";
		}else if($condition==="10"){
			$conditionSql.=" ".$field." is not null ";
		}
		$conditionSql.=" ".$rightParentheses." ";
		$conditionSql.=" ".$logic." ";
		return $conditionSql;
	}
	
	/**
	 * 获取逻辑内容
	 * @param logic 逻辑码值
	 * @return 逻辑内容
	 */
	static function getConditionLogicContent($logic){
		$content = "";
		if($logic==="0"){
			$content = "and";
		}else if($logic==="1"){
			$content = "or";
		}
		return $content;
	}
	
	/**
	 * 获取高级查询的排序SQL
	 * @param advanceQuerySorts 排序列表
	 * @return 条件SQL
	 * @throws Exception
	 */
	static function getAdvanceQuerySortSql($advanceQuerySorts){
//		定义条件SQL
		$sortSql = "";
		if($advanceQuerySorts!=null&&count($advanceQuerySorts)>0){
//			加入前置的and参数
			$sortSql.=" order by ";
			foreach($advanceQuerySorts as $advanceQuerySort){
//				获取参数：field-字段名 logic-排序逻辑
				$field = $advanceQuerySort["field"];
				$logic = $advanceQuerySort["logic"];
//				拼接SQL
				$sortSql .= QueryUtils::getSingleAdvanceQuerySortSql($field, $logic);
			}
			$sortSql = substr($sortSql, 0, strlen($sortSql)-3);
		}
//		返回条件SQL
		return $sortSql;
	}
	
	/**
	 * 拼接单条的高级排序SQL
	 * @param sortSql 排序SQL
	 * @param field 字段信息
	 * @param logic 逻辑符号 0-asc 1-desc
	 */
	static function getSingleAdvanceQuerySortSql($field, $logic){
//		获取左括号内容、右括号内容、逻辑符号内容
		$logic = QueryUtils::getSortLogicContent($logic);
//		根据条件类型拼接SQL
		return " ".$field." ".$logic.",  ";
	}
	
	/**
	 * 获取排序逻辑内容
	 * @param logic 逻辑码值
	 * @return 逻辑内容
	 */
	static function getSortLogicContent($logic){
		$content = "";
		if($logic==="0"){
			$content = "asc";
		}else if($logic==="1"){
			$content = "desc";
		}
		return $content;
	}
	
}
?>