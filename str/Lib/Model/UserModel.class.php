<?php
class UserModel extends Model {
	protected $_validate         =         array(
			array('Username','require','用户名必须填写！'), //默认情况下用正则进行验证
			array('Username','','用户名已经存在！',0,'unique',1), //默认情况下用正则进行验证
			array('Name','require','真实姓名必须填写！'), //默认情况下用正则进行验证
			array('WorkID','require','工号必须填写！'), //默认情况下用正则进行验证
			array('WorkID','','工号已经存在！',0,'unique',1), //默认情况下用正则进行验证
			//array('Mobile','/^1[3|4|5|8|7][0-9]\d{4,8}$/','手机号码错误！','2','regex',3),
	);

}