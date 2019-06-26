<?php
class TechMonitoringAction extends Action
{
	/**
	+--------------------------------
	* 技术监测页
	+--------------------------------
	* @date: 2017年6月27日 下午3:46:55
	* @author: Str
	* @param: variable
	* @return:
	*/
    public function index(){   
        //获取用户ID
        $_SESSION['userCode']=$_GET['userCode'];
        //报警类型
        $AlarmType=M('alarm_slip_type');
        $alarmTypes=$AlarmType->where('parentId=0')->field("id,displayName")->select();
        foreach ($alarmTypes as $k=>$v){
            $k==0?$alarmTypesHtml="<option value='empty' >请选择</option>":'';
            $alarmTypesHtml.="<option value='$v[id]'>$v[displayName]</option>";
        }
        
        //频道名称
        $Channel=M('channel_info');
        $channel=$Channel->field("channelId,channelName")->select();
        foreach ($channel as $k=>$v){
            $k==0?$channelHtml="<option value='empty' >请选择</option>":'';
            $channelHtml.="<option value='$v[channelId]'>$v[channelName]</option>";
        }
        
        //信号类型
        $Signal=M('signal_info');
        $signal=$Signal->field("signalCode,signalType")->order("signalTypeId")->select();
        foreach ($signal as $k=>$v){
            $k==0?$signalHtml="<option value='empty' >请选择</option>":'';
            $signalHtml.="<option value='$v[signalCode]'>$v[signalType]</option>";
        }
        
        $this->assign('dengjiURL',C('dengjiURL'));
        $this->assign('alarmTypes',$alarmTypesHtml);
        $this->assign('channel',$channelHtml);
        $this->assign('signal',$signalHtml);
        $this->display();
    }
    
