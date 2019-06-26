<?php
class PublicAction extends Action
{
	/**
	+--------------------------------
	* 获取报警数据到本地数据库
	+--------------------------------
	* @date: 2017年6月27日 下午3:46:55
	* @author: Str
	* @param: variable
	* @return:
	*/
    public function getAlarmMessage(){
        //读取上一次读取的时间
        $Time=M('time');
        $timeVideo=$Time->where("id=1")->find();
		$timeRadio=$Time->where("id=2")->find();
        
        //保存当前时间
        //$Time->setField("time",date("Y-m-d H:i:s"));
        
        //读取视频报警数据库
        $model=new Model();
        $model->db("1",C("videoAlarm"));
        $video=$model->query("select * from t_alarm_message where alarmHappenTime>='$timeVideo[time]' order by alarmHappenTime desc");
		if($video){
			$Time->where("id=1")->setField("time",$video[0]['alarmHappentime']);
			//保存到自己的数据库中
			$Alarm=M("alarm_message");
			foreach($video as $v){
				$v['duration']=strtotime($v['alarmEndtime'])-strtotime($v['alarmHappentime']);
				$v['ifRadio']=0;
				$v['instruct']=0;//已结束
				$Alarm->add($v);
				//echo $Alarm->getLastSql();
			}
		}
        

/*         //读取音频报警数据库
		$model2=new Model();
        $model2->db("2",C("radioAlarm"));
        $radio=$model2->query("select * from t_alarm_message where alarmHappenTime>='$timeRadio[time]'");
		
		if($radio){
			$Time->where("id=2")->setField("time",$radio[0]['alarmHappentime']);
			 //保存到自己的数据库中
			foreach($radio as $v){
				if($v['programName']&&$v['channelId']){
					unset($v['otherAlarmInfo']);//删除表中没有的字段
					unset($v['otherSlipInfo']);
					$v['duration']=strtotime($v['alarmEndtime'])-strtotime($v['alarmHappentime']);
					$v['ifRadio']=1;
					$Alarm->add($v);
				}
			}
		}
        */
		unset($model);
		//unset($model2);
    }
    
    /********************************************************************WSDL****************************************************************/
    /**
    +--------------------------------
    * 电视异态登记提交
    +--------------------------------
    * @date: 2017年6月29日 下午2:36:11
    * @author: Str
    * @param: variable
    * @return:
    */
    public function TVSubmit($data){
		$ws=C('WSDL');//webservice服务的地址
		try{
			$client=new SoapClient($ws);
		}catch(Exception $e){ 
			return 2;
		} 

        $params1=array('Alarmdate'=>'2017-06-06',
            'alarmHappentime'=>'2017-06-06 11:12:20',
            'alarmEndtime'=>'2017-06-06 11:13:00',
            'Duration'=>'40',
            'signalType'=>'DVB_C',
            'frequencyValue'=>'291MHZ',
            'channelName'=>'东方卫视',
            'programName'=>'神奇地球第一期',
            'alarmType'=>'静帧',
            'alarmLevel'=>'123abc',
            'description'=>'这是一个异态的描述',
            'faultReason'=>'引起的原因是什么',
            'dutyMan1'=>'张三',
            'dutyMan2'=>'李四',
            'createTime'=>'2017-06-06 11:30:00',
            'createMan'=>'administrator',
            'ifNewstime'=>'0',
        );
        
         $params=array('Alarmdate'=>$data['alarmDate'],
            'alarmHappentime'=>$data['alarmHappentime'],
            'alarmEndtime'=>$data['alarmEndtime'],
            'Duration'=>$data['duration'],
            'signalType'=>$data['signalType'],
            'frequencyValue'=>$data['frequencyValue'],
            'channelName'=>$data['channelName'],
            'programName'=>$data['programName'],
            'alarmType'=>$data['alarmType'],
            'alarmLevel'=>$data['alarmLevel'],
            'description'=>$data['description'],
            'faultReason'=>$data['faultReason'],
            'dutyMan1'=>$data['dutyMan1'],
            'dutyMan2'=>$data['dutyMan2'],
            'createTime'=>$data['createTime'],
            'createMan'=>$data['createMan'],
            'ifNewstime'=>'0',
        ); 
		
		//读取yishen_temp表
		$Yishen=M("yishen_temp");
		$res=$Yishen->select();
		foreach($res as $k=>$v){
			$yishen[$k]['pd']=$v["programName"];
			$yishen[$k]['pdid']=$v["channelID"];
			$yishen[$k]['kssj']=$v["starttime"];
			$yishen[$k]['jssj']=$v["endtime"];
		}
		
		$p=array(
				'Description'=>$data['description'],
				'FSDD'=>$data['FSDD'],//责任单位DM_Name
				'YXJM'=>$data['YXJM'],//影响节目
				'Type'=>$data['Type'], //传输类型 TT_Name
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
            //删除yishen_temp表
			$Yishen->where("1=1")->delete();
            return 1;
        }else{
            return 0;
        }
    }
    
