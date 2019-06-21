<?php
class VideoProduceModel extends Model {
	
	protected $_validate         =         array(
			array('TaskName','require','任务名称必须填写！'), //默认情况下用正则进行验证
			array('TaskName','','任务名称不能重复！',0,'unique',1), //默认情况下用正则进行验证
	);
	
	/**
	 * 任务表详细数据查询
	 * @param string $taskID  任务的ID号
	 * @param string $condition 查询条件
	 * @return array
	 */
	public function videoProducePrograme($taskID){
		$model=new Model();
		$condition="where TaskID='$taskID' and RequestID!='task' and IfSendPDSH=0 ";
		$sql="select *, (select Name from CJ_User where Id=V.PDID) as PD,
		(select Name from CJ_User where Id=V.SHID) as SH  from CJ_VideoProduce V ".$condition ."ORDER BY Id";
		$res=$model->query($sql);
		
		//如果没有查询出结果，说明是单条的任务
		if(!$res){
			$condition="where TaskID='$taskID' and RequestID='task' and IfSendPDSH=0 ";
			$sql="select *, (select Name from CJ_User where Id=V.PDID) as PD,
			(select Name from CJ_User where Id=V.SHID) as SH  from CJ_VideoProduce V ".$condition ."ORDER BY Id";
			$res=$model->query($sql);
		}
		return $res;
	} 
	
	
	/**
	+--------------------------------
	* 查询某人本周排班情况
	* 根据节目单查询
	+--------------------------------
	* @date: 2016-1-6 下午5:30:00
	* @author: Str
	* @param: $userID 用户id
	* @param: $taskID 任务单号
	* @return: array
	*/
	public function weekWorkArrange($userID,$taskID){
		
	}
}