<?php
class TongchouAction extends CommonAction
{
	//【统筹首页】
	//
    public function index()
    {
    	$this->assign("level3","active open");
    	$this->assign("level3_1","active");
		$this->display('index');
    }
    
   //【发送导演主持】
   //发送导演演员页面
   public function send(){
	   	$status=$_GET['status'];
	   	if($status=="this"){//本周任务
	   		$this->assign("level3_2_1","active");
	   	}else if($status=="next"){//下周任务
	   		$this->assign("level3_2_2","active");
	   	}
	   	//读取本周或下周的任务列表
	   	$file="./Excel/1.xlsx";
	   	$arr_excel=getXmlToArray($file);
	   	$this->assign("excel",$arr_excel);//excel数据
	   	$this->assign("status",$status);
	   	$this->assign("level3","active open");
	    $this->assign("level3_2","active");
	   	$this->display('send');
   }
   
   //【会议室安排】
   //
   public function arrange(){
	   	$status=$_GET['status'];
	   	if($status=="this"){//本周任务
	   		$this->assign("level3_3_1","active");
	   	}else if($status=="next"){//下周任务
	   		$this->assign("level3_3_2","active");
	   	}
	   	//读取本周或下周的任务列表
	   	$file="./Excel/1.xlsx";
	   	$arr_excel=getXmlToArray($file);
	   	$this->assign("excel",$arr_excel);//excel数据
	   	$this->assign("status",$status);
	   	$this->assign("level3","active open");
	   	$this->assign("level3_3","active");
	   	$this->display('arrange');
   }
   
   //【技术排班】
   public function technology(){
	   	$status=$_GET['status'];
	   	if($status=="this"){//本周任务
	   		$this->assign("level3_3_1","active");
	   	}else if($status=="next"){//下周任务
	   		$this->assign("level3_3_2","active");
	   	}
	   	//读取本周或下周的任务列表
	   	$file="./Excel/1.xlsx";
	   	$arr_excel=getXmlToArray($file);
	   	$this->assign("excel",$arr_excel);//excel数据
	   	$this->assign("status",$status);
	   	$this->assign("level3","active open");
	   	$this->assign("level3_4","active");
	   	$this->display('technology');
   }
   
   //会议室时间安排弹出层
   public function room_time_list(){
   		$this->display("room_time_list");
   }
   
   //eccel导入后网页弹出表格页
   public function excel_table(){
   	$file="./Excel/1.xlsx";
   	$arr_excel=getXmlToArray($file);
   	$this->assign("excel",$arr_excel);//excel数据
   	$this->display('excel_table');
   }
   
   //【统筹首页】
   //表格行编辑
   public function edit(){
   	$this->display('edit');
   }
}
?>