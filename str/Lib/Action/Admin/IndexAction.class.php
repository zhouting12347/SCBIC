<?php
class IndexAction extends CommonAction
{
	//管理系统首页
    public function index()
    {
        echo 321;die();
    	if(!$_SESSION[C('USER_AUTH_KEY')]){
    		//$url= U('Public/login');
    		$this->redirect('Public/login');
    	}
    	$this->assign("level0","active");
		$this->display('index');
    }
    
    /**
    +--------------------------------
    * 获取我的任务
    +--------------------------------
    * @date: 2016-3-21 下午5:36:30
    * @author: Str
    * @param: variable
    * @return:
    */
   	public function getMyProgram(){
   		$uid=$_SESSION[uid];
   		$startTime=date("Y-m-d",$_GET[start]);
		$endTime=date("Y-m-d",$_GET[end]-28800);
		$VideoProduce=M("VideoProduce");
		$sql="SELECT *,Id as id,ProgramDate as start,ProgramDate as end FROM CJ_VideoProduce WHERE 
		ProgramDate BETWEEN '$startTime' and '$endTime' and 
		(PDID=$uid OR SHID=$uid OR WMID=$uid OR Live1ID=$uid OR Live2ID=$uid OR 
		Light1ID=$uid OR Light2ID=$uid OR Light3ID=$uid OR Camera1ID=$uid OR Camera2ID=$uid OR 
		Camera3ID=$uid OR Camera4ID=$uid OR TDID=$uid OR ProduceModifyID=$uid OR PreDirectorID=$uid 
		OR BroadCastID=$uid OR Video1ID=$uid OR Video2ID=$uid OR AudioID=$uid OR AudioTSID=$uid OR 
		ProducerID=$uid OR CaptionID=$uid OR UploadID=$uid)";
		$program=$VideoProduce->query($sql);
		foreach($program as $k=>$vo){
			$program[$k]['title'].="(".$vo['ProgramReq'].")";
		}
		echo json_encode($program);
   	}
	
   	/**
   	+--------------------------------
   	* 查看日历中任务详情
   	+--------------------------------
   	* @date: 2016-3-22 上午10:47:19
   	* @author: Str
   	* @param: variable
   	* @return:
   	*/
   	public function check_program(){
   		$VideoProduce=M("VideoProduce");
   		$Id=$_GET['id'];
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
   		FROM CJ_VideoProduce as V WHERE Id=$Id";
   		$program=$VideoProduce->query($sql);
   		$this->assign('program',$program);
   		$this->display('check_program');
   	}
   	
}
?>