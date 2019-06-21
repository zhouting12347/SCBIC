<?php
class RadioAction extends Action
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
            $condition="ifRadio=1 AND duration!=0";
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
        $sql="SELECT *,(SELECT frequencyName FROM t_radio_frequency_info WHERE frequencyId=t_alarm_message.frequencyId AND t_alarm_message.frequencyId!=null) AS frequencyName,
        (SELECT displayName FROM t_alarm_slip_type WHERE id=alarmType) AS alarmType,
        (SELECT displayName FROM t_alarm_slip_type WHERE id=slipType) AS slipType,
        (SELECT signalType FROM t_signal_info WHERE signalCode=t_alarm_message.signalCode) as signalCode,
        (SELECT channelName FROM t_channel_info WHERE channelId=t_alarm_message.channelId) as channelName 
        FROM t_alarm_message WHERE $_SESSION[condition] ORDER BY alarmHappentime DESC LIMIT $Page->firstRow,$Page->listRows";
        //echo $sql;die();
        $list=$Alarm->query($sql);
        foreach($list as $v){
            $v[instruct]==0?$instruct="结束":$instruct="未结束";
            $v[sureStatus]==0?$sureStatus="未确认":$sureStatus="确认";
            switch($v[alarmLevel]){
                case 1:
                    $alarmLevel="一级";break;
                case 2:
                    $alarmLevel="二级";break;
                case 3:
                    $alarmLevel="三级";break;
                case 4:
                    $alarmLevel="非等级差错";break;
            }
            $alarms.="<tr id='$v[alarmId]'><td>".$v[alarmHappentime]."</td><td>".$v[alarmEndtime]."</td><td>".$v[programName]."</td>
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
    * 异态登记表单保存和提交
    +--------------------------------
    * @date: 2017年7月21日 上午11:40:03
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxAbnormalHandler(){
        $alarmId=$_POST['alarmID'];
        $AlarmMsg=M("alarm_message");
        $res=$AlarmMsg->where("alarmId='$alarmId'")->find();
        
        //保存到t_alarm_send表中
        //$data['alarmId']=$alarmId;
        $data['alarmDate']=substr($res['alarmHappentime'],0,10);
        $data['alarmHappentime']=$res['alarmHappentime'];
        $data['alarmEndtime']=$res['alarmEndtime'];
        $data['duration']=$res['duration'];
        $data['signalType']=$_POST['signalType'];
        $data['frequencyValue']=$_POST['frequencyValue'];
        $data['channelName']=$_POST['channel'];
        $data['programName']=$res['programName'];
        $data['alarmType']=$_POST['alarmType'];
        $data['alarmLevel']=$_POST['alarmLevel'];
        $data['description']=$_POST['description'];
        $data['faultReason']=$_POST['faultReason'];
        $data['dutyMan1']=$_POST['dutyMan1'];
        $data['dutyMan2']=$_POST['dutyMan2'];
        $data['createMan']=$_SESSION['userCode'];
        $data['createTime']=date("Y-m-d H:i:s");
        $data['submitStatus']=0;
        $data['ifRadio']=1;
        $data['monitorStation']=$_POST['monitorStation'];
        $data['verifyDepartment']=$_POST['verifyDepartment'];
        $data['sourceForm']=$_POST['sourceForm'];
        $data['ADPhone']=$_POST['ADPhone'];
        $data['ADPeople']=$_POST['ADPeople'];
        $data['submitMan']=$_POST['submitMan'];
        $data['submitPhone']=$_POST['submitPhone'];
        $data['ApproveUnit']=$_POST['ApproveUnit'];
        $AlarmSend=M("alarm_send");
        $res=$AlarmSend->add($data);
        $data['alarmId']=$res;
        
        if(!$res){
            $this->ajaxReturn('','保存失败',0);
        }
        //提交表单
        if($_POST['type']=='submit'){
            //调用接口
            import("@.Action.Home.PublicAction");
            $Public=new PublicAction();
            $SoapRes=$Public->RadioSubmit($data);
            if($SoapRes){
                $AlarmSend->where("alarmId='$res'")->setField('submitStatus',1);
                $this->ajaxReturn('','提交成功',1);
            }else{
                $AlarmSend->where("alarmId='$res'")->setField('submitStatus',2);
                $this->ajaxReturn('','提交失败',0);
            }
        //保存表单返回结果
        }else if($_POST['type']=='save'){
            if($res){
                $this->ajaxReturn('','保存成功',1);
            }else{
                $this->ajaxReturn('','保存失败',0);
            }
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
            $condition="(submitStatus=0 or submitStatus=2) and ifRadio=1";
            ($_POST['manualKeyword']!="")?$condition.=" and (frequencyValue like '%$_POST[manualKeyword]%' or monitorStation like '%$_POST[manualKeyword]%' or 
            description like '%$_POST[manualKeyword]%' or sourceForm like '%$_POST[manualKeyword]%' or ApproveUnit like '%$_POST[manualKeyword]%' or ADPhone like '%$_POST[manualKeyword]%'
             or ADPeople like '%$_POST[manualKeyword]%')":'';
            $_SESSION["manualConditionRadio"]=$condition;
        }
        
        if(!$_SESSION["manualConditionRadio"]){
            $_SESSION["manualConditionRadio"]="(submitStatus=0 or submitStatus=2) and ifRadio=1";
        }
        
        $count=$AlarmSend->where("$_SESSION[manualConditionRadio]")->count(); // 查询满足要求的总记录数
        $Page=new Page($count,2); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->nowPage=$_POST['nowPage'];
        $show=$Page->ajaxShow(); // ajax分页显示输出
        
        $sql="SELECT * FROM t_alarm_send WHERE $_SESSION[manualConditionRadio] ORDER BY alarmHappentime DESC LIMIT $Page->firstRow,$Page->listRows";
        $res=$AlarmSend->query($sql);
        foreach($res as $v){
            $alarms.="<tr id='$v[alarmId]'><td>".$v[alarmDate]."</td><td>".$v[frequencyValue]."</td><td>".$v['monitorStation']."</td>
                        <td>".$v[verifyDepartment]."</td><td>".$v['sourceForm']."</td><td>".$v['ADPhone']."</td><td>".$v['ADPeople']."</td>
                        <td>".$v['submitMan']."</td><td>".$v['submitPhone']."</td>
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
        $AlarmSend=M("alarm_send");
        if(!$_POST['alarmId']){
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
        $AlarmSend=M("alarm_send");
        $data=$AlarmSend->where("alarmId='$alarmId'")->find();
        
        //调用soap接口
        import("@.Action.Home.PublicAction");
        $Public=new PublicAction();
        $SoapRes=$Public->RadioSubmit($data);
        if($SoapRes==2){
			$this->ajaxReturn('','连接超时！',0);
		}else if($SoapRes==1){
            $AlarmSend->where("alarmId='$alarmId'")->setField('submitStatus',1);
            //复制音频文件到本地
            //....
            
            $this->ajaxReturn('','提交成功',1);
        }else{
            $AlarmSend->where("alarmId='$alarmId'")->setField('submitStatus',2);
            $this->ajaxReturn('','提交失败',0);
        }
    }
    
    public function getRadioURL(){
        $id=$_GET['id'];
        $Alarm=M(alarm_message);
        $alarm=$Alarm->where("alarmId=$id")->find();
        if(!$alarm){
            $this->ajaxReturn('','',0);
        }
        echo $id;
    }
    
    
}
?>