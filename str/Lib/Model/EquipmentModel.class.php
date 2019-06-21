<?php
class EquipmentModel extends Model {
	protected $_validate         =         array(
			array('EquipmentID','require','设备ID必须！'), //默认情况下用正则进行验证
			array('EquipmentID','','设备ID已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
			
	);
	
	
}