    /**
    +--------------------------------
    * 广播调频异态登记提交
    +--------------------------------
    * @date: 2017年6月29日 下午2:51:33
    * @author: Str
    * @param: variable
    * @return:
    */
    public function RadioSubmit($data){
        $ws=C('WSDL');//webservice服务的地址
        try{
            $client=new SoapClient($ws);
        }catch(Exception $e){
            return 2;
        }
        $params1=array('Alarmdate'=>'2017-06-06',
            'alarmHappentime'=>'2017-06-06 11:12:20',
            'frequencyValue'=>'291MHZ',
            'monitorStation'=>'上海站',
            'description'=>'XX单位',
            'ApproveUnit'=>'非法播出内容描述',
            'createTime'=>'2017-06-06 11:30:00',
            'createMan'=>'administrator');
        
        $params=array('Alarmdate'=>$data['alarmDate'],
            'alarmHappentime'=>$data['alarmHappentime'],
            'frequencyValue'=> $data['frequencyValue'],
            'monitorStation'=>$data['monitorStation'],
            'description'=>$data['verifyDepartment'],
            'ApproveUnit'=> $data['ApproveUnit'],
            'createTime'=>$data['createTime'],
            'createMan'=>$data['createMan'],
            'sourceForm'=>$data['sourceForm'],
            'ADPhone'=>$data['ADPhone'],
            'ADPeople'=>$data['ADPeople'],
            'submitMan'=>$data['submitMan'],
            'submitPhone'=>$data['submitPhone']);
        
        $res=$client->RadioSubmit($params);
        $AlarmSend=M('alarm_send');
        unset($client);
        if($res->RadioSubmitResult->Success=="true"){
            $alarmId=$data['alarmId'];
            $AlarmSend->where("alarmID='$alarmId'")->setField("workflowID",$res->RadioSubmitResult->workflowID);
            return 1;
        }else{
            return 0;
        }
        //$res->RadioSubmitResult->Success;
        //$res->RadioSubmitResult->workflowID;
    }
	
	 /**
    +--------------------------------
    * 预警状态查询，发送状态
    +--------------------------------
    * @date: 2017年6月29日 下午3:32:03
    * @author: Str
    * @param: variable
    * @return:
    */
	public function checkWarningStatus(){
		$WarningStatus=M("warning_send_status");
		$Station=M("station");
		$res=$WarningStatus->where("Ifsended=0")->select();
		foreach($res as $v){
			$path=C("warningMsgOutPath").$v[workflowID].".xml";
			$workflowID=$v[workflowID]; //wid
		    //$path="./xml/".$v[workflowID].".xml";
			$xml=simplexml_load_file($path);
			for($i=0;$i<count($xml->Schedule);$i++){
			    $stationName=$xml->Schedule[$i]->Station; //站点
			    //状态
			    if($xml->Schedule[$i]->Status=="发送成功"){
			        $stationStatus=1;
			    } else{
			        $stationStatus=0;
			    }
			    $station=$Station->where("name='$stationName'")->find(); //查询站点缩写编号
			    $this->WarningSubmit($station[num],$workflowID,$stationStatus); //调用接口
			    //echo $stationStatus."@".$station[num]."@".$workflowID;echo "<br>";
			}
			//文件存在，修改文件状态
			if(is_file($path)){
			 $WarningStatus->where("workflowID='$workflowID'")->setField("Ifsended",1);//warning_send_status状态置为1
			}
		}
	}
    
