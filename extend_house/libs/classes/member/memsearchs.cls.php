<?php
class cls_memsearchs extends cls_memsearchsbase{
    //多少天内失效（高级经纪人/VIP装修公司/VIP品牌商家）
   	protected function user_gtype_enddate(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
        $_field = 'grouptype'.$cfg['groupnum'].'date';
		if($this->nvalue[$key]){//针对性处理wherestr
			global $timestamp;
			$this->wheres[$key] = $cfg['pre'].$_field.">='".$timestamp."'".' AND '.$cfg['pre'].$_field."<='".($timestamp + 86400 * $this->nvalue[$key])."'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "天内失效" : $cfg['title'];
			$this->htmls[$key] = $this->input_text($key,$this->nvalue[$key],'注册时间',2).$title;
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
}
