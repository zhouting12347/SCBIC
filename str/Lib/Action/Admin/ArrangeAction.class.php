<?php
class ArrangeAction extends CommonAction
{
	/**
	+--------------------------------
	* 排班首页
	+--------------------------------
	* @date: 2016-3-1 下午5:21:55
	* @author: Str
	* @param: variable
	* @return:
	*/
    public function arrange_index()
    {
    	$VideoProduce=M('VideoProduce');
    	$task=$VideoProduce->where("RequestID='task' and PassOrNot=1")->select();
    	$this->assign('task',$task);
    	$this->assign("level7","active open");
    	$this->assign("level7_1","active");
		$this->display('arrange_index');
    }
    
  
    
   /**
   +--------------------------------
   * 技术排班
   +--------------------------------
   * @date: 2016-3-1 下午5:28:45
   * @author: Str
   * @param: variable
   * @return:
   */
    public function technology_arrange(){
    	$VideoProduce=M("VideoProduce");
    	//获取登录用户的所有组长排班角色
    	$RoleModel=new RoleModel();
    	$userRoles=$RoleModel->getUserRoles($_SESSION['uid']);
    	foreach($userRoles as $v){
    		$this->assign($v,$v);
    	}
    	
    	if($_GET['week']=="this"){
    		//本周任务
    		$week="YEARWEEK(now())";
    		$this->assign("level7_2_1","active");
    	}else if($_GET['week']=="next"){
    		//下周任务
    		$week="YEARWEEK(now())+1";
    		$this->assign("level7_2_2","active");
    	}
    	$this->assign("week",$_GET['week']);
    	$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
    	(select Name from CJ_User where Id=V.WMID) as WM,
    	(select Name from CJ_User where Id=V.Live1ID) as Live1, 
    	(select Name from CJ_User where Id=V.Live2ID) as Live2,
    	(select Name from CJ_User where Id=V.Light1ID) as Light1,
    	(select Name from CJ_User where Id=V.Light2ID) as Light2,
    	(select Name from CJ_User where Id=V.Light3ID) as Light3,
    	(select Name from CJ_User where Id=V.Camera1ID) as Camera1,
    	(select Name from CJ_User where Id=V.Camera2ID) as Camera2,
    	(select Name from CJ_User where Id=V.Camera3ID) as Camera3,
    	(select Name from CJ_User where Id=V.Camera4ID) as Camera4,
    	(select Name from CJ_User where Id=V.TDID) as TD,
    	(select Name from CJ_User where Id=V.ProduceModifyID) as ProduceModify,
    	(select Name from CJ_User where Id=V.BroadCastID) as BroadCast,
    	(select Name from CJ_User where Id=V.Video1ID) as Video1,
    	(select Name from CJ_User where Id=V.Video2ID) as Video2,
    	(select Name from CJ_User where Id=V.AudioID) as Audio,
    	(select Name from CJ_User where Id=V.AudioTSID) as AudioTS,
    	(select Name from CJ_User where Id=V.UploadID) as Upload 
    	FROM CJ_VideoProduce as V WHERE PassOrNot=1 and YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = $week";
    	$program=$VideoProduce->query($sql);
    	$this->assign("program",$program);
    	$this->assign("level7","active open");
    	$this->assign("level7_2","active open");
    	$this->display('technology_arrange');
    }
    
    /**
    +--------------------------------
    * 排班人员选择弹出层
    +--------------------------------
    * @date: 2016-3-4 下午2:32:09
    * @author: Str
    * @param: variable
    * @return:
    */
    public function arrange_layer(){
    	$layerName=$_GET['layerName'];
    	$Role=new RoleModel();
    	//取出该排班层人员
    	$user_array=$Role->getAllArrangeUser($layerName);
    	$this->assign('user',$user_array);
    	$this->assign("programID",$_GET[programID]);
    	$this->display($layerName."_layer");
    }
    
