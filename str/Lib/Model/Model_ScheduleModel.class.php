<?php
class Model_ScheduleModel extends Model {
	protected $_validate         =         array(
			array('ModelStartTime','require','开始时间必须！'), //默认情况下用正则进行验证
			array('ModelEndTime','require','结束时间必须！'), //默认情况下用正则进行验证
			//array('name','','帐号名称已经存在！',0,’unique’,1), // 在新增的时候验证name字段是否唯一
	);
	
	
}