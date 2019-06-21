<?php
class MessageModel extends Model {
	protected $_validate         =         array(
	);
	
	/**
	 +--------------------------------
	 * 查询我的未读消息条数
	 +--------------------------------
	 * @date: 2016-3-21 下午1:35:45
	 * @author: Str
	 * @param: variable
	 * @return: int
	 */
	public function getMsgCount(){
		$Message=M("Message");
		$count=$Message->where("ReceiverID=$_SESSION[uid] and Status=1")->count();
		return $count;
	}
	
	/**
	 +--------------------------------
	 * 系统消息发送
	 +--------------------------------
	 * @date: 2016-3-23 下午3:46:25
	 * @author: Str
	 * @param: $programID,$type
	 * @return:
	 */
	public static function systemMessage($programID,$type){
		$VideoProduce=M("VideoProduce");
		$res=$VideoProduce->where("Id=$programID")->find();
		$Message=M("Message");
		switch($type){
			//选择导演主持之后发送消息
			case "PDSH":
				//发送导演
				$data['ReceiverID']=$res['PDID'];
				$data['SenderID']=9999999999;
				$data['Info']="你有一条导演任务";
				$data['SendTime']=date("Y-m-d H:i:s",time());
				$Message->add($data);
				//发送主持
				$data['ReceiverID']=$res['SHID'];
				$data['Info']="你有一条主持任务";
				$Message->add($data);
				break;	
			//发送会议通知
			case "Meeting":
				//发送导演
				$data['ReceiverID']=$res['PDID'];
				$data['SenderID']=9999999999;
				$data['Info']="你有一条会议任务";
				$data['SendTime']=date("Y-m-d H:i:s",time());
				$Message->add($data);
				//发送主持
				$data['ReceiverID']=$res['SHID'];
				$data['Info']="你有一条会议任务";
				$Message->add($data);
				break;
		}
		
	}
	
}