    /**
    +--------------------------------
    * 报警查询分页
    +--------------------------------
    * @date: 2017年6月28日 上午11:14:06
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxGetAlarm(){
        $Alarm=M("alarm_message"); // 实例化User对象
        import("@.ORG.Page"); // 导入分页类
        
        //判断是否需要重置查询条件
        if($_POST['condition']==1){
            //拼接查询条件sql
            $condition="ifRadio=0";
            //报警类型
            ($_POST['alarmType']!="empty")?$condition.=" and alarmType='$_POST[alarmType]'":'';
            
            //信号类型
            ($_POST['signalCode']!="empty")?$condition.=" and signalCode='$_POST[signalCode]'":'';
            
            //频率值
            ($_POST['frequencyId']!="empty")?$condition.=" and frequencyId='$_POST[frequencyId]'":'';
            
            //频道名称
            ($_POST['channelId']!="empty")?$condition.=" and channelId='$_POST[channelId]'":'';
            
            //确认状态
            ($_POST['sureStatus']!="empty")?$condition.=" and sureStatus='$_POST[sureStatus]'":'';
            
            //结束状态
            ($_POST['instruct']!="empty")?$condition.=" and instruct='$_POST[instruct]'":'';
           
            //报警时间
            ($_POST['alarmHappenTime']&&$_POST['alarmEndTime'])?
            $condition.=" and ((alarmHappenTime between '$_POST[alarmHappenTime]' and '$_POST[alarmEndTime]') or (alarmEndTime between '$_POST[alarmHappenTime]' and '$_POST[alarmEndTime]')) ":'';
            
            //持续时间
            ($_POST['durationStart']&&$_POST['durationEnd'])?
            $condition.=" and (duration between '$_POST[durationStart]' and '$_POST[durationEnd]')":'';
            
			
			//报警等级
			if(count($_POST['alarmLevel'])>0){
				foreach($_POST['alarmLevel'] as $k=>$v){
					if($k+1==count($_POST['alarmLevel'])){
						$alarmLevel.=$v;
					}else{
						$alarmLevel.=$v.",";
					} 
				}
				$condition.=" and alarmLevel in (".$alarmLevel.")";
			}
            $_SESSION["condition"]=$condition;
        }
		$showNum=$_POST["showNum"];
        $count=$Alarm->where($_SESSION["condition"])->count(); // 查询满足要求的总记录数
        $Page=new Page($count,$showNum); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->nowPage=$_POST['nowPage'];
        $show=$Page->ajaxShow(); // ajax分页显示输出
     
        //$list=$Alarm->order('alarmHappentime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $sql="SELECT *,(SELECT frequencyName FROM t_frequency_info WHERE frequencyId=t_alarm_message.frequencyId) AS frequencyName,
        (SELECT displayName FROM t_alarm_slip_type WHERE id=alarmType) AS alarmType,
        (SELECT displayName FROM t_alarm_slip_type WHERE id=slipType) AS slipType,
        (SELECT signalType FROM t_signal_info WHERE signalCode=t_alarm_message.signalCode) as signalCode,
        (SELECT channelName FROM t_channel_info WHERE channelId=t_alarm_message.channelId) as channelName 
        FROM t_alarm_message WHERE $_SESSION[condition] and t_alarm_message.channelId<>'' ORDER BY alarmHappentime DESC LIMIT $Page->firstRow,$Page->listRows";
        //echo $sql;die();
        $list=$Alarm->query($sql);
        foreach($list as $k=>$v){
            $v[instruct]==0?$instruct="结束":$instruct="未结束";
			switch($v[sureStatus]){
				case 0:
					$sureStatus="未确认";break;
				case 1:
					$sureStatus="确认";break;
				case 2:
					$sureStatus="误报";break;
			}
			
            switch($v[alarmLevel]){
                case 1:
                    $alarmLevel="一级";
                    $color="<tr data-index=$k style='background-color:rgb(249, 135, 135)'";
                    break;
                case 2:
                    $alarmLevel="二级";
                     $color="<tr data-index=$k style='background-color: rgb(251, 202, 112)'";
                     break;
                case 3:
                    $alarmLevel="三级";
                    $color="<tr data-index=$k style='background-color:rgb(251, 251, 201)'";
                    break;
                case 4:
                    $alarmLevel="非等级差错";
                    $color="<tr data-index=$k";
                    break;
            }
            $alarms.=$color." id='$v[alarmId]'><td>".$v[alarmHappentime]."</td><td>".$v[alarmEndtime]."</td><td>".$v[channelName]."</td>
                <td>".$v[frequencyName]."</td><td>".$v[signalCode]."</td><td>上海</td>
                    <td>".$v[alarmType]."</td><td>".$v[slipType]."</td><td>".$alarmLevel."</td>
                        <td>".$v[duration]."</td><td>".$instruct."</td><td>".$sureStatus."</td>
                            <td style='display:none;'>".$v[programName]."</td></tr>";
        }        
       
        $data[0]=$show;$data[1]=$alarms;
        $this->ajaxReturn($data,'',1);
    }
    
    /**
    +--------------------------------
    * 查询子节点报警类型
    +--------------------------------
    * @date: 2017年7月12日 下午3:13:14
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxGetSecondAlarmType(){
        //报警类型
        $pid=$_GET['pid'];
        $AlarmType=M('alarm_slip_type');
        $alarmTypes=$AlarmType->where("parentCodeValue='$pid'")->field("id,displayName")->select();
        foreach ($alarmTypes as $k=>$v){
            $alarmTypesHtml.="<option value='$v[id]'>$v[displayName]</option>";
        }
        if($alarmTypes){
            $this->ajaxReturn($alarmTypesHtml,'',1);
        }else{
            $this->ajaxReturn('','',0);
        }
    }
    
    /**
    +--------------------------------
    * 根据信号类型读取频率值
    +--------------------------------
    * @date: 2017年7月12日 下午3:45:58
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxGetFrequencyId(){
        $signalTypeId=$_GET['signalTypeId'];
        $Frequency=M('frequency_info');
        $frequency=$Frequency->where("signalTypeId='$signalTypeId'")->field("frequencyId,frequencyName")->select();
        foreach ($frequency as $k=>$v){
            $frequencyHtml.="<option value='$v[frequencyId]'>$v[frequencyName]</option>";
        }
        if($frequency){
            $this->ajaxReturn($frequencyHtml,'',1);
        }else{
            $this->ajaxReturn('','',0);
        }
    }
    
    /**
    +--------------------------------
    * 异态登记表单保存和提交,一审登记
    +--------------------------------
    * @date: 2017年7月21日 上午11:40:03
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxAbnormalHandler(){
	   //调用接口
		import("@.Action.Home.PublicAction");
		$Public=new PublicAction();
		$SoapRes=$Public->TVSubmit($_POST);
		if($SoapRes==2){
			$this->ajaxReturn('','连接超时！',0);
		}else if($SoapRes==1){
			$this->ajaxReturn('','提交成功！',1);
		}else{
			$this->ajaxReturn('','提交失败！',0);
		}
       
    }
    
    /**
    +--------------------------------
    * 获取保存未发送的异常登记
    +--------------------------------
    * @date: 2017年7月21日 下午4:27:40
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxGetAlarmSend(){
        import("@.ORG.Page"); // 导入分页类
        $AlarmSend=M("alarm_send");
        $_POST['nowPage']?'':$_POST['nowPage']=1;
        
        //判断是否需要重置查询条件
        if($_POST['condition']==1){
            $condition="(submitStatus=0 or submitStatus=2) and ifRadio=0";
            ($_POST['manualKeyword']!="")?$condition.=" and (programName like '%$_POST[manualKeyword]%' or channelName like '%$_POST[manualKeyword]%')":'';
            $_SESSION["manualCondition"]=$condition;
        }
        
        if(!$_SESSION["manualCondition"]){
            $_SESSION["manualCondition"]="(submitStatus=0 or submitStatus=2) and ifRadio=0";
        }
        
        $count=$AlarmSend->where("$_SESSION[manualCondition]")->count(); // 查询满足要求的总记录数
        $Page=new Page($count,2); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->nowPage=$_POST['nowPage'];
        $show=$Page->ajaxShow(); // ajax分页显示输出
        $sql="SELECT * FROM t_alarm_send WHERE $_SESSION[manualCondition] ORDER BY alarmHappentime DESC LIMIT $Page->firstRow,$Page->listRows";
        $res=$AlarmSend->query($sql);
        foreach($res as $v){
            $alarms.="<tr id='$v[alarmId]'><td>".$v[alarmDate]."</td><td>".$v[signalType]."</td><td>".$v[frequencyValue]."</td>
                        <td>".$v[channelName]."</td><td>".$v[programName]."</td><td>".$v[alarmType]."</td><td>".$v[alarmLevel]."</td>
                        <td>".$v[description]."</td><td>".$v[faultReason]."</td>
                        <td>
                            <button type='button' class='btn btn-info btn-sm' data-toggle='tooltip' data-placement='top' title='编辑' style='padding:5px 10px 3px;'>
    						  <span class='glyphicon glyphicon-edit' aria-hidden='true'></span>
    						</button>
    						<button type='button' class='btn btn-success btn-sm' data-toggle='tooltip' data-placement='top' title='提交' style='padding:5px 10px 3px;'>
    						  <span class='glyphicon glyphicon-ok' aria-hidden='true'></span>
    						</button>
    						<button type='button' class='btn btn-danger btn-sm' data-toggle='tooltip' data-placement='top' title='删除' style='padding:5px 10px 3px;'>
    						  <span class='glyphicon glyphicon-remove' aria-hidden='true'></span>
    						</button></td>
                        </tr>";
        }
        $data[0]=$show;$data[1]=$alarms;
        $this->ajaxReturn($data,'',1);
    }
    
    /**
    +--------------------------------
    * 根据alarmId号获取信息
    +--------------------------------
    * @date: 2017年7月25日 下午3:58:32
    * @author: Str
    * @param: variable
    * @return:
    */
    public function getAlarmHandler(){
        $id=$_GET['id'];
        $AlarmSend=M("alarm_send");
        $data=$AlarmSend->where("alarmId='$id'")->find();
        if($data){
            $this->ajaxReturn($data,'',1);
        }else{
            $this->ajaxReturn('','',0);
        }
    }
    
