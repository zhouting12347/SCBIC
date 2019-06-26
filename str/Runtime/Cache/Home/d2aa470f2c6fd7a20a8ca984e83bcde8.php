<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Insert title here</title>
<link rel="stylesheet" href="__PUBLIC__/layui/css/layui.css">
<link rel="stylesheet" href="__PUBLIC__/video/video.css">
<link rel="stylesheet" href="__PUBLIC__/css/index.css">
</head>
<body class="layui-layout-body">
	<div class="layui-row  layui-col-space5">
		<!-- 播放器 -->
		<div class="layui-col-md3  layui-col-space5">
			<div class="layui-row">
				<div class="layui-bg-blue">信号监测</div>
				 <video-js id="vid1" controls autoplay=true style="width:100%;height:290px;">
				    <source src="https://1akamai-axtest.akamaized.net/routes/lapd-v1-acceptance/www_c4/Manifest.m3u8" type="application/x-mpegURL">
				  </video-js>
			</div>
			
			<div class="layui-row">
				<div class="layui-bg-blue">信号比对</div>
				<video-js id="vid2" controls autoplay=true style="width:100%;height:290px;">
				    <source src="https://1akamai-axtest.akamaized.net/routes/lapd-v1-acceptance/www_c4/Manifest.m3u8" type="application/x-mpegURL">
				  </video-js>
			</div>
			
			<div class="layui-row">
				<div class="layui-bg-blue">直播回看</div>
				<video-js id="vid3" controls autoplay=true style="width:100%;height:290px;">
				    <source src="https://1akamai-axtest.akamaized.net/routes/lapd-v1-acceptance/www_c4/Manifest.m3u8" type="application/x-mpegURL">
				  </video-js>
			</div>
		</div>
		<!-- End 播放器 -->
		
		
		
		
		
		
		<!-- 数据表格 -->
		<div class="layui-col-md4  layui-col-space5">
			<div class="layui-row">
				<div class="layui-bg-green">信号监测列表</div>
				<table class="layui-hide" id="alarmTable"  lay-filter="myAlarmTable"></table>
			</div>
			<div class="layui-row">
				<div class="layui-bg-green">高标清同播监测列表</div>
				<table class="layui-hide" id="samePlayTable"  lay-filter="mySamePlayTable"></table>
			</div>
		</div>
		<!-- End 数据表格 -->
		
		<!-- 监测处理表格 -->
		<div class="layui-col-md5  layui-col-space5">
			<div class="layui-row">
				<div class="layui-bg-orange">监测处理</div>
				<table class="layui-hide" id="handleTable"  lay-filter="myHandleTable"></table>
			</div>
		</div>
		<!-- End 监测处理表格 -->
	</div>

