<?php
class IndexAction extends CommonAction
{
  /**
  +--------------------------------
  * 首页
  +--------------------------------
  * @date: 2019年6月24日 下午3:01:46
  * @author: zt
  * @param: variable
  * @return:
  */
    public function index(){
        $Channel=M("volume_channel");
        $Transfertype=M("volume_transfertype");
        
        $this->assign("channel",$Channel->select());
        $this->assign("transfertype",$Transfertype->select());
        $this->assign("date",date("Y-m-d",time()));
        $this->display();
    }

    /**
    +--------------------------------
    * 加入到一审表操作
    +--------------------------------
    * @date: 2019年6月26日 上午10:38:10
    * @author: zt
    * @param: variable
    * @return:
    */
    public function addYishenHandler(){
        $Yishen=M('yishen');
        $dataReturn=array();
        //如果是双击加入的信息
        if($_GET['single']==1){
            $Yishen->where("1=1")->delete();
            $data['AlarmId']=$_POST['alarmId'];
            $MC_ID=$data['MC_ID']=$_POST['MC_ID'];
            $startDateTime=$data['StartDateTime']=$_POST['alarmHappentime'];
            $endDateTime=$data['EndDateTime']=$_POST['alarmEndtime'];
            $data['MC_Format']=$_POST['MC_Format'];
            $data['TT_ID']=$_POST['TT_ID'];
            $data['C_ID']=$_POST['C_ID'];
            $Yishen->add($data);
            
            //查询该频道是否在重点时段，是否在停机维修
            $Model=new Model();
            $Model->db(1,C("DB_SCBIC2"));
            $mcndrel=$Model->query("select * from t_mcndrel where MC_ID='$MC_ID' and ((MNR_StartDateTime<='$startDateTime' and '$startDateTime'<MNR_EndDateTime) or (MNR_StartDateTime>='$startDateTime' and '$endDateTime'>MNR_StartDateTime))"); //重点时段
            $tempdown=$Model->query("select * from t_tempdown where MC_ID='$MC_ID' and ((TD_Start<='$startDateTime' and '$startDateTime'<TD_End) or (TD_Start>='$startDateTime' and '$endDateTime'>TD_Start))"); //停机维护
            $importantdate=$Model->query("select * from t_importantdate where ((ID_StartDateTime<='$startDateTime' and '$startDateTime'<ID_EndDateTime) or (ID_StartDateTime>='$startDateTime' and '$endDateTime'>ID_StartDateTime))");//重保期
            
            if($mcndrel){
                $dataReturn['importantTime']=1;
            }else{
                $dataReturn['importantTime']=0;
            }
            if($tempdown){
                $dataReturn['scheduleStop']=1;
            }else{
                $dataReturn['scheduleStop']=0;
            }
            
            if($importantdate){
                $dataReturn['importantdate']=1;
            }else{
                $dataReturn['importantdate']=0;
            }
         
            //查询事故等级
            //查询频道
            $VolumeMonitorchannel=M("volume_monitorchannel");
            $monitorchannel=$VolumeMonitorchannel->where("MC_ID='$MC_ID'")->find();
            $channelID=$monitorchannel['C_ID'];
            //查询频道等级
            $Channel=M("volume_channel");
            $channel=$Channel->where("C_ID='$channelID'")->find();
            $CL_ID=$channel['CL_ID']; //频道等级
            //查询以上条件是否符合事故定性表中记录
            $fault=$Model->query("select * from t_faultleveldefine where CL_ID=$CL_ID and FLD_IfImportantTime=$dataReturn[importantTime] and FLD_IfImportantDay=$dataReturn[importantdate] and $_POST[duration]>FLD_MinSecond limit 1");
            if($fault){
                //查询FL_ID对应等级名称
                $faultLevel=$Model->query("select * from t_faultlevel where FL_ID=$fault[FL_ID]");
                $dataReturn['faultName']=$faultLevel['FL_Name'];
            }else{
                $dataReturn['faultName']="无";
            }
            //echo $Yishen->getLastSql();
        }else{
            foreach($_POST['data'] as $v){
                $data['AlarmId']=$v['alarmId'];
                $data['MC_ID']=$v['MC_ID'];
                $data['StartDateTime']=$v['alarmHappentime'];
                $data['EndDateTime']=$v['alarmEndtime'];
                $data['MC_Format']=$v['MC_Format'];
                $data['TT_ID']=$v['TT_ID'];
                $data['C_ID']=$v['C_ID'];
                $Yishen->add($data);
            }
        }
            $this->ajaxReturn($dataReturn,'操作成功',1);
    }
    
    /**
     +--------------------------------
     * 删除一审表操作
     +--------------------------------
     * @date: 2019年6月26日 上午10:38:10
     * @author: zt
     * @param: variable
     * @return:
     */
    public function delYishenHandler(){
        $Yishen=M('yishen');
        $res=$Yishen->where("AlarmId='$_GET[alarmId]'")->delete();
        if($res){
            $this->ajaxReturn('','操作成功',1);
        }else{
            $this->ajaxReturn('','操作失败',0);
        }
    }
    