    /**
    +--------------------------------
    * 预警转发结果提交
    +--------------------------------
    * @date: 2017年6月29日 下午3:01:19
    * @author: Str
    * @param: variable
    * @return:
    */
    public function WarningSubmit($station,$wid,$status){
        $ws=C('WSDLYJ');//webservice服务的地址
        $client=new SoapClient ($ws);
        $params=array('warningID'=>$wid,
            'sendDate'=>'',
            'sendStation'=>$station,
            'ifSuccess'=>$status,
            'failReason'=>'');
			//dump($params);
        $res=$client->WarningSubmit($params);
        //dump($res);
        //$res->warningSubmitResult->Success; 
        //$res->warningSubmitResult->warningID;
    }
    
    /*****************************************************************************************************************************************************/
    
    /*****************************************************************************************************************************************************/
    /**
    +--------------------------------
    * 异态上报详情获取，手机页面
    +--------------------------------
    * @date: 2017年6月29日 下午3:13:49
    * @author: Str
    * @param: variable
    * @return:
    */
    public function getDetail(){
        $workflowID=$_GET['workflowID'];
		$isMobile=$_GET['isMobile'];
        $Alarm=M(alarm_send);
        $alarm=$Alarm->where("workflowID='$workflowID'")->find();
		//获取视频地址
        $videoURL=C("cutVideoPath").$alarm['alarmDate']."/".$alarm[cutFileName].".mp4";
        
		//文件名称
		$ymd=substr($alarm['alarmHappentime'],0,10);
        $ymdArray=explode("-", $ymd);
        $h=substr($alarm['alarmHappentime'],11,2);
        $fileName=$ymdArray[0].$ymdArray[1].$ymdArray[2].$h."0000.ts";
		//路径
		
		
        if($alarm){
            $this->assign("alarm",$alarm);
            $this->assign("videoURL",$videoURL);
			if($isMobile){
				$this->display("getDetail");
			}else{
				$this->display("getDetailPC");
			}
        }else{
			echo iconv("UTF-8","GB2312","查询不到该条信息！");
        }
        //import("@.ORG.CmsResponse");
        //$txt=$_GET['workflowID']."\r\n";
        //根据获取的workflowID显示报警页面
        //......
        /* $myfile=fopen("./Public/getDetail.txt", "a");
        fwrite($myfile,$txt);
        fclose($myfile);
        if($txt){
             $cmsResponse=new CmsResponse(true);
	         return $cmsResponse;
        }else{
            $cmsResponse=new CmsResponse(false);
	        return $cmsResponse;
        } */
    }
    
    /**
    +--------------------------------
    * 预警转发提交
    +--------------------------------
    * @date: 2017年6月29日 下午3:32:03
    * @author: Str
    * @param: variable
    * @return:
    */
    public function warningMsg(){
       /* $txt=$_GET['WID']."|".$_GET['Text']."|".$_GET['Station']."\r\n";
        $myfile=fopen("./Public/warningMsg.txt", "a");
        fwrite($myfile,$txt);
        fclose($myfile);  */
        if($_GET['WID']&&$_GET['Text']&&$_GET['Station']){
            $Station=M('station');
            $text=iconv("utf-8","gb2312",$_GET['Text']);
            $station_arr=explode(";", $_GET['Station']);
            $txt='<?xml version="1.0" encoding="GB2312"?><config ver="record_1.0">';
            foreach ($station_arr as $k=>$v){
                $res=$Station->where("num='$v'")->find();
                $name=iconv("utf-8","gb2312",$res[name]);
                $txt.='<Schedule Date="'.date("Y-m-d").'"><Event>'.($k+1).'</Event>
                        <Title>'.$text.'</Title>
                        <Station>'.$name.'</Station>
						<Status></Status>
                        </Schedule>';
            }
            $txt.="</config>";
            //生成xml文件
            $myfile=fopen(C("warningMsgInPath").$_GET[WID].".xml","a");
            //$myfile=fopen("./xml/".$_GET[WID].".xml","a");
            fwrite($myfile,$txt);
            fclose($myfile);
			//保存到数据库
			$WarningStatus=M("warning_send_status");
			$data['workflowID']=$_GET['WID'];
			$WarningStatus->add($data);
            print 1;
        }else{
            print 0;
        }
    }
    
