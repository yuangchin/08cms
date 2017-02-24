<?php
/**
 * 不良词
 *
 * @example   请求范例URL：/index.php?/ajax/badwords
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Badwords_Base extends _08_Models_Base
{
    
	private $badwords = array();
	
	public function __toString()
    {
        $bwcfgs = cls_cache::Read('badwords');
		$this->badwords = empty($bwcfgs['wsearch']) ? array() : $bwcfgs['wsearch'];
		$act = empty($this->_get['act']) ? 'get' : $this->_get['act'];
		return $this->$act(); //act=get,reg,editor,match
    }
	
	// 获取不良词
	public function get(){
		$re = array();
		foreach($this->badwords as $key){
			$re[] = str_replace(array("<`/","/i`>"),'',"<`$key`>");	
		}
		return $re;
	}
	// ajax检测不良词
	public function reg(){
		$res = $this->_match();
		$str = empty($res) ? '' : '含有不良关键词: '.implode(',',$res);
		return $str;
	}
	// text检测不良词
	public function text(){
		$res = $this->_match();
		$str = empty($res) ? '' : "\n".implode("\n",$res);
		return $str;
	}
	// 匹配不良词
	public function match(){
		return $this->_match();
	}
	
	// 匹配关键字
	public function _match(){
		$mcharset = cls_env::getBaseIncConfigs('mcharset'); 
		$source = @$this->_get['content'];
		$source = cls_string::iconv('UTF-8', $mcharset, $source);
		$res = array();
        if(!empty($this->badwords)){ 
			foreach($this->badwords as $kw){
				preg_match($kw,$source,$re1);
				if(!empty($re1[0])) $res[] = $re1[0]; 
			}
			return $res;
        }
	}
	
}

/*

*/
