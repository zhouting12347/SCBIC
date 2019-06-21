<?php
class MeetingRoomModel extends Model {
	protected $_validate         =         array(
			array('RoomName','require','会议室名称必须！'), //默认情况下用正则进行验证
			array('RoomName','','会议室名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
	);
	
	/**
	 * 会议室详细条目查询
	 * @param string $taskID  任务的ID号
	 * @param string $condition 查询条件
	 * @return array
	 */
	public function meetingProducePrograme($taskID){
		$model=new Model();
		$condition="where TaskID='$taskID' and RequestID!='task' and IfSendPDSH=1 ";
		$sql="select *, (select Name from CJ_User where Id=V.PDID) as PD,
		(select Name from CJ_User where Id=V.SHID) as SH,(select RoomName from CJ_MeetingRoom where Id=RoomID) as RoomName  from CJ_VideoProduce V ".$condition ."ORDER BY Id";
		$res=$model->query($sql);
		//如果没有查询出结果，说明是单条的任务
		if(!$res){
			$condition="where TaskID='$taskID' and RequestID='task' and IfSendPDSH=1 ";
			$sql="select *, (select Name from CJ_User where Id=V.PDID) as PD,
			(select Name from CJ_User where Id=V.SHID) as SH ,(select RoomName from CJ_MeetingRoom where Id=RoomID) as RoomName from CJ_VideoProduce V ".$condition ."ORDER BY Id";
			$res=$model->query($sql);
		}
		return $res;
	}
	
	/**
	+--------------------------------
	* 获取本周总任务，或下周总任务
	+--------------------------------
	* @date: 2016-1-11 上午11:25:47
	* @author: Str
	* @param: $weektime    this本周 next下周
	* @return: array
	*/
	public function getTaskByWeek($weektime){
		$VideoProduce=M('VideoProduce');
		//会议室安排在选定并发送导演主持之后
		//IfSendPDSH=1
		if($weektime=="this"){
			//本周任务
			$sql="SELECT distinct TaskID FROM CJ_VideoProduce WHERE YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = YEARWEEK(now()) and PassOrNot=1 and IfSendPDSH=1";
		}else if($weektime=="next"){
			//下周任务
			$sql="SELECT distinct TaskID FROM CJ_VideoProduce WHERE YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = YEARWEEK(now())+1 and PassOrNot=1 and IfSendPDSH=1";
		}
		$taskID=$VideoProduce->query($sql);
		foreach($taskID as $vo){
			//根据taskID查询总任务
			$task[]=$VideoProduce->where("TaskID='$vo[TaskID]' and RequestID='task' ")->find();
		}
		return $task;
	}
	
}