    /**
     +--------------------------------
     * 根据C_ID,查询EPG信息
     +--------------------------------
     * @date: 2019年5月8日 下午2:20:54
     * @author: zt
     * @param: variable
     * @return:
     */
    public function video_get_epg_handler(){
        $Model=new Model();
        $Model->db(1,C("DB_EPG"));
        $limit=$_GET['limit'];
        $firstRow=($_GET['page']-1)*$limit;
        $C_ID=$_GET['C_ID'];
        $date=$_GET['date'];
        
        $sql="SELECT COUNT(*) as count FROM t_epg WHERE C_ID='$C_ID' and startDate='$date'";
        $count=$Model->query($sql);
        $sql="SELECT * FROM t_epg WHERE C_ID='$C_ID' and startDate='$date' ORDER BY startDate limit $firstRow,$limit";
        $data=$Model->query($sql);
        $current=date("Y-m-d H:i:s",time());
        foreach($data as $k=>$v){
            $data[$k]['time']=substr($v['startTime'],11,8);
            if($current>=$v['endTime']){
                $data[$k]['status']=2;
            }else if($v['startTime']<=$current&&$current<$v['endTime']){
                $data[$k]['status']=1;
            }else if($current<$v['startTime']){
                $data[$k]['status']=0;
            }
        }
        
        $result=array(
            'code'=>0,
            'msg'=>'',
            'count'=>$count[0]['count'],
            'data'=>$data
        );
        echo json_encode($result);
    }
    
    public function video_get_live_record_url_handler(){
        $starttime=$_GET['starttime'];
        $endtime=$_GET['endtime'];
        //超时时间设置
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'timeout'=>8,
            )
        );
        
        if($_GET['status']==2){
            $getVideoUrl=C('get_video_url')."&channelId=".$_GET['MC_ID']."&startTime=".urlencode($starttime)."&endTime=".urlencode($endtime);
        }else if($_GET['status']==1){
            $getVideoUrl=C('get_live_url')."&channelId=".$_GET['MC_ID'];
        }
        $res=file_get_contents($getVideoUrl,false,stream_context_create($opts));
        $res=json_decode($res);
        $videoURL=$res->url;
        if(!$videoURL){
            $this->ajaxReturn('','服务器返回播放地址无效',0);
        }else{
            $this->ajaxReturn($videoURL,'',1);
        }
    }
    
    /**
    +--------------------------------
    * 一审页面
    +--------------------------------
    * @date: 2019年6月27日 下午1:00:53
    * @author: zt
    * @param: variable
    * @return:
    */
    public function video_yishen_layer(){
        $Model=new Model();
        $Model->db(1,C("DB_SCBIC2"));
        $dutymember=$Model->query("select * from t_dutymember");
        $transfertype=$Model->query("select * from t_transfertype");
        $this->assign('transfertype',$transfertype);
        $this->assign('dutymember',$dutymember);
        $this->display();
    }
    
    /**
     +--------------------------------
     * 提交一审到接口
     +--------------------------------
     * @date: 2019年5月8日 下午2:20:54
     * @author: zt
     * @param: variable
     * @return:
     */
    public function video_yishen_submit_handler(){
        $ws=C('WSDL');//webservice服务的地址
        try{
            $client=new SoapClient($ws);
        }catch(Exception $e){
            $this->ajaxReturn("","接口访问失败",0);
        }
        
        //读取yishen表
        $Yishen=M("yishen");
        $res=$Yishen
        ->join("left join t_volume_monitorchannel on t_volume_monitorchannel.MC_ID=t_yishen.MC_ID")
        ->select();
        foreach($res as $k=>$v){
            $yishen[$k]['pd']=$v["MC_Name"];
            $yishen[$k]['pdid']=$v["MC_ID"];
            $yishen[$k]['kssj']=$v["StartDateTime"];
            $yishen[$k]['jssj']=$v["EndDateTime"];
        }
        
        $p=array(
            'Description'=>$_POST['description'],
            'FSDD'=>$_POST['FSDD'],//责任单位DM_Name
            'YXJM'=>$_POST['YXJM'],//影响节目
            'Type'=>$_POST['Type'], //传输类型 TT_Name
            'YXFWSub'=>$yishen
        );
        $params=array(
            'workflowCode'=>'ABJCKBD',
            'userCode'=>'Administrator',
            'finishStart'=>true,
            'EntityParamValues'=>json_encode($p)
        );
        /* $myfile = fopen("test.txt", "w") or die("Unable to open file!");
         $txt =$params;
         fwrite($myfile, $txt);
         fclose($myfile); */
        $res=$client->StartWorkflowByEntityTransJson($params);
        unset($client);
        if($res->StartWorkflowByEntityTransJsonResult->Success=="true"){
            //删除yishen表
            $Yishen->where("1=1")->delete();
            $this->ajaxReturn("","操作成功",1);
        }else{
            $this->ajaxReturn("","操作失败",0);
        }
    }
    
    /**
    +--------------------------------
    * 确认报警操作
    +--------------------------------
    * @date: 2019年6月27日 下午2:27:07
    * @author: zt
    * @param: variable
    * @return:
    */
    public function index_confirm_alarm_handler(){
        $AlarmMessage=M("alarm_message");
        $Yishen=M("yishen");
        foreach ($_POST['data'] as $v){
            $AlarmMessage->where("alarmId='$v[AlarmId]'")->setField('sureStatus',1);
            //删除yishen表中记录
            $Yishen->where("AlarmId='$v[AlarmId]'")->delete();
        }
        $this->ajaxReturn("","操作成功",1);
    }
}
?>