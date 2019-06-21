<?php
class DirectorAction extends CommonAction
{
	/**
	+--------------------------------
	* 一周会议安排
	+--------------------------------
	* @date: 2016-1-4 上午11:42:43
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function week_meeting(){
		$Director=new DirectorModel();
		//未完成会议
		$meet_unfinish=$Director->getMeetingByWeek("unfinish");
		//已完成会议
		$meet_finish=$Director->getMeetingByWeek("finish");
		$this->assign('meet_unfinish',$meet_unfinish);
		$this->assign('meet_finish',$meet_finish);
		$this->assign("level4","active open");
		$this->assign("level4_1","active");
		$this->display('week_meeting');
	}
	
	/**
	+--------------------------------
	* 导演参加会议操作
	+--------------------------------
	* @date: 2016-1-25 下午3:48:18
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function attendMeetingHandler(){
		$programID=$_GET["programID"];
		$VideoProduce=M("VideoProduce");
		//参加会议，其他状态重置
		$res=$VideoProduce->where("Id=$programID")->setField(array("PD_Already","IfChange","ChangeReason"),array(1,null,null));
		if($res){
			$this->ajaxReturn('','操作成功！',1);
		}else{
			$this->ajaxReturn('','操作失败！',0);
		}
	}
	
	/**
	+--------------------------------
	* 会议无法参加层
	+--------------------------------
	* @date: 2016-1-25 下午3:59:25
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function reason_layer(){
		$this->assign("programID",$_GET['programID']);
		$this->display("reason_layer");
	}
	
	/**
	+--------------------------------
	* 会议无法参加原因添加操作
	+--------------------------------
	* @date: 2016-1-25 下午4:04:51
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function reasonHandler(){
		$VideoProduce=M("VideoProduce");
		$res=$VideoProduce->where("Id=$_POST[programID]")->setField(array("IfChange","ChangeReason"),array(1,$_POST['reason']));
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
	* 会议结束总结层
	+--------------------------------
	* @date: 2016-1-26 上午10:42:59
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function finish_meeting_layer(){
		$this->assign('programID',$_GET[programID]);
		$this->display('finish_meeting_layer');
	}
	
	/**
	+--------------------------------
	* 导演会议结束操作
	+--------------------------------
	* @date: 2016-1-25 下午5:20:50
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function finishMeetingHandler(){
		$VideoProduce=M("VideoProduce");
		$res=$VideoProduce->where("Id=$_POST[programID]")->setField(array("IfMeetEnd","MeetMemo"),array(1,$_POST['memo']));
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
	* 会议总结编辑层
	+--------------------------------
	* @date: 2016-1-26 下午2:22:35
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function edit_finish_meeting_layer(){
		$VideoProduce=M("VideoProduce");
		$program=$VideoProduce->where("Id=$_GET[programID]")->find();
		$this->assign('program',$program);
		$this->display('edit_finish_meeting_layer');
	}
	
	/**
	 +--------------------------------
	 * 会议总结修改操作
	 +--------------------------------
	 * @date: 2016-1-25 下午5:20:50
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	public function editFinishMeetingHandler(){
		$VideoProduce=M("VideoProduce");
		$res=$VideoProduce->where("Id=$_POST[programID]")->setField(array("IfMeetEnd","MeetMemo"),array(1,$_POST['memo']));
		if($res){
			$this->assign("flag",0);
			$this->success();
		}else{
			$this->assign("flag",0);
			$this->error();
		}
	}
	/*******************************************一周工作安排*******************************************/
	/**
	+--------------------------------
	* 一周工作安排首页
	+--------------------------------
	* @date: 2016-2-16 下午2:34:11
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function week_work(){
		$VideoProduce=M("VideoProduce");
		//一周内任务
		$sql="SELECT *,(select Name from CJ_User where Id=V.PDID) as PD,
		(select Name from CJ_User where Id=V.SHID) as SH
		FROM CJ_VideoProduce as V WHERE PDID=$_SESSION[uid] and 
		YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) =YEARWEEK(now())";
		$program=$VideoProduce->query($sql);
		$this->assign("program",$program);
		$this->assign("level4","active open");
		$this->assign("level4_2","active");
		$this->display('week_work');
	}
	/*******************************************申请设备*******************************************/
	/**
	+--------------------------------
	* 申请设备页
	+--------------------------------
	* @date: 2016-1-26 下午2:29:34
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function borrow_equipment(){
		$VideoProduce=M("VideoProduce");
		
		if($_GET['week']=="this"){
			//本周任务
			$week="YEARWEEK(now())";
			$this->assign("level4_3_1","active");
		}else if($_GET['week']=="next"){
			//下周任务
			$week="YEARWEEK(now())+1";
			$this->assign("level4_3_2","active");
		}
		$this->assign("week",$_GET['week']);
		
		//本周及下周导演相关任务
		$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
		 (select Name from CJ_User where Id=V.SHID) as SH 
		 FROM CJ_VideoProduce as V WHERE PDID=$_SESSION[uid] and  
		 YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = $week";
		$program=$VideoProduce->query($sql);
		$this->assign("program",$program);
		
		//状态为可用的设备
/* 		$Equipment=M("Equipment");
		$equipment=$Equipment->where("EquipmentStatus='available' and IsDel=0")->select();
		$this->assign('equipment',$equipment); */
		$this->assign("level4","active open");
		$this->assign("level4_3","active open");
		$this->display('borrow_equipment'); 
	}
	
	/**
	+--------------------------------
	* 棚拍设备申请操作
	+--------------------------------
	* @date: 2016-1-27 下午3:13:33
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function borrowEquipmentHandler(){
 		if(!$_POST['ReturnDate']){
			$this->ajaxReturn('','请选择归还日期！',0);
		}else if(!($_POST[CameraID]||$_POST[MicroID]||$_POST[BeeID]||$_POST[LightID]||$_POST[MattingID])){
			$this->ajaxReturn('','请选择要借的设备！',0);
		}else{
			$VideoProduce=M("VideoProduce");
			$VideoProduce->create();
			$res=$VideoProduce->save();
			if($res){
				//变更设备库状态为借用
				$Equipment=M("Equipment");
				$_POST[CameraID]?$Equipment->where("Id=$_POST[CameraID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$_POST[MicroID]?$Equipment->where("Id=$_POST[MicroID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$_POST[BeeID]?$Equipment->where("Id=$_POST[BeeID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$_POST[LightID]?$Equipment->where("Id=$_POST[LightID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$_POST[MattingID]?$Equipment->where("Id=$_POST[MattingID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$this->ajaxReturn('','操作成功！',1);}
			else{
				$this->ajaxReturn('','操作失败！',0);
				}
			}
		}
	
	/**
	+--------------------------------
	* 查看已借用设备
	+--------------------------------
	* @date: 2016-1-29 下午3:53:01
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function checkBorrowEquipment(){
		$VideoProduce=M('VideoProduce');
		$program=$VideoProduce->where("Id=$_GET[programID]")->find();
		$Equipment=M('Equipment');
		$program[CameraID]?$equipment[]=$Equipment->where("Id=$program[CameraID]")->find():0;
		$program[MicroID]?$equipment[]=$Equipment->where("Id=$program[MicroID]")->find():0;
		$program[BeeID]?$equipment[]=$Equipment->where("Id=$program[BeeID]")->find():0;
		$program[LightID]?$equipment[]=$Equipment->where("Id=$program[LightID]")->find():0;
		$program[MattingID]?$equipment[]=$Equipment->where("Id=$program[MattingID]")->find():0;
		foreach($equipment as $v){
			$data.="[设备类型:".$v['EquipmentType']."],[序列号:".$v['EquipmentSerial']."]<br>";
		}
		$this->ajaxReturn($data,0,1);
	}
	
	/**
	+--------------------------------
	* 外拍设备借用层
	+--------------------------------
	* @date: 2016-2-2 上午10:38:04
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function outside_borrow_layer(){
/* 		//状态为可用的设备
		$Equipment=M("Equipment");
		$equipment=$Equipment->where("EquipmentStatus='available' and IsDel=0")->select();
		$this->assign('equipment',$equipment); */
		$this->assign('programID',$_GET[programID]);
		$this->display("outside_borrow_layer");
	}
	
	/**
	+--------------------------------
	* 外拍借用操作
	+--------------------------------
	* @date: 2016-2-2 上午10:55:38
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function outsideBorrowHandler(){
		if(!$_POST['OutDate']){
			$this->assign("flag",0);
			$this->error("请选择录制日期！");
		}
		
		/* else if(!($_POST[CameraID]||$_POST[BeeID]||$_POST[LightID])){
			$this->assign("flag",0);
			$this->error("请选择要借用的设备！");
		}else{
			$VideoProduce=M("VideoProduce");
			$VideoProduce->create();
			$res=$VideoProduce->save();
			if($res){
				//变更设备库状态为借用
				$Equipment=M("Equipment");
				$_POST[CameraID]?$Equipment->where("Id=$_POST[CameraID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$_POST[BeeID]?$Equipment->where("Id=$_POST[BeeID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$_POST[LightID]?$Equipment->where("Id=$_POST[LightID]")->setField(array("LeasePersonID","EquipmentStatus","LeaseTime"),array("$_SESSION[uid]","leased",date("Y-m-d H:i:s",time()))):0;
				$this->assign("flag",0);
				$this->success();
			}else{
				$this->assign("flag",0);
				$this->error();
				}
			} */
			$VideoProduce=M("VideoProduce");
			$VideoProduce->create();
			$res=$VideoProduce->save();
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
	* 棚拍操作
	+--------------------------------
	* @date: 2016-2-16 下午4:13:16
	* @author: Str
	* @param: variable
	* @return: ajax
	*/
	public function studioShotHandler(){
		$programID=$_GET['programID'];
		$VideoProduce=M("VideoProduce");
		$res=$VideoProduce->where("Id=$programID")->setField("IfStudioShot",1);
		if($res){
			$this->ajaxReturn('','操作成功！',1);
		}else{
			$this->ajaxReturn('','操作失败！',0);
		}
	}
	/**
	+--------------------------------
	* 设备归还操作
	+--------------------------------
	* @date: 2016-2-2 上午11:26:00
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function returnEquipmentHandler(){
		$programID=$_GET[programID];
		
	}
	
	/*******************************************动影像*******************************************/
	/**
	+--------------------------------
	* 外拍及动影像页面
	+--------------------------------
	* @date: 2016-2-2 下午3:45:44
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function moving_image(){
		$VideoProduce=M("VideoProduce");
		
		if($_GET['week']=="this"){
			//本周任务
			$week="YEARWEEK(now())";
			$this->assign("level4_4_1","active");
		}else if($_GET['week']=="next"){
			//下周任务
			$week="YEARWEEK(now())+1";
			$this->assign("level4_4_2","active");
		}
		$this->assign("week",$_GET['week']);
		
		//本周及下周导演相关任务(已申请完设备)
		$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
		 (select Name from CJ_User where Id=V.SHID) as SH 
		 FROM CJ_VideoProduce as V WHERE PDID=$_SESSION[uid] and IfGoOut=1 and 
		 YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = $week";
		$program=$VideoProduce->query($sql);
		$this->assign("program",$program);
		$this->assign("level4","active open");
		$this->assign("level4_4","active open");
		$this->display('moving_image');
	}
	
	/** 
	+--------------------------------
	* 外拍时间层
	+--------------------------------
	* @date: 2016-2-4 上午11:36:52
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function moving_time_layer(){
		$this->assign('programID',$_GET[programID]);
		$this->display('moving_time_layer');
	}
	
	/**
	+--------------------------------
	* 选择动影像日期操作
	+--------------------------------
	* @date: 2016-2-2 下午4:52:51
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function movingImageTimeHandler(){
		$VideoProduce=M('VideoProduce');
		if(!($_POST['MovieDate']&&$_POST['MovieHalfDay'])){
			$this->assign("flag",0);
			$this->error("请选择动影像时间！");
		}
		//查询动影像时间是否冲突
		$res=$VideoProduce->where("MovieDate='$_POST[MovieDate]' and MovieHalfDay='$_POST[MovieHalfDay]'")->find();
		if($res){
			$this->assign("flag",0);
			$this->error("动影像时间冲突！");
		}
		$VideoProduce->create();
		$res=$VideoProduce->save();
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
	* 外拍总结层
	+--------------------------------
	* @date: 2016-2-4 下午3:57:21
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function outside_summary_layer(){
		$this->assign('programID',$_GET[programID]);
		$this->display('outside_summary_layer');
	}
	
	/**
	+--------------------------------
	* 外拍总结操作
	+--------------------------------
	* @date: 2016-2-4 下午3:58:08
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function outsideSummaryHandler(){
		$VideoProduce=M('VideoProduce');
		if(!$_POST['Poutline']){
			$this->assign("flag",0);
			$this->error("请填写小结内容！");
		}
		$VideoProduce->create();
		$res=$VideoProduce->save();
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
	* 检测动影像时间是否冲突
	+--------------------------------
	* @date: 2016-2-4 下午4:45:07
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function checkMovieTime(){
		$VideoProduce=M('VideoProduce');
		$am=$VideoProduce->where("MovieDate='$_GET[date]' and MovieHalfDay='am'")->find();
		$pm=$VideoProduce->where("MovieDate='$_GET[date]' and MovieHalfDay='pm'")->find();
		$str="";
		$am?0:$str.="上午";
		if($pm){
			$am?$str.="无空闲时间":0;
		}else{
			$am?$str.="下午":$str.="和下午";
		}
		$this->ajaxReturn($str,0,1);
	}
	
	/*******************************************后期制作*******************************************/
	/**
	+--------------------------------
	* 后期制作页
	+--------------------------------
	* @date: 2016-2-5 上午11:45:41
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function post_production(){
		$VideoProduce=M("VideoProduce");
		
		if($_GET['week']=="this"){
			//本周任务
			$week="YEARWEEK(now())";
			$this->assign("level4_5_1","active");
		}else if($_GET['week']=="next"){
			//下周任务
			$week="YEARWEEK(now())+1";
			$this->assign("level4_5_2","active");
		}
		$this->assign("week",$_GET['week']);
		
		//本周导演后期相关任务
		$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
		 (select Name from CJ_User where Id=V.SHID) as SH
		 FROM CJ_VideoProduce as V WHERE PDID=$_SESSION[uid] and PassOrNot=1 
		 and YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) =$week";
		$program=$VideoProduce->query($sql);
		$this->assign("program",$program);
		$this->assign("level4","active open");
		$this->assign("level4_5","active open");
		$this->display('post_production');
	}
	
	/**
	+--------------------------------
	* 后期制作 字幕弹出层
	+--------------------------------
	* @date: 2016-2-14 下午4:13:07
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function post_production_layer(){
		$programID=$_GET['programID'];
		//查询填写过的内容
		$this->assign("programID",$programID);
		$this->display("post_production_layer");
	}
	
	/**
	+--------------------------------
	* 按关键字查询节目
	+--------------------------------
	* @date: 2016-2-6 上午11:06:25
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function searchProgramByWord(){
		$VideoProduct=M('VideoProduce');
		if(!$_GET[word]){
			$this->ajaxReturn('',0,0);
		}
		$map["ProgramReq"] = array("like","%$_GET[word]%");
		$program=$VideoProduct->where($map)->field("Id,ProgramReq,ProgramDate")->select();
		if($program){
			$this->ajaxReturn($program,0,1);
		}else{
			$this->ajaxReturn('',0,0);
		}
	}
	
	/**
	+--------------------------------
	* 后期制作字幕信息提交操作
	+--------------------------------
	* @date: 2016-2-14 下午4:01:17
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function postProductionLayerHandler(){
		$VideoProduce=M("VideoProduce");
		if(!$VideoProduce->create()){
			$this->assign('flag',0);
			$this->error($VideoProduce->getError());
		}else{
			$VideoProduce->save();
			$this->assign('flag',0);
			$this->success();
		}
	}
	
	/**
	+--------------------------------
	* 后期字幕导演评分层
	+--------------------------------
	* @date: 2016-2-15 下午2:47:02
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function post_caption_summary_layer(){
		$programID=$_GET['programID'];
		$this->assign("programID",$programID);
		$this->display("post_caption_summary_layer");
	}
	
	/**
	+--------------------------------
	* 后期字幕导演评分操作
	+--------------------------------
	* @date: 2016-2-15 下午3:05:06
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function postCaptionSummaryHandler(){
		$VideoProduce=M("VideoProduce");
		if(!$VideoProduce->create()){
			$this->assign('flag',0);
			$this->error($VideoProduce->getError());
		}else{
			$VideoProduce->save();
			$this->assign('flag',0);
			$this->success();
		}
	}
	
	/*******************************************导演已完成任务*******************************************/
	/**
	+--------------------------------
	* 导演已完成任务
	+--------------------------------
	* @date: 2016-2-15 下午4:23:28
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function finished_program(){
		$VideoProduce=M('VideoProduce');
		//取出总任务含有导演任务
    	$sql="SELECT distinct(TaskID) FROM CJ_VideoProduce WHERE PDID=$_SESSION[uid]";
    	$res=$VideoProduce->query($sql);
    	foreach($res as $v){
    		$task[]=$VideoProduce->where("RequestID='task' and PassOrNot=1 and TaskID='$v[TaskID]' ")->find();
    	}
    	$this->assign('task',$task);
    	$this->assign("level4","active open");
    	$this->assign("level4_6","active");
		$this->display('finished_program');
	}
}
?>