    /**
     +--------------------------------
     * 发送短信调用链接
     +--------------------------------
     * @date: 2017年10月10日 下午3:45:05
     * @author: Str
     * @param: json
     * @return:
     */
    public function sendMsg(){
        //$json='{"PhoneNumber":"13916783624;13912345434;13845675434;13095867453","Content":"啊啊啊啊，啊啊啊啊啊","Type":"0"}';
        $json=file_get_contents('php://input');
        $info=json_decode($json);
        $words=$info->Content;         //短信内容
        $PhoneNumber=$info->PhoneNumber;
        $type=$info->Type;  //短信类型
        $mobile=explode(";", $PhoneNumber);  //电话号码数组
        
        if(!$mobile[0]){
            print 0;
            die();
        }
        
        $msg=array();
        $msg[]=mb_substr($words, 0,30,'utf-8');
        $msg[]=mb_substr($words, 30,30,'utf-8');
        $msg[]=mb_substr($words, 60,30,'utf-8');
        $msg[]=mb_substr($words, 90,30,'utf-8');
        $msg[]=mb_substr($words, 120,30,'utf-8');
        $msg[]=mb_substr($words, 150,30,'utf-8');
        
        import("@.ORG.ServerAPI"); //短信类
        $AppKey = '03b94e8c919c19aea8fe70f0de5350d4';
        $AppSecret = 'abbbebd6cae9';
        $p = new ServerAPI($AppKey,$AppSecret,'fsockopen');     //fsockopen伪造请求
         
         
        //发送模板短信
        $res=$p->sendSMSTemplate('3094145',$mobile,$msg);
    
        //保存到数据库中
        $Message=M("message");
        $data['content']=$words;
        $data['date']=date("Y-m-d");
        $data['time']=date("H:i:s");
        $data['type']=$type;
        $data['groupid']="";
        $res["code"]==200?$data['status']=1:$data['status']=0;
        $data["code"]=$res["code"];  //短信状态
        $data["sendid"]=$res["obj"];  //短信id
        $Message->add($data);
        if($res["code"]==200){
            print 1;
        }else{
            print 0;
        }
    }
	
    
    /**
    +--------------------------------
    * 扫描当前时间视频文件夹内是否产生对应的文件
    +--------------------------------
    * @date: 2017年9月18日 下午2:43:03
    * @author: Str
    * @param: variable
    * @return:
    */
     public function checkfile(){
         $Program=M("program_number");
         $Checkfile=M("checkfile");
         $program=$Program->select();
         $ymd=date("Y-m-d");
         $hour=date("H");
         $datetime=date("Y-m-d H:i:s");
         foreach($program as $v){
             $path=C("tsPath").$v[pathNumber]."-720-RecordFull-".$v[programName]."/".$ymd."/";
             $path=iconv('UTF-8','GB18030',$path);
             $file_array=scandir($path);
			 //dump($file_array);
             $flag=0;
             foreach($file_array as $v){
                 if($hour==substr($v,8,2)){
                    $flag=1;
                 }
             }
             if($flag==0){//未查询到生成的文件,记录到数据库中
                 $data['createTime']=$datetime;
                 $data['programName']=$v[programName];
                 $Checkfile->add($data);
             }
         }
     }
     
     /**
     +--------------------------------
     * 未生成文件表格
     +--------------------------------
     * @date: 2017年9月18日 下午3:18:02
     * @author: Str
     * @param: variable
     * @return:
     */
     public function checkfileTable(){
         $this->display();
     }
     
