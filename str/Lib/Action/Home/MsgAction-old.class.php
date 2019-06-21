<?php
class MsgAction extends Action
{
    /**
    +--------------------------------
    * 短信发送页
    +--------------------------------
    * @date: 2017年10月10日 上午11:10:19
    * @author: Str
    * @param: variable
    * @return:
    */
    public function sendMsg(){
        $Group=M("group");
        $group=$Group->select();
        $this->assign('group',$group);
        $this->assign('msg',$_GET['msg']);
        $this->display();
    }
    
    /**
    +--------------------------------
    * 发送短信操作
    +--------------------------------
    * @date: 2017年10月11日 下午4:43:40
    * @author: Str
    * @param: variable
    * @return:
    */
    public function sendMsgHandler(){
         $groupId=$_POST["groupid"];
         $UserGroupRel=M("usergroup_rel");
         $sql="SELECT *,(SELECT phone FROM t_user where id=t_usergroup_rel.userid and ifuse=1) as phone FROM t_usergroup_rel WHERE groupid=$groupId";
         $res=$UserGroupRel->query($sql);
         //接收的手机号码
         $mobile=array();
         foreach($res as $v){
            if($v[phone]){
                $mobile[]=$v[phone];
            }
          }
          if(empty($mobile)){
              $this->ajaxReturn('','',0);
          }
          //短信内容
          $words=$_POST["words"];
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
          $data['groupid']=$groupId;
          $res["code"]==200?$data['status']=1:$data['status']=0;
          $data["code"]=$res["code"];
          $Message->add($data);
          if($res["code"]==200){
              $this->ajaxReturn("","",1);
          }else{
              $this->ajaxReturn("","",0);
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
    public function sendMsgUrl(){
        //$json='{"PhoneNumber":"13916783624;13912345434;13845675434;13095867453","Content":"啊啊啊啊，啊啊啊啊啊"}';
        $json=$_POST['msg'];
        $info=json_decode($json);
        $words=$info->Content;         //短信内容
        $PhoneNumber=$info->PhoneNumber;
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
        $data['groupid']="";
        $res["code"]==200?$data['status']=1:$data['status']=0;
        $data["code"]=$res["code"];
        $Message->add($data);
        if($res["code"]==200){
            print 1;
        }else{
            print 0;
        }
    }
    
    /**
    +--------------------------------
    * 添加用户操作
    +--------------------------------
    * @date: 2017年10月11日 上午11:10:52
    * @author: Str
    * @param: variable
    * @return:
    */
    public function addUserHandler(){
        $User=M("user");
        if (!$User->create()){
           $this->ajaxReturn('','',0);
        }else{
            $User->add();
            $this->ajaxReturn('','',1);
        }
    }
    
    /**
    +--------------------------------
    * 用户表
    +--------------------------------
    * @date: 2017年10月11日 下午1:32:58
    * @author: Str
    * @param: variable
    * @return:
    */
    public function ajaxGetUser(){
        import("@.ORG.Page"); // 导入分页类
        $User=M("user");
        
        //判断是否需要重置查询条件
        if($_POST['condition']==1){
            $condition="1=1";
            ($_POST['keyword']!="")?$condition.=" and (name like '%$_POST[keyword]%' or depart like '%$_POST[keyword]%')":'';
            $_SESSION["userCondition"]=$condition;
        }
        
        $count=$User->where($_SESSION["userCondition"])->count(); // 查询满足要求的总记录数
        $Page=new Page($count,10); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->nowPage=$_POST['nowPage'];
        $show=$Page->ajaxShow(); // ajax分页显示输出
        $sql="SELECT * FROM t_user WHERE $_SESSION[userCondition] ORDER BY id DESC LIMIT $Page->firstRow,$Page->listRows";
        $list=$User->query($sql);
        foreach($list as $v){
            if($v[ifuse]==1){
                $status="启用";
            }else{
                $status="禁用";
            }
            $user.="<tr><td>".$v[name]."</td><td>".$v[phone]."</td>
                <td>".$v[duty]."</td><td>".$v[depart]."</td><td>".$v[comment]."</td><td>".$status."</td>
                <td><button type='button' class='btn btn-info' id='$v[id]' data-toggle='modal' data-target='.YTDJ2' >编辑</button></td>
                </tr>";
        }
        $data[0]=$show;$data[1]=$user;
        $this->ajaxReturn($data,'',1);
    }
    
    /**
    +--------------------------------
    * 编辑用户，获取当前用户信息
    +--------------------------------
    * @date: 2017年10月11日 下午2:19:38
    * @author: Str
    * @param: variable
    * @return:
    */
    public function getEditUser(){
        $id=$_POST[id];
        $User=M('user');
        $user=$User->where("id=$id")->find();
        $this->ajaxReturn($user,'',1);
    }
    
    /**
    +--------------------------------
    * 编辑用户操作
    +--------------------------------
    * @date: 2017年10月11日 下午2:43:39
    * @author: Str
    * @param: variable
    * @return:
    */
    public function editUserHandler(){
        $User=M("user");
        if (!$User->create()){
            $this->ajaxReturn('','',0);
        }else{
            $User->save();
            $this->ajaxReturn('','',1);
        }
    }
    
    /**
    +--------------------------------
    * 分组页
    +--------------------------------
    * @date: 2017年10月11日 下午2:59:36
    * @author: Str
    * @param: variable
    * @return:
    */
    public function groupManage(){
        $Group=M('group');
        $group=$Group->order("id desc")->select();
        $this->assign('group',$group);
        $this->display();
    }
    
    /**
    +--------------------------------
    * 添加分组操作
    +--------------------------------
    * @date: 2017年10月11日 下午2:56:35
    * @author: Str
    * @param: variable
    * @return:
    */
    public function addGroupHandler(){
        $Group=M("group");
        if (!$Group->create()){
            $this->ajaxReturn('','',0);
        }else{
            $Group->add();
            $this->ajaxReturn('','',1);
        }
    }
    
    /**
     +--------------------------------
     * 编辑分组，获取当前分组信息
     +--------------------------------
     * @date: 2017年10月11日 下午2:19:38
     * @author: Str
     * @param: variable
     * @return:
     */
    public function getEditGroup(){
        $id=$_POST[id];
        $Group=M('group');
        $group=$Group->where("id=$id")->find();
        $this->ajaxReturn($group,'',1);
    }
    
    /**
     +--------------------------------
     * 编辑分组操作
     +--------------------------------
     * @date: 2017年10月11日 下午2:56:35
     * @author: Str
     * @param: variable
     * @return:
     */
    public function editGroupHandler(){
        $Group=M("group");
        if (!$Group->create()){
            $this->ajaxReturn('','',0);
        }else{
            $Group->save();
            $this->ajaxReturn('','',1);
        }
    }
    
    /**
    +--------------------------------
    * 删除分组操作
    +--------------------------------
    * @date: 2017年10月11日 下午3:15:46
    * @author: Str
    * @param: variable
    * @return:
    */
    public function delGroupHandler(){
        $groupid=$_POST["id"];
        $Group=M("group");
        $UserGroup=M("usergroup_rel");
        $Group->where("id=$groupid")->delete();
        $UserGroup->where("groupid=$groupid")->delete();
        $this->ajaxReturn('','',1);
    }
    
    /**
    +--------------------------------
    * 用户分组关系页
    +--------------------------------
    * @date: 2017年10月11日 下午3:36:58
    * @author: Str
    * @param: variable
    * @return:
    */
    public function userGroupRel(){
        $User=M("user");
        $Group=M("group");
        $user=$User->select();
        $group=$Group->select();
        $this->assign('user',$user);
        $this->assign('group',$group);
        $this->display();
    }
    
    /**
    +--------------------------------
    * 用户加入到分组操作
    +--------------------------------
    * @date: 2017年10月11日 下午3:56:29
    * @author: Str
    * @param: variable
    * @return:
    */
    public function userToGroup(){
        $userId_arr=explode(",",$_POST["userId"]);
        $groupId=$_POST["groupId"];
        $UserGroupRel=M("usergroup_rel");
        foreach($userId_arr as $v){
            if(!$UserGroupRel->where("userid=$v and groupid=$groupId")->find()){
                $data['userid']=$v;
                $data['groupid']=$groupId;
                $UserGroupRel->add($data);
            }
        }
         $sql="SELECT *,(SELECT name FROM t_user where id=t_usergroup_rel.userid) as name,(SELECT depart FROM t_user where id=t_usergroup_rel.userid) as depart, 
		 (SELECT duty FROM t_user where id=t_usergroup_rel.userid) as duty FROM t_usergroup_rel WHERE groupid=$groupId";
         $res=$UserGroupRel->query($sql);
         foreach ($res as $v){
              $user.="<option value='$v[userid]'>".formatString($v[name]).formatString($v[depart]).formatString($v[duty])."</option>";
          }
          $this->ajaxReturn($user,'',1);
    }
    
    /**
    +--------------------------------
    * 获取分组的用户
    +--------------------------------
    * @date: 2017年10月11日 下午4:18:04
    * @author: Str
    * @param: variable
    * @return:
    */
    public function getGroupUser(){
        $groupId=$_POST['groupid'];
        $UserGroupRel=M("usergroup_rel");
        $sql="SELECT *,(SELECT name FROM t_user where id=t_usergroup_rel.userid) as name,(SELECT depart FROM t_user where id=t_usergroup_rel.userid) as depart, 
		 (SELECT duty FROM t_user where id=t_usergroup_rel.userid) as duty FROM t_usergroup_rel WHERE groupid=$groupId";
        $res=$UserGroupRel->query($sql);
        foreach ($res as $v){
           $user.="<option value='$v[userid]'>".formatString($v[name]).formatString($v[depart]).formatString($v[duty])."</option>";
        }
        $this->ajaxReturn($user,'',1);
    }
    
    /**
    +--------------------------------
    * 从组中移除用户
    +--------------------------------
    * @date: 2017年10月11日 下午4:36:47
    * @author: Str
    * @param: variable
    * @return:
    */
    public function removeUser(){
        $userId_arr=explode(",",$_POST["userId"]);
        $groupId=$_POST["groupId"];
        $UserGroupRel=M("usergroup_rel");
        foreach($userId_arr as $v){
            $UserGroupRel->where("userid=$v and groupid=$groupId")->delete();
        }
         $sql="SELECT *,(SELECT name FROM t_user where id=t_usergroup_rel.userid) as name,(SELECT depart FROM t_user where id=t_usergroup_rel.userid) as depart, 
		 (SELECT duty FROM t_user where id=t_usergroup_rel.userid) as duty FROM t_usergroup_rel WHERE groupid=$groupId";
        $res=$UserGroupRel->query($sql);
        foreach ($res as $v){
            $user.="<option value='$v[userid]'>".formatString($v[name]).formatString($v[depart]).formatString($v[duty])."</option>";
        }
        $this->ajaxReturn($user,'',1);
        
    }
    
    /**
     +--------------------------------
     * 获取短信记录
     +--------------------------------
     * @date: 2017年10月11日 下午1:32:58
     * @author: Str
     * @param: variable
     * @return:
     */
    public function ajaxGetMessage(){
        import("@.ORG.Page"); // 导入分页类
        $Message=M("message");
    
        //判断是否需要重置查询条件
        if($_POST['condition']==1){
            $condition="1=1";
            ($_POST['keyword']!="")?$condition.=" and (content like '%$_POST[keyword]%')":'';
            ($_POST['date']!="")?$condition.=" and date='$_POST[date]'":'';
            $_SESSION["messageCondition"]=$condition;
        }
    
        $count=$Message->where($_SESSION["messageCondition"])->count(); // 查询满足要求的总记录数
        $Page=new Page($count,10); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->nowPage=$_POST['nowPage'];
        $show=$Page->ajaxShow(); // ajax分页显示输出
        $sql="SELECT *,(SELECT name FROM t_group WHERE id=t_message.groupid) as groupName FROM t_message WHERE $_SESSION[messageCondition] ORDER BY id DESC LIMIT $Page->firstRow,$Page->listRows";
        $list=$Message->query($sql);
        foreach($list as $v){
            if($v[status]==1){
                $status="成功";
            }else{
                $status="失败";
            }
            $user.="<tr><td>".$v[date]."</td><td>".$v[time]."</td>
                <td style='width:800px;'>".$v[content]."</td><td>".$v[groupName]."</td><td>".$status."</td><td>".$v[code]."</td>
                    </tr>";
        }
        $data[0]=$show;$data[1]=$user;
        $this->ajaxReturn($data,'',1);
    }
}
?>