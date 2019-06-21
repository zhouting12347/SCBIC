<?php
class ManageAction extends CommonAction{
	
	/**
	 +------------------------------------------
	 * 模特库
	 +------------------------------------------
	 */
	public function model_his(){
		$Model_His=M("Model_His");
		$model=$Model_His->where("IsDel=0")->select();
		$this->assign('model',$model);
		$this->assign("level9","active open");
		$this->assign("level9_1","active open");
		$this->assign("level9_1_2","active");
		$this->display("model_his");
	}
	
	/**
	 +------------------------------------------
	 * 模特添加弹出
	 +------------------------------------------
	 */
	public function add_model(){
		$this->display("add_model");
	}
	
	/**
	 +------------------------------------------
	 * 模特库添加操作 
	 +------------------------------------------
	 */
	public function addModelHandler(){
		$Model_His=D("Model_His");
		if (!$Model_His->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Model_His->getError());
		}else{
			// 验证通过 可以进行其他数据操作

			import('ORG.Net.UploadFile');
			//模特照片上传
			if($_FILES[photo][name]){
				$upload = new UploadFile();// 实例化上传类
				$upload->maxSize  =2097152; // 设置附件上传大小
				$upload->allowExts  = array('jpg','png','jpge','gif','bmp'); // 设置附件上传类型
				$upload->savePath =  './Uploads/modelPhoto/'; // 设置附件上传目录
				$upload->saveRule=time;
				$res=$upload->uploadOne($_FILES['photo']);
				if(!$res){
					// 上传错误提示错误信息
					$this->assign('flag',0);
					$this->error($upload->getErrorMsg());
				}else{
					$_POST['ModelPhotoDir']='/Uploads/modelPhoto/'.$res[0]['savename'];
				}
				unset($upload);
				unset($res);
			}
			
			//模特视频上传
/* 			if($_FILES[video][name]){
				
			} */
			$Model_His->create();
			$res=$Model_His->add();
			if($res){
				$this->assign("flag",0);
				$this->success();
			}else{
				$this->assign("flag",0);
				$this->error();
			}
		}
	}
	
	/**
	 +------------------------------------------
	 * 模特编辑页
	 +------------------------------------------
	 */
	public function edit_model(){
		$Model_His=M("Model_His");
		$id=$_GET['id'];
		$model=$Model_His->where("Id=$id")->find();
		$this->assign('model',$model);
		$this->display("edit_model");
	}
	
	/**
	 +------------------------------------------
	 * 模特编辑操作
	 +------------------------------------------
	 */
	public function editModelHandler(){
		$Model_His=D("Model_His");
		if (!$Model_His->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Model_His->getError());
		}else{
			// 验证通过 可以进行其他数据操作
		
			import('ORG.Net.UploadFile');
			//模特照片上传
			if($_FILES[photo][name]){
				$upload = new UploadFile();// 实例化上传类
				$upload->maxSize  =2097152; // 设置附件上传大小
				$upload->allowExts  = array('jpg','png','jpge','gif','bmp'); // 设置附件上传类型
				$upload->savePath =  './Uploads/modelPhoto/'; // 设置附件上传目录
				$upload->saveRule=time;
				$res=$upload->uploadOne($_FILES['photo']);
				if(!$res){
					// 上传错误提示错误信息
					$this->assign('flag',0);
					$this->error($upload->getErrorMsg());
				}else{
					$_POST['ModelPhotoDir']='/Uploads/modelPhoto/'.$res[0]['savename'];
				}
				unset($upload);
				unset($res);
			}
			
				$Model_His->create();
				$res=$Model_His->save();
				if($res){
					$this->assign("flag",0);
					$this->success();
				}else{
					$this->assign("flag",0);
					$this->error();
				}
			}
	}
	
	/**
	 +--------------------------------
	 * 模特添加到档期页面
	 +--------------------------------
	 * @date: 2015-12-25 下午4:21:04
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	public function addModelSchedule(){
		$this->assign('id',$_GET[id]);
		$this->display('addModelSchedule');
	}
	
	/**
	 +--------------------------------
	 * 添加档期操作
	 +--------------------------------
	 * @date: 2015-12-25 下午4:25:50
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	public function addModelScheduleHandler(){
		$Model_Schedule=D("Model_Schedule");
		if (!$Model_Schedule->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Model_Schedule->getError());
		}else{
			$res=$Model_Schedule->add();
			if($res){
				$this->assign("flag",0);
				$this->success();
			}else{
				$this->assign("flag",0);
				$this->error();
			}
		}
	}

	/**
	+--------------------------------
	* 模特删除
	+--------------------------------
	* @date: 2015-12-25 上午11:27:31
	* @author: Str
	* @param: variable
	* @return: json
	*/
	public function delModelHandler(){
		$Model_His=M('Model_His');
		$id=$_GET['id'];
		$res=$Model_His->where("Id=$id")->setField('IsDel',1);
		if($res){
			$this->ajaxReturn('','删除成功！',1);
		}else{
			$this->ajaxReturn('','删除失败！',0);
		}
	}
	
	/**********************************************档期模特****************************************************************************/
	/**
	 +--------------------------------
	 * 档期模特首页
	 +--------------------------------
	 * @date: 2015-12-25 下午5:15:39
	 * @author: Str
	 * @param: variable
	 * @return:
	 */
	public  function model_schedule(){
		$Model=new Model();
		$sql="select * from CJ_Model_His as His,CJ_Model_Schedule as Schedule where His.Id=Schedule.ModelID";
		$model=$Model->query($sql);
		$this->assign("level9","active open");
		$this->assign("level9_1","active open");
		$this->assign("level9_1_1","active");
		$this->assign('model',$model);
		$this->display("model_schedule");
	}
	
	
	
	/**
	+--------------------------------
	* 取消档期
	+--------------------------------
	* @date: 2015-12-25 下午5:04:29
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function delModelScheduleHandler(){
		$Model_Schedule=M("Model_Schedule");
		$ModelID=$_GET['id'];
		$res=$Model_Schedule->where("ModelID=$ModelID")->delete();
		if($res){
			$this->ajaxReturn('','取消档期成功！',1);
		}else{
			$this->ajaxReturn('','取消档期失败！',0);
		}
	}
	
	/**
	+--------------------------------
	* 模特请假页
	+--------------------------------
	* @date: 2015-12-29 上午10:16:40
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function model_leave(){
		$this->assign('id',$_GET[id]);
		$this->display('model_leave');
	}
	
	/**
	+--------------------------------
	* 添加模特请假操作
	+--------------------------------
	* @date: 2015-12-29 上午10:30:05
	* @author: Str
	* @param: variable
	* @return: 
	*/
	public function modelLeaveHandler(){
		$Model_Schedule=M("Model_Schedule");
		if (!$Model_Schedule->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Model_Schedule->getError());
		}else{
			$res=$Model_Schedule->save();
			if($res){
				$this->assign("flag",0);
				$this->success();
			}else{
				$this->assign("flag",0);
				$this->error();
			}
		}
	} 
	
	//模特视频弹出层
	public function model_video(){
		$this->display("model_video");
	}
	
	
	
	
	/**********************************************会议室****************************************************************************/
	/**
	+--------------------------------
	* 会议室首页
	+--------------------------------
	* @date: 2015-12-29 上午10:59:06
	* @author: Str
	* @param: variable
	* @return:
	*/	
	public  function room(){
		$Room=M("MeetingRoom");
		$room=$Room->where("IsDel=0")->select();
		$this->assign("room",$room);
		$this->assign("level9","active open");
		$this->assign("level9_1","active open");
		$this->assign("level9_1_3","active");
		$this->display("room");
	}

	/**
	+--------------------------------
	* 添加会议室弹出层
	+--------------------------------
	* @date: 2015-12-29 上午11:40:31
	* @author: Str
	* @param: variable
	* @return:
	*/
	public  function add_room(){
		$this->display("add_room");
	}
	
	/**
	+--------------------------------
	* 添加会议室操作
	+--------------------------------
	* @date: 2015-12-29 上午11:40:47
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function addRoomHandler(){
		$Room=D("MeetingRoom");
		if (!$Room->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Room->getError());
		}else{
			// 验证通过 可以进行其他数据操作
		
			import('ORG.Net.UploadFile');
			//模特照片上传
			if($_FILES[photo][name]){
				$upload = new UploadFile();// 实例化上传类
				$upload->maxSize  =2097152; // 设置附件上传大小
				$upload->allowExts  = array('jpg','png','jpge','gif','bmp'); // 设置附件上传类型
				$upload->savePath =  './Uploads/roomPhoto/'; // 设置附件上传目录
				$upload->saveRule=time;
				$res=$upload->uploadOne($_FILES['photo']);
				if(!$res){
					// 上传错误提示错误信息
					$this->assign('flag',0);
					$this->error($upload->getErrorMsg());
				}else{
					$_POST['RoomPicDir']='/Uploads/roomPhoto/'.$res[0]['savename'];
				}
				unset($upload);
				unset($res);
			}
			
				$Room->create();
				$res=$Room->add();
				if($res){
					$this->assign("flag",0);
					$this->success();
				}else{
					$this->assign("flag",0);
					$this->error();
				}
			}
	}
	
	/**
	+--------------------------------
	* 会议室编辑页
	+--------------------------------
	* @date: 2015-12-29 下午3:47:07
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function edit_room(){
		$id=$_GET['id'];
		$Room=M("MeetingRoom");
		$room=$Room->where("Id=$id")->find();
		$this->assign('room',$room);
		$this->display("edit_room");
	}
	
	/**
	+--------------------------------
	* 会议室编辑操作
	+--------------------------------
	* @date: 2015-12-29 下午4:00:29
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function editRoomHandler(){
		$Room=D("MeetingRoom");
		if (!$Room->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Room->getError());
		}else{
			// 验证通过 可以进行其他数据操作
		
			import('ORG.Net.UploadFile');
			//模特照片上传
			if($_FILES[photo][name]){
				$upload = new UploadFile();// 实例化上传类
				$upload->maxSize  =2097152; // 设置附件上传大小
				$upload->allowExts  = array('jpg','png','jpge','gif','bmp'); // 设置附件上传类型
				$upload->savePath =  './Uploads/roomPhoto/'; // 设置附件上传目录
				$upload->saveRule=time;
				$res=$upload->uploadOne($_FILES['photo']);
				if(!$res){
					// 上传错误提示错误信息
					$this->assign('flag',0);
					$this->error($upload->getErrorMsg());
				}else{
					$_POST['RoomPicDir']='/Uploads/roomPhoto/'.$res[0]['savename'];
				}
				unset($upload);
				unset($res);
			}
				
			$Room->create();
			$res=$Room->save();
			if($res){
				$this->assign("flag",0);
				$this->success();
			}else{
				$this->assign("flag",0);
				$this->error();
			}
		}
	}
	
	/**
	+--------------------------------
	* 会议室删除操作
	+--------------------------------
	* @date: 2015-12-29 下午4:02:11
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function delRoomHandler(){
		$Room=M('MeetingRoom');
		$id=$_GET['id'];
		$res=$Room->where("Id=$id")->setField('IsDel',1);
		if($res){
			$this->ajaxReturn('','删除成功！',1);
		}else{
			$this->ajaxReturn('','删除失败！',0);
		}
	}
	
	
	/***********************************************设备管理*****************************************************/
	
	/**
	+--------------------------------
	* 设备管理主页
	+--------------------------------
	* @date: 2015-12-29 下午4:15:21
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function equipment(){
		$Equipment=M('Equipment');
		$equipment=$Equipment->where("IsDel=0")->select();
		$this->assign('equipment',$equipment);
		$this->assign("level9","active open");
		$this->assign("level9_1","active open");
		$this->assign("level9_1_4","active");
		$this->display("equipment");
	}
	
	/**
	+--------------------------------
	* 添加设备页
	+--------------------------------
	* @date: 2015-12-30 上午10:56:33
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function add_equipment(){
		$this->display("add_equipment");
	}
	
	/**
	+--------------------------------
	* 添加设备操作
	+--------------------------------
	* @date: 2015-12-30 上午10:58:15
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function addEquipmentHandler(){
		$Equipment=D("Equipment");
		if (!$Equipment->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Equipment->getError());
		}else{
			// 验证通过 可以进行其他数据操作
		
			import('ORG.Net.UploadFile');
			//设备图片上传
			if($_FILES[photo][name]){
				$upload = new UploadFile();// 实例化上传类
				$upload->maxSize  =2097152; // 设置附件上传大小
				$upload->allowExts  = array('jpg','png','jpge','gif','bmp'); // 设置附件上传类型
				$upload->savePath =  './Uploads/equipmentPhoto/'; // 设置附件上传目录
				$upload->saveRule=time;
				$res=$upload->uploadOne($_FILES['photo']);
				if(!$res){
					// 上传错误提示错误信息
					$this->assign('flag',0);
					$this->error($upload->getErrorMsg());
				}else{
					$_POST['EquipmentPicDir']='/Uploads/equipmentPhoto/'.$res[0]['savename'];
				}
				unset($upload);
				unset($res);
			}
				
			$Equipment->create();
			$res=$Equipment->add();
			if($res){
				$this->assign("flag",0);
				$this->success();
			}else{
				$this->assign("flag",0);
				$this->error();
			}
		}
	}
	
	/**
	+--------------------------------
	* 设备编辑页
	+--------------------------------
	* @date: 2015-12-30 上午11:46:16
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function edit_equipment(){
		$id=$_GET['id'];
		$Equipment=M("Equipment");
		$equipment=$Equipment->where("Id=$id")->find();
		$this->assign('equipment',$equipment);
		$this->display('edit_equipment');
	}
	
	/**
	+--------------------------------
	* 设备编辑操作
	+--------------------------------
	* @date: 2015-12-30 上午11:54:39
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function editEquipmentHandler(){
		$Equipment=D("Equipment");
		if (!$Equipment->create()){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->assign('flag',0);
			$this->error($Equipment->getError());
		}else{
			// 验证通过 可以进行其他数据操作
		
			import('ORG.Net.UploadFile');
			//模特照片上传
			if($_FILES[photo][name]){
				$upload = new UploadFile();// 实例化上传类
				$upload->maxSize  =2097152; // 设置附件上传大小
				$upload->allowExts  = array('jpg','png','jpge','gif','bmp'); // 设置附件上传类型
				$upload->savePath =  './Uploads/equipmentPhoto/'; // 设置附件上传目录
				$upload->saveRule=time;
				$res=$upload->uploadOne($_FILES['photo']);
				if(!$res){
					// 上传错误提示错误信息
					$this->assign('flag',0);
					$this->error($upload->getErrorMsg());
				}else{
					$_POST['EquipmentPicDir']='/Uploads/equipmentPhoto/'.$res[0]['savename'];
				}
				unset($upload);
				unset($res);
			}
		
			$Equipment->create();
			$res=$Equipment->save();
			if($res){
				$this->assign("flag",0);
				$this->success();
			}else{
				$this->assign("flag",0);
				$this->error();
			}
		}
	}
	
	/**
	+--------------------------------
	* 删除设备操作
	+--------------------------------
	* @date: 2015-12-30 下午12:02:03
	* @author: Str
	* @param: variable
	* @return:
	*/
	public function delEquipmentHandler(){
		$Equipment=M("Equipment");
		$id=$_GET['id'];
		$res=$Equipment->where("Id=$id")->setField('IsDel',1);
		if($res){
			$this->ajaxReturn('','删除成功！',1);
		}else{
			$this->ajaxReturn('','删除失败！',0);
		}
	}
	
}