    /**
    +--------------------------------
    * 保存报警表单
    +--------------------------------
    * @date: 2017年7月25日 下午4:36:10
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxSaveAlarmSendHandler(){
        //dump($_POST);die();
        $AlarmSend=M("alarm_send");
		$alarmId=$_POST['alarmId'];
		$_POST['alarmHappentime']=$_POST['alarmHappentime']." ".$_POST['startClock'];
		$_POST['alarmEndtime']=$_POST['alarmEndtime']." ".$_POST['endClock'];
		$_POST['ifRadio']=0;
		$isExist=$AlarmSend->where("alarmId='$alarmId'")->find();
        if(!$isExist){
            $_POST['createTime']=date("Y-m-d H:i:s");
            $_POST['submitStatus']=0;
            $_POST['createMan']=$_SESSION['userCode'];
        }
        if ($AlarmSend->create()){
            if($_POST['alarmId']){
                $res=$AlarmSend->save();
            }else{
                $res=$AlarmSend->add();
            }
            if($res){
                $this->ajaxReturn("","",1);
            }else{
                $this->ajaxReturn("","表单保存失败",0);
            }
        }
        else{
            $this->ajaxReturn("","表单保存失败",0);
        }
    }
    
    /**
    +--------------------------------
    * 删除选中的报警
    +--------------------------------
    * @date: 2017年7月27日 上午11:39:10
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxDelAlarmSendHandler(){
        $alarmId=$_GET['alarmId'];
        $AlarmSend=M("alarm_send");
        $res=$AlarmSend->where("alarmId='$alarmId'")->delete();
        if($res){
            $this->ajaxReturn('','',1);
        }else{
            $this->ajaxReturn('','',0);
        }
    }
    
    /**
     +--------------------------------
     * 提交选中的报警
     +--------------------------------
     * @date: 2017年7月27日 上午11:39:10
     * @author: Str
     * @param: variable
     * @return:
     */
    public function ajaxSubmitAlarmSendHandler(){
        $alarmId=$_GET['alarmId'];
        $manualFileName=$_POST['manualFileName'];
        $AlarmSend=M("alarm_send");
        $data=$AlarmSend->where("alarmId='$alarmId'")->find();
        
        //$res=$AlarmSend->where("alarmId='$alarmId'")->setField('submitStatus',1);
        //调用接口
        import("@.Action.Home.PublicAction");
        $Public=new PublicAction();
        $SoapRes=$Public->TVSubmit($data);
		if($SoapRes==2){
			//删除保存的文件
			//$AlarmSend->where("alarmId='$alarmId'")->delete();
			$this->ajaxReturn('','连接超时！',0);
		}else if($SoapRes==1){
            //$AlarmSend->where("alarmId='$alarmId'")->setField('submitStatus',1);
            //异态下载
            //$this->abnormalDownload($alarmId,$manualFileName);
            $this->ajaxReturn('','提交成功',1);
        }else{
            //$AlarmSend->where("alarmId='$alarmId'")->setField('submitStatus',2);
            $this->ajaxReturn('','提交失败',0);
        }
    }
    
