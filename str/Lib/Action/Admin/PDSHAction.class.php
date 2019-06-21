<?php
class PDSHAction extends CommonAction
{
	/**
	+--------------------------------
	* 安排导演主持人首页
	+--------------------------------
	* @date: 2016-1-4 上午11:42:43
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function index(){
		//读取任务列表
		$VideoProduce=M('VideoProduce');
		$task=$VideoProduce->where("RequestID='task' and IfSendPDSH=0 and PassOrNot=1")->select();//未选择导演主持
		$this->assign('task',$task);
		$this->assign("level3","active");
		$this->display();
	}
	
	/**
	+--------------------------------
	* 选择导演主持页
	+--------------------------------
	* @date: 2016-1-4 下午2:58:45
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function selectPDSH(){
		//详细条目
		$VideoProduce=new VideoProduceModel();
		$taskID=$_GET['taskID'];
		$program=$VideoProduce->videoProducePrograme($taskID);
		
		//选出所有主持人和导演
		$Director=new DirectorModel();
		$director=$Director->selectUserByRole('导演',$taskID);
		$host=$Director->selectUserByRole('主持',$taskID);
		$this->assign('director',$director);
		$this->assign('host',$host);
		$this->assign('program',$program);
		$this->assign('taskID',$taskID);
		$this->display('selectSHPD');
	}
	
	/**
	+--------------------------------
	* 提交主持人导演
	+--------------------------------
	* @date: 2016-1-6 下午4:27:55
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function selectPDSHHandler(){
		$VideoProduce=M('VideoProduce');
		$res=$VideoProduce->where("Id=$_POST[programID] ")->setField(array('PDID','SHID'),array($_POST[director],$_POST[host]));
		if($res){
   			$this->ajaxReturn('','操作成功！',1);
   		}else{
   			$this->ajaxReturn('','操作失败！',0);
   		}
	}
	
	/**
	+--------------------------------
	* 导演，主持本周安排
	+--------------------------------
	* @date: 2016-1-6 下午5:26:25
	* @author: Str
	* @param: variable
	* @return: 
	*/
	public function week_arrange(){
		$Director=new DirectorModel();
		$work=$Director->weekWorkArrangeByTask($_GET['taskID'], $_GET['userID'], $_GET['role']);
		$this->assign('work',$work);
		$this->display('week_arrange');
	}
	
	/**
	+--------------------------------
	* 发送导演主持操作
	+--------------------------------
	* @date: 2016-1-7 上午11:39:16
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function sendPDSHHandler(){
		$programID=$_GET['programID'];
		$VideoProduce=M('VideoProduce');
 		$res=$VideoProduce->where("Id=$programID")->setField('IfSendPDSH',1);
		if($res){
			//发送通知
			MessageModel::systemMessage($programID,"PDSH");
			$this->ajaxReturn('','操作成功！',1);
		}else{
			$this->ajaxReturn('','操作失败！',0);
		}
	}
	
	/**
	+--------------------------------
	* 发送全部选好的导演主持操作
	+--------------------------------
	* @date: 2016-1-7 下午4:40:17
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function allSendPDSHHandler(){
		$taskID=$_GET['taskID'];
		$VideoProduce=M('VideoProduce');
		//取出未发送通知的条目
		$program=$VideoProduce->where("TaskID='$taskID' and PDID is not null and SHID is not null and IfSendPDSH=0")->select();
		$res=$VideoProduce->where("TaskID='$taskID' and PDID is not null and SHID is not null")->setField('IfSendPDSH',1);
		if($res){
			//发送通知
			for($i=0;$i<count($program);$i++){
				MessageModel::systemMessage($program[$i][Id],"PDSH");
			}
			$this->ajaxReturn('','操作成功！',1);
		}else{
			$this->ajaxReturn('','操作失败！',0);
		}
	}
	
	
	
	/**
	+--------------------------------
	* 关闭窗口时，检查是否存在未发送的任务
	* 有未发送任务的提示，如果全部都发送，则整条总任务置为发送状态
	+--------------------------------
	* @date: 2016-1-7 下午2:42:05
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function checkIfSendPDSH(){
		$taskID=$_GET['taskID'];
		$VideoProduce=M('VideoProduce');
		$res=$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->find();
		//如果是多条导入
		if($res['InputMethod']=="Form"){
			$ifSend=$VideoProduce->where("TaskID='$taskID' and RequestID!='task' and PDID is not null and SHID is not null and IfSendPDSH=0 ")->find();
			if($ifSend){
				$this->ajaxReturn('','还有未发送导演主持的任务！',1);
			}else{
				//查询总任务内的条目是否全部发送，全部发送则IfSendPDSH置为1
				$isAllSend=$VideoProduce->where("TaskID='$taskID' and RequestID!='task' and PDID is null and SHID is null and IfSendPDSH=0 ")->find();
				if(!$isAllSend){
					//如果全部发送了
					$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->setField('IfSendPDSH',1);
				}
				$this->ajaxReturn('','已全部发送',0);
			}
		}
		//单条录入的
		else if($res['InputMethod']=="Single"){
			$ifSend=$VideoProduce->where("TaskID='$taskID' and RequestID='task' and PDID is not null and SHID is not null and IfSendPDSH=0 ")->find();
			if($ifSend){
				$this->ajaxReturn('','还有未发送导演主持的任务！',1);
			}else{
				//该条总任务IfSendPDSH置为1
				$VideoProduce->where("TaskID='$taskID'")->setField('IfSendPDSH',1);
				$this->ajaxReturn('','已全部发送',0);
			}
		}
	}

}
?>