     //分页
     public function ajaxGetCheckfile(){
         $Checkfile=M("checkfile");
         import("@.ORG.Page"); // 导入分页类
         $_POST['nowPage']?'':$_POST['nowPage']=1;
         $count=$Checkfile->count();
         $Page=new Page($count,15); // 实例化分页类 传入总记录数和每页显示的记录数
         $Page->nowPage= $_POST['nowPage'];
         $show=$Page->ajaxShow(); //分页显示输出
         $sql="SELECT * FROM t_checkfile ORDER BY createTime DESC LIMIT $Page->firstRow,$Page->listRows";
         $res=$Checkfile->query($sql);
         foreach($res as $v){
             $list.="<tr><td>$v[programName]</td><td>$v[createTime]</td></tr>";
         }
         $data[0]=$show;$data[1]=$list;
         $this->ajaxReturn($data,'',1);
     }
    
    /**
    +--------------------------------
    * 发送短信
    +--------------------------------
    * @date: 2017年9月21日 上午11:00:22
    * @author: Str
    * @param: variable
    * @return:
    */
     public function sendMessage(){
         import("@.ORG.ServerAPI"); //短信类
         $AppKey = '421647d791d6fcbbd8c0ca513f08c1c3';
         $AppSecret = 'bdab002e143f';
         $p = new ServerAPI($AppKey,$AppSecret,'fsockopen');     //fsockopen伪造请求
         //接收的手机号码
         $mobile=array();
         $mobile[]=13916783624;
         $mobile[]=13764221164;
         //短信内容
         $msg=array();
         $msg[]="习近平强调，加强两国的政治互信，不断巩固和发展中新关系";
         $msg[]="，符合两国和两国人民利益，也有利于地区和世界的和平";
         $msg[]="、稳定与繁荣。";
         $msg[]="";
         //发送模板短信
         $res=$p->sendSMSTemplate('3121004',$mobile,$msg);
         dump($res);
     }
    
    public  function scandir(){
        $path="Z:/1-1-720-RecordFull-欢笑剧场C347/";
		$path=iconv('UTF-8','GB18030',$path);
        dump(scandir($path));
        //dump(readdir ($path));
    }
	
	public function test(){
		$k=0;
		for($i=0;$i<100;$i++){
			try{   
				$ws=C('WSDL');//webservice服务的地址
				$client=new SoapClient($ws);
				$res=$client->TVSubmit();
				dump($res);
				if($res){$k++;}
				unset($client);unset($res);
			}catch(Exception $e){   
				return false;
			} 
		}		
		$myfile=fopen("./Public/getDetail.txt", "a");
		fwrite($myfile,$i."@".$k."\r\n");
		fclose($myfile);
		die();
	}
	
	/**
	+--------------------------------
	* 短信抄送
	+--------------------------------
	* @date: 2018年2月6日 下午12:05:54
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function response(){
	    $headers = apache_request_headers();
	    $json=file_get_contents('php://input');
	    $response=json_decode($json);
	    $data['sendid']=$response->objects[0]->sendid;
	    $data['result']=$response->objects[0]->result;
	    $data['mobile']=$response->objects[0]->mobile;
	    $data['reportTime']=$response->objects[0]->reportTime;
	    $data['sendTime']=$response->objects[0]->sendTime;
	
	    $Response=M("response");
	    //查询是否存在重复的回执
	    $res=$Response->where("sendid='$data[sendid]' and mobile='$data[mobile]'")->find();
	    if(!$res){
	        $Response->add($data);
	    }
	}
	
	/**
	+--------------------------------
	* 短信详情页
	+--------------------------------
	* @date: 2018年2月6日 下午4:03:14
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function msgDetails(){
	    $sendid=$_GET['id'];
	    $Response=M("response");
	    $res=$Response
		->join("left join t_user on t_user.phone=t_response.mobile")
		->where("sendid='$sendid'")->order("result")->select();
	    $this->assign('response',$res);
	    $this->display();
	}
}
?>