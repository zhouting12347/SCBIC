/**
 * 
 */
jQuery(function($){
	$('.input-mask-start,.input-mask-end').mask('99:99:99');
	$('.input-mask-start,.input-mask-end').keyup(function(){
		//获取光标位置
		var cursorPosition=$(this).getCursorPosition();
		console.log(cursorPosition);
		switch(cursorPosition){
			case 1:
				var time=$(this).val();
				//判断第一位数
				var number=time.substr(0,1);
				if(number>2){
					$(this).val("__:__:__");
					$(this).get(0).setSelectionRange(0,1);
				}
				break;
			case 3:
				var time=$(this).val();
				//判断第二位数
				var number1=time.substr(0,1);
				var number2=time.substr(1,1);
				if((number1==2)&&(number2>3)){
					$(this).val(number1+"_:__:__");
					$(this).get(0).setSelectionRange(1,2);
				}
				break;
			case 4:
				var time=$(this).val();
				//判断第四位数
				var number=time.substr(3,1);
				if(number>=6){
					$(this).val(time.substr(0,2)+":__:__");
					$(this).get(0).setSelectionRange(3,4);
				}
			case 7:
				var time=$(this).val();
				//判断第七位数
				var number=time.substr(6,1);
				if(number>=6){
					$(this).val(time.substr(0,5)+":__");
					$(this).get(0).setSelectionRange(6,7);
				}
			default:
				break;
		}
	});
	
	//获取光标位置
	 $.fn.getCursorPosition = function() {
		var el = $(this).get(0);
		var pos = 0;
		if ('selectionStart' in el) {
			pos = el.selectionStart;
		} else if ('selection' in document) {
			el.focus();
			var Sel = document.selection.createRange();
			var SelLength = document.selection.createRange().text.length;
			Sel.moveStart('character', -el.value.length);
			pos = Sel.text.length - SelLength;
		}
		return pos;
	}
	
	 //计算人工登记持续时间
	 $(".input-mask-end").blur(function(){
		 var startDate=$("#startTime3").val();
		 var endDate=$("#endTime3").val();
		 var startClock=$("#startClock").val();
		 var endClock=$("#endClock").val();
		 if(startDate&&endDate&&startClock&&endClock){
			 var date1=startDate.split("-");
			 var time1=startClock.split(":");
			 var date2=endDate.split("-");
			 var time2=endClock.split(":");
			 var duration=date2[2]*24*3600+time2[0]*3600+time2[1]*60+time2[2]*1-date1[2]*24*3600-time1[0]*3600-time1[1]*60-time1[2]*1;
			 $("#duration3").val(duration);
		 }
	 });
	 
	$('.contral').click(function(){
		if(action){
			$.playBar.Stop();
			action=false;
			pause();
			$(this).html("开始");
			}else{
				$.playBar.Begin();
				action=true;
				$(this).html("停止");
				play();
				}
		});
	$.playBar.addBar($('.playBar'),1000*3600);//第一个参数是需要显示播放器的容器，第二个参数为时间，单位毫秒
	$.playBar.changeBarColor("#72dfff");//设置进度条颜色

	
	//根据条件查询报警信息
	$("#alarmSearch").click(function(){
		var showNum=$("#showNum").val();
		var data="&nowPage=1&condition=1&showNum="+showNum;
		$.ajax({
			url: '/index.php/Home/TechMonitoring/ajaxGetAlarm',
			dataType: 'json',
			type: 'POST',
			cache:false,
			data:$("#form1").serialize()+data,
			success: function(msg) {
				if(msg.status){
					$('#page').html(msg.data[0]);
					$('#TB01 tbody').html(msg.data[1]);
					initTableCheckbox();
				}else{
					
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	//左侧分页栏ajax读取数据
	$("#page").delegate("a","click",function(){
		var showNum=$("#showNum").val();
		var nowPage=$(this).attr('page');
		var data="nowPage="+nowPage+"&showNum="+showNum;
		$.ajax({
			url: '/index.php/Home/TechMonitoring/ajaxGetAlarm',
			dataType: 'json',
			type: 'POST',
			cache:false,
			data:data,
			success: function(msg) {
				if(msg.status){
					$('#page').html(msg.data[0]);
					$('#TB01 tbody').html(msg.data[1]);
					initTableCheckbox();
				}else{
					
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	//分页显示条数改变，加载数据
	$("#showNum").change(function(){
		$("#alarmSearch").click();
	});
	
	//警报类型1选择触发警报类型2
	$("#alarmType1").change(function(){
		var id=$("#alarmType1").val();
		$.ajax({
			url: '/index.php/Home/TechMonitoring/ajaxGetSecondAlarmType?pid='+id,
			dataType: 'json',
			type: 'GET',
			cache:false,
			success: function(msg) {
				if(msg.status){
					$("#alarmType2").html(msg.data);
				}else{
					$("#alarmType2").html('<option value="empty" >--</option>');
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	//根据信号类型读取频率值
	$("#signalType").change(function(){
		var id=$("#signalType").val();
		$.ajax({
			url: '/index.php/Home/TechMonitoring/ajaxGetFrequencyId?signalTypeId='+id,
			dataType: 'json',
			type: 'GET',
			cache:false,
			success: function(msg) {
				if(msg.status){
					$("#frequencyId").html(msg.data);
				}else{
					$("#frequencyId").html('<option value="empty" >--</option>');
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	//表格双击
	$('#TB01 tbody').delegate('tr','dblclick',function(){
		$(this).find("td:eq(0)").find("input").prop("checked","checked");  //勾选
		$(this).find("td:eq(0)").find("input").parent().parent().addClass('warning');
		if(loadingStatus==1){return 0;}
		//获取当前视频的日期和小时
		videoDate=$(this).find("td:eq(1)").html();
		videoDate=videoDate.substr(0,10);
		videoHour=$(this).find("td:eq(1)").html();
		videoHour=videoHour.substr(11,2);
		loadingStatus=1;
		loadingLayer(loadingStatus);
		//获取表格内的信息
		startTime=$(this).find("td:eq(1)").html(); //开始时间
		endTime=$(this).find("td:eq(2)").html(); //结束时间
		channel=$(this).find("td:eq(3)").html(); //频道名称
		frequency=$(this).find("td:eq(4)").html(); //频率值
		signalType=$(this).find("td:eq(5)").html(); //信号类型
		station=$(this).find("td:eq(6)").html(); //监测站
		alarmType=$(this).find("td:eq(7)").html(); //报警类型
		slipType=$(this).find("td:eq(8)").html(); //差错类型
		alarmLevel=$(this).find("td:eq(9)").html(); //差错等级
		duration=$(this).find("td:eq(10)").html(); //持续时间
		instruct=$(this).find("td:eq(11)").html(); //结束状态
		sureStatus=$(this).find("td:eq(12)").html(); //确认状态
		programName=$(this).find("td:eq(13)").html();//节目名称
		alarmID=$(this).attr('id');
		
		//当前报警时间是否【重保期】、是否【重点时段】、是否【临时停机】、是否【例行停机】、是否【试播期】
		$.ajax({
			url: '/index.php/Home/TechMonitoring/ajaxGetStatus?id='+alarmID,
			dataType: 'json',
			type: 'GET',
			cache:false,
			success: function(msg) {
				if(msg.status){
					//console.log(msg);
					$("#importantdate").val(msg.data.important);
					$("#datesheet").val(msg.data.datesheet);
					$("#mcndrel").val(msg.data.mcndrel);
					$("#tempdown").val(msg.data.tempdown);
					$("#temponair").val(msg.data.temponair);
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
		
		
		tr=$(this);
		
		//计算视频路径
		getVideoURL();
		//清空手动加载视频
		manualFileName='';
		
		alarmDetail();
	});
	
});

//异态登记按钮点击
$('#abnormalForm').click(function(){
	$('#form2')[0].reset()
	$("#startTime2").val(startTime);
	//$("#alarmDate2").val(startTime.slice(0,10));
	$("#startTime2").val(startTime);
	$("#endTime2").val(endTime);
	$("#duration2").val(duration);
	$("#signalType2").val(signalType);
	$("#channel2").val(channel);
	$("#alarmType2").val(alarmType);
	$("#programName2").val(programName);
	$("#alarmType-2").val(alarmType);
	$("#alarmLevel2").val(alarmLevel);
	$("#alarmID2").val(alarmID);
	$("#frequencyValue2").val(frequency);
});

//异常登录提交表单
$('#abnormalSave,#abnormalSubmit').click(function(){
    //判断一审表是否有数据
	var yishen=$("#yishen tbody tr");
	if(yishen.length==0){
		alert("一审登记表没有数据！");
		return 0;
	}
	if(!$("#description").val()){
		alert("描述内容不能为空");
		return 0;
	}
	//$('#abnormalSave,#abnormalSubmit').attr('disabled',true);
	var type=$(this).val();
	var data="&type="+type+"&signalType="+signalType+"&channel="+channel+"&alarmType="+alarmType+
	"&slipType="+slipType+"&alarmLevel="+alarmLevel+"&frequencyValue="+frequency+"&manualFileName="+manualFileName;
	$.ajax({
		url:'/index.php/Home/TechMonitoring/ajaxAbnormalHandler',
		dataType: 'json',
		type: 'POST',
		cache:false,
		data:$("#form2").serialize()+data,
		success: function(msg){
			if(msg.status){
				alert(msg.info);
				//更新一审表
				updateYishenTable();
			}else{
				alert(msg.info);
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//异态发送
$("#manualCheck").click(function(){
	//读取保存未发送的异常登记
	$.ajax({
		url: '/index.php/Home/TechMonitoring/ajaxGetAlarmSend',
		dataType: 'json',
		type: 'GET',
		cache:false,
		success: function(msg){
			if(msg.status){
				$('#page2').html(msg.data[0]);
				$('#TB02 tbody').html(msg.data[1]);
			}else{
				
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//异态发送搜索按钮
$("#manualSearch").click(function(){
	var data="nowPage=1&condition=1&manualKeyword="+$("#manualKeyword").val();
	$.ajax({
		url: '/index.php/Home/TechMonitoring/ajaxGetAlarmSend',
		dataType: 'json',
		type: 'POST',
		cache:false,
		data:data,
		success: function(msg) {
			if(msg.status){
				$('#page2').html(msg.data[0]);
				$('#TB02 tbody').html(msg.data[1]);
			}else{
				
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//异态发送分页栏ajax读取数据
$("#page2").delegate("a","click",function(){
	var nowPage=$(this).attr('page');
	var data="nowPage="+nowPage;
	$.ajax({
		url: '/index.php/Home/TechMonitoring/ajaxGetAlarmSend',
		dataType: 'json',
		type: 'POST',
		cache:false,
		data:data,
		success: function(msg) {
			if(msg.status){
				$('#page2').html(msg.data[0]);
				$('#TB02 tbody').html(msg.data[1]);
			}else{
				
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//异态发送编辑按钮
$("#TB02 tbody").delegate("button[class='btn btn-info btn-sm']","click",function(){
	var alarmId=$(this).parent().parent().attr("id");
	$.ajax({
		url: '/index.php/Home/TechMonitoring/getAlarmHandler?id='+alarmId,
		dataType: 'json',
		type: 'GET',
		cache:false,
		success: function(msg) {
			if(msg.status){
				//alert(msg.data.alarmId);
				$("#alarmDate3").val(msg.data.alarmDate);
				$("#startTime3").val(msg.data.alarmHappentime);
				$("#endTime3").val(msg.data.alarmEndtime);
				$("#duration3").val(msg.data.duration);
				$("#signalType3").val(msg.data.signalType);
				$("#frequencyValue3").val(msg.data.frequencyValue);
				$("#channel3").val(msg.data.channelName);
				$("#programName3").val(msg.data.programName);
				$("#alarmType3").val(msg.data.alarmType);
				$("#alarmLevel3").val(msg.data.alarmLevel);
				$("#description3").val(msg.data.description);
				$("#faultReason3").val(msg.data.faultReason);
				$("#dutyMan1-3").val(msg.data.dutyMan1);
				$("#dutyMan2-3").val(msg.data.dutyMan2);
				$("#alarmId3").val(msg.data.alarmId);
			}else{
				
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//异态发送重置按钮
$("#manualReset").click(function(){
	$("#form3")[0].reset();
	$("#alarmId3").val('');
});

//异态发送保存按钮
$("#manualSave").click(function(){
	$('#abnormalSave,#abnormalSubmit').attr('disabled',true);
	$.ajax({
		url: '/index.php/Home/TechMonitoring/ajaxSaveAlarmSendHandler',
		dataType: 'json',
		type: 'POST',
		cache:false,
		data:$("#form3").serialize(),
		success: function(msg) {
			if(msg.status){
				/*$.alert({
					 title:'',
                     type: 'green',
                     content: '保存成功！',
                 });*/
				 alert("保存成功！");
				 refreshPage();
			}else{
			/*	$.alert({
					 title:'',
                    type: 'red',
                    content: '保存失败！',
                });*/
				alert("保存失败！");
			}
			$('#abnormalSave,#abnormalSubmit').removeAttr('disabled');
		},
		error:function(){
			alert("发生错误！");
			$('#abnormalSave,#abnormalSubmit').removeAttr('disabled');
		}
	});
	$("#form3")[0].reset();
	$("#alarmId3").val('');
});

//删除按钮
$("#TB02 tbody").delegate("button[class='btn btn-danger btn-sm']","click",function(){
	var alarmId=$(this).parent().parent().attr("id");
	if(confirm("是否确定删除条目?")){
		$.ajax({
			url: '/index.php/Home/TechMonitoring/ajaxDelAlarmSendHandler?alarmId='+alarmId,
			dataType: 'json',
			type: 'GET',
			cache:false,
			success: function(msg) {
				if(msg.status){
					 alert("删除成功！");
					 refreshPage();
				}else{
					alert("删除失败！");
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
    }else{
		return false;
	}
});

//提交按钮
$("#TB02 tbody").delegate("button[class='btn btn-success btn-sm']","click",function(){
	var alarmId=$(this).parent().parent().attr("id");
	if(confirm("是否确定提交条目?")){
		$.ajax({
			url: '/index.php/Home/TechMonitoring/ajaxSubmitAlarmSendHandler?alarmId='+alarmId+"&manualFileName="+manualFileName,
			dataType: 'json',
			type: 'GET',
			cache:false,
			success: function(msg) {
				if(msg.status){
					 alert(msg.info);
					 refreshPage();
				}else{
					alert(msg.info);
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});				
    }else{
		return false;
	}
});

//提交，删除，编辑后刷新当前分页
function refreshPage(){
	var data="nowPage=1";
	$.ajax({
		url: '/index.php/Home/TechMonitoring/ajaxGetAlarmSend',
		dataType: 'json',
		type: 'POST',
		cache:false,
		success: function(msg) {
			if(msg.status){
				$('#page2').html(msg.data[0]);
				$('#TB02 tbody').html(msg.data[1]);
			}else{
				
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
}


//人工登记
$("#dengji").click(function(){
	window.open(dengjiURL,"_blank");
});
//异态下载按钮
/*$("#abnormalDownload").click(function(){
	if(!alarmID){
		$.alert({
			 title:'',
           type: 'red',
           content: '下载失败！',
       });
		return false;
	}
	$.ajax({
		url: '/index.php/Home/TechMonitoring/abnormalDownload?id='+alarmID+"&manualFileName="+manualFileName,
		dataType: 'json',
		type: 'GET',
		cache:false,
		success: function(msg) {
			if(msg.status){
				 $.alert({
					 title:'',
                     type: 'green',
                     content: '下载成功！',
                 });
				 refreshPage();
			}else{
				$.alert({
					 title:'',
                    type: 'red',
                    content: '下载失败！',
                });
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});*/

//报警确认
$("#alarmConfirm").click(function(){
	alarmLevel=$("input:radio[name='alarmLevel1']:checked").val();
	sureStatus=$("input:radio[name='sureStatus1']:checked").val();
	if(alarmLevel==undefined||sureStatus==undefined){
		alert("请选择差错等级和确认状态！");
		return 0;
	}
	var level;
	var status;
	switch(alarmLevel){
		case "一级":
			level=1;break;
		case "二级":
			level=2;break;
		case "三级":
			level=3;break;
		case "非等级差错":
			level=4;break;
	}
	
	switch(sureStatus){
		case "未确认":
			status=0;break;
		case "确认":
			status=1;break;
		case "误报":
			status=2;break;
	}
	
	//勾选的报警条数
	var alarms=$("#TB01 tbody tr[class='warning']");
	if(alarms.length==0){
		alert("请先勾选报警信息！");
		return 0;
	}
	var data="id=";
	for(i=0;i<alarms.length;i++){
		if(i!=alarms.length-1){
			data+=alarms[i].id+"@";
		}else{
			data+=alarms[i].id;
		}
	}
	
	data+="&alarmLevel="+level+"&sureStatus="+status;
	//提交报警确认
	$.ajax({
		url: '/index.php/Home/TechMonitoring/alarmConfirm',
		dataType: 'json',
		type: 'POST',
		data:data,
		cache:false,
		success: function(msg) {
			if(msg.status){
				alert("操作成功！");
				$("#alarmSearch").click();
			}else{
				alert("操作失败！");
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
	
});

//手动选择播放的视频
$("button[class='btn btn-block btn-success file']").click(function(){
	$("#file")[0].click();
});

//快退
$("button[id='backward']").click(function(){
	var seconds=$(this).attr("value");
	backward(seconds);
});

//快进
$("button[id='forward']").click(function(){
	var seconds=$(this).attr("value");
	forward(seconds);
});

//文件选择框选择后获取文件名
$("#file").change(function(){
	var str=$(this).val();
	var arr=str.split('\\');//注split可以用字符或字符串分割
	manualFileName=arr[arr.length-1];//这就是要取得的文件名称
	$.ajax({
		url: '/index.php/Home/TechMonitoring/manualGetPath?id='+alarmID,
		dataType: 'json',
		type: 'GET',
		cache:false,
		success: function(msg) {
			if(msg.status){
				//播放选择的视频
				videoURL=msg.data+manualFileName;
				loadVideo(msg.info);
			}else{
				alert("加载失败！");
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});

});

//添加一审信息
$(".yishen").click(function(){
	var alarms=$("#TB01 tbody tr[class='warning']");
	if(alarms.length==0){
		alert("请先勾选报警信息！");
		return 0;
	}
	var data="id=";
	for(i=0;i<alarms.length;i++){
		if(i!=alarms.length-1){
			data+=alarms[i].id+"@";
		}else{
			data+=alarms[i].id;
		}
	}
	
	//提交勾选的一审信息
	$.ajax({
		url: '/index.php/Home/TechMonitoring/addYishen',
		dataType: 'json',
		type: 'POST',
		data:data,
		cache:false,
		success: function(msg) {
			updateYishenTable();
		},
		error:function(){
			alert("发生错误！");
		}
	});
	
});

//一审表删除
$("#yishen").delegate("button","click",function(){
	var id=$(this).attr('id');
	$.ajax({
		url: '/index.php/Home/TechMonitoring/delYishen?id='+id,
		dataType: 'json',
		type: 'POST',
		cache:false,
		success: function(msg) {
			updateYishenTable();
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//刷新一审表
function updateYishenTable(){
	$.ajax({
		url: '/index.php/Home/TechMonitoring/getYishen',
		dataType: 'json',
		type: 'POST',
		cache:false,
		success: function(msg) {
			console.log(msg);
			if(msg.status){
				$("#yishen tbody").html(msg.data.tr1);
				$("#yishen2 tbody").html(msg.data.tr2);
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
}




//表格前加checkbox
function initTableCheckbox(){
	  var $thr = $('#TB01 thead tr');
	 // var $quanxuan = $('#quanxuan');
	  var $checkAllTh = $('<th><input type="checkbox" id="checkAll" name="checkAll" /></th>');
	  /*将全选/反选复选框添加到表头最前，即增加一列*/
	  if(checkboxHead==0){//第一次运行时添加全选框
		  $thr.prepend($checkAllTh);
		  //$quanxuan.html($checkAllTh);
	  }else{
		 $("#checkAll").prop('checked',false); //取消全选打勾
	  }
    /*“全选/反选”复选框*/
    var $checkAll = $thr.find('input');
    $checkAll.click(function(event){
        /*将所有行的选中状态设成全选框的选中状态*/
        $tbr.find('input').prop('checked',$(this).prop('checked'));
        /*并调整所有选中行的CSS样式*/
        if ($(this).prop('checked')) {
            $tbr.find('input').parent().parent().addClass('warning');
        } else{
            $tbr.find('input').parent().parent().removeClass('warning');
        }
        /*阻止向上冒泡，以防再次触发点击操作*/
        event.stopPropagation();
    });
    /*点击全选框所在单元格时也触发全选框的点击操作*/
    $checkAllTh.click(function(){
        $(this).find('input').click();
    });
    var $tbr = $('#TB01 tbody tr');
    var $checkItemTd = $('<td><input type="checkbox" name="checkItem" /></td>');
    /*每一行都在最前面插入一个选中复选框的单元格*/
    $tbr.prepend($checkItemTd);
    /*点击每一行的选中复选框时*/
    $tbr.find('input').click(function(event){
        /*调整选中行的CSS样式*/
        $(this).parent().parent().toggleClass('warning');
        /*如果已经被选中行的行数等于表格的数据行数，将全选框设为选中状态，否则设为未选中状态*/
        $checkAll.prop('checked',$tbr.find('input:checked').length == $tbr.length ? true : false);
        /*阻止向上冒泡，以防再次触发点击操作*/
        event.stopPropagation();
    });
    /*点击每一行时也触发该行的选中操作*/
    $tbr.click(function(){
        $(this).find('input').click();
    });
    checkboxHead=1;
}
