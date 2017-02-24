<?php
defined('M_COM') || exit('No Permission');
class cls_cuschs extends cls_cuschsbase{
	
	// 楼盘分销推荐客户 列表 - 推荐状态
	protected function user_status(){
		$key = substr(__FUNCTION__,5);
		
		$field = $this->fields[$key]; 
		if(!$field || !in_array($field['datatype'],array('select','mselect','cacc',))) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		$a_field = new cls_field;
		$field['issearch'] = 1;//强制为可搜索字段
		$val = isset($GLOBALS[$key]) ? $GLOBALS[$key] : '-1'; 
		$a_field->init($field,$val);
		$a_field->deal_search($cfg['pre']);
		if(!empty($a_field->ft)) $this->filters += $a_field->ft;
		if(!empty($a_field->searchstr)) $this->wheres[$key] = $a_field->searchstr;
		unset($a_field);
		if(empty($cfg['hidden'])){
			$sarr = cls_field::options_simple($field,array('blank' => '&nbsp; &nbsp; '));
			$title = empty($cfg['title']) ? "-{$field['cname']}-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('' => $title) + $sarr,$val);
		}else $this->htmls[$key] = $this->input_hidden($key,@$GLOBALS[$key]);
	}
	
    protected function user_leixing(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);   
		if($this->nvalue[$key]){//针对性处理wherestr	
			$this->wheres[$key] = $cfg['pre']."leixing = '".$this->nvalue[$key]."'";
		}
        if(empty($cfg['hidden'])){
            $fields = cls_cache::Read("cufields",4);
			$arr = array('0'=>"-举报类型-") + cls_field::options($fields['leixing']);
    		$this->htmls[$key] = $this->input_select($key,$arr,$this->nvalue[$key]);
		}else{
		    $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]); 
		}
    }
    
    protected function user_state(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);       
		if($this->nvalue[$key] != -1 ){//针对性处理wherestr	
  	         $this->wheres[$key] = $cfg['pre']."state = '".$this->nvalue[$key]."'";
		}
        if(empty($cfg['hidden'])){
            $arr = array(-1=>'处理状态',0=>'未处理',1=>'已处理');           
    		$this->htmls[$key] = $this->input_select($key,$arr,$this->nvalue[$key]);
		}else{
		    $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]); 
		}
    }      
    
    //委托房源的委托状态
    protected function user_jjrstatus(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);       
		if($this->nvalue[$key] != -1 && $this->nvalue[$key] != 0){//针对性处理wherestr
            if($this->nvalue[$key] == 3){
                $this->wheres[$key] = $cfg['pre']."owerstatus = '0' AND ".$cfg['pre']."jjrstatus = '0'";
            }else{
                $this->wheres[$key] = $cfg['pre']."jjrstatus = '".$this->nvalue[$key]."'";
            }
		}
        if(empty($cfg['hidden'])){
            $arr = array(-1=>'委托状态',1=>'已拒绝委托',2=>'已接受委托',3=>'等待处理');           
    		$this->htmls[$key] = $this->input_select($key,$arr,$this->nvalue[$key]);
		}else{
		    $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]); 
		}
    } 
	  
    //楼盘按意向分类
    protected function user_lpdyfl($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);

		if($this->nvalue[$key] !== 0){
			$this->wheres[$key] = $cfg['pre']."dyfl LIKE "."'%".$this->nvalue[$key]."%'";
		 }

        if(empty($cfg['hidden'])){
            $arr = cls_field::options(cls_cache::Read('cufield', $this->A['cuid'], 'dyfl'));
            array_unshift($arr,'-意向分类-');
    		$this->htmls[$key] = $this->input_select($key,$arr,$this->nvalue[$key]);
		}else{
		    $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]); 
		}
	}
	
    /**
     * 用于搜索是否是真实的交互（比如报名信息，后台会自行添加，凡是后台添加的都是假信息） 
     */
    protected function user_istrue(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);         
		if($this->nvalue[$key] != -1 ){//针对性处理wherestr	
  	         $this->wheres[$key] = $cfg['pre']."istrue = '".$this->nvalue[$key]."'";
		}   
        $arr = array(-1=>'--真假信息--',0=>'虚假信息',1=>'真实信息');           
		$this->htmls[$key] = $this->input_select($key,$arr,$this->nvalue[$key]);
    }      
}
