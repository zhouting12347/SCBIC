<?php
class PublicAction extends CommonAction
{
	//登录页面
    public function login()
    {
    	$this->assign("Username",$_COOKIE['Username']);
    	$this->assign("Password",$_COOKIE['Password']);
		$this->display('login');
    }
    
    //注销
    public function logout()
    {
    	session_unset();
		session_destroy();
    	$this->redirect('Public/login');
    }
    
    //检测用户登录
    public function checkLogin(){
   		//表单数据不能为空
		if($_POST['Username']&&$_POST['Password']){
			$pwd= $_POST['Password'];
			$username= $_POST['Username'];
				//创建数据库对象
				$user=M('User');
				//根据用户名查询
				$map['Username']=$username;
				//$map['status']=array('gt',0);
				//加载RBAC类
				import('ORG.Util.RBAC');
				//通过authenticate读取用户信息
				$result=RBAC::authenticate($map);
				//dump($result);
				
				//用户是否禁用
				if($result['Status']==0){
					$this->assign("flag",0);
					$this->error("你已经被禁用");
				}
				//设置cookie
				if($_POST['Remember']==1){
					$time=time()+86400*30; // 设置24小时的有效期
					setcookie("Username",$_POST[Username],$time); // 设置一个名字为var_name的cookie，并制定了有效期
					setcookie("Password",$_POST[Password],$time); // 再将过期时间设置进cookie以便你能够知道var_name的过期时间
				}
				//是否为管理员账户
				if($result['Username']==C('RBAC_ADMIN')){
					$_SESSION[C('ADMIN_AUTH_KEY')]=true;
				}
				if($result){
					if($result['Password']==md5($pwd)){
						$_SESSION[C('USER_AUTH_KEY')]=$result['Id'];
						$_SESSION["WorkID"]=$result['WorkID'];
						$_SESSION["Username"]=$result['Username'];
						//使用saveAccessList缓存访问权限
						RBAC::saveAccessList();
						
						//查询我的消息条数
						$Message=new MessageModel();
						$_SESSION["messageCount"]=$Message->getMsgCount();
						
						$this->redirect('Index/index');
					}else{
						$this->assign("flag",0);
						$this->error("用户密码错误");
					}
				}else{
					$this->assign("flag",0);
					$this->error("用户名不存在或已经被禁用");
				}
			}else{
					$this->assign("flag",0);
					$this->error("用户名不存在或已经被禁用");
			}
    }
    //发送私信
    public function send_msg()
    {
		$this->display('send_msg');
    }
    
    //fullcalendar
    public function getEvents(){
    	$data=array(
			0 => array(
			'id'=>'001',
			'title' => '任务一',
			'start' => '2015-10-09',
			'color'=>'gray'
			),
			1 => array(
			'id'=>'002',
			'title' => '任务二',
			'start' => '2015-10-10',
			),
			2 => array(
			'id'=>'003',
			'title' => '任务三',
			'start' => '2015-10-10',
			'color'=>'gray'
			 )
		);
    	echo json_encode($data);
    	//$this->ajaxReturn($data);
    }
    
    /**
     +------------------------------------------
     * 根据访问模块，显示模块对应的任务详情页
     * 单条任务中的节目信息表，显示任务中的所有信息
     +------------------------------------------
     */
    public function check_program(){
    	$VideoProduce=D('VideoProduce');
    	$taskID=$_GET['taskID'];
    	switch($_GET['controller']){
    		//表单首页
    		case 'Luzhi':
    			$program=$VideoProduce->where("TaskID='$taskID' and RequestID!='task' ")->select();
    			//如果没查询出来，说明是单条任务，继续查询
    			if(!$program){
    				$program=$VideoProduce->where("TaskID='$taskID' and RequestID='task' ")->select();
    			}
    			break;
    		//排班首页
    		case 'Arrange':
    			$sql="SELECT * ,(select Name from CJ_User where Id=V.PDID) as PD,
		    	(select Name from CJ_User where Id=V.WMID) as WM,
		    	(select Name from CJ_User where Id=V.Live1ID) as Live1, 
		    	(select Name from CJ_User where Id=V.Live2ID) as Live2,
		    	(select Name from CJ_User where Id=V.Light1ID) as Light1,
		    	(select Name from CJ_User where Id=V.Light2ID) as Light2,
		    	(select Name from CJ_User where Id=V.Light3ID) as Light3,
		    	(select Name from CJ_User where Id=V.Camera1ID) as Camera1,
		    	(select Name from CJ_User where Id=V.Camera2ID) as Camera2,
		    	(select Name from CJ_User where Id=V.Camera3ID) as Camera3,
		    	(select Name from CJ_User where Id=V.Camera4ID) as Camera4,
		    	(select Name from CJ_User where Id=V.TDID) as TD,
		    	(select Name from CJ_User where Id=V.ProduceModifyID) as ProduceModify,
		    	(select Name from CJ_User where Id=V.BroadCastID) as BroadCast,
		    	(select Name from CJ_User where Id=V.Video1ID) as Video1,
		    	(select Name from CJ_User where Id=V.Video2ID) as Video2,
		    	(select Name from CJ_User where Id=V.AudioID) as Audio,
		    	(select Name from CJ_User where Id=V.AudioTSID) as AudioTS,
		    	(select Name from CJ_User where Id=V.UploadID) as Upload,
		    	(select Name from CJ_User where Id=V.ProducerID) as Producer,
		    	(select Name from CJ_User where Id=V.CaptionID) as Caption 
		    	FROM CJ_VideoProduce as V WHERE ProgramDate is not null and TaskID='$taskID'";
    			$program=$VideoProduce->query($sql);
    			break;
    		//导演完成任务
    		case 'Director':
    			$program=$VideoProduce->where("TaskID='$taskID' and ProgramDate is not null and PDID=$_SESSION[uid]")->select();
    			break;
    	}
    	
    	$this->assign('program',$program);
    	$this->display($_GET['controller'].':check_program');
    }
    
    
    public function weChatTest(){
    	define("TOKEN", "zt47");
		$wechatObj = new wechatCallbackapiTest();
		//$wechatObj->valid();
		$wechatObj->responseMsg();
    }
    
}

class wechatCallbackapiTest
{
	public function valid()
	{
		$echoStr = $_GET["echostr"];
		//valid signature , option
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}

	public function responseMsg()
	{
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr)){
			/* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
			 the best way is to check the validity of xml by yourself */
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content);
			$time = time();
			$textTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";
			if(!empty( $keyword ))
			{
				$msgType = "text";
				$contentStr = "Welcome to wechat world! 微信";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				echo $resultStr;
			}else{
				echo "Input something...";
			}

		}else {
			echo "xxxx";
			exit;
		}
	}
	
	public  function saveMsg(){
			
	}
	
	private function checkSignature()
	{
		// you must define TOKEN by yourself
		if (!defined("TOKEN")) {
			throw new Exception('TOKEN is not defined!');
		}
		 
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		 
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		// use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		 
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	+--------------------------------
	* 获取访问令牌
	* 2小时重新获取一次
	+--------------------------------
	* @date: 2016-4-27 上午11:02:00
	* @author: Str
	* @param: variable
	* @return:
	*/
	private function getAccessToken(){
		
	}
}

?>