	 /**
     +--------------------------------
     * 当前报警时间是否【重保期】、是否【重点时段 t_datesheet 逻辑】、是否【临时停机 t_tempdown】、是否【例行停机 t_mcndrel】、是否【试播期 t_temponair 逻辑】
     +--------------------------------
     * @date: 2017年8月7日 下午2:19:38
     * @author: Str
     * @param: variable
     * @return:
     */
	public function ajaxGetStatus(){
		$AlarmMessage=M("alarm_message");
		$alarmId=$_GET['id'];
		$res=$AlarmMessage->where("alarmId='$alarmId'")->find();
		$startdatetime=$res['alarmHappentime'];
		$enddatetime=$res['alarmEndtime'];
		$starttime=substr($startdatetime,11,8);
		$endtime=substr($enddatetime,11,8);
		$MC_ID=$res['channelId'];
		
	    $Model=new Model();
        $Model->db(1,C("DB_SCBIC2"));
		
		$res=$Model->query("SELECT * FROM t_monitorchannel WHERE MC_ID='$MC_ID' LIMIT 1");
		$C_ID=$res[0]['C_ID'];			
		//重保期
		$res=$Model->query("SELECT * FROM t_importantdate WHERE ((ID_StartDateTime<='$startdatetime' and '$startdatetime'<ID_EndDateTime) or (ID_StartDateTime>='$startdatetime' and '$enddatetime'>ID_StartDateTime)) LIMIT 1");  
		if($res){
			$data['important']="是";
		}else{
			$data['important']="否";
		}
		//重点时段
		$res=$Model->query("SELECT * FROM t_datesheet WHERE C_ID='$C_ID' and ((DS_StartTime<='$starttime' and '$starttime'<DS_EndTime) or (DS_StartTime>='$starttime' and '$endtime'>DS_StartTime)) LIMIT 1");  
		if($res){
			$data['datesheet']="是";
		}else{
			$data['datesheet']="否";
		}
		//临时停机
		$res=$Model->query("SELECT * FROM t_tempdown WHERE MC_ID='$MC_ID' and ((TD_Start<='$startdatetime' and '$startdatetime'<TD_End) or (TD_Start>='$startdatetime' and '$enddatetime'>TD_Start)) LIMIT 1");  
		if($res){
			$data['tempdown']="是";
		}else{
			$data['tempdown']="否";
		}
		//列行停机
		$res=$Model->query("SELECT * FROM t_mcndrel WHERE MC_ID='$MC_ID' and ((MNR_StartDateTime<='$startdatetime' and '$startdatetime'<MNR_EndDateTime) or (MNR_StartDateTime>='$startdatetime' and '$enddatetime'>MNR_StartDateTime)) LIMIT 1");  
		if($res){
			$data['mcndrel']="是";
		}else{
			$data['mcndrel']="否";
		}
		//试播期
		$res=$Model->query("SELECT * FROM t_temponair WHERE ((TO_StartDateTime<='$startdatetime' and '$startdatetime'<TO_EndDateTime) or (TO_StartDateTime>='$startdatetime' and '$enddatetime'>TO_StartDateTime)) LIMIT 1");  
		if($res){
			$data['temponair']="是";
		}else{
			$data['temponair']="否";
		}
		
		$this->ajaxReturn($data,'',1);
	}
	
