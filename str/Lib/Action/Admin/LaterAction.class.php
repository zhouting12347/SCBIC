<?php
class LaterAction extends CommonAction
{
	//未分配任务
    public function undistributed_task()
    {
    	//读取任务列表
    	$file="./Excel/1.xlsx";
    	$arr_excel=getXmlToArray($file);
    	$this->assign("excel",$arr_excel);//excel数据
    	$this->assign("level5","active open");
    	$this->assign("level5_1","active");
		$this->display('undistributed_task');
    }
    
    //流程跟踪
    public function process_trace()
    {
    	//读取任务列表
    	$file="./Excel/1.xlsx";
    	$arr_excel=getXmlToArray($file);
    	foreach($arr_excel as $key=>$val){
    		$arr_excel[$key]['rand']=mt_rand(1,4);
    	}
    	$this->assign("excel",$arr_excel);//excel数据
    	$this->assign("level5","active open");
    	$this->assign("level5_2","active");
    	$this->display('process_trace');
    }
    
    //任务提交
    public function submit_task()
    {
    	//读取任务列表
    	$file="./Excel/1.xlsx";
    	$arr_excel=getXmlToArray($file);
    	$this->assign("excel",$arr_excel);//excel数据
    	$this->assign("level5","active open");
    	$this->assign("level5_3","active");
    	$this->display('submit_task');
    }
}
?>