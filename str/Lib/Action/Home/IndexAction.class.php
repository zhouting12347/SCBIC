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
        //如果是双击加入的信息
        if($_GET['single']==1){
            $Yishen->where("1=1")->delete();
            $data['AlarmId']=$_POST['alarmId'];
            $data['MC_ID']=$_POST['MC_ID'];
            $data['StartDateTime']=$_POST['alarmHappentime'];
            $data['EndDateTime']=$_POST['alarmEndtime'];
            $data['MC_Format']=$_POST['MC_Format'];
            $data['TT_ID']=$_POST['TT_ID'];
            $data['C_ID']=$_POST['C_ID'];
            $Yishen->add($data);
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
        $this->ajaxReturn('','操作成功',1);
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
}
?>