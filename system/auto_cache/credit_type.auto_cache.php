<?php
//商城的导航
class credit_type_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$credit_type = $GLOBALS['fcache']->get($key);
		if($credit_type === false)
		{
			$temp_credit_type = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_credit_type where is_effect = 1 order by sort ASC");
			$credit_type = array();
			foreach($temp_credit_type as $k=>$v){
				$credit_type['list'][$v['type']] =  $v;
				$credit_type['type'][] = $v['type'];
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$credit_type);
		}
		return $credit_type;
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['fcache']->rm($key);
	}
	public function clear_all()
	{
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['fcache']->clear();
	}
}
?>