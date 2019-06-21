<?php
class DirectorModel extends Model {
	protected $_validate         =         array(
			//array('ModelName','require','模特姓名必须！'), //默认情况下用正则进行验证
			//array('name','','帐号名称已经存在！',0,’unique’,1), // 在新增的时候验证name字段是否唯一
	);
	
	/**
	 +--------------------------------
	 * 查询角色名称下所有的查询用户
	 +--------------------------------
	 * @date: 2016-1-6 下午3:11:48
	 * @author: Str
	 * @param: $roleName  角色名称
	 * @Param: $taskID 任务ID
	 * @return: array
	 */
	public function selectUserByRole($roleName,$taskID){
		$Role=M('Role');
		$res=$Role->where("name='$roleName' ")->find();
		$roleID=$res['id'];
	
		//查询角色用户对应表,查询出该角色下的所有用户
		$sql="select * from CJ_Role_user,CJ_User where CJ_Role_user.role_id=$roleID and CJ_Role_user.user_id=CJ_User.id";
		$user=$Role->query($sql);
		$VideoProduce=M('VideoProduce');
		//根据当前总任务ID 查询每个用户在总任务中分配到的任务条数
		foreach($user as $k=>$vo){
			$PDTaskCount=$VideoProduce->where("TaskID='$taskID' and PDID=$vo[Id]")->count();
			$SHTaskCount=$VideoProduce->where("TaskID='$taskID' and SHID=$vo[Id]")->count();
			$user[$k]['PDTaskCount']=$PDTaskCount;
			$user[$k]['SHTaskCount']=$SHTaskCount;
		}
		return $user;
	}
	
	/**
	+--------------------------------
	* 查询导演主持当前总任务内的排班情况
	+--------------------------------
	* @date: 2016-1-7 上午10:18:46
	* @author: Str
	* @param: $taskID
	* @param: $userID 用户ID
	* @param: $role 主持或导演
	* @return: array
	*/
	public function weekWorkArrangeByTask($taskID,$userID,$role){
		$VideoProduce=M('VideoProduce');
		if($role=="director"){
			$condition="TaskID='$taskID' and PDID='$userID' ";
		}else if($role=="host"){
			$condition="TaskID='$taskID' and SHID='$userID' ";
		}
		$res=$VideoProduce->where($condition)->select();
		return $res;
	}
	
	/**
	+--------------------------------
	* 导演一周会议
	+--------------------------------
	* @date: 2016-1-7 上午10:23:42
	* @author: Str
	* @param: $status finish unfinish 未完成 已完成会议
	* @return: array
	*/
	public function getMeetingByWeek($status){
		$VideoProduce=M("VideoProduce");
		//本周未完成会议，会议已发起，导演ID为登录人，导演未确认开会
		if($status=='unfinish'){
			$sql="SELECT *,(select Name from CJ_User where Id=V.PDID) as PD,
			(select Name from CJ_User where Id=V.SHID) as SH,
			(select RoomName from CJ_MeetingRoom where Id=V.RoomID) as RoomName 
			FROM CJ_VideoProduce as V WHERE PDID=$_SESSION[uid] and MeetSenderID is not null and 
			YEARWEEK(date_format(MeetStartTime,'%Y-%m-%d')) = YEARWEEK(now()) and IfChange is null and IfMeetEnd is null";
		}else if($status=='finish'){//会议状态为完成
			$sql="SELECT *,(select Name from CJ_User where Id=V.PDID) as PD,
			(select Name from CJ_User where Id=V.SHID) as SH,
			(select RoomName from CJ_MeetingRoom where Id=V.RoomID) as RoomName 
			FROM CJ_VideoProduce as V WHERE PDID=$_SESSION[uid] and MeetSenderID is not null and 
			IfMeetEnd=1 and YEARWEEK(date_format(MeetStartTime,'%Y-%m-%d')) = YEARWEEK(now())";
		}
		$meet=$VideoProduce->query($sql);
		return $meet;
	}
}