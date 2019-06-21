/**
 * 
 */
jQuery(function($){
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
			url: '/index.php/Home/Radio/ajaxGetAlarm',
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
			url: '/index.php/Home/Radio/ajaxGetAlarm',
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
			url: '/index.php/Home/Radio/ajaxGetSecondAlarmType?pid='+id,
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
			url: '/index.php/Home/Radio/ajaxGetFrequencyId?signalTypeId='+id,
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
		tr=$(this);
		
		//计算视频路径
		getRadioURL();
		//清空手动加载视频
		manualFileName='';
		
		alarmDetail();
	});
	
});

//异态登记按钮点击
$('#abnormalForm').click(function(){
	$('#form2')[0].reset()
	$("#startTime2").val(startTime);
	$("#alarmDate2").val(startTime.slice(0,10));
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
	var type=$(this).val();
	var data="&type="+type+"&signalType="+signalType+"&channel="+channel+"&alarmType="+alarmType+
	"&slipType="+slipType+"&alarmLevel="+alarmLevel+"&frequencyValue="+frequency;
	$.ajax({
		url: '/index.php/Home/Radio/ajaxAbnormalHandler',
		dataType: 'json',
		type: 'POST',
		cache:false,
		data:$("#form2").serialize()+data,
		success: function(msg){
			alert(msg.info);
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//人工登记搜索按钮
$("#manualSearch").click(function(){
	var data="nowPage=1&condition=1&manualKeyword="+$("#manualKeyword").val();
	$.ajax({
		url: '/index.php/Home/Radio/ajaxGetAlarmSend',
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

//人工登记
$("#manualCheck").click(function(){
	//读取保存未发送的异常登记
	$.ajax({
		url: '/index.php/Home/Radio/ajaxGetAlarmSend',
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

//人工登记分页栏ajax读取数据
$("#page2").delegate("a","click",function(){
	var nowPage=$(this).attr('page');
	var data="nowPage="+nowPage;
	$.ajax({
		url: '/index.php/Home/Radio/ajaxGetAlarmSend',
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

//人工登记编辑按钮
$("#TB02 tbody").delegate("button[class='btn btn-info btn-sm']","click",function(){
	var alarmId=$(this).parent().parent().attr("id");
	$.ajax({
		url: '/index.php/Home/Radio/getAlarmHandler?id='+alarmId,
		dataType: 'json',
		type: 'GET',
		cache:false,
		success: function(msg) {
			if(msg.status){
				//alert(msg.data.alarmId);
				$("#alarmDate3").val(msg.data.alarmDate);
				$("#startTime3").val(msg.data.alarmHappentime);
				$("#frequencyValue3").val(msg.data.frequencyValue);
				$("#monitorStation3").val(msg.data.monitorStation);
				$("#verifyDepartment3").val(msg.data.verifyDepartment);
				$("#sourceForm3").val(msg.data.sourceForm);
				$("#ADPhone3").val(msg.data.ADPhone);
				$("#ADPeople3").val(msg.data.ADPeople);
				$("#submitMan3").val(msg.data.submitMan);
				$("#submitPhone3").val(msg.data.submitPhone);
				$("#ApproveUnit3").val(msg.data.ApproveUnit);
				$("#alarmId3").val(msg.data.alarmId);
			}else{
				
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

//人工登记新建按钮
$("#manualReset").click(function(){
	$("#form3")[0].reset();
	$("#alarmId3").val('');
});

//人工登记保存按钮
$("#manualSave").click(function(){
	$.ajax({
		url: '/index.php/Home/Radio/ajaxSaveAlarmSendHandler',
		dataType: 'json',
		type: 'POST',
		cache:false,
		data:$("#form3").serialize(),
		success: function(msg) {
			if(msg.status){
				 alert("保存成功！");
				 refreshPage();
			}else{
				 alert("保存失败！");
			}
		},
		error:function(){
			alert("发生错误！");
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
        		url: '/index.php/Home/Radio/ajaxDelAlarmSendHandler?alarmId='+alarmId,
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
    		url: '/index.php/Home/Radio/ajaxSubmitAlarmSendHandler?alarmId='+alarmId,
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
		url: '/index.php/Home/Radio/ajaxGetAlarmSend',
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
}

//异态下载按钮
$("#abnormalDownload").click(function(){
	if(!alarmID){
		$.alert({
			 title:'',
           type: 'red',
           content: '下载失败！',
       });
		return false;
	}
	$.ajax({
		url: '/index.php/Home/TechMonitoring/fileCopy?id='+alarmID+"&manualFileName="+manualFileName,
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
                    content: msg.info,
                });
			}
		},
		error:function(){
			alert("发生错误！");
		}
	});
});

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
				/* $.alert({
					 title:'',
                     type: 'green',
                     content: '操作成功！',
                 });*/
				alert("操作成功！");
				$("#alarmSearch").click();
			}else{
				/*$.alert({
					 title:'',
                    type: 'red',
                    content: '操作失败！',
                });*/
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

//文件选择框选择后获取文件名
$("#file").change(function(){
	var str=$(this).val();
	var arr=str.split('\\');//注split可以用字符或字符串分割
	manualFileName=arr[arr.length-1];//这就是要取得的图片名称
});

//表格前加checkbox
function initTableCheckbox(){
	  var $thr = $('#TB01 thead tr');
	  var $checkAllTh = $('<th><input type="checkbox" id="checkAll" name="checkAll" /></th>');
	  /*将全选/反选复选框添加到表头最前，即增加一列*/
	  if(checkboxHead==0){//第一次运行时添加全选框
		  $thr.prepend($checkAllTh);
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
