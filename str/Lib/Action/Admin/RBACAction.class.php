<?php
class RBACAction extends CommonAction
{
	//********************************************用户管理***************************************************
	//用户管理页
    public function user()
    {
    	$User=M('User');
    	$res=$User->select();
    	//角色查询
    	foreach($res as $k=>$val){
    		$sql="select (select name from CJ_Role where id=R.role_id) as BelongRole from CJ_Role_user as R where user_id=$val[Id]";
    		$result=$User->query($sql);
    		$role="";
    		foreach($result as $val){
    			$role.='['.$val['BelongRole'].']';
    		}
    		$res[$k]['BelongRole']=$role;
    	}
    	$this->assign("level9","active open");
    	$this->assign("level9_2","active open");
    	$this->assign("level9_2_2","active");
    	$this->assign('users',$res);
		$this->display('user');
    }
    
   //添加用户页
   public function addUser(){
   		$this->display();
   }
   
   //添加用户操作
   public function addUserHandler(){
	   	$User=D('User');
	   	$_POST['Password']=md5($_POST['Password']);
	   	//dump($_POST);exit();
	   	if (!$User->create()){
	   		// 如果创建失败 表示验证没有通过 输出错误提示信息
	   		$this->assign("flag",0);
	   		exit($this->error($User->getError()));
	   	}else{
	   		// 验证通过 可以进行其他数据操作
	   		$res=$User->add();
	   	}
	    if($res){
	    	$this->assign("flag",0);
	   		$this->success("操作成功");
	   	}else{
	   		$this->assign("flag",0);
	   		$this->error("操作失败");
	   	} 
   }
   
   /**
   +--------------------------------
   * 编辑用户页
   +--------------------------------
   * @date: 2016-3-10 下午5:19:49
   * @author: Str
   * @param: variable
   * @return:
   */
   public function editUser(){
   	$User=M("User");
   	$user=$User->where("Id=$_GET[userId]")->find();
   	$this->assign("user",$user);
   	$this->display();
   }
   
   /**
   +--------------------------------
   * 编辑用户操作
   +--------------------------------
   * @date: 2016-3-10 下午5:27:22
   * @author: Str
   * @param: variable
   * @return:
   */
   public function editUserHandler(){
	   	$User=D('User');
	   	if (!$User->create()){
	   		// 如果创建失败 表示验证没有通过 输出错误提示信息
	   		$this->assign("flag",0);
	   		exit($this->error($User->getError()));
	   	}else{
	   		// 验证通过 可以进行其他数据操作
	   		$res=$User->save();
	   	}
	   	if($res){
	   		$this->assign('flag',0);
	   		$this->success("操作成功");
	   	}else{
	   		$this->assign('flag',0);
	   		$this->error("操作失败");
	   	}
   }
   
    //用户角色分配
    public function roleAccess(){
    	$userId=$_GET[userId];
    	$Role=M('Role');
    	//取出所有角色
    	$roles=$Role->select();
    	//取出该用户的角色
    	$role_user=M('Role_user');
    	$userRoles=$role_user->where("user_id=$userId")->select();
    	$this->assign('roles',$roles);
    	$this->assign('userRoles',$userRoles);
    	$this->assign('userId',$userId);
    	$this->display();
    }
    
    //角色分配操作
    public function roleAccessHandler(){
    	$userId=$_POST['userId'];
    	//删除该用户已分配的角色
    	$role_user=M('Role_user');
    	$role_user->where("user_id=$userId")->delete();

    	//添加选中的角色
    	foreach($_POST[role_id] as $role_id){
    		$role_user->add(array('role_id'=>$role_id,'user_id'=>$userId));
    	}
    	$this->assign("flag",0);
    	$this->success("操作成功");
    }
    
    /**
    +--------------------------------
    * 重置用户密码
    +--------------------------------
    * @date: 2016-3-24 下午3:57:05
    * @author: Str
    * @param: variable
    * @return:
    */
    public function resetPassword(){
    	$userId=$_GET['userId'];
    	$User=M("User");
    	$res=$User->where("Id=$userId")->setField("Password",md5(111111));
  		if($res){
			$this->ajaxReturn('','操作成功！',1);
		}else{
			$this->ajaxReturn('','操作失败！',0);
		}
    }
    //************************************************************角色管理*******************************
    //角色管理
    public function role()
    {
    	$this->assign("level9","active open");
    	$this->assign("level9_2","active open");
    	$this->assign("level9_2_3","active");
    	//查询角色表
    	$Role=M("Role");
    	$res=$Role->select();
    	$this->assign('roles',$res);
    	$this->display('role');
    }
    
    //添加角色
    public function addRole(){
    	$this->display();
    }
    

    //添加角色操作
    public function addRoleHandler(){
    	$Role=D('Role');
    	if (!$Role->create()){
    		// 如果创建失败 表示验证没有通过 输出错误提示信息
    		$this->assign("flag",0);
    		exit($this->error($Role->getError()));
    	}else{
    		// 验证通过 可以进行其他数据操作
    		$res=$Role->add();
    	}
    	if($res){
    		$this->assign('flag',0);
    		$this->success("操作成功");
    	}else{
    		$this->assign('flag',0);
    		$this->error("操作失败");
    	}
    }
    