<script src="__PUBLIC__/js/jquery.min.js"></script>
<script src="__PUBLIC__/layui/layui.all.js"></script>
<script src="__PUBLIC__/video/video.js"></script>
<script src="__PUBLIC__/js/index.js"></script>
<script type="text/javascript">
var player1;
var player2;
var player3;
window.onload=function(){
	 var vid1=document.getElementById('vid1');
	 var vid2=document.getElementById('vid2');
	 var vid3=document.getElementById('vid3');
	 player1=videojs(vid1);
	 player2=videojs(vid2);
	 player3=videojs(vid3);
	 
	  var table=layui.table;
	  
	  //*************************************************************信号监测表*******************************************************************
	  table.render({
	    elem: '#alarmTable'
	    ,url:'/index.php/Table/getTableData'
	    ,toolbar: '#alarmToolBar'
	    //,even:true
	    ,height:"290"
		,limit:5
	    ,limits:[5,20,50,100]
	    ,where:{tableName:"alarm_message",tableType:"alarm"}
	    ,cols: [[
	       {type:'checkbox'}
	      ,{field:'MC_Name',title: '监测频道'}
	      ,{field:'alarmHappentime',width:145,title: '开始时间'}
	      ,{field:'alarmEndtime',width:145,title: '结束时间'}
	      ,{field:'duration',title: '持续时间'}
	      ,{field:'alarmType_name',title: '类型'}
	    ]]
	    ,page: true
	    ,done: function(res, curr, count){
	    
	      }
	  });
	  
	  
	  //头工具栏事件
	  table.on('toolbar(myAlarmTable)', function(obj){
	    //console.log(checkStatus);
	    switch(obj.event){
	    	//批量添加到一审表
	   		 case "add":
	   			var checkStatus = table.checkStatus('alarmTable');
	   			addMultiYishen(checkStatus);
	    		 break;
	   		 case "reload":
	   			 layui.table.reload("alarmTable");
	   			 break;
	    }
	  });
	  
	  //监听行双击事件
	  table.on('rowDouble(myAlarmTable)', function(obj){
		var index = layer.load(1);
	   	var data = obj.data;
	    var alarmID=data.alarmId;
	    //标注选中样式
	    obj.tr.addClass('layui-table-click').siblings().removeClass('layui-table-click');
	    //添加到一审表
	    addYishen(data);
	    //接口获取播放地址
    	$.ajax({
    		url: '/index.php/Home/TechMonitoring1/getVideoURL?id='+alarmID,
    		dataType: 'json',
    		type: 'GET',
    		cache:false,
    		success: function(msg){
    			if(msg.status==1){
    				player1.src(msg.data);
    			}else if(msg.status==0){
    				layer.msg("点播地址获取失败");
    			}
    			layer.close(index);
    		},
    		error:function(){
    			layer.msg("发生错误！");
    			layer.close(index);
    		}
    	});
	  });
	  //*************************************************************END 信号监测表*******************************************************************
	 
	  
	  //*************************************************************高清同播监测表*******************************************************************
	    table.render({
	    elem: '#samePlayTable'
	    ,url:'/index.php/Table/getTableData'
	    ,toolbar: '#samePlayToolBar'
	    //,even:true
	    ,height:"290"
		,limit:5
	    ,limits:[5,20,50,100]
	    ,where:{tableName:"alarm_message",tableType:"samePlay"}
	    ,cols: [[
	       {type:'checkbox'}
	      ,{field:'MC_Name',title: '高清频道'}
	      ,{field:'alarmHappentime',width:145,title: '开始时间'}
	      ,{field:'alarmEndtime',width:145,title: '结束时间'}
	      ,{field:'duration',title: '持续时间'}
	      ,{field:'REL_MC_Name',title: '标清频道'}
	    ]]
	    ,page: true
	    ,done: function(res, curr, count){
	    
	      }
	  });
	  
	  
	  //头工具栏事件
	  table.on('toolbar(mySamePlayTable)', function(obj){
	    //console.log(checkStatus);
	    switch(obj.event){
	    	//批量添加到一审表
	   		 case "add":
	   			var checkStatus = table.checkStatus('alarmTable');
	   			addMultiYishen(checkStatus);
	    		 break;
	   		 case "reload":
	   			 layui.table.reload("alarmTable");
	   			 break;
	    }
	  });
	  
	  //监听行双击事件
	  table.on('rowDouble(myAlarmTable)', function(obj){
		var index = layer.load(1);
	   	var data = obj.data;
	    var alarmID=data.alarmId;
	    //标注选中样式
	    obj.tr.addClass('layui-table-click').siblings().removeClass('layui-table-click');
	    //添加到一审表
	    addYishen(data);
	    //接口获取播放地址
    	$.ajax({
    		url: '/index.php/Home/TechMonitoring1/getVideoURL?id='+alarmID,
    		dataType: 'json',
    		type: 'GET',
    		cache:false,
    		success: function(msg){
    			if(msg.status==1){
    				player1.src(msg.data);
    			}else if(msg.status==0){
    				layer.msg("点播地址获取失败");
    			}
    			layer.close(index);
    		},
    		error:function(){
    			layer.msg("发生错误！");
    			layer.close(index);
    		}
    	});
	  });
	  //*************************************************************END高清同播监测表*******************************************************************
	  
	  
	   //*************************************************************监测处理表*******************************************************************
	   table.render({
	    elem: '#handleTable'
	    ,url:'/index.php/Table/getTableData'
	    //,even:true
	    ,height:"290"
		,limit:5
	    ,limits:[5,20,50,100]
	    ,where:{tableName:"yishen",tableType:"yishen"}
	    ,cols: [[
	       {type:'checkbox'}
	      ,{field:'MC_Name',title: '监测频道'}
	      ,{field:'StartDateTime',width:145,title: '开始时间'}
	      ,{field:'EndDateTime',width:145,title: '结束时间'}
	      ,{field:'MC_Format',title: '高标清'}
	      ,{field:'TT_Name',title: '传输类型'}
	      ,{field:'C_Name',title: '逻辑频道'}
	      ,{field:'action', title: '操作',width:100,align:'center', toolbar: '#handleBar'}
	    ]]
	    ,page: true
	    ,done: function(res, curr, count){
	    
	      }
	  });
	  
     table.on('tool(myHandleTable)', function(obj){
		 var alarmId=obj.data.AlarmId;
		 if(obj.event="delete"){
			 $.ajax({
		         type: "POST",
		         url:'/index.php/Index/delYishenHandler?alarmId='+alarmId,
		         dataType: "json",
		         success: function(msg){
		         			//刷新handleTable，一审提交表
		         			if(msg.status==1){
		         				layui.table.reload("handleTable");
		         			}
		           },
		         error: function(){
		         	layer.msg("发生错误");
		         }
			 });
		 }
	 });
	   
	  //*************************************************************END监测处理表*******************************************************************
}
//添加一审表
function addYishen(data){
	 $.ajax({
         type: "POST",
         url:'/index.php/Index/addYishenHandler?single=1',
         data:data,
         dataType: "json",
         success: function(msg){
         			//刷新handleTable，一审提交表
         			layui.table.reload("handleTable");
           },
         error: function(){
         	layer.msg("发生错误");
         }
     });
}

//批量添加一审表
function addMultiYishen(data){
	$.ajax({
        type: "POST",
        url:'/index.php/Index/addYishenHandler',
        data:data,
        dataType: "json",
        success: function(msg){
        	//刷新handleTable，一审提交表
 			layui.table.reload("handleTable");
          },
        error: function(){
        	layer.msg("发生错误");
        }
    });
}
</script>
<script type="text/html" id="alarmToolBar">
  <div class="layui-btn-container">
    <button type="button" class="layui-btn layui-btn-sm" style="background-color:#00B000;" lay-event="add">加入相关</button>
	<button type="button" class="layui-btn layui-btn-normal layui-btn-sm" style="margin-right: 0px;" lay-event="reload">刷新</button>
  </div>
</script>
<script type="text/html" id="samePlayToolBar">
  <div class="layui-btn-container">
    <button type="button" class="layui-btn layui-btn-sm" style="background-color:#00B000;" lay-event="add">加入相关</button>
	<button type="button" class="layui-btn layui-btn-normal layui-btn-sm" style="margin-right: 0px;" lay-event="reload">刷新</button>
  </div>
</script>
<script type="text/html" id="handleBar">
  <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delete">删除</a>
</script>
</body>
</html>