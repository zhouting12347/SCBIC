<?php
class Model_HisModel extends Model {
	protected $_validate         =         array(
			array('ModelName','require','模特姓名必须！'), //默认情况下用正则进行验证
			//array('name','','帐号名称已经存在！',0,’unique’,1), // 在新增的时候验证name字段是否唯一
	);
	
	
}