<?php
class RoleModel extends Model {
	protected $_validate         =         array(
			array('name','require','角色名必须填写！'), //默认情况下用正则进行验证
			array('name','','角色名已经存在！',0,'unique',1), //默认情况下用正则进行验证
	);
	
	/**
	+--------------------------------
	* 获取用户所有的组长排班角色
	+--------------------------------
	* @date: 2016-3-3 上午11:22:03
	* @author: Str
	* @param: userID
	* @return: array
	*/
	public function getUserRoles($userID){
		$RoleUser=M("Role_user");
		$sql="SELECT (SELECT name FROM CJ_Role WHERE Id=R.role_id) as roleName FROM CJ_Role_user as R WHERE R.user_id=$userID";
		$res=$RoleUser->query($sql);
		foreach($res as $v){
			$userRoles[]=$v[roleName];
		}
		//去除不是组长排班的角色
		$headman=array("舞美组长","现场统筹组长","灯光组长","摄像组长","TD组长","制作修改组长","播出组长","VIDEO组长","AUDIO组长","AUDIOTS组长","上传组长","后期制作组长","字幕组长");
		//所有排班组长角色与用户的角色取交集
		$roles=array_intersect($headman,$userRoles);
		return $roles;
	}
	
	/**
	+--------------------------------
	* 取出弹出层工位的所有人员
	+--------------------------------
	* @date: 2016-3-4 下午3:32:54
	* @author: Str
	* @param: $layerName 工位角色名称
	* @return: array
	*/
	public function getAllArrangeUser($layerName){
		$Role=M("Role");
		//rolo表中角色对应的id号
 		switch($layerName){
			case "wm":
				$res=$Role->where("name='舞美'")->field("Id")->find();
				break;
			case "live":
				$res=$Role->where("name='现场统筹'")->field("Id")->find();
				break;
			case "light":
				$res=$Role->where("name='灯光'")->field("Id")->find();
				break;
			case "camera":
				$res=$Role->where("name='摄像'")->field("Id")->find();
				break;
			case "td":
				$res=$Role->where("name='TD'")->field("Id")->find();
				break;
			case "produceModify":
				$res=$Role->where("name='制作修改'")->field("Id")->find();
				break;
			case "broadCast":
				$res=$Role->where("name='播出'")->field("Id")->find();
				break;
			case "video":
				$res=$Role->where("name='VIDEO'")->field("Id")->find();
				break;
			case "audio":
				$res=$Role->where("name='AUDIO'")->field("Id")->find();
				break;
			case "audioTS":
				$res=$Role->where("name='AUDIO.TS'")->field("Id")->find();
				break;
			case "upload":
				$res=$Role->where("name='上传'")->field("Id")->find();
				break;
			case "produce":
				$res=$Role->where("name='后期制作'")->field("Id")->find();
				break;
			case "caption":
				$res=$Role->where("name='字幕'")->field("Id")->find();
				break;
		}
		
		$sql="SELECT user_id,(SELECT name FROM CJ_User WHERE Id=CJ_Role_user.user_id) as name 
		FROM CJ_Role_user LEFT JOIN CJ_User ON CJ_Role_user.user_id=CJ_User.id  
		WHERE role_id=$res[Id] and CJ_User.Status=1";
		$user_array=$Role->query($sql);
		return $user_array;
	}
}