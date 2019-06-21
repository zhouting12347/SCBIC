<?php
class LuzhiAction extends CommonAction
{
	 /**
	 +------------------------------------------
	 *【录制表格--任务首页】
     * 我的录制任务首页
     +------------------------------------------
     */
    public function index()
    {
    	$VideoProduce=M('VideoProduce');
    	//待完成任务（表格未提交和提交未审核的任务）
    	$unfinish_task=$VideoProduce
    	->where("InputUserID='$_SESSION[uid]' and RequestID='task' and ((IfSendAudit=0 and IfAudit=0) or (IfSendAudit=1 and IfAudit=0) or (IfSendAudit=1 and IfAudit=1 and PassOrNot=2))")
    	->select();
    	
    	//已完成任务（表格提交并通过审核的任务）
    	$finished_task=$VideoProduce
    	->where("InputUserID='$_SESSION[uid]' and RequestID='task' and IfSendAudit=1 and IfAudit=1 and PassOrNot=1")
    	->select();
    	$this->assign('unfinish_task',$unfinish_task);
    	$this->assign('finished_task',$finished_task);
    	$this->assign("level1","active open");
    	$this->assign("level1_1","active");
		$this->display('index');
    }
    
    /**
     +------------------------------------------
     * 查看提交的录制任务
     +------------------------------------------
     */
   	public function check_table(){
    	$VideoProduce=D('VideoProduce');
    	$taskID=$_GET['taskID'];
    	$program=$VideoProduce->where("TaskID='$taskID' and RequestID!='task' ")->select();
    	//如果没查询出来，说明是单条任务，继续查询
    	if(!$program){
    		$program=$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->select();
    	}
    	$this->assign('program',$program);
    	$this->display('check_table');
    }
    
    /**
     +------------------------------------------
     *【录制表格--任务首页--查看，编辑】
     * 单条任务中的节目信息表，显示任务中的所有信息
     +------------------------------------------
     */
    public function program_table(){
    	$VideoProduce=D('VideoProduce');
    	$taskID=$_GET['taskID'];
   		$program=$VideoProduce->where("TaskID='$taskID' and RequestID!='task' ")->select();
   		//如果没查询出来，说明是单条任务，继续查询
     	if(!$program){
     		$program=$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->select();
     	}
    	$this->assign('program',$program);
    	$this->display('program_table');
    }
    
    /**
     +------------------------------------------
     *【录制表格--任务首页--删除】
     * 删除任务，以及任务下的所有详细节目单
     * 只可删除未提交任务
     +------------------------------------------
     * @return json
     +------------------------------------------
     */
    public function delTask(){
    	$taskID=$_GET['taskID'];
    	$VideoProduce=M('VideoProduce');
    	$res=$VideoProduce->where("TaskID=$taskID")->delete();
    	if($res){
    		$this->ajaxReturn('','删除成功！',1);
    	}else{
    		$this->ajaxReturn('','删除失败！',0);
    	}
    }
    
    /**
     +------------------------------------------
     *【录制表格--任务首页--提交】
     * 任务提交审核
     +------------------------------------------
     * @return json
     +------------------------------------------
     */
    public function submitTaskHandler(){
    	$task=$_GET['taskID'];
    	$VideoProduce=M('VideoProduce');
    	//任务提交时，初始化总任务状态
    	$data['IfSendAudit']=1;
    	$data['IfAudit']=0;
    	$data['AuditerID']=null;
    	$data['AuditTime']=null;
    	$data['PassOrNot']=0;
    	$data['Reason']=null;
    	$VideoProduce->where("TaskID='$task' and RequestID='task'")->save($data);
    	unset($data);
    	
    	//初始化任务详情的状态
    	$data['PassOrNot']=0;
    	$data['Reason']=null;
    	$data['IfUpdate']=null;
    	$VideoProduce->where("TaskID='$task' and RequestID!='task'")->save($data);
    	unset($data);
    	
    	$this->ajaxReturn('','提交成功！',1);

    }
    
    /**
     +------------------------------------------
     *【录制表格--表单导入】
     * excel表单导入页
     +------------------------------------------
     */
    public function import(){
    	$this->assign("level1","active open");
    	$this->assign("level1_2","active");
    	$this->display('import');
    }
    
