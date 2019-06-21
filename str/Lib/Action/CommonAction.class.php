<?php
//前端公共类
class CommonAction extends Action{
	/**
	 +------------------------------------------------------------------------------
	 * 登录初始化判断
	 +------------------------------------------------------------------------------
	 *
	 +------------------------------------------------------------------------------
	 */
	function _initialize(){
		//判断是否开启认证，并且当前模块需要验证
		if(C('USER_AUTH_ON')&&!in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))){
			//导入RBAC类，开始验证
			import('ORG.Util.RBAC');
			//通过accessDecision获取权限信息
			if(!RBAC::AccessDecision(GROUP_NAME)){
				//没有获取到权限信息时需要执行的代码
				//1、用户没有登录
				if(!$_SESSION[C('USER_AUTH_KEY')]){
					//$url= U('Public/login');
					$this->redirect('Public/login');
				}
				//$this->redirect('Public/login');
				$this->assign("flag",0);
				$this->error("你没有操作权限");
			}
		}
	}
	
	
}