    /**
    +--------------------------------
    * 添加排班人员操作
    +--------------------------------
    * @date: 2016-3-7 上午10:34:29
    * @author: Str
    * @param: variable
    * @return:
    */
    public function addArrangeHandler(){
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
    * 排班人员编辑弹出层
    +--------------------------------
    * @date: 2016-3-7 下午3:54:10
    * @author: Str
    * @param: variable
    * @return:
    */
    public function arrange_edit_layer(){
    	$layerName=$_GET['layerName'];
    	//取出该排班层人员
    	$Role=new RoleModel();
    	$user_array=$Role->getAllArrangeUser($layerName);
    	$this->assign('user',$user_array);
    	
    	//取出已经选择的人员
    	$VideoProduce=M("VideoProduce");
    	$program=$VideoProduce->where("Id=$_GET[programID]")->find();
    	$this->assign("program",$program);
   			
    	$this->assign("programID",$_GET[programID]);
    	$this->display($layerName."_edit_layer");
    }
    
    /**
    +--------------------------------
    * 排班人员编辑操作
    +--------------------------------
    * @date: 2016-3-7 下午4:21:23
    * @author: Str
    * @param: variable
    * @return:
    */
    public function editArrangeHandler(){
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
    * 后期排班制作页
    +--------------------------------
    * @date: 2016-3-8 下午3:43:53
    * @author: Str
    * @param: variable
    * @return:
    */
    public function produce_index(){
    	//获取登录用户的所有组长排班角色
    	$RoleModel=new RoleModel();
    	$userRoles=$RoleModel->getUserRoles($_SESSION['uid']);
    	foreach($userRoles as $v){
    		$this->assign($v,$v);
    	}
    	$VideoProduce=M("VideoProduce");
    	if($_GET['week']=="this"){
    		//本周任务
    		$week="YEARWEEK(now())";
    		$this->assign("level7_3_1","active");
    	}else if($_GET['week']=="next"){
    		//下周任务
    		$week="YEARWEEK(now())+1";
    		$this->assign("level7_3_2","active");
    	}
    	$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
    	(select Name from CJ_User where Id=V.ProducerID) as Produce  
    	FROM CJ_VideoProduce as V WHERE PostHardGrade in('NEW','MODIFY','OLD') and 
    	YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = $week";
    	$program=$VideoProduce->query($sql);
    	$this->assign("week",$_GET['week']);
    	$this->assign("program",$program);
    	$this->assign("level7","active open");
    	$this->assign("level7_3","active open");
    	$this->display("produce_index");
    }
    
    /**
    +--------------------------------
    * 后期制作快速分配
    +--------------------------------
    * @date: 2016-3-10 上午11:15:01
    * @author: Str
    * @param: variable
    * @return:
    */
    public function quick_assign(){
    	//选出所有的后期制作人员
    	$Role=M("Role");
    	if($_GET[type]=='produce'){
    		$res=$Role->where("name='后期制作'")->field("Id")->find();
    	}elseif($_GET[type]=='caption'){
    		$res=$Role->where("name='字幕'")->field("Id")->find();
    	}
    	$sql="SELECT user_id,(SELECT name FROM CJ_User WHERE Id=CJ_Role_user.user_id) as name
    	FROM CJ_Role_user LEFT JOIN CJ_User ON CJ_Role_user.user_id=CJ_User.id
    	WHERE role_id=$res[Id] and CJ_User.Status=1";
    	$staff=$Role->query($sql);
    	$this->assign("staff",$staff);
    	$this->assign("week",$_GET['week']);
    	$this->display($_GET[type]."_quick_assign");
    }
    
    /**
    +--------------------------------
    * 后期制作快速分配操作
    +--------------------------------
    * @date: 2016-3-10 下午2:34:08
    * @author: Str
    * @param: variable
    * @return:
    */
    public function produceQuickAssignHandler(){
    	import("Think.Util.Staff");
    	
    	//选出该周未分配后期人员的任务
    	$VideoProduce=M("VideoProduce");
    	if($_POST['week']=="this"){
    		$week="YEARWEEK(now())";//本周任务
    	}else if($_POST['week']=="next"){
    		$week="YEARWEEK(now())+1";//下周任务
    	}
    	$task=$VideoProduce->where("YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) =$week and IfProducerSelected is null")
    	->field("Id,PostHardGrade")->select();
    	
    	//初始化后期员工类
    	$num=0;
    	shuffle($_POST['producer']);//随机排序员工
    	foreach($_POST['producer'] as $v){
    		$staff[$num]=new Staff($v);
    		$num++;
    	}
    	//遍历任务分配人员
    	foreach($task as $t){
    		//选出任务权重最小的人员
    		$n=$staff[0]->taskScore;
    		$key=0;
    		foreach($staff as $k=>$s){
    			if($n>$s->taskScore){$key=$k;}
    		}
    		//计算权重分数
    		switch($t['PostHardGrade']){
    			case "NEW":
    				$staff[$key]->taskScore+=4;
    				break;
    			case "MODIFY":
    				$staff[$key]->taskScore+=2;
    				break;
    			case "OLD":
    				$staff[$key]->taskScore+=1;
    				break;
    				
    		}
    		//分配权重值最小人员
    		$VideoProduce->where("Id=$t[Id]")->setField(array("ProducerID","IfProducerSelected"),array($staff[$key]->id,1));
    	}
    	$this->assign('flag',0);
    	$this->success();
    }
    
    /**
     +--------------------------------
     * 后期制作快速分配操作
     +--------------------------------
     * @date: 2016-3-10 下午2:34:08
     * @author: Str
     * @param: variable
     * @return:
     */
    public function captionQuickAssignHandler(){
    	import("Think.Util.Staff");
    	 
    	//选出该周未分配后期人员的任务
    	$VideoProduce=M("VideoProduce");
    	if($_POST['week']=="this"){
    		$week="YEARWEEK(now())";//本周任务
    	}else if($_POST['week']=="next"){
    		$week="YEARWEEK(now())+1";//下周任务
    	}
    	$task=$VideoProduce->where("YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) =$week and IfCaptionSelected is null")
    	->field("Id,CaptionHardGrade")->select();
    	 
    	//初始化后期员工类
    	$num=0;
    	shuffle($_POST['caption']);//随机排序员工
    	foreach($_POST['caption'] as $v){
    		$staff[$num]=new Staff($v);
    		$num++;
    	}
    	//遍历任务分配人员
    	foreach($task as $t){
    		//选出任务权重最小的人员
    		$n=$staff[0]->taskScore;
    		$key=0;
    		foreach($staff as $k=>$s){
    			if($n>$s->taskScore){
    				$key=$k;
    			}
    		}
    		//计算权重分数
    		switch($t['CaptionHardGrade']){
    			case "NEW":
    				$staff[$key]->taskScore+=4;
    				break;
    			case "MODIFY":
    				$staff[$key]->taskScore+=2;
    				break;
    			case "OLD":
    				$staff[$key]->taskScore+=1;
    				break;
    
    		}
    		//分配权重值最小人员
    		$VideoProduce->where("Id=$t[Id]")->setField(array("CaptionID","IfCaptionSelected"),array($staff[$key]->id,1));
    	}
    	$this->assign('flag',0);
    	$this->success();
    }
    
    /**
    +--------------------------------
    * 字幕排班页
    +--------------------------------
    * @date: 2016-3-14 下午1:36:40
    * @author: Str
    * @param: variable
    * @return:
    */
    public function caption_index(){
    	//获取登录用户的所有组长排班角色
    	$RoleModel=new RoleModel();
    	$userRoles=$RoleModel->getUserRoles($_SESSION['uid']);
    	foreach($userRoles as $v){
    		$this->assign($v,$v);
    	}
    	$VideoProduce=M("VideoProduce");
    	if($_GET['week']=="this"){
    		//本周任务
    		$week="YEARWEEK(now())";
    		$this->assign("level7_4_1","active");
    	}else if($_GET['week']=="next"){
    		//下周任务
    		$week="YEARWEEK(now())+1";
    		$this->assign("level7_4_2","active");
    	}
    	$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
    	(select Name from CJ_User where Id=V.CaptionID) as Caption
    	FROM CJ_VideoProduce as V WHERE CaptionHardGrade in('NEW','MODIFY','OLD') and
    	YEARWEEK(date_format(ProgramDate,'%Y-%m-%d')) = $week";
    	$program=$VideoProduce->query($sql);
    	$this->assign("week",$_GET['week']);
    	$this->assign("program",$program);
    	$this->assign("level7","active open");
    	$this->assign("level7_4","active open");
    	$this->display("caption_index");
    }
}
?>