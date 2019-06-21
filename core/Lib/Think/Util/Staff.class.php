<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 STR
// +----------------------------------------------------------------------
// | Author: ZT 2016.3.10
// +----------------------------------------------------------------------

/**
+--------------------------------
* 制作人员类
+--------------------------------
* @date: 2016-3-10 上午9:54:53
* @author: Str
* @param: $GLOBALS
* @return: 
*/

class Staff extends Think
{
	//员工id
	public $id;
	
	//任务数量
	public $taskNum=0;
	
	//任务权重值
	public $taskScore=0;
	
	/**
	+--------------------------------
	* 架构函数
	+--------------------------------
	* @date: 2016-3-10 上午10:01:43
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function __construct($id){
		$this->id=$id;
	}
	
}