    /**
     +--------------------------------
     * 异态下载 生成xml文件
     +--------------------------------
     * @date: 2017年8月7日 下午2:19:38
     * @author: Str
     * @param: variable
     * @return:
     */
    public function abnormalDownload($id,$manualFileName){
       $AlarmSend=M(alarm_send);
	   $AlarmMessage=M(alarm_message);
       $alarm=$AlarmSend->where("alarmId='$id'")->find();
	   
	   $Program=M("program_number");
	   $program=$Program->where("programName='$alarm[programName]'")->find();
	   
       $inTime=hsToSeconds(substr($alarm["alarmHappentime"],-5));
	  
       if($manualFileName){
           $inTime=$inTime-substr($manualFileName,10,2)*60-substr($manualFileName,12,2);
           $fileName=$manualFileName;
       }else{	   
			//FM文件
			if($alarm['signalType']=="FM"){
			  $path=C('originalFMFilePath').$program['pathNumber']."-720-RecordFull-".$program[programName]."/".substr($alarm[alarmHappentime],0,10)."/";
			}else{
			  //查找目录中对应的文件，视频文件
			  $path=C('originalVideoFilePath').$program['pathNumber']."-720-RecordFull-".$program[programName]."/".substr($alarm[alarmHappentime],0,10)."/";
			}
		   //查找目录中对应的文件
			$path=iconv('UTF-8','GB18030',$path);
			$file_array=scandir($path);
			$hour=substr($alarm[alarmHappentime],11,2);
			foreach($file_array as $v){
				if($hour==substr($v,8,2)){
					$fileName=$v;
					break;
				}
			}
       }
       $outTime=$inTime+getDurationTime($alarm["alarmHappentime"], $alarm["alarmEndtime"]);
	   if($alarm['signalType']=="FM"){
		   $videoURL=C('originalFMFilePath').$program['pathNumber']."-720-RecordFull-".$program[programName]."\\".substr($alarm[alarmHappentime],0,10)."\\".$fileName;
		}else{
		  //查找目录中对应的文件，视频文件
		   $videoURL=C('originalVideoFilePath').$program['pathNumber']."-720-RecordFull-".$program[programName]."\\".substr($alarm[alarmHappentime],0,10)."\\".$fileName;
		}
	   
       //截取的视频文件名称
       $cutFileName=GetRandStr(8);
       //截取文件名称保存到数据库
       $AlarmSend->where("alarmId='$id'")->setField("cutFileName",$cutFileName);
       
       //生成xml
       $content='<?xml version ="1.0" encoding="utf-8"?>
                        <cnpsXML CarbonAPIVer ="1.2" TaskType ="JobQueue" JobName="tastJob">
                        <Sources>
                        <Module_0 Filename ="'.$videoURL.'" Inpoint.QWD="'.(($inTime-3)*27027000).'" Outpoint.QWD="'.(($outTime+3)*27027000).'"/>
                        </Sources>
                        <Destinations>
                        <Module_0 PresetGUID ="{4BB58E0F-74FF-4E55-AB03-C6DDB8A7230C}">
                        <ModuleData CML_P_BaseFileName ="'.$cutFileName.'" CML_P_Path ="'.C("cutFilePath").$alarm['alarmDate'].'"/>
                        </Module_0>
                        </Destinations>
                        </cnpsXML>';
       
       $path="./xml/".time().GetRandStr(8).".xml";
       $of=fopen($path,'w');//创建并打开dir.txt
       if($of){
           $res=fwrite($of,$content);//把执行文件的结果写入txt文件
       }
       fclose($of);
      /*  if($res){
           $this->ajaxReturn('','',1);
       }else{
           $this->ajaxReturn('','',0);
       } */
    }
    
