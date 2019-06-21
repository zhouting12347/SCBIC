<?php
class LiveAction extends Action
{
    /**
    +--------------------------------
    * 直播流主页
    +--------------------------------
    * @date: 2017年8月2日 上午11:06:59
    * @author: Str
    * @param: variable
    * @return:
    */
    public function index(){
        //查询所有直播地址
        $Live=M("live_address");
        $live=$Live->select();
        $this->assign('live',$live);
        $this->display();
    }
    
    /**
    +--------------------------------
    * 查询直播地址
    +--------------------------------
    * @date: 2017年8月2日 下午3:54:25
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxGetLive(){
        $Live=M("live_address"); 
        //import("@.ORG.Page"); // 导入分页类
        
        //判断是否需要重置查询条件
        if($_POST['condition']==1){
            $condition="1=1";
            ($_POST['keyword']!="")?$condition.=" and (programName like '%$_POST[keyword]%' or address like '%$_POST[keyword]%')":'';
            if($_POST['source']!="all"){
				$condition.=" and source='$_POST[source]'";
			}
			$_SESSION["liveCondition"]=$condition;
        }
        
        //$count=$Live->where($_SESSION["liveCondition"])->count(); // 查询满足要求的总记录数
        //$Page=new Page($count,5); // 实例化分页类 传入总记录数和每页显示的记录数
        //$Page->nowPage=$_POST['nowPage'];
       // $show=$Page->ajaxShow(); // ajax分页显示输出
        $sql="SELECT * FROM t_live_address WHERE $_SESSION[liveCondition]";
        $list=$Live->query($sql);
        foreach($list as $v){
            $live.="<tr id='$v[id]'><td>".$v[programName]."</td><td>".$v[address]."</td>
                <td><button type='button' class='btn btn-info'>主</button>
                        &nbsp<button type='button' class='btn btn-success 1'>1</button>
                        &nbsp<button type='button' class='btn btn-success 2'>2</button>
                        &nbsp<button type='button' class='btn btn-success 3'>3</button>
                </td>
                </tr>";
        }
        $data[0]=$show;$data[1]=$live;
        $this->ajaxReturn($data,'',1);
    }
    
    /**
    +--------------------------------
    * 管理页查询
    +--------------------------------
    * @date: 2017年9月12日 上午11:02:42
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxGetManageLive(){
        $Live=M("live_address");
        import("@.ORG.Page"); // 导入分页类
        
        //判断是否需要重置查询条件
        if($_POST['condition']==1){
            $condition="1=1";
            ($_POST['keyword']!="")?$condition.=" and (programName like '%$_POST[keyword]%' or address like '%$_POST[keyword]%')":'';
            $_SESSION["liveCondition"]=$condition;
        }
        $count=$Live->where($_SESSION["liveCondition"])->count(); // 查询满足要求的总记录数
        $Page=new Page($count,5); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->nowPage=$_POST['nowPage'];
        $show=$Page->ajaxShow(); // ajax分页显示输出
        $sql="SELECT * FROM t_live_address WHERE $_SESSION[liveCondition] LIMIT $Page->firstRow,$Page->listRows";
        $list=$Live->query($sql);
        foreach($list as $v){
            $live.="<tr id='$v[id]'><td>".$v[programName]."</td><td>".$v[address]."</td><td>".$v[source]."</td>
                <td><button type='button' class='btn btn-danger'>删除</button></td>
                </tr>";
        }
        $data[0]=$show;$data[1]=$live;
        $this->ajaxReturn($data,'',1);
    }
    
    /**
    +--------------------------------
    * 添加直播流操作
    +--------------------------------
    * @date: 2017年8月4日 下午2:45:32
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxSaveAddressHandler(){
        $data['programName']=$_POST['programName'];
        $data['address']=$_POST['address'];
		$data['source']=$_POST['source'];
        $Live=M("live_address");
        $res=$Live->add($data);
        if($res){
            $this->ajaxReturn('','',1);
        }else{
            $this->ajaxReturn('','',0);
        }
    }
    
    /**
    +--------------------------------
    * 删除一条直播流
    +--------------------------------
    * @date: 2017年8月4日 下午3:03:08
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxDelAddressHandler(){
        $Live=M("live_address");
        $id=$_GET['id'];
        $res=$Live->where("id=$id")->delete();
      if($res){
            $this->ajaxReturn('','',1);
        }else{
            $this->ajaxReturn('','',0);
        }
    }
    
}
?>