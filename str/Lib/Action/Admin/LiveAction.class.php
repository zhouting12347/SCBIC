<?php
class LiveAction extends CommonAction
{
	//直播信息录入
    public function live_form()
    {
    	$this->assign("level6","active open");
    	$this->assign("level6_1","active");
		$this->display('live_form');
    }
    
    //直播信息更改
    public function live_change()
    {
    	$this->assign("level6","active open");
    	$this->assign("level6_2","active");
    	$this->display('live_change');
    }
    
    //赠品安排
    public function freebie()
    {
    	$this->assign("level6","active open");
    	$this->assign("level6_3","active");
    	$this->display('freebie');
    }
    
    //生产直播单
    public function export_xml()
    {
    	$this->assign("level6","active open");
    	$this->assign("level6_4","active");
    	$this->display('export_xml');
    }
    
}
?>