<?php
class AuditAction extends CommonAction
{
	/**
	 +------------------------------------------
	 * 审核首页
	 +------------------------------------------
	 */
    public function index()
    {
    	$VideoProduce=M('VideoProduce');
    	$unaudit=$VideoProduce->where("RequestID='task' and IfSendAudit=1 and IfAudit=0")->select();//未审核任务
    	$audited=$VideoProduce->where("RequestID='task' and IfSendAudit=1 and IfAudit=1 and PassOrNot=1")->select();//已审核通过任务
    	
    	$this->assign('unaudit',$unaudit);
    	$this->assign('audited',$audited);
    	$this->assign("level2","active");
		$this->display('index');
    }
    
   /**
    +------------------------------------------
    * 审核任务详细内容
    +------------------------------------------
    */
   public function program_table(){
		$VideoProduce=D('VideoProduce');
    	$taskID=$_GET['taskID'];
/*     	$condition="where TaskID='$taskID' and RequestID!='task'";
    	$program=$VideoProduce->videoProduceSelect($taskID,$condition); */
    	$program=$VideoProduce->where("TaskID='$taskID' and RequestID!='task' ")->select();
    	//如果没查询出来，说明是单条任务，继续查询
    	if(!$program){
    		$program=$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->select();
    		//PassOrNot 0未操作 1通过 2 不通过
    		//查询是否有未通过的详细任务，有未通过的任务则提交按钮显示【退回修改】，无未通过任务显示【全部通过】
    		$res=$VideoProduce
    		->where("TaskID='$taskID' and RequestID='task' and PassOrNot=2 ")
    		->find();
    	}else{
	    	$res=$VideoProduce
	    	->where("TaskID='$taskID' and RequestID!='task' and PassOrNot=2 ")
	    	->find();
    	}
    	if(!$res){
    		$this->assign('button',1);//显示【全部通过】按钮
    	}else{
    		$this->assign('button',2);//显示【退回修改】按钮
    	}
    	$this->assign('taskID',$taskID);
    	$this->assign('program',$program);
    	$this->display('program_table');
   }
   
   /**
    +------------------------------------------
    * 审核完成的任务
    +------------------------------------------
    */
   public function check_table(){
   		$VideoProduce=D('VideoProduce');
  	 	$taskID=$_GET['taskID'];
  	 	$program=$VideoProduce->where("TaskID='$taskID' and RequestID!='task' ")->select();
  	 	//如果没查询出来，说明是单条任务，继续查询
  	 	if(!$program){
  	 		$program=$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->select();
  	 	}
  	 	$this->assign('program',$program);
  	 	$this->display('check_table');
   }
   
   /**
    +------------------------------------------
    * 不通过原因弹出层
    +------------------------------------------
    */
   public function reason_layer(){
   		$id=$_GET['id'];
   		$this->assign('id',$id);
   		$this->display('reason_layer');
   }
   
  /**
   +------------------------------------------
   * 单条详细任务不通过操作
   +------------------------------------------
   */
   public function singleNotPassHandler(){
   		if(!$_POST['Reason']){
   			$this->assign('flag',1);
   			$this->error("请填写不通过原因");
   		}
   		$VideoProduce=M('VideoProduce');
   		$data['Id']=$_POST['Id'];
   		$data['Reason']=$_POST['Reason'];
   		$data['PassOrNot']=2;
   		$res=$VideoProduce->save($data);
   		if($res){
   			$this->assign('flag',0);
   			$this->success();
   		}else{
   			$this->assign('flag',0);
   			$this->error();
   		}
   }
   
   /**
    +------------------------------------------
    * 整条任务退回修改操作
    +------------------------------------------
    */
   public function allReturnHandler(){
   		$VideoProduce=M('VideoProduce');
   		$taskID=$_GET['taskID'];
   		//退回任务重置状态
   		$data['IfAudit']=0; //重置为未审核状态
   		$data['IfSendAudit']=0; //重置为未提交状态
   		$data['PassOrNot']=2; //未通过审核
   		$data['AuditerID']=$_SESSION['uid'];  //审核人ID
   		$data['AuditTime']=date('Y-m-d H:i:s'); //审核时间
   		$res=$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->save($data);
   		if($res){
   			$this->ajaxReturn('','操作成功！',1);
   		}else{
   			$this->ajaxReturn('','操作失败！',0);
   		}
   }
   
   /**
    +------------------------------------------
    * 整条任务全部通过操作
    +------------------------------------------
    */
   public function allPassHandler(){
  	 	$VideoProduce=M('VideoProduce');
   		$taskID=$_GET['taskID'];
   		//修改任务通过状态
   		$data['IfAudit']=1; //已经审核
   		$data['PassOrNot']=1; //通过审核
   		$data['AuditerID']=$_SESSION['uid'];  //审核人ID
   		$data['AuditTime']=date('Y-m-d H:i:s'); //审核时间
   		$res=$VideoProduce->where("TaskID='$taskID'")->save($data);
   		if($res){
   			$this->ajaxReturn('','操作成功！',1);
   		}else{
   			$this->ajaxReturn('','操作失败！',0);
   		}
   }
}
?>