<?php
class PersonalAction extends CommonAction{
	//修改密码页
	public function changePassword(){
		$this->display('changePassword');
	}
	
	//修改密码操作
	public function changePasswordHandler(){
		$Id=$_SESSION['uid'];//用户ID
		if($_POST['newPwd1']!=$_POST['newPwd2']){
			$this->assign("flag",0);
			$this->error("两次输入的密码不相同！");
		}
		if($_POST['newPwd1']==null||$_POST['newPwd2']==null){
			$this->assign("flag",0);
			$this->error("密码不能为空！");
		}
		$User=M('User');
		$user=$User->where("Id=$Id")->find();
		if(md5($_POST['oldPwd'])!=$user['Password']){
			$this->assign("flag",0);
			$this->error("旧密码输入错误！");
		}else if(md5($_POST['oldPwd'])==$user['Password']){
			$res=$User->where("Id=$Id")->setField("Password",md5($_POST['newPwd1']));
			if($res){
				session_unset();
				session_destroy();
				$this->redirect("Public/success2");
			}else{
				$this->assign("flag",0);
				$this->error();
			}
		}
	}
}