    //编辑角色
    public function editRole(){
    	$roleId=$_GET[roleId];//角色id
    	$Role=M('Role');
    	$res=$Role->where("id=$roleId")->find();
    	$this->assign('role',$res);
    	$this->display();
    }
    
    //编辑角色操作
    public function editRoleHandler(){
    	$Role=D('Role');
    	if (!$Role->create()){
    		// 如果创建失败 表示验证没有通过 输出错误提示信息
    		$this->assign("flag",0);
    		exit($this->error($Role->getError()));
    	}else{
    		// 验证通过 可以进行其他数据操作
    		$res=$Role->save();
    	}
    	if($res){
    		$this->assign('flag',0);
    		$this->success("操作成功");
    	}else{
    		$this->assign('flag',0);
    		$this->error("操作失败");
    	}
    }
   
    //权限分配页
    public function rightAccess(){
    	$roleId=$_GET[roleId];
    	$Node=M('Node');
    	//取出全部控制器节点
    	$res=$Node->where('pid=1')->select();
    	//取出控制器所有对应的操作节点
    	foreach($res as $k=>$v){
    		$child_nodes=$Node->where("pid=$v[id]")->select();
    		$res[$k][]=$child_nodes;
    	}
    	//取出该角色对应的所有权限
    	$Access=M('Access');
    	$rightNodes=$Access->where("role_id=$roleId")->field('node_id')->select();
    	$this->assign('rightNodes',$rightNodes);
    	$this->assign('roleId',$roleId);
    	$this->assign('nodes',$res);
    	$this->display();
    }
    
    //权限分配处理
    public function rightAccessHandler(){
    	//删除所有权限
    	$Access=M('Access');
    	$Access->where("role_id=$_POST[roleId]")->delete();
    	 
    	//再分配权限，取出控制器节点ID
    	//加入Admin模块根节点
    	$Access->add(array('role_id'=>$_POST[roleId],'node_id'=>1,'level'=>1,'pid'=>0));
    	foreach($_POST as $k=>$node){
    		//如果为数字,为控制器id
    		if(is_numeric($k)){
    			//添加控制器权限到access表
    			$Access->add(array('role_id'=>$_POST[roleId],'node_id'=>$k,'level'=>2,'pid'=>1));
    			//取出操作的节点ID
    			foreach($node as $nodeId){
    				//添加操作权限
    				$Access->add(array('role_id'=>$_POST[roleId],'node_id'=>$nodeId,'level'=>3,'pid'=>$k));
    			}
    		}
    	}
    	$this->assign("flag",0);
    	$this->success("操作成功");
    }
    
    //角色人员列表
    public function userList(){
    	$role_user=M('Role_user');
    	$res=$role_user->table("CJ_Role_user role_user,CJ_User user")
    	->where("role_user.role_id=$_GET[roleId] and role_user.user_id=user.Id")
    	->select();
    	$this->assign("user",$res);
    	$this->display();
    }


    
    //************************************************************节点管理***************************
    //节点管理
    public function node()
    {
    	//取出所有节点
    	$nodes=M('Node');
    	$res=$nodes->select();
    	$this->assign("nodes",$res);
    	$this->assign("level9","active open");
    	$this->assign("level9_2","active open");
    	$this->assign("level9_2_4","active");
    	$this->display('node');
    }
    
    //读取节点
    public function getNodes(){
    	//取出所有节点
    	$Nodes=M('Node');
    	$res=$Nodes->select();
    	$nodes='';
    	//转换成字符串
    	foreach($res as $v){
    		if($v[level]>1){
    			$tmp=",open:true";
    		}
    		$nodes.='{id:'.$v[id].',pId:'.$v[pid].',name:"'.$v[title].'('.$v[name].')",nLevel:'.$v[level].$tmp.'},';
    	}
    	echo '['.$nodes.']';
    }
    
    //添加节点层
    public function addNode(){
    	//查询父节点名称
    	$Node=M('Node');
    	$res=$Node->where("id=$_GET[id]")->find();
    	$this->assign('pNode',$res['title']);
    	$this->assign('id',$_GET['id']);
    	$this->assign('level',$_GET['level']+1);
    	$this->display('addNode');
    }
    
    //添加节点操作
    public function addNodeHandler(){
    	$Node=D('Node');
    	if (!$Node->create()){
    		$this->assign("flag",0);
    		exit($this->error($Node->getError()));
    	}else{
    		// 验证通过 可以进行其他数据操作
    		$res=$Node->add();
    		if($res){
    			$this->assign("flag",0);
    			$this->success();
    		}else{
    			$this->assign("flag",0);
    			$this->error();
    		}
    	}
    }
    
    //删除节点操作
    public function delNodeHandler(){
    	$Node=M('Node');
    	//查询是否存在子节点，不允许删除有子节点的父节点
    	$res=$Node->where("pid=$_POST[id]")->find();
    	if($res){
    		$this->ajaxReturn('该节点存在子节点','',0);
    	}else{
    		//删除node节点
    		$res=$Node->where("id=$_POST[id]")->delete();
    		if($res){
    			$this->ajaxReturn('删除成功','',1);
    		}else{
    			$this->ajaxReturn('删除失败','',0);
    		}
    		
    	}
    }
   
}
?>