    /**
     +--------------------------------
     * 获取视频的播放路径
     +--------------------------------
     * @date: 2017年8月7日 下午4:15:08
     * @author: Str
     * @param: variable
     * @return:
     */
    public function getVideoURL_old(){
        $id=$_GET['id'];
        $Alarm=M(alarm_message);
        $alarm=$Alarm->where("alarmId=$id")->find();
        if(!$alarm){
            $this->ajaxReturn('','',0);
        }
        //$fileName=$this->getFileName($alarm["alarmHappentime"]);  //文件名称
		
        //路径
        $Program=M("program_number");
		$program=$Program->where("programName='$alarm[programName]'")->find();
		//FM文件
		if($alarm['signalCode']==11){
		  $path=C('originalFMFilePath').$program['pathNumber']."-720-RecordFull-".$program[programName]."/".substr($alarm[alarmHappentime],0,10)."/";
		}else{
		  //查找目录中对应的文件，视频文件
		  $path=C('originalVideoFilePath').$program['pathNumber']."-720-RecordFull-".$program[programName]."/".substr($alarm[alarmHappentime],0,10)."/";
		}
		
		$path=iconv('UTF-8','GB18030',$path);
		$file_array=scandir($path);
		$hour=substr($alarm[alarmHappentime],11,2);
		foreach($file_array as $v){
			if($hour==substr($v,8,2)){
				$fileName=$v;
				break;
			}
		}
		if($alarm['signalCode']==11){
		   $videoURL=C('originalFMPath').$program['pathNumber']."-720-RecordFull-".$program[programName]."/".substr($alarm[alarmHappentime],0,10)."/".$fileName;
		}else{
		  //查找目录中对应的文件，视频文件
		   $videoURL=C('originalVideoPath').$program['pathNumber']."-720-RecordFull-".$program[programName]."/".substr($alarm[alarmHappentime],0,10)."/".$fileName;
		}
       
		$startSecond=$this->getStartSecond($alarm["alarmHappentime"]);
		
        $this->ajaxReturn($videoURL,$startSecond,1);
    }
	
