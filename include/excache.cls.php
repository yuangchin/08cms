<?
!defined('M_COM') && exit('No Permisson');
define('EX_CACHE_EXPIRE',3000);
class cls_excache{
	public $enable = false;
	public $obj;
	public $keys;
	public $lastclearexpire = 0;
    public $__cache_type;
    private static $_Instance = NULL;			# 单例模式
	
	final public static function OneInstance(){
        if(!(self::$_Instance instanceof self)){
			self::$_Instance = new self();
		}
		return self::$_Instance;
	}	
    
	function __construct(){
		global $ex_memcache_server,$ex_memcache_port,$ex_memcache_pconnect,$ex_memcache_timeout,$ex_eaccelerator,$ex_xcache,$ex_secache,$ex_secache_size;
		if(extension_loaded('memcache') && !empty($ex_memcache_server)){
			$this->obj = new ex_memcache();
			$this->obj->init(array('server' => $ex_memcache_server,'port' => $ex_memcache_port,'pconnect' => $ex_memcache_pconnect,'timeout' => $ex_memcache_timeout,));
			$this->obj->enable || $this->obj = null;
            $this->__cache_type = 'Memcached';
		}
		if(!is_object($this->obj) && function_exists('eaccelerator_get') && $ex_eaccelerator){
			$this->obj = new ex_eaccelerator();
			$this->obj->init();            
            $this->__cache_type = 'eAccelerator';
		}
		if(!is_object($this->obj) && function_exists('xcache_get') && $ex_xcache){
			$this->obj = new ex_xcache();
			$this->obj->init();
            $this->__cache_type = 'xcache';
		}
		if(!is_object($this->obj) && $ex_secache){
			$this->obj = new ex_secache();
			$this->obj->init(array('size' => $ex_secache_size,'datafile' => M_ROOT."dynamic/secache/cachedata",));
			$this->obj->enable || $this->obj = null;
            $this->__cache_type = 'secache';
		}
		
		$this->keys = array();
		if(is_object($this->obj)){
			$this->enable = true;
			$this->type = str_replace('ex_','',get_class($this->obj));
			$this->keys = $this->get('ex_cache_keys');
			is_array($this->keys) || $this->keys = array();
			if(!($this->lastclearexpire = $this->get('lastclearexpire'))) $this->lastclearexpire = 0;
		}
	}
	function get($key){
		global $timestamp;
		$ret = null;
		if($this->enable && ($key == 'ex_cache_keys' || (isset($this->keys[$key]) && (!$this->keys[$key] || $this->keys[$key] > $timestamp)))){
			$ret = $this->obj->get($this->_key($key));
			if(!is_array($ret)){
				$ret = null;
			}else return $ret[0];
		}
		return $ret;
	}
	function set($key, $value, $ttl = 0){
		global $timestamp;
		$ret = null;
		if($this->enable){
			if($ret = $this->obj->set($this->_key($key), array($value), $ttl)){
				if($timestamp - $this->lastclearexpire > EX_CACHE_EXPIRE) $this->clearexpire();
				$this->keys[$key] = $ttl ? $timestamp + $ttl : 0;
				$this->obj->set($this->_key('ex_cache_keys'),array($this->keys));
			}
		}
		return $ret;
	}
	function clearexpire(){
		global $timestamp;
		foreach ($this->keys as $k => $v){
			if($v && $timestamp > $v){
				$this->type == 'secache' && $this->obj->rm($this->_key($k));
				unset($this->keys[$k]);
			}
		}
		$this->lastclearexpire = $timestamp + EX_CACHE_EXPIRE;
		$this->set('lastclearexpire',$timestamp);
	}
	function rm($key){
		$ret = null;
		if($this->enable){
			$ret = $this->obj->rm($this->_key($key));
			if($ret){
				unset($this->keys[$key]);
				$this->obj->set($this->_key('ex_cache_keys'), array($this->keys));
			}
		}
		return $ret;
	}
	function clear(){
		if($this->enable && is_array($this->keys)){
			$this->keys['ex_cache_keys'] = 0;
			foreach ($this->keys as $k => $v){
				$this->obj->rm($this->_key($k));
			}
		}
		$this->keys = array();
		return true;
	}
	function _key($str){
		global $excache_prefix;
		return (empty($excache_prefix) ? substr(md5($_SERVER['HTTP_HOST']),0,6).'_' : $excache_prefix).$str;
	}
	
}
class ex_memcache{
	var $enable;
	var $obj;
	function ex_memcache(){
	}
	function init($cfg){
		if(!empty($cfg['server'])){
			$this->obj = new Memcache;
			$connect = $cfg['pconnect'] ? @$this->obj->pconnect($cfg['server'],$cfg['port'],$cfg['timeout']) : @$this->obj->connect($cfg['server'],$cfg['port'],$cfg['timeout']);
			$this->enable = $connect ? true : false;
		}
	}
	function get($key) {
		return $this->obj->get($key);
	}
	function set($key, $value, $ttl = 0) {
		return $this->obj->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
	}
	function rm($key) {
		return $this->obj->delete($key);
	}
}
class ex_eaccelerator{
	function ex_eaccelerator(){
	}
	function init($cfg = array()){
	}
	function get($key){
		return eaccelerator_get($key);
	}
	function set($key, $value, $ttl = 0){
		return eaccelerator_put($key, $value, $ttl);
	}
	function rm($key){
		return eaccelerator_rm($key);
	}
}
class ex_xcache{
	function ex_xcache(){
	}
	function init($cfg = array()){
	}
	function get($key){
		return xcache_get($key);
	}
	function set($key, $value, $ttl = 0) {
		return xcache_set($key, $value, $ttl);
	}
	function rm($key) {
		return xcache_unset($key);
	}
}
class ex_secache{
	var $enable;
	var $obj;
	function ex_secache(){
	}
	function init($cfg) {
		if(!empty($cfg['size']) && !empty($cfg['datafile'])){
    		define('SECACHE_SIZE',$cfg['size'].'M');
			require(M_ROOT."include/secache.cls.php");
			$this->obj = new secache;
			$this->obj->workat($cfg['datafile']);
			$this->enable = true;
		}
	}
	function get($key){
		return $this->obj->fetch($this->_key($key),$value) ? $value : false;
	}
	function set($key, $value, $ttl = 0){
		return $this->obj->store($this->_key($key),$value);
	}
	function rm($key){
		return $this->obj->delete($this->_key($key));
	}
	function _key($str){
		return md5($str);
	}
}
