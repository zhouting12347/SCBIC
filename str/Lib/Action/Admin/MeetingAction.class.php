<?php
class MeetingAction extends CommonAction{
	
	/**
	+--------------------------------
	* 本周任务，下周任务列表
	+--------------------------------
	* @date: 2016-1-8 下午2:11:10
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function index(){
		$Meeting=new MeetingRoomModel();
		//本周总任务
		$taskThis=$Meeting->getTaskByWeek("this");
		//下周总任务
		$taskNext=$Meeting->getTaskByWeek("next");
		$this->assign('this',$taskThis);
		$this->assign('next',$taskNext);
		$this->assign("level5","active");
		$this->display('index');
	}
	
	/**
	+--------------------------------
	* 详细会议安排
	+--------------------------------
	* @date: 2016-1-11 下午2:53:20
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function arrange(){
		$Meeting=new MeetingRoomModel();
		$taskID=$_GET['taskID'];
		$program=$Meeting->meetingProducePrograme($taskID);
		//会议室分页
		import('@.ORG.Page');
		$Meeting=M('MeetingRoom');
		$count=$Meeting->where("IsAvailable=1 and IsDel=0")->count();
		$Page=new Page($count,10); // 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show(); // 分页显示输
		$this->assign('page',$show); // 赋值分页输出
		$room=$Meeting->where("IsAvailable=1 and IsDel=0")
		->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('program',$program);
		$this->assign('room',$room);
		$this->assign('taskID',$taskID);
		$this->display('arrange');
	}
	
	/**
	+--------------------------------
	* ajax会议室分页查询
	+--------------------------------
	* @date: 2016-1-12 上午11:15:16
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function ajaxRoomPage(){
		import('@.ORG.Page');
		$Meeting=M('MeetingRoom');
		$condition="";
		if($_GET['maxPeople']){//按照最大人数查询
			$condition.=" and MaxPeople>=$_GET[maxPeople]";
		}
		if($_GET['meetStartTime']&&$_GET['meetEndTime']){
			//查询该时段被占用的会议室
			$VideoProduce=M("VideoProduce");
			$res=$VideoProduce->where("((MeetStartTime<='$_GET[meetStartTime]' and MeetEndTime>='$_GET[meetStartTime]') 
				or (MeetStartTime>='$_GET[meetStartTime]' and MeetStartTime<='$_GET[meetEndTime]'))" )
			->field("RoomID")->Distinct(true)->select();
			foreach($res as $vo){
				$selectedRoom[]=$vo[RoomID];
			}
				$arr="(".implode(",",$selectedRoom).")";
				$condition.=" and Id not in $arr";
		}
		$count=$Meeting->where("IsAvailable=1 and IsDel=0 $condition")->count();
		$Page=new Page($count,10); // 实例化分页类 传入总记录数和每页显示的记录数
		$data['show']= $Page->show(); // 分页显示输
		$room=$Meeting->where("IsAvailable=1 and IsDel=0 $condition")
		->limit($Page->firstRow.','.$Page->listRows)->select();
		if($room){
			//排列会议室html代码
			foreach($room as $vo){
				if(!$vo[RoomEquipment]){
					$vo[RoomEquipment]="-";
				}
				$data['room'].='<div class="pricing-span" style="margin-left:5px;">
									<div class="widget-box pricing-box-small widget-color-blue">
										<div class="widget-header">
											<h5 class="widget-title bigger lighter">'.$vo[RoomName].'</h5>
										</div>
										<div class="widget-body">
											<div class="widget-main no-padding">
												<ul class="list-unstyled list-striped pricing-table">
													<div class="room-image">
														<img src="'.$vo[RoomPicDir].'" id="'.$vo[Id].'" style="cursor:pointer" />
													</div>
													<li>'.$vo[RoomSquare].'平方</li>
													<li>'.$vo[MaxPeople].'人</li>
													<li>'.$vo[RoomEquipment].'</li>
												</ul>
											</div>
											<div>
												<a href="#" class="btn btn-block btn-sm btn-primary" id="'.$vo[Id].'" >
													<span>选择</span>
												</a>
											</div>
										</div>
									</div>
								</div>';
			}
			$this->ajaxReturn($data,'',1);
		}else{
			$this->ajaxReturn('','未查询到相关结果',0);
		}
	}
	
	/**
	+--------------------------------
	* 会议时间层
	+--------------------------------
	* @date: 2016-1-12 下午3:28:23
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function meetingTime(){
		$this->assign('id',$_GET['id']);
		$this->assign('programID',$_GET['programID']);
		$this->assign('meetStartTime',$_GET['meetStartTime']);
		$this->assign('meetEndTime',$_GET['meetEndTime']);
		$this->display('meetingTime');
	}
	
	/**
	+--------------------------------
	* 添加会议操作
	+--------------------------------
	* @date: 2016-1-12 下午4:19:38
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function addMeetingHandler(){
		//查询会议室时间是否占用
		$VideoProduce=M('VideoProduce');
		$res=$VideoProduce->where("((MeetStartTime<='$_POST[MeetStartTime]' and MeetEndTime>='$_POST[MeetStartTime]') 
				or (MeetStartTime>='$_POST[MeetStartTime]' and MeetStartTime<='$_POST[MeetEndTime]')) 
				and(RoomID=$_POST[RoomID]) and Id!=$_POST[Id]")
		->find();
		if($res){
			$this->assign('flag',0);
			$this->error("会议室已被占用");
		}else{
			//查询该条目详情
			$program=$VideoProduce->where("Id=$_POST[Id]")->find();
			//查询导演时间是否冲突
			$PDConflict=$VideoProduce->where("((MeetStartTime<='$_POST[MeetStartTime]' and MeetEndTime>='$_POST[MeetStartTime]') 
				or (MeetStartTime>='$_POST[MeetStartTime]' and MeetStartTime<='$_POST[MeetEndTime]')) 
				and(PDID=$program[PDID]) and Id!=$_POST[Id]")
			->find();
			//查询主持时间是否冲突
			$SHConflict=$VideoProduce->where("((MeetStartTime<='$_POST[MeetStartTime]' and MeetEndTime>='$_POST[MeetStartTime]')
					or (MeetStartTime>='$_POST[MeetStartTime]' and MeetStartTime<='$_POST[MeetEndTime]'))
					and(SHID=$program[SHID]) and Id!=$_POST[Id]")
			->find();
			
			//查询MD时间是否冲突
			$MDConflict=$VideoProduce->where("((MeetStartTime<='$_POST[MeetStartTime]' and MeetEndTime>='$_POST[MeetStartTime]')
					or (MeetStartTime>='$_POST[MeetStartTime]' and MeetStartTime<='$_POST[MeetEndTime]'))
					and(MD='$program[MD]') and Id!=$_POST[Id]")
			->find();
			if(!($PDConflict||$SHConflict||$MDConflict)){
				//保存会议室ID，开始时间，结束时间
				$VideoProduce->create();
				$VideoProduce->save();
				$this->assign('flag',0);
				$this->success("操作成功");
			}else{
				isset($PDConflict)?$string.="导演时间冲突！":'';
				isset($SHConflict)?$string.="主持时间冲突！":'';
				isset($MDConflict)?$string.="MD时间冲突！":'';
				$this->assign('flag',0);
				$this->error($string);
			}
		}
	}
	
	/**
	+--------------------------------
	* 修改导演主持
	+--------------------------------
	* @date: 2016-1-13 下午4:23:16
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function editPDSH(){
		$programID=$_GET['programID'];
		//选出当前的主持和导演
		$VideoProduce=M('VideoProduce');
		$program=$VideoProduce->where("Id=$programID")->find();
		$this->assign('program',$program);
		//选出所有主持人和导演
		$Director=new DirectorModel();
		$director=$Director->selectUserByRole('导演','');
		$host=$Director->selectUserByRole('主持','');
		$this->assign('director',$director);
		$this->assign('host',$host);
		$this->assign('programID',$programID);
		$this->display('editPDSH');
	}
	
	/**
	+--------------------------------
	* 修改导演主持操作
	+--------------------------------
	* @date: 2016-1-13 下午5:00:37
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function editPDSHHandler(){
		//检查PD,SH时间是否冲突
		$VideoProduce=M('VideoProduce');
		$program=$VideoProduce->where("Id=$_POST[Id]")->find();
		//查询导演时间是否冲突
		$PDConflict=$VideoProduce->where("((MeetStartTime<='$program[MeetStartTime]' and MeetEndTime>='$program[MeetStartTime]')
				or (MeetStartTime>='$program[MeetStartTime]' and MeetStartTime<='$program[MeetEndTime]'))
				and(PDID=$_POST[PDID]) and Id!=$_POST[Id]")
				->find();
		//查询主持时间是否冲突
		$SHConflict=$VideoProduce->where("((MeetStartTime<='$program[MeetStartTime]' and MeetEndTime>='$program[MeetStartTime]')
				or (MeetStartTime>='$program[MeetStartTime]' and MeetStartTime<='$program[MeetEndTime]'))
				and(SHID=$_POST[SHID]) and Id!=$_POST[Id]")
				->find();
		if(!($PDConflict||$SHConflict)){
			//保存会议室ID，开始时间，结束时间
			$VideoProduce->create();
			$VideoProduce->save();
			$this->assign('flag',0);
			$this->success("操作成功");
		}else{
			isset($PDConflict)?$string.="导演时间冲突！":'';
			isset($SHConflict)?$string.="主持时间冲突！":'';
			$this->assign('flag',0);
			$this->error($string);
		}
	}
	
	/**
	+--------------------------------
	* 会议室单条发送
	+--------------------------------
	* @date: 2016-1-14 上午11:26:54
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function sendMeetingRoom(){
		$programID=$_GET['programID'];
		$VideoProduce=M('VideoProduce');
		$res=$VideoProduce->where("Id=$programID")->setField('MeetSenderID',$_SESSION['uid']);
		if($res){
			//发送通知
			MessageModel::systemMessage($programID,"Meeting");
			$this->ajaxReturn('','操作成功！',1);
		}else{
			$this->ajaxReturn('','操作失败！',0);
		}
	}
	
	/**
	+--------------------------------
	* 会议通知全部发送
	+--------------------------------
	* @date: 2016-1-14 上午11:45:55
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function sendAllMeetingRoom(){
		$taskID=$_GET['taskID'];
		$VideoProduce=M('VideoProduce');
		//取出未发送通知的条目
		$program=$VideoProduce->where("TaskID='$taskID' and PDID is not null and SHID is not null and MD is not null and IfSendPDSH=1 and RoomID is not null and MeetSenderID is null")->select();
		$res=$VideoProduce->where("TaskID='$taskID' and PDID is not null and SHID is not null and MD is not null and IfSendPDSH=1 and RoomID is not null")
		->setField('MeetSenderID',$_SESSION['uid']);
		if($res){
			//发送通知
			for($i=0;$i<count($program);$i++){
				MessageModel::systemMessage($program[$i][Id],"Meeting");
			}
			$this->ajaxReturn('','操作成功！',1);
		}else{
			$this->ajaxReturn('','操作失败！',0);
		}
	}
	
	/**
	+--------------------------------
	* 会议室使用时间表
	+--------------------------------
	* @date: 2016-1-14 下午4:37:54
	* @author: Str
	* @param: variable
	* @return: array
	*/
	public function roomTimeList(){
		isset($_GET['roomID'])?$roomID=$_GET['roomID']:$roomID=$_POST['roomID']; //会议室ID
		isset($_POST['meetDate'])?$time=$_POST['meetDate']:$time=date("Y-m-d",time()); //会议日期
		$model=new Model();
		
		//该日会议室安排
		$sql="select MeetStartTime,MeetEndTime,(select Name from CJ_User where Id=V.PDID) as PD,
		(select Name from CJ_User where Id=V.SHID) as SH,MD
		from CJ_VideoProduce V where RoomID=$roomID and MeetStartTime LIKE '$time%' ORDER BY MeetStartTime";
		$roomTimeList=$model->query($sql);
		
		//会议室名称
		$sql="select RoomName from CJ_MeetingRoom where Id=$roomID";
		$roomName=$model->query($sql);
		$this->assign('roomTimeList',$roomTimeList);
		$this->assign('roomName',$roomName[0]['RoomName']);
		$this->assign('time',$time);
		$this->assign('roomID',$roomID);
		$this->display('roomTimeList');
		
	}
	
}