	/**
     +--------------------------------
     * 获取视频的播放路径
     +--------------------------------
     * @date: 2017年8月7日 下午4:15:08
     * @author: Str
     * @param: variable
     * @return:
     */
    public function getVideoURL(){
        $id=$_GET['id'];
        $Alarm=M(alarm_message);
        $alarm=$Alarm->where("alarmId=$id")->find();
        if(!$alarm){
            $this->ajaxReturn('','',0);
        }
        $getVideoUrl=C('get_video_url')."&channelId=".$alarm['channelId']."&startTime=".urlencode($alarm['alarmHappentime'])."&endTime=".urlencode($alarm['alarmEndtime']);    
		//echo $getVideoUrl;
		$res=file_get_contents($getVideoUrl);
		//dump($res);
		$res=json_decode($res);
		$videoURL=$res->url;
		if(!$videoURL){
			$this->ajaxReturn($videoURL,'',0);
		}
		$startSecond=$this->getStartSecond($alarm["alarmHappentime"]);
        $this->ajaxReturn($videoURL,$startSecond,1);
    }
    
    /**
     +--------------------------------
     * 获取对比视频的播放路径
     +--------------------------------
     * @date: 2017年8月7日 下午4:15:08
     * @author: Str
     * @param: variable
     * @return:
     */
    public function getRelVideoURL(){
        $id=$_GET['id'];
        $Alarm=M(alarm_message);
        $alarm=$Alarm->where("alarmId=$id")->find();
        if(!$alarm){
            $this->ajaxReturn('','',0);
        }
        $getVideoUrl=C('get_video_url')."&channelId=".$alarm['relChannelId']."&startTime=".urlencode($alarm['alarmHappentime'])."&endTime=".urlencode($alarm['alarmEndtime']);
        //echo $getVideoUrl;
        $res=file_get_contents($getVideoUrl);
        //dump($res);
        $res=json_decode($res);
        $videoURL=$res->url;
        if(!$videoURL){
            $this->ajaxReturn($videoURL,'',0);
        }
        $startSecond=$this->getStartSecond($alarm["alarmHappentime"]);
        $this->ajaxReturn($videoURL,$startSecond,1);
    }
	
	  /**
     +--------------------------------
     * 手动选择路径时播放路径
     +--------------------------------
     * @date: 2017年8月7日 下午4:15:08
     * @author: Str
     * @param: variable
     * @return:
     */
    public function manualGetPath(){
        $id=$_GET['id'];
        $Alarm=M(alarm_message);
        $alarm=$Alarm->where("alarmId=$id")->find();
        if(!$alarm){
            $this->ajaxReturn('','',0);
        }
        $fileName=$this->getFileName($alarm["alarmHappentime"]);  //文件名称
        //路径
        $Program=M("program_number");
		$program=$Program->where("programName='$alarm[programName]'")->find();
        $videoURL=C('originalVideoPath').$program['pathNumber']."-720-RecordFull-".$program[programName]."/".substr($alarm[alarmHappentime],0,10)."/";
		$startSecond=$this->getStartSecond($alarm["alarmHappentime"]);
		
        $this->ajaxReturn($videoURL,$startSecond,1);
    }
    
    public function getFileName($time){
        $ymd=substr($time,0,10);
        $ymdArray=explode("-", $ymd);
        $h=substr($time,11,2);
        return $ymdArray[0].$ymdArray[1].$ymdArray[2].$h."0000.ts";
    }
    
	public function getStartSecond($time){
		$His=substr($time,11,8);
        $HisArray=explode(":", $His);
		return ($HisArray[1]*60+$HisArray[2]);
	}
    
