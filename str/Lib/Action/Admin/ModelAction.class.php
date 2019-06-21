<?php
class ModelAction extends CommonAction
{
	
	/**
	+--------------------------------
	* 检测档期模特是否过期 过期则删除档期模特
	+--------------------------------
	* @date: 2016-2-19 上午11:18:21
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function _before_model_apply(){
		//查询超过日期的档期模特
		$Model_Schedule=M("Model_Schedule");
		$Model_His=M("Model_His");
		$overtimeModel=$Model_Schedule->where("ModelEndTime<curdate()")->select();
		if($overtimeModel){
			foreach($overtimeModel as $v){
				//删除过期的档期模特
				$Model_Schedule->where("Id=$v[Id]")->delete();
				//修改模特库Model_His IsSchedule值为0
				$Model_His->where("Id=$v[ModelID]")->setField("IsSchedule",0);
			}
		}
	}
	/**
	+--------------------------------
	* 模特申请页
	+--------------------------------
	* @date: 2016-2-18 上午10:06:25
	* @author: Str
	* @param: variable
	* @return:
	*/
    public function model_apply()
    {
    	$VideoProduce=M("VideoProduce");
    	
    	if($_GET['week']=="this"){
    		//本周任务
    		$week="YEARWEEK(now())";
    		$this->assign("level6_1_1","active");
    	}else if($_GET['week']=="next"){
    		//下周任务
    		$week="YEARWEEK(now())+1";
    		$this->assign("level6_1_2","active");
    	}
    	$this->assign("week",$_GET['week']);
    	
    	//选出本周及下周的任务,需在设备申请之后才能选择模特
    	$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
    	(select Name from CJ_User where Id=V.SHID) as SH 
    	FROM CJ_VideoProduce as V WHERE (IfBorrowed=1 or IfStudioShot=1) and  
    	YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = $week";
    	$program=$VideoProduce->query($sql);
    	
    	//档期模特
    	$ModelSchedule=M("Model_Schedule");    	
    	$model=$ModelSchedule->join("CJ_Model_His on CJ_Model_Schedule.ModelID=CJ_Model_His.Id")->select();
    	
    	$this->assign("program",$program);
    	$this->assign("model",$model);
    	$this->assign("level6","active open");
    	$this->assign("level6_1","active open");
		$this->display('model_apply');
    }
	
    /**
    +--------------------------------
    * 根据外拍和棚拍 查询可用模特 ajax
    +--------------------------------
    * @date: 2016-2-19 上午11:01:45
    * @author: Str
    * @param: variable
    * @return: json
    */
    public function getFreeScheduleModel(){
    	//查询节目信息
    	$VideoProduce=M("VideoProduce");
    	$program=$VideoProduce->where("Id=$_GET[programID]")->find();
    	echo 
    	//查询出档期内未请假的模特
    	$ModelSchedule=M("Model_Schedule");
    	$scheduleModel=$ModelSchedule
    	->where("($program[ProgramDate]<LeaveStartDate or $program[ProgramDate]>LeaveEndDate) or (LeaveStartDate is null and LeaveEndDate is null )")
    	->join("CJ_Model_His on CJ_Model_Schedule.ModelID=CJ_Model_His.Id")
    	->select();
    	//查询当天已经安排工作的档期模特
    	$program["OutDate"]?$ShootTime=$program["OutDate"]:$ShootTime=$program["ProgramDate"];//拍摄时间
    	
    	$ModelWork=M("ModelWork");
    	$res=$ModelWork->where("ShootTime='$ShootTime'")->field("ModelID")->select();
    	//结果集转为一维数组
    	foreach($res as $arr){
    		foreach($arr as $v){
    			$workModel[]=$v;
    		}
    	}
    	//选出该日档期模特中没有安排工作的模特
		foreach($scheduleModel as $v){
			if(!in_array($v['ModelID'], $workModel)){
				$availableModel[]=$v;
			}
		}
		
		//如果是修改模特，加入已选择的模特
		$res=$ModelWork->where("ProgramID='$_GET[programID]'")
		->join("CJ_Model_His on CJ_ModelWork.ModelID=CJ_Model_His.Id")
		->select();
		foreach($res as $v){
			$v['check']=1;
			$availableModel[]=$v;
		}
		
		//返回结果
		if($availableModel){
    		$this->ajaxReturn($availableModel,$program['ModelRemark'],1);
		}else{
			$this->ajaxReturn('','',0);
		}
    }
    
    /**
    +--------------------------------
    * 模特申请操作
    +--------------------------------
    * @date: 2016-2-19 下午3:10:26
    * @author: Str
    * @param: variable
    * @return:
    */
    public function modelApplyHandler(){
    	$data["ProgramID"]=$_POST["programID"];
    	$data["workTime"]=$_POST["workTime"];
    	
    	$VideoProduce=M("VideoProduce");
    	//查询拍摄时间
    	$program=$VideoProduce->where("Id=$data[ProgramID]")->find();
    	$program["OutDate"]?$data["ShootTime"]=$program["OutDate"]:$data["ShootTime"]=$program["ProgramDate"];//拍摄时间
    	
    	//标记模特申请完成
    	$program=$VideoProduce->where("Id=$data[ProgramID]")
    	->setField(array('IfSelectedModel','ModelRemark'),array(1,$_POST["ModelRemark"]));
    	
    	$ModelWork=M("ModelWork");
    	//如果是修改模特，先删除ModelWork表中选择的模特
    	if($_GET["update"]==1){
    		$ModelWork->where("ProgramID='$data[ProgramID]'")->delete();
    	}
    	//插入到ModelWork表中
    	foreach($_POST['model'] as $v){
    		$data["ModelID"]=$v;
    		$ModelWork->add($data);
    	}
		
    	$this->ajaxReturn('','操作成功！',1);
    }
    
    /**
    +--------------------------------
    * 模特详细资料层
    +--------------------------------
    * @date: 2016-3-2 上午11:04:07
    * @author: Str
    * @param: variable
    * @return:
    */
    public function model_info_layer(){
    	$Model=M("Model_His");
    	$model=$Model->where("Id=$_GET[modelID]")->find();
    	$this->assign('model',$model);
    	$this->display("model_info_layer");
    }
}
?>