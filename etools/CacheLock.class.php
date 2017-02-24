<?php
/**
 * CacheLock 进程锁,主要用来进行cache失效时的单进程cache获取，防止过多的SQL请求穿透到数据库
 * 用于解决PHP在并发时候的锁控制,通过文件/eaccelerator进行进程间锁定
 * 如果没有使用eaccelerator则进行进行文件锁处理，会做对应目录下产生对应粒度的锁
 * 使用了eaccelerator则在内存中处理，性能相对较高
 * 不同的锁之间并行执行，类似mysql innodb的行级锁
 * 本类在sunli的phplock的基础上做了少许修改  http://code.google.com/p/phplock 
 * @author yangxinqi
 *
 */
class CacheLock
{
	//文件锁存放路径
	private $path = null;
	//文件句柄
	private $fp = null;
	//锁粒度,设置越大粒度越小
	private $hashNum = 256;
	//cache key 
	private $name;
	//是否存在eaccelerator标志
	private  $eAccelerator = false;
	
	/**
	 * 构造函数
	 * 传入锁的存放路径，及cache key的名称，这样可以进行并发
	 * @param string $path 锁的存放目录，以"/"结尾
	 * @param string $name cache key
	 */
	public function __construct($name, $path = ''){
		//判断是否存在eAccelerator,这里启用了eAccelerator之后可以进行内存锁提高效率
		$this->eAccelerator = function_exists("eaccelerator_lock");
		if(!$this->eAccelerator){
			if(!$path)$this->mkdir($path = M_ROOT . 'dynamic/cachelock');
			$this->path = "$path/" . ($this->_mycrc32($name) % $this->hashNum) . '.lock';
		}
		$this->name = $name;
	}
	
	/**
	 * crc32
	 * crc32封装
	 * @param int $string
	 * @return int
	 */
	private function _mycrc32($string){
		$crc = abs(crc32($string));
		if($crc & 0x80000000){
			$crc ^= 0xffffffff;
			$crc += 1;
		}
		return $crc;
	}
	/**
	 * mkdir
	 * 路径创建
	 * @param string $path
	 * @return int
	 */
	private function mkdir($path){
		if(is_dir($path))return true;
		if(!$this->mkdir(dirname($path)) || @!mkdir($path,0777))return false;
		return true;
	}
	/**
	 * 加锁
	 * Enter description here ...
	 */
	public function lock(){
		//如果无法开启ea内存锁，则开启文件锁
		if(!$this->eAccelerator){
			//配置目录权限可写
			$this->fp = fopen($this->path, 'w+');
			if($this->fp === false)return false;
			return flock($this->fp, LOCK_EX);
		}else{
			return eaccelerator_lock($this->name);
		}
	}
	
	/**
	 * 解锁
	 * Enter description here ...
	 */
	public function unlock(){
		if(!$this->eAccelerator){
			if($this->fp !== false){
				flock($this->fp, LOCK_UN);
				clearstatcache();
				//进行关闭
				fclose($this->fp);
#				unlink($this->path);
				return true;
			}
		}else{
			return eaccelerator_unlock($this->name);
		}
	}
}
?>