	/**
	 +--------------------------------
	 * 异态下载，radio文件拷贝
	 +--------------------------------
	 * @date: 2017年8月7日 下午4:15:08
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	public function fileCopy(){
	    $id=$_GET['id'];
	    $Alarm=M(alarm_message);
	    $alarm=$Alarm->where("alarmId=$id")->find();
	    if(!$alarm){
	        $this->ajaxReturn('','下载失败！',0);
	    }
	    $fileName=$this->getFileName($alarm["alarmHappentime"]);  //文件名称
	    //路径
	    $filePath="C:\\Uninstall.xml";
	    $newfile=C("radioCopyPath");
	    if (file_exists($filePath) == false){
	        $this->ajaxReturn('','文件不在,无法复制',0);
	    }
	    $result=copy($filePath,$newfile);
	    if ($result==1){
	         $this->ajaxReturn('','',1);
	    }
	    
	}
	
	/**
	 +--------------------------------
	 * 报警确认
	 +--------------------------------
	 * @date: 2017年8月7日 下午4:15:08
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	 public function alarmConfirm(){
		$id=$_POST['id'];
		$alarmLevel=$_POST['alarmLevel'];
		$sureStatus=$_POST['sureStatus'];
		$Message=M("alarm_message");
		$id_array=explode("@",$id);
		foreach($id_array as $v){
		  $Message->where("alarmId='$v'")->setField(array('alarmLevel','sureStatus'),array($alarmLevel,$sureStatus));
		}
        $this->ajaxReturn('','',1);
	 }
	 
	 /**
	 +--------------------------------
	 * 更新一审表
	 +--------------------------------
	 * @date: 2017年8月7日 下午4:15:08
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	 public function addYishen(){
		$id=$_POST['id'];
		$id_arr=explode("@",$id);
		$Alarm=M("alarm_message");
		$Yishen=M("yishen_temp");
		$i=0;
		foreach($id_arr as $v){
			if(!$Yishen->where("alarmID=$v")->find()){
				$alarm=$Alarm->where("alarmId=$v")->find();
				$data['alarmID']=$alarm['alarmId'];
				$data['programName']=$alarm['programName'];
				$data['channelID']=$alarm['channelId'];
				$data['starttime']=$alarm['alarmHappentime'];
				$data['endtime']=$alarm['alarmEndtime'];
				$Yishen->add($data);
				$i++;
			}
		}
		if($i){
			$this->ajaxReturn("","",1);
		}else{
			$this->ajaxReturn("","",0);
		}
	 }
	 
	 /**
	 +--------------------------------
	 * 读取一审表
	 +--------------------------------
	 * @date: 2017年8月7日 下午4:15:08
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	 public function getYishen(){
		$Yishen=M("yishen_temp");
		$yishen=$Yishen->select();
		$tr="";
		$tr2="";
		$Model=new Model();
        $Model->db(1,C("DB_SCBIC2"));
		$dutymember="";
		$transfer="";
		foreach($yishen as $k=>$v){
			$MC_ID=$v['channelID'];
			$monitorchannel=$Model->query("SELECT * FROM t_monitorchannel WHERE MC_ID='$MC_ID' LIMIT 1");
			$DM_ID=$monitorchannel[0]['DM_ID'];
			$TT_ID=$monitorchannel[0]['TT_ID'];
			$duty=$Model->query("SELECT * FROM t_dutyMember WHERE DM_ID='$DM_ID' LIMIT 1");
			$type=$Model->query("SELECT * FROM t_transfertype WHERE TT_ID='$TT_ID' LIMIT 1");
			$tr.="<tr><td>".$v['programName']."</td><td>".$v['starttime']."</td><td>".$v['endtime']."</td><td><button type='button' id='".$v[alarmID]."' class='btn btn-sm btn-danger'>删除</button></td></tr>";
			$tr2.="<tr><td>".$v['programName']."</td><td>".$v['starttime']."</td><td>".$v['endtime']."</td><td>".$duty[0]['DM_Name']."</td><td>".$type[0]['TT_Name']."</td></tr>";
			if($duty[0]['DM_Name']){
				$k!=0?$dutymember.=",":null;
				$dutymember.=$duty[0]['DM_Name'];
			}
			if($type[0]['TT_Name']){
				$k!=0?$transfer.=",":null;
				$transfer.=$type[0]['TT_Name'];
			}
		}
		$data['tr1']=$tr;
		$data['tr2']=$tr2;
		$data['dutymember']=$dutymember;
		$data['transfer']=$transfer;
		$this->ajaxReturn($data,'',1);
	 }
	
	/**
	 +--------------------------------
	 * 删除一审表
	 +--------------------------------
	 * @date: 2017年8月7日 下午4:15:08
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	 public function delYishen(){
		$Yishen=M("yishen_temp");
		$res=$Yishen->where("alarmID=$_GET[id]")->delete();
		if($res){
			$this->ajaxReturn("","",1);
		}else{
			$this->ajaxReturn("","",0);
		}
	 }
	
    public function uploadHandler(){
        dump($_FILES);
    }
}
?>