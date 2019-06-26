<?php
class TableAction extends Action{
    
	/**
	+--------------------------------
	* 获取页面表格数据
	+--------------------------------
	* @date: 2018年8月8日 下午1:39:34
	* @author: zt
	* @param: variable
	* @return:
	*/
    public function getTableData(){
        $tableName=$_GET['tableName'];
        $tableType=$_GET['tableType'];
        $Table=M($tableName);
        $limit=$_GET['limit'];
        $firstRow=($_GET['page']-1)*$limit;
        $count=$Table->count();
        //表格类型
        switch($tableType){
            //alarm_message,信号监测表
            case "alarm":
                $condition="sureStatus=0 and alarmType<>7000036 and alarmType<>7000037 and alarmType<>7000044";
                $data=$Table
                ->where($condition)
                ->join("left join t_volume_monitorchannel on t_volume_monitorchannel.MC_ID=t_alarm_message.channelId")
                ->limit($firstRow.','.$limit)
                ->select();
                $count=$Table->where($condition)->count();
                break;
            
             //高标清同播
            case "samePlay":
                $condition="sureStatus=0 and alarmType=7000036 and relChannelId<>''";
                $data=$Table
                ->where($condition)
                ->join("left join t_volume_monitorchannel on t_volume_monitorchannel.MC_ID=t_alarm_message.channelId")
                ->limit($firstRow.','.$limit)
                ->select();
                $Monitorchannel=M('volume_monitorchannel');
                foreach ($data as $k=>$v){
                    $channel=$Monitorchannel->where("MC_ID='$v[relChannelId]'")->find();
                    $data[$k]['REL_MC_Name']=$channel['MC_Name'];
                }
                $count=$Table->where($condition)->count();
                break;
                
            //重要转播
            case "important":
                $condition="sureStatus=0 and alarmType=7000037 and relChannelId<>''";
                $data=$Table
                ->where($condition)
                ->join("left join t_volume_monitorchannel on t_volume_monitorchannel.MC_ID=t_alarm_message.channelId")
                ->limit($firstRow.','.$limit)
                ->select();
                $Monitorchannel=M('volume_monitorchannel');
                foreach ($data as $k=>$v){
                    $channel=$Monitorchannel->where("MC_ID='$v[relChannelId]'")->find();
                    $data[$k]['REL_MC_Name']=$channel['MC_Name'];
                }
                $count=$Table->where($condition)->count();
                break;
                
           //停机检修
            case "stop":
                $condition="sureStatus=0 and alarmType=7000044";
                $data=$Table
                ->where($condition)
                ->join("left join t_volume_monitorchannel on t_volume_monitorchannel.MC_ID=t_alarm_message.channelId")
                ->limit($firstRow.','.$limit)
                ->select();
                $count=$Table->where($condition)->count();
                break;
                
             //监测频道查询 
            case "monitorchannel_search":
                $condition="1=1";
                empty($_GET['C_ID'])?"":$condition.=" and t_volume_monitorchannel.C_ID=$_GET[C_ID]";
                empty($_GET['TT_ID'])?"":$condition.=" and t_volume_monitorchannel.TT_ID=$_GET[TT_ID]";
                is_numeric($_GET['MC_Format'])?$condition.=" and t_volume_monitorchannel.MC_Format=$_GET[MC_Format]":null;
                empty($_GET['MC_Name'])?"":$condition.=" and t_volume_monitorchannel.MC_Name like '%$_GET[MC_Name]%'";
                $_GET['MC_IfCCTV']==1?$condition.=" and t_volume_monitorchannel.MC_IfCCTV=1":null;
                $count=$Table->where($condition)->count();
                $data=$Table
                ->join("left join t_volume_transfertype on t_volume_transfertype.TT_ID=t_volume_monitorchannel.TT_ID")
                ->join("left join t_volume_channel on t_volume_channel.C_ID=t_volume_monitorchannel.C_ID")
                ->where($condition)
                ->order("t_volume_monitorchannel.OPTime desc")->limit($firstRow.','.$limit)->select();
                foreach($data as $k=>$v){
                    if($v['MC_Format']==2){
                        $data[$k]['MC_Name']=$data[$k]['MC_Name']."(HD)";
                    }
                }
                break;
                
            //yishen 一审表
            case "yishen":
            $data=$Table
            ->join("t_volume_monitorchannel on t_volume_monitorchannel.MC_ID=t_yishen.MC_ID")
            ->join("t_volume_transfertype on t_volume_transfertype.TT_ID=t_yishen.TT_ID")
            ->join("t_volume_channel on t_volume_channel.C_ID=t_yishen.C_ID")
            ->limit($firstRow.','.$limit)
            ->select();
            $count=$Table->count();
                break;
            //成员表
            case "member":
                $condition="1=1";
                empty($_GET[keyword])?null:$condition.=" and t_member.M_Name like '%$_GET[keyword]%'";
                $data=$Table->join("left join t_unittype on t_member.U_ID=t_unittype.U_ID")
                ->join("left join t_unitlevel on t_member.UL_ID=t_unitlevel.UL_ID")
                ->join("left join t_area on t_member.A_ID=t_area.A_ID")
                ->where($condition)
                ->order("t_member.OPTime desc")
                ->limit($firstRow.','.$limit)->select();
                $count=$Table->where($condition)->count();
                break;
                
            default:
                $data=$Table->order("OPTime desc")->limit($firstRow.','.$limit)->select();
        }
        
        $result=array(
            'code'=>0,
            'msg'=>'',
            'count'=>$count,
            'data'=>$data
        );
        echo json_encode($result);
    }
    
  
}