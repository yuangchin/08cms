<?php
defined('M_COM') || exit('No Permisson');
class cls_fieldDecorator
{
	private $field;
		
	public function __construct( cls_field $field )
    {
        $this->field = $field;
    }
	
	public function getField(){
		return $this->field;	
	}
	
	public function trfield($varpre,$custom=array()){
		$varr = $this->varr($varpre,$custom);
		$this->trspecial($varr);
	}
	
	protected function varr($varpre='',$custom=array()){
		$field = $this->getField();
		if(empty($field->field['ename']) || empty($field->field['available'])) return array();		
		$varname = $field->_varname($varpre);				
		foreach(array('datatype','mode','guide','min','max','cnmode','wmid', 'filter', 'editor_height',
                      'rpid', 'auto_page_size', 'auto_compression_width') as $var)
		{
			if(isset($field->field[$var])) {
				$$var = $field->field[$var];
			} else {
				$$var = '';
			}
		}		
		$ret = array();            
        $configs = array('type' =>$datatype,'varname' => $varname);
        if(in_array($datatype, array('image', 'images'), true)){
            	$configs['auto_compression_width'] = $auto_compression_width;
            	$configs['wmid'] = $wmid;				
				$configs['isSingle'] = !empty($custom['isSingle']) ? 1 : ($datatype=='image' ? 1 : 0);
        }
		$ret += $this->specialarr(array_merge($configs,$custom));
		return $ret;
	}
	
	protected function trspecial($varr){
		echo $varr['frmcell'];
	}
	
	protected function specialarr($cfg = array()){
		foreach(array('validator','value','guide','code',) as $v) $$v = '';
		foreach(array('mode','min','max','coid','source','vmode','smode','wmid', 'filter', 'editor_height', 'rpid', 'auto_compression_width') as $v)
		{
			$$v = 0;
		}
		extract($cfg, EXTR_OVERWRITE);
		$ret = array('type' => $type,'varname' => $varname,'frmcell' => '','mode');
		
		if(in_array($type,array('image', 'images'))){			
			$ret['frmcell'] = self::showButton($cfg);
		}
		return $ret;
	}

	protected static function showButton(array $config){
		$cms_abs = cls_env::mconfig('cms_abs');				
		$varname = $config['varname'];
		$host = cls_env::TopDomain($cms_abs); 
		$host && $host = $_SERVER['SERVER_NAME']; // html5下上传,加上domain参数
		$upload_url = array('upload'=>'post','wmid'=>$config['wmid'],'auto_compression_width'=>$config['auto_compression_width'],'domain'=>$host,);
		$upload_config = array(
		'url'=> $cms_abs . _08_Http_Request::uri2MVC($upload_url),
		'num'=>empty($config['num'])?8:$config['num'],
		'isSingle'=>empty($config['isSingle']) ? 0 : 1,	
		);
		$uploadToJson = json_encode($upload_config);
		
		$buttonString = <<<HTML
				
			<ul class="_08_upload_list">
				<li class="_08_upload_action">
                   <input type="file" accept="image/jpg,image/jpeg,image/png,image/gif"  value=""/>
				   <input type="hidden" name="{$varname}" />
                </li>		
            </ul>
			<script type="text/javascript"> setTimeout('(new _08_uploadHTML5({$uploadToJson})).init()',1000)</script>
HTML;
	return $buttonString;
	}


}