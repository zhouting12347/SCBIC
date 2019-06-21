<?php
return array(
	//'配置项'=>'配置值'
	//'APP_DEBUG' => true,
	//'LOG_RECORD'=>true, // 进行日志记录
	//'LOG_RECORD_LEVEL'    =>  array('EMERG','ALERT','CRIT','ERR','WARN','NOTIC','INFO','DEBUG','SQL'), // 允许记录的日志级别
	'SHOW_ERROR_MSG' => true, // 显示错误信息
	'APP_GROUP_LIST' => 'Admin,Home',
	'DEFAULT_GROUP' => 'Home',
	'DEFAULT_MODULE'=>'TechMonitoring', //默认模块
	'DB_TYPE' => 'mysqli', // 数据库类型
	'DB_CHARSET' => 'utf8',
 	'DB_NAME' => 'SCBIC', // 数据库名称
	'DB_HOST' => 'localhost', // 数据库服务器地址
	'DB_USER' => 'root', // 数据库用户名
	'DB_PWD' => 'root', // 数据库密码
	'DB_PORT' => '3306', // 数据库端口
	'DB_PREFIX' => 't_', // 数据表前缀 
	'LOG_RECORD' => true, // 开启日志记录
    'LOG_RECORD_LEVEL' =>array('SQL'),
	
    'videoAlarm' => 'mysqli://root:111111@10.2.2.10:3306/frp', //视频报警数据库
    'radioAlarm' => 'mysqli://root:111111@10.2.1.104:3306/monitor_center', //音频报警数据库
	
	'get_live_url' => 'http://10.2.2.10:8080/FRP/VedioThirdty.do?method=execute&key=1008920',//获取直播视频播放地址接口
	'get_video_url' => 'http://10.2.2.10:8080/FRP/VedioThirdty.do?method=execute&key=1008920&isDownload=0', //获取录播视频播放地址接口
	
    
	//'LOGIN_GATEWAY' => 'Admin-Public/login', // 默认认证网关
	'TMPL_ACTION_ERROR'     => 'Public:error', // 默认错误跳转对应的模板文件//flag=0 返回上一页，flag=1跳转指定页
	'TMPL_ACTION_SUCCESS'   => 'Public:success', // 默认成功跳转对应的模板文件//flag=0 刷新父页面，flag=1跳转指定页
	'PAGE_ROLLPAGE'=>8,//分页栏每页显示的页数
	//'WSDL'=>'http://10.2.1.35:8010/Portal/WebServices/CusDevWebService.asmx?wsdl',
	'WSDL'=>'http://10.2.6.235:8010/Portal/Webservices/ExternalStartService.asmx?wsdl',
    'STREAM_URL'=>'http://10.2.1.29/video',
    
	//生成xml时文件路径
    'originalVideoFilePath'=>'Z:\\JSJC\\SysRecord\\StreamTS\\',  //原始文件路径
    'originalFMFilePath'=>'Z:\\JSJC\\SysRecord\\Audio\\',//FM文件
    'cutFilePath'=>'D:\\media\\', //截取的文件路径
    
	//播放视频时文件路径
	//'originalVideoPath'=>'http://10.2.1.29:8000/',  //原始文件路径 appache
    'originalVideoPath'=>'http://10.2.1.29:9000/JSJC/SysRecord/StreamTS/',  //原始文件路径 nginx
	'originalFMPath'=>'http://10.2.1.29:9000/JSJC/SysRecord/Audio/', //FM文件 nginx
    'cutVideoPath'=>'http://10.2.1.29:8001/media/', //截取的文件路径
    
    //radio复制路径
    'radioCopyPath'=>'C:\\radio\\Uninstall.xml', 
    
    //录制的视频文件路径
	'tsPath'=>'Z:/',
    
    //预警转发in文件路径
    'warningMsgInPath'=>'C:\\SCBIC\\AlartService\\in-xml\\',
    //预警转发out文件路径
    'warningMsgOutPath'=>'C:\\SCBIC\\AlartService\\out-xml\\',
    
    //人工登记路径
    'dengjiURL'=>'http://10.2.1.35:8010/Portal/StartInstance.aspx?WorkflowCode=TVSignalException&PageAction=Close',
    'DB_SCBIC2'=>'mysqli://root:root@localhost:3306/scbic2', //导入数据，数据库地址
	
	//RBAC 配置
	'USER_AUTH_ON'=>false, //是否需要认证
	'USER_AUTH_TYPE'=>1, //认证类型
	'USER_AUTH_KEY'=>'uid',  // 认证识别号
	'USER_AUTH_MODEL'=>'User',//模型实例（用户表名）
	'RBAC_ADMIN'=>'admin',//管理员账户名称
	'ADMIN_AUTH_KEY'=>'administrator',//超级管理员
	'NOT_AUTH_MODULE'=>'',   //无需认证模块
	'NOT_AUTH_ACTION'=>'',   //无需认证方法
	'USER_AUTH_GATEWAY'=>'Admin-Public/login', //认证网关
	//RBAC_DB_DSN  数据库连接DSN
	'RBAC_ROLE_TABLE'=>'', //角色表名称
	'RBAC_USER_TABLE'=>'', //用户和角色对应关系表名称
	'RBAC_ACCESS_TABLE'=>'', //权限分配表名称
	'RBAC_NODE_TABLE'=>'',  // 权限表名称
);
?>