    /**
     +------------------------------------------
     * 文件导入到数据库
     +------------------------------------------
     */
    public function importHandler(){
    	if(!$_POST['TaskName']){
    		$this->assign('flag',1);
    		$this->error("请填写任务名称!");
    	}
    	import('ORG.Net.UploadFile');
    	//上传时间作为文件夹名
    	$fileName=date("Y-m-d");
    	if (!file_exists('./Excel/'.$fileName)){
    		mkdir ('./Excel/'.$fileName);
    	}
    	$upload = new UploadFile();// 实例化上传类
    	$upload->maxSize  =510000; // 设置附件上传大小
    	$upload->allowExts  = array('xlsx'); // 设置附件上传类型
    	$upload->savePath =  './Excel/'.$fileName.'/'; // 设置附件上传目录
    	$upload->saveRule=time;
    	if(!$upload->upload()) {
    		// 上传错误提示错误信息
    		$this->assign('flag',1);
	    	$this->assign('jumpUrl',"/Luzhi/import");
	    	$this->error();
    	}else{
    		$taskID=time();
    		//保存任务
    		$VideoProduce=D('VideoProduce');
    		$data['TaskName']=$_POST['TaskName']; //任务名称
    		$data['TaskID']=$taskID; 
    		$data['InputDate']=date('Y-m-d H:i:s'); //导入时间
    		$data['InputMethod']='Form'; //导入方式
    		$data['InputUserID']=$_SESSION['uid'];  //导入人ID
    		$data['RequestID']='task'; //主任务RequestID标识为task
    		$VideoProduce->add($data);
    		unset($data);
    		// 上传成功 获取上传文件信息
    		$fileInfo=$upload->getUploadFileInfo();
    		$filePath=$fileInfo[0]['savepath'].$fileInfo[0]['savename'];
    		$excel_arr=array_splice(ExcelToArray($filePath),1); //删除excel第一行标题行
    		//dump($excel_arr);
    		//节目数组插入到数据库中
    		foreach($excel_arr as $val){
    			$data['TaskID']=$taskID; //主任务ID
    			$data['InputUserID']=$_SESSION['uid']; //导入人ID
    			$data['InputMethod']='Form'; //导入方式
    			$date_array=explode('-',$val[0]);
    			//$data['ProgramDate']="20".$date_array['2']."-".$date_array['1']."-".$date_array['0']; //节目日期
    			$data['ProgramDate']=$val[0]; //节目日期
    			$requsetID=$date_array['0'].$date_array['1'].$date_array['2'].GetRandStr(8);//节目ID 16位
    			$data['RequestID']=$requsetID;//节目ID 16位
    			$data['TaskName']=$requsetID;
				$data['ProgramWeek']=$val[2]; //节目周几
				$data['BroadChannel']=$val[3]; //频道
				$data['BroadTime']=$val[4]; //播放时间
				//$data['BroadLength']='' //播放长度
				$data['VideoSite']=$val[5]; //拍摄场地
				$data['ProgramReq']=$val[6]; //节目名称
				$data['MD']=$val[9]; //采购
				$data['MEMO']=$val[10]; //时长
				$data['Guest']=$val[17]; //嘉宾
				$VideoProduce->add($data);
    		}
    		$this->assign('flag',1);
	    	$this->assign('jumpUrl',"/Luzhi/import");
	    	$this->success();
    	}
    }
    
    /**
     +------------------------------------------
     *【录制表格--单条导入】
     * 单条任务录入
     +------------------------------------------
     */
    public function single_task(){
    	$this->assign("level1","active open");
    	$this->assign("level1_3","active");
    	$this->display('single_task');
    }
    
    /**
     +------------------------------------------
     * 单条导入操作
     +------------------------------------------
     */
    public function addSingleTaskHandler(){
    		//保存任务
    		$VideoProduce=D('VideoProduce');
    		if (!$VideoProduce->create()){
    			// 如果创建失败 表示验证没有通过 输出错误提示信息
    			$this->assign('flag',0);
    			$this->error($VideoProduce->getError());
    		}else{
	    		$data['TaskName']=$_POST['TaskName']; //任务名称
	    		$data['TaskID']=time(); 
	    		$data['InputDate']=date('Y-m-d H:i:s'); //导入时间
	    		$data['InputMethod']='Single'; //导入方式
	    		$data['InputUserID']=$_SESSION['uid'];  //导入人ID
	    		$data['RequestID']='task'; //主任务RequestID标识为task
	    		
	    		$date_array=explode('-',$_POST['ProgramDate']);
	    		$data['ProgramDate']=$_POST['ProgramDate']; //节目日期
	    		$data['ProgramWeek']=$_POST['ProgramWeek']; //节目第几周
	    		$data['BroadChannel']=$_POST['BroadChannel']; //频道
	    		$data['BroadTime']=$_POST['BroadTime']; //播放时间
	    		//$data['BroadLength']='' //播放长度
	    		$data['VideoSite']=$_POST['VideoSite']; //拍摄场地
	    		$data['ProgramReq']=$_POST['ProgramReq']; //节目名称
	    		$data['MD']=$_POST['MD']; //采购
	    		$data['MEMO']=$_POST['MEMO']; //时长
	    		$data['Guest']=$_POST['Guest']; //嘉宾
	    		//添加数据
	    		$VideoProduce->add($data);
	    		$this->assign('flag',1);
	    		$this->assign('jumpUrl',"/Luzhi/single_task");
	    		$this->success();
    		
    		}
    		

    }
    
    //【4】修改提交任务
    public function updata_task(){
    	$this->assign("level1","active open");
    	$this->assign("level1_4","active");
    	$this->display('updata_task');
    }
    
    /**
    +--------------------------------
    * 任务跟踪
    * 查询本周和下周的任务
    +--------------------------------
    * @date: 2016-3-16 下午4:06:40
    * @author: Str
    * @param: variable
    * @return:
    */
    public function trace_task(){
    	$VideoProduce=M('VideoProduce');
    	$task=$VideoProduce->where("RequestID='task' and PassOrNot=1")->select();
    	$this->assign('task',$task);
    	$this->assign("level1","active open");
    	$this->assign("level1_5","active");
    	$this->display('trace_task');
    }
    
    /**
    +--------------------------------
    * 任务跟踪查看任务详情
    +--------------------------------
    * @date: 2016-3-16 下午4:37:42
    * @author: Str
    * @param: variable
    * @return:
    */
    public function trace_check_program(){
    	$VideoProduce=M("VideoProduce");
    	$taskID=$_GET['taskID'];
    	$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
    	(select Name from CJ_User where Id=V.SHID) as SH,
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
    	(select Name from CJ_User where Id=V.UploadID) as Upload,
    	(select Name from CJ_User where Id=V.ProducerID) as Producer,
    	(select Name from CJ_User where Id=V.CaptionID) as Caption
    	FROM CJ_VideoProduce as V WHERE ProgramDate is not null and TaskID='$taskID'";
    	$program=$VideoProduce->query($sql);
    	$this->assign('program',$program);
    	$this->display('trace_check_program');
    }
    
    /**
    +--------------------------------
    * 任务查询
    +--------------------------------
    * @date: 2016-3-17 上午10:31:19
    * @author: Str
    * @param: variable
    * @return:
    */
    public function task_search(){
    	$this->assign("level1","active open");
    	$this->assign("level1_6","active");
    	$this->display('task_search');
    }
    /**
    +--------------------------------
    * 任务查询操作
    +--------------------------------
    * @date: 2016-3-17 上午10:31:40
    * @author: Str
    * @param: variable
    * @return:
    */
    public function taskSearchHandler(){
    	$VideoProduce=M("VideoProduce");
    	//总任务查询
    	if($_POST['type']=="task"){
    		$condition="RequestID='task' and PassOrNot=1";
    		$_POST['taskName']?$condition.=" and TaskName like '%$_POST[taskName]%'":'';
    		$_POST['taskTime']?$condition.=" and InputDate like '$_POST[taskTime]%'":'';
    		$task=$VideoProduce->where($condition)->select();
    		$this->assign('taskName',$_POST['taskName']);
    		$this->assign('taskTime',$_POST['taskTime']);
    		$this->assign("task",$task);
    	//单条任务查询
    	}elseif($_POST['type']=="program"){
    		$condition="ProgramDate is not null  and PassOrNot=1";
    		$_POST['programName']?$condition.=" and ProgramReq like '%$_POST[programName]%'":'';
    		$_POST['programDate']?$condition.=" and ProgramDate='$_POST[programDate]'":'';
    		$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
	    	(select Name from CJ_User where Id=V.SHID) as SH,
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
	    	(select Name from CJ_User where Id=V.UploadID) as Upload,
	    	(select Name from CJ_User where Id=V.ProducerID) as Producer,
	    	(select Name from CJ_User where Id=V.CaptionID) as Caption
	    	FROM CJ_VideoProduce as V WHERE $condition";
    		$program=$VideoProduce->query($sql);
    		$this->assign("program",$program);
    		$this->assign('programName',$_POST['programName']);
    		$this->assign('programDate',$_POST['programDate']);
    	}
    	
    	$this->assign("type",$_POST['type']);
    	$this->display("task_search");
    }
    
    //文件上传
    public function upload(){
    	import('ORG.Net.UploadFile');
    	//用户名作为文件夹名
    	$fileName="A";
    	if (!file_exists('./Excel/'.$fileName)){
    		mkdir ('./Excel/'.$fileName);
    	}
    	$upload = new UploadFile();// 实例化上传类
    	$upload->maxSize  =510000; // 设置附件上传大小
    	$upload->allowExts  = array('xlsx'); // 设置附件上传类型
    	$upload->savePath =  './Excel/'.$fileName.'/'; // 设置附件上传目录
    	$upload->saveRule=time;
    	if(!$upload->upload()) { // 上传错误提示错误信息
    		$this->redirect("Public/error");
    	}else{ // 上传成功 获取上传文件信息
    		$this->redirect("Public/success");
    	}
    }
    
    
    /**
     * 任务明细编辑
     */
    public function edit(){
    	$VideoProduce=M('VideoProduce');
    	$res=$VideoProduce->where("Id=$_GET[id]")->find();
    	$this->assign('program',$res);
    	$this->display('edit');
    }
    
    /**
     * 编辑任务明细操作
     */
    public function editProgramHandler(){
    	$VideoProduce=D('VideoProduce');
    	if (!$VideoProduce->create()){
    		// 如果创建失败 表示验证没有通过 输出错误提示信息
    		$this->assign('flag',0);
    		$this->error($VideoProduce->getError());
    	}else{
    		$VideoProduce->save();
    		$this->assign('flag',0);
    		$this->success();
    	}
    	
    }
}
?>