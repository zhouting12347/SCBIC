<?php
class MessageAction extends Action
{
	
	/**
	+--------------------------------
	* 消息首页
	+--------------------------------
	* @date: 2016-3-17 下午5:08:59
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function message_index(){
		$Message=M("Message");
		$sql="SELECT *,(SELECT Name FROM CJ_User WHERE CJ_User.Id=CJ_Message.SenderID) as Sender
		 FROM CJ_Message WHERE ReceiverID=$_SESSION[uid] AND Status !=3 ORDER BY SendTime DESC";
		$message=$Message->query($sql);
		$this->assign("message",$message);
		$this->display("message_index");
	}
	
	/**
	+--------------------------------
	* 发送私信
	+--------------------------------
	* @date: 2016-3-18 下午4:17:10
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function send_msg(){
		//取出除自己外的所有人
		$User=M("User");
		$user=$User->where("Status=1 and Id!=$_SESSION[uid]")->select();
		$this->assign("user",$user);
		$this->display('send_msg');
	}
	
	/**
	+--------------------------------
	* 发送消息操作
	+--------------------------------
	* @date: 2016-3-17 下午1:41:43
	* @author: Str
	* @param: variable
	* @return:
	*/
    public function sendMessageHandler()
    {
    	if(!$_POST['ReceiverID']){
    		$this->assign("flag",0);
    		$this->error("请选择接收人");
    	}
    	if(!$_POST['Info']){
    		$this->assign("flag",0);
    		$this->error("请填写发送内容");
    	}
    	$_POST['SendTime']=date("Y-m-d H:i:s",time());
    	$_POST['SenderID']=$_SESSION[uid];
    	$Message=M('Message');
    	$Message->create();
    	$res=$Message->add();
		if($res){
			$this->assign("flag",0);
			$this->success();
		}else{
			$this->assign("flag",0);
			$this->error();
		}
    }
    /**
    +--------------------------------
    * 标记为已读
    +--------------------------------
    * @date: 2016-3-18 下午4:49:21
    * @author: Str
    * @param: variable
    * @return:
    */
    public function markReadHandler(){
    	$Message=M("Message");
		foreach($_POST['messageID'] as $v){
			$Message->where("Id=$v")->setField("Status",2);
		}
		$Message=new MessageModel();
		$_SESSION["messageCount"]=$Message->getMsgCount();
		$this->redirect("Message/message_index");
    }
    
    /**
    +--------------------------------
    * 删除信息操作
    +--------------------------------
    * @date: 2016-3-18 下午4:11:44
    * @author: Str
    * @param: variable
    * @return:
    */
    public function delMessageHandler(){
    	$Message=M("Message");
		foreach($_POST['messageID'] as $v){
			$Message->where("Id=$v")->setField("Status",3);
		}
		$Message=new MessageModel();
		$_SESSION["messageCount"]=$Message->getMsgCount();
		$this->redirect("Message/message_index");
    }
   
  
}
?>