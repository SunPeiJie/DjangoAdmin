<?php

class Column {
	
	/**
	 * 编号
	 */
	public $id;
	
	/**
	 * 是否参与高级查询
	 */
	public $search;
	
	/**
	 * 是否作为导出列导出[default:true]
	 */
	public $export;
	
	/**
	 * 是否作为打印列打印[default:true]
	 */
	public $print;
	
	/**
	 * 是否作为扩展列隐藏备用[default:true(对于自定义的复选或相关操作内容，请设置为false以免数据冲突)]
	 */
	public $extra;
	
	/**
	 * 显示的列名
	 */
	public $title;
	
	/**
	 * 字段类型，用于高级查询
	 */
	public $type;
	
	/**
	 * 码表映射，用于高级查询及显示
	 */
	public $codeTable;
	
	/**
	 * 列样式
	 */
	public $columnStyle;
	
	/**
	 * 列样式表
	 */
	public $columnClass;
	
	/**
	 * 列头样式
	 */
	public $headerStyle;
	
	/**
	 * 列头样式表
	 */
	public $headerClass;
	
	/**
	 * 彻底隐藏[default:false]
	 */
	public $hide;
	
	/**
	 * 隐藏类别[lg|md|sm|xs，默认为空，全部显示]
	 */
	public $hideType;
	
	/**
	 * 快速查询
	 */
	public $fastQuery;
	
	/**
	 * 快速查询类别
	 */
	public $fastQueryType;
	
	/**
	 * 格式化[money|date|time]
	 */
	public $format;
	
	/**
	 * 回调方法，参数：record value
	 */
	public $resolution;

}
?>