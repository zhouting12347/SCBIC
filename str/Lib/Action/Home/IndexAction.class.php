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
}
?>