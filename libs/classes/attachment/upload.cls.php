<?php
defined('M_COM') || exit('No Permission');
class cls_upload
{    
    /**
     * 未知错误
     * 
     * @var int
     */
    const ERROR_UNKNOW = -1;
    
    /**
     * 本地上传方案为空。
     * 
     * @var int
     */
    const ERROR_PROGRAM_NOT_FOUND = 1;
    
    /**
     * 上传文件类型不对。
     * 
     * @var int
     */
    const ERROR_FILE_TYPE = 2;
    
    /**
     * 超出该文件类型大小限制。
     * 
     * @var int
     */
    const ERROR_FILE_SIZE_LIMIT = 3;
    
    /**
     * 硬盘空间不够。
     * 
     * @var int
     */
    const ERROR_DISK_SPACE_LIMIT = 4;
    
    /**
     * 文件无效。
     * 
     * @var int
     */
    const ERROR_FILE_INVALID = 5;
    
    /**
     * 不存在要上传的文件。
     * 
     * @var int
     */
    const ERROR_FILE_NOT_FOUND = 6;
    
    /**
     * 超过系统允许的空间大小
     * 
     * @var int
     */
    const ERROR_USER_SPACE_LIMIT = 7;
    
    /**
     * 上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。
     * 
     * @var int
     */
    const ERROR_INI_SIZE_LIMIT = 8;
    
    /**
     * 文件只有部分被上传。
     * 
     * @var int
     */
    const ERROR_PARTIAL = 9;
    
    /**
     * 上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
     * 
     * @var int
     */
    const ERROR_FORM_SIZE_LIMIT = 10;
    
    /**
     * 找不到临时文件夹。
     * 
     * @var int
     */
    const ERROR_NO_TMP_DIR = 11;
    
    /**
     * 文件写入失败。
     * 
     * @var int
     */
    const ERROR_CANT_WRITE = 12;
    
    /**
     * 远程方案为空。
     */
    const ERROR_REMOTE_PROGRAM_NOT_FOUND = 13;
    
    /**
     * 请不要上传有害的文件。
     */
    const ERROR_BAD_FILE = 14;
    
	var $current_dir = '';//被指定的上传保存路径,用于通过文件管理器中上传文件,格式为/xxx/
	var $ufids = array();//记录上传的文件id
	var $upload_size = 0;//记录上传及缩图的文件大小(K)
	var $capacity;//会员上传空间余量(K),-1为不限
    protected static $_file = null;
    protected static $_Instance = NULL;			# 单例模式
    
    /**
     * 上传资源数组
     * 
     * @var   array
     * @since nv50
     */
    private $result = array();
    
    /**
     * 上传类型，值目前支持 upload和base64
     * 
     * @var   string
     * @since nv50
     */
    private $type;
    
    /**
     * 要上传的文件类型
     * 
     * @var   string
     * @since nv50
     */
    private $file_type = 'image';
    
    private $lfile_types = array();
    
    /**
     * 本地上传方案数组
     * 
     * @var   array
     * @since nv50
     */
    private $localfile = array();
    
    /**
     * 配置信息
     * 
     * @var   array
     * @since nv50
     */
    private $configs = array();
    
    /**
     * 当前用户对象句柄
     * 
     * @var   object
     * @since nv50
     */
    private $curuser = null;
	
	final public static function OneInstance(){
        if(!(self::$_Instance instanceof self)){
			self::$_Instance = new self();
		}
		return self::$_Instance;
	}	
	
	public function __construct()
    {
		$this->init();
	}
    
	public function init()
    {
		$this->curuser = cls_UserMain::CurUser();
		$this->current_dir = '';
		$this->ufids = array();
		$this->upload_size = 0;
		$this->capacity = $this->curuser->upload_capacity();
        self::$_file = _08_FilesystemFile::getInstance();
        $this->result = array();
        $this->type = 'upload';
        $this->configs = $this->lfile_types = array();
	}
    
    /**
     * 本地文件上传
     * 
     * @param  string $localname 表单文件域名称
     * @param  string $file_type 文件类型
     * @param  int    $configs   配置数组，目前带有：水印方案ID、自动压缩宽度
     * @return array             返回上传后的状态数组
     * 
     * @since  nv50
     */
	public function local_upload($localname,$file_type='image', $configs = array())
    {
		$uploadfile = array();
		$file_saved = false;
        $this->result['error'] = 0;
        $this->file_type = $file_type;
		$wmid = (is_array($configs) && isset($configs['wmid'])) ? (int) $configs['wmid'] : (int) $configs;
        
        if ( !$this->checkProgram() )
        {
            return $this->result;
        }
        
        $method = $this->type;
        $uploadfile = $this->$method($localname);
        
        if ( empty($uploadfile) )
        {
            if ( empty($this->result['error']) )
            {
                $this->result['error'] = self::ERROR_FILE_NOT_FOUND; //'不存在要上传的文件。!'
            }
            
            return $this->result;
        }
        
		$uploadfile['mid'] = $this->curuser->info['mid'];
		$uploadfile['mname'] = $this->curuser->info['mname'];
        
		if( in_array($this->result['extension'], array('jpg','jpeg','gif','png','swf','bmp')) )
        {
            # 获取文件的宽度与高度
            $infos = @getimagesize($uploadfile['target']);
			if( isset($infos[0]) && isset($infos[1]) )
            {
				$this->result['width'] = $infos[0];
				$this->result['height'] = $infos[1];
			}
            else 
            {
				self::$_file->delFile($uploadfile['target']);
				$this->result['error'] = self::ERROR_FILE_INVALID;//'无效的图片上传!'
				return $this->result;
            }
            
            # 自动压缩图片大小
			$auto_compression_width = (isset($configs['auto_compression_width']) && intval($configs['auto_compression_width'])) ? (int) $configs['auto_compression_width'] : 0;
            $this->auto_compression($auto_compression_width,$uploadfile['target']);
            
            # 给图片打水印
			if( in_array($this->result['extension'], array('jpg', 'jpeg', 'gif', 'png', 'bmp')) && 
                $this->image_watermark($uploadfile['target'], $wmid) )
            {
				$this->result['size'] = filesize($uploadfile['target']);
			}
		}
        
        # 移动文件到FTP
		if(cls_url::is_remote_atm($uploadfile['url']))
        {
			include_once M_ROOT."include/ftp.fun.php";
			ftp_upload($uploadfile['target'],$uploadfile['url']);
		}
        
        # 更新会员允许的空间值
		$this->upload_size += ceil($this->result['size'] / 1024);
		if($this->capacity != -1)
        {
			$this->capacity -= ceil($this->result['size'] / 1024);
			$this->capacity = max(0,$this->capacity);
		}
		$this->result['remote'] = $uploadfile['url'];
        $insertData = array('filename' => $uploadfile['filename'], 'url' => $uploadfile['url'], 'type' => $this->file_type, 
                            'createdate' => TIMESTAMP, 'mid' => $uploadfile['mid'], 'mname' => $uploadfile['mname'], 
                            'size' => $this->result['size']);
        $db = _08_factory::getDBO();
        $db->insert( '#__userfiles', $insertData )->exec();
		if($ufid = $db->insert_id()) $this->ufids[] = $ufid;
		$this->result['ufid'] = $ufid;
		unset($uploadfile);
		return $this->result;
	}
	 /**
     * 自动压缩图片尺寸
     * @param  int $auto_compression_width 压缩尺寸(长宽同时限制)
     * @param  string $target  图片地址
     * @since  nv50
     */
	protected function auto_compression($auto_compression_width,$target){
		if ($auto_compression_width && in_array($this->file_type, array('image', 'images'), true) && 
               ($auto_compression_width < $this->result['width'] || $auto_compression_width < $this->result['height']))
            {
				if($this->result['width']>$this->result['height']){
					$auto_compression_height = ceil($auto_compression_width * $this->result['height'] / $this->result['width']);                    
					$this->image_resize($target, $auto_compression_width, $auto_compression_height, $target);
					$this->result['width'] = $auto_compression_width;
					$this->result['height'] = $auto_compression_height;  
					$this->result['size'] = filesize($target);   
				}else{
					$auto_compression_height = ceil($auto_compression_width * $this->result['width'] / $this->result['height']);
					$this->image_resize($target, $auto_compression_height, $auto_compression_width, $target);
					$this->result['width'] = $auto_compression_height;
					$this->result['height'] = $auto_compression_width;  
					$this->result['size'] = filesize($target);
				}  
            }
	}
    
    /**
     * 使用 $_FILES 的方式上传文件
     */
    public function upload($localname)
    {
		if(empty($_FILES[$localname]) || !mis_uploaded_file($_FILES[$localname]['tmp_name']) || 
           !$_FILES[$localname]['tmp_name'] || !$_FILES[$localname]['name'] || $_FILES[$localname]['tmp_name'] == 'none')
        {
			$this->result['error'] = self::ERROR_FILE_NOT_FOUND;//'要上传的文件不存在!'
			return false;
		}
        
        $uploadfile = $_FILES[$localname];
        if ( !empty($uploadfile['error']) )
        {
            $this->setErrorCode($uploadfile['error']);
			return false;
        }
        
		$this->result['extension'] = strtolower(mextension($uploadfile['name']));
        $this->result['original'] = $uploadfile['name'];
        $this->result['size'] = $uploadfile['size'];
        
        // 判断是否超过用户允许的空间大小、与上传方案允许的大小和扩展名
        if ( $this->checkUserSpaceSizeLimit($uploadfile['size']) || !$this->checkExt() || !$this->checkSize() )
        {
            @unlink($uploadfile['tmp_name']);
            return false;
        }
        
        $uploadfile['filename'] = $this->getFileName( addslashes($uploadfile['name']) );
		$uploadpath = $this->upload_path($this->file_type);
		$uploadfile['url'] = $uploadpath.$uploadfile['filename'];
		$uploadfile['target'] = M_ROOT.$uploadpath.$uploadfile['filename'];
		@chmod($uploadfile['target'], 0644);
        
        if ( false === @move_uploaded_file($uploadfile['tmp_name'], $uploadfile['target']) )
        {
            @copy($uploadfile['tmp_name'], $uploadfile['target']);
            @unlink($uploadfile['tmp_name']);
        }
        
        return $uploadfile;
    }
    
    /**
     * 使用 BASE64 的方式上传文件
     */
    public function base64($localname)
    {
        $uploadfile = array();
        $post = cls_env::_POST($localname);
        if ( !empty($post[$localname]) )
        {
            $localfile = $post[$localname];
            $needle = 'base64,';
            if ( false !== strpos($localfile, $needle) )
            {
                $localfile = substr($localfile, strpos($localfile, $needle) + strlen($needle));
            }
            $localfile = base64_decode($localfile);
            if (preg_match('/(\$_POST|\$_GET|<\?php|<%.*?%>)/i', $localfile))
            {
    			$this->result['error'] = self::ERROR_BAD_FILE;// 有害文件
    			return false;
            }
            
            if ( isset($this->configs['oriName']) )
            {
                _08_FilesystemFile::filterFileParam($this->configs['oriName']);
                $this->result['original'] = trim($this->configs['oriName']);
                $this->result['extension'] = strtolower(mextension($this->result['original']));
            }
            else
            {
            	$this->result['original'] = $this->result['extension'] = '';
            }
            $this->result['size'] = strlen($localfile);            
        
            // 判断是否超过用户允许的空间大小、与上传方案允许的大小和扩展名
            if ( $this->checkUserSpaceSizeLimit($this->result['size']) || !$this->checkExt() || !$this->checkSize() )
            {
                return false;
            }
            
            $uploadfile['filename'] = $this->getFileName( addslashes($this->result['original']) );        
    		$uploadpath = $this->upload_path($this->file_type);
    		$uploadfile['url'] = $uploadpath.$uploadfile['filename'];
    		$uploadfile['target'] = M_ROOT.$uploadpath.$uploadfile['filename'];
            @chmod($uploadfile['target'], 0644);
            if ( self::$_file->_fopen($uploadfile['target'], 'wb') )
            {
                self::$_file->_fwrite($localfile);
                self::$_file->_fclose();
            }       
        }
        
        return $uploadfile;
    }
    
    /**
     * 设置上传错误代码
     * 
     * @param int $code 状态代码
     * 
     * @since nv50
     */
    public function setErrorCode( $code )
    {
        $code = (int) $code;
        switch($code)
        {
            # 上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。
            case UPLOAD_ERR_INI_SIZE: $this->result['error'] = self::ERROR_INI_SIZE_LIMIT; break;
            
            # 上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
            case UPLOAD_ERR_FORM_SIZE: $this->result['error'] = self::ERROR_FORM_SIZE_LIMIT; break;
            
            # 文件只有部分被上传。
            case UPLOAD_ERR_PARTIAL : $this->result['error'] = self::ERROR_PARTIAL; break;
            
            # 没有文件被上传。
            case UPLOAD_ERR_NO_FILE: $this->result['error'] = self::ERROR_FILE_NOT_FOUND; break;
            
            # 找不到临时文件夹。
            case UPLOAD_ERR_NO_TMP_DIR: $this->result['error'] = self::ERROR_NO_TMP_DIR; break;
            
            # 文件写入失败。
            case UPLOAD_ERR_CANT_WRITE: $this->result['error'] = self::ERROR_CANT_WRITE; break;
            
            default: $this->result['error'] = self::ERROR_UNKNOW; break;
        }
    }
    
    /**
     * 检测文件大小
     * 
     * @return bool $status 如果检测通过返回TRUE，否则返回FALSE
     * 
     * @since  nv50
     */
    public function checkSize()
    {
        $status = true;
        
        if ( $this->result['size'] > disk_free_space(M_ROOT) )
        {
			$this->result['error'] = self::ERROR_DISK_SPACE_LIMIT;//'硬盘空间不够!'
            $status = false;            
        }
        
        if( !empty($this->localfile[$this->result['extension']]['minisize']) && 
           ($this->result['size'] < 1024 * $this->localfile[$this->result['extension']]['minisize']) )
        {
			$this->result['error'] = self::ERROR_FILE_SIZE_LIMIT;//'超出该文件类型大小限制!'
            $status = false;
		}
        
		if( !empty($this->localfile[$this->result['extension']]['maxsize']) && 
            ($this->result['size'] > 1024 * $this->localfile[$this->result['extension']]['maxsize']) )
        {
			$this->result['error'] = self::ERROR_FILE_SIZE_LIMIT;//'超出该文件类型大小限制!'
            $status = false;
		}
        
        return $status;
    }

    /**
     * 检测上传方案
     * 
     * @return bool 方案存在时返回TRUE，否则返回FALSE
     * 
     * @since  nv50
     */
    public function checkProgram()
    {
        $status = true;
        $this->localfile = cls_atm::getLocalFilesExts($this->file_type);
        if ( !empty($this->lfile_types) )
        {
            foreach ( (array) $this->lfile_types as $type ) 
            {
                if ( !isset($this->lfile_types[$this->file_type]) )
                {
                    $this->localfile = array_merge($this->localfile, cls_atm::getLocalFilesExts($type));
                }
            }
        }
        
        if ( empty($this->localfile) )
        {
            $status = false;
            # 上传方案不存在
            $this->result['error'] = self::ERROR_PROGRAM_NOT_FOUND;
        }
        
        return $status;
    }
    
    /**
     * 检查文件扩展
     */
    public function checkExt()
    {
        # 获取系统设置－网站参数－附件设置里游客允许上传附件类型        
        $nouser_exts = cls_env::mconfig('nouser_exts');
        if(!in_array($this->result['extension'], array_keys($this->localfile))){//文件类型不在本地上传方案中
			$this->result['error'] = self::ERROR_FILE_TYPE;//'禁止上传文件类型!'
		}
        
        # 检测游客允许上传附件类型
		if (!empty($nouser_exts) && 
            empty($this->curuser->info['mid']) && 
            !in_array($this->result['extension'], explode(',',@$nouser_exts), true)
        ) {
			$this->result['error'] = self::ERROR_FILE_TYPE;//'禁止上传文件类型!'
		}
        
        return empty($this->result['error']) ? true : false;
    }
    
    /**
     * 检测用户空间大小限制
     * 
     * @param  int  $size 文件大小
     * @return bool       如果超过了大小返回TRUE，否则返回FALSE
     * 
     * @since  nv50
     */
    public function checkUserSpaceSizeLimit( $size )
    {
        if ( ($this->capacity != -1) && ($size > 1024 * $this->capacity) )
        {
            $this->result['error'] = self::ERROR_USER_SPACE_LIMIT;
            return true;
        }
			
        return false;
    }
    
    /**
     * 获取当前上传错误信息
     * 
     * @return string $error_message 如果有错误时返回错误信息，否则返回空字符串
     * 
     * @since  nv50
     */
    public function getErrorMessage()
    {
        $error_message = '';
        $this->result['error'] = (empty($this->result['error']) ? 0 : (int) $this->result['error']);
        
        if ( in_array($this->result['error'], array(self::ERROR_FILE_SIZE_LIMIT, self::ERROR_USER_SPACE_LIMIT)) )
        {
            if ( !empty($this->localfile[$this->result['extension']]['maxsize']) && 
                 !empty($this->localfile[$this->result['extension']]['minisize']) )
            {
                $maxsize = $this->localfile[$this->result['extension']]['maxsize'];
                $minsize = $this->localfile[$this->result['extension']]['minisize'];
                $size_message = "允许的范围是：{$minsize} - {$maxsize}KB";
            }
            else
            {
            	$size_message = '';
            }
        }
        
        if ( $this->result['error'] )
        {
            switch($this->result['error'])
            {
                case self::ERROR_PROGRAM_NOT_FOUND: $error_message = '本地上传方案为空，请先在后台系统设置－方案管理－上传方案里设置。'; break;
                case self::ERROR_FILE_TYPE: $error_message = '上传文件类型不对。'; break;
                case self::ERROR_FILE_SIZE_LIMIT: $error_message = '超出该文件类型大小限制。' . $size_message; break;
                case self::ERROR_DISK_SPACE_LIMIT: $error_message = '硬盘空间不够。'; break;
                case self::ERROR_FILE_INVALID: $error_message = '文件无效。'; break;
                case self::ERROR_FILE_NOT_FOUND: $error_message = '不存在要上传的文件。'; break;
                case self::ERROR_USER_SPACE_LIMIT: $error_message = '超过系统允许的空间大小。' . $size_message; break;
                case self::ERROR_INI_SIZE_LIMIT: $error_message = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。'; break;
                case self::ERROR_PARTIAL: $error_message = '文件只有部分被上传。'; break;
                case self::ERROR_FORM_SIZE_LIMIT: $error_message = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。'; break;
                case self::ERROR_NO_TMP_DIR: $error_message = '找不到临时文件夹。'; break;
                case self::ERROR_CANT_WRITE: $error_message = '文件写入失败。'; break;
                case self::ERROR_REMOTE_PROGRAM_NOT_FOUND: $error_message = '远程方案为空，请先在后台系统设置－方案管理－远程下载里设置。'; break;
                case self::ERROR_BAD_FILE: $error_message = '请不要上传有害的文件。'; break;
                default: $error_message = '未知错误。'; break;
            }
        }
        return $error_message;
    }
    
    /**
     * 获取上传文件名称
     * 
     * @param  string $localfilename 本地文件名
     * @return string $filename      返回一个新的文件名称
     * 
     * @since  nv50
     */
    public function getFileName( $localfilename = '' )
    {
        $localfilename = ($localfilename ? $localfilename : cls_env::GetLicense());
        $filename = (date('dHis').substr(md5($localfilename.microtime()),5,10).cls_string::Random(4,1) . '.' . $this->result['extension']);
    	$filename = preg_replace("/(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)(\.|$)/i", "_\\1\\2", $filename);
        
        return $filename;
    }
    
    /**
     * 获取远程附件，目前只适用于单个远程附件
     * 
     * @param  string $remotefile 远程附件地址
     * @param  int    $rpid       远程方案ID
     * @param  int    $configs   配置数组，目前带有：水印方案ID、自动压缩宽度
     * @return array              返回获取到的远程附件新本地化地址信息数组，如果出错会返回带有原附件地址的信息数组
     * 
     * @since  nv50
     */
	public function remote_upload($remotefile,$rpid = 0, $configs = array())
    {
		$curuser = cls_UserMain::CurUser();
		$this->result = array('remote' => $remotefile, 'error' => 0);
		if(!$this->capacity) return $this->result;
		$rprojects = cls_cache::Read('rprojects');
        $rpid = (int) $rpid;             
        $wmid = (is_array($configs) && isset($configs['wmid'])) ? (int) $configs['wmid'] : (int) $configs;
		 
		if(empty($rpid) || empty($rprojects[$rpid]['rmfiles']))
        {
            $this->result['error'] = self::ERROR_REMOTE_PROGRAM_NOT_FOUND;
            return $this->result;
        }
		if(cls_url::islocal($remotefile,1)) return $this->result;
		if(!empty($rprojects[$rpid]['excludes'])){
			foreach($rprojects[$rpid]['excludes'] as $k){
				if(in_str($k,$remotefile))
                {
                    $this->result['error'] = self::ERROR_REMOTE_PROGRAM_NOT_FOUND;
                    return $this->result;
                }
			}
		}
		$this->localfile = $rprojects[$rpid]['rmfiles'];
		$extension = strtolower(mextension($remotefile));
		if(in_array($extension,array_keys($this->localfile))){
			$rmfile = $this->localfile[$extension];
		}
        else
        {
            $this->result['error'] = self::ERROR_FILE_TYPE;
            return $this->result;
        }
		
		$uploadfile = array();
		$uploadfile['mid'] = $curuser->info['mid'];
		$uploadfile['mname'] = $curuser->info['mname'];
		$file_saved = false;
        $this->result['extension'] = $rmfile['extname'];
        $this->result['original'] = $remotefile;
		$uploadfile['filename'] = $this->getFileName($remotefile);
		$uploadpath = $this->upload_path($rmfile['ftype']);
		$uploadfile['url'] = $uploadpath.$uploadfile['filename'];
		$uploadfile['target'] = $target = M_ROOT.$uploadpath.$uploadfile['filename'];
		@chmod($target, 0644);

        include_once M_ROOT."include/http.cls.php";
		$m_http = new http;
		if($rprojects[$rpid]['timeout']) $m_http->timeout = $rprojects[$rpid]['timeout'];
		$file_saved = $m_http->savetofile($remotefile,$target,$rmfile['maxsize']);
		unset($m_http);
		
		if(!$file_saved){
			self::$_file->delFile($target);
            $this->result['error'] = self::ERROR_FILE_NOT_FOUND;
			return $this->result;
		}
		if(filesize($target) < $rmfile['minisize'] * 1024){
			self::$_file->delFile($target);
            $this->result['error'] = self::ERROR_FILE_SIZE_LIMIT;
			return $this->result;
		}
		$this->result['size'] = filesize($target);
		if(in_array($rmfile['extname'], array('jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp'))){//图片或是flash
			if(!$infos = @getimagesize($target)){
				self::$_file->delFile($target);
                $this->result['error'] = self::ERROR_FILE_INVALID;
				return $this->result;
			}
			
			$this->result['width'] = $uploadfile['width'] = @$infos[0];
			$this->result['height'] = $uploadfile['height'] = @$infos[1];
            
            # 自动压缩图片大小
			$auto_compression_width = (is_array($configs) && isset($configs['auto_compression_width']) && intval($configs['auto_compression_width'])) ? (int) $configs['auto_compression_width'] : 0;
			$this->auto_compression($auto_compression_width,$uploadfile['target']);
            
			if(in_array($rmfile['extname'], array('jpg', 'jpeg', 'gif', 'png', 'bmp'))){
				if($this->image_watermark($target, $wmid)) $this->result['size'] = filesize($target);
			}
		}
		if(cls_url::is_remote_atm($uploadfile['url'])){
			include_once M_ROOT."include/ftp.fun.php";
			ftp_upload($target,$uploadfile['url']);
		}
		$this->upload_size += ceil($this->result['size'] / 1024);
		if($this->capacity != -1){
			$this->capacity -= ceil($this->result['size'] / 1024);
			$this->capacity = max(0,$this->capacity);
		}
		$this->result['remote'] = $uploadfile['url'];
        
        $insertData = array('filename' => $uploadfile['filename'], 'url' => $uploadfile['url'], 'type' => $rmfile['ftype'], 
                            'createdate' => TIMESTAMP, 'mid' => $uploadfile['mid'], 'mname' => $uploadfile['mname'], 
                            'size' => $this->result['size']);
        $db = _08_factory::getDBO();
        $db->insert( '#__userfiles', $insertData )->exec();
		if($ufid = $db->insert_id()) $this->ufids[] = $ufid;
		unset($uploadfile);
		return $this->result;
	}
    
    /**
     * 上传压缩包文件
     * 
     * @deprecated nv50
     */
	function zip_upload($localname,$type='image',$wmid = 0){
		global $memberid,$_FILES,$dir_userfile,$db,$tblprefix,$timestamp;
		include_once M_ROOT.'include/zip.cls.php';
		$curuser = cls_UserMain::CurUser();
		$uploadfile = $result = array();
		$file_saved = false;
		
		$localfiles = cls_cache::Read('localfiles');
		$localfile = $localfiles[$type];
        $result['error'] = 0;
		foreach($localfile as $k => $v){
			if(empty($v['islocal'])){
				unset($localfile[$k]);
			}
		}
		if(!$_FILES[$localname] || !mis_uploaded_file($_FILES[$localname]['tmp_name']) || !$_FILES[$localname]['tmp_name'] || !$_FILES[$localname]['name'] || $_FILES[$localname]['tmp_name'] == 'none'){
			$result['error'] = 1;//'不存在的上传文件!'
			return $result;
		}
		$uploadfile = $_FILES[$localname];
		$localfilename = addslashes($uploadfile['name']);
		$uploadfile['mid'] = $curuser->info['mid'];
		$uploadfile['mname'] = $curuser->info['mname'];
		$uploadpath = $this->upload_path($type);
		$fuploadpath = M_ROOT.$uploadpath;

		if(empty($localfile)){//本地上传方案为空
			@unlink($uploadfile['tmp_name']);
			$result['error'] = 1;
			return $result;
		}
		if($this->capacity != -1 && $uploadfile['size'] > 1024 * $this->capacity){//超过空间
			@unlink($uploadfile['tmp_name']);
			$result['error'] = 1;
			return $result;
		}
		$zip=new PHPZip($uploadfile['tmp_name']);
		$lst=$zip->filelist();
		$result['count'] = count($lst);
		$ret=array();
		$capacity=1024 * $this->capacity;
		$size=0;
		foreach($lst as $z){
			if($z['folder']){
				$result['count']--;
				continue;
			}
			$extension = strtolower(mextension($z['name']));
			if(!in_array($extension,array_keys($localfile))){//文件类型不在本地上传方案中
				continue;
			}
			if(!empty($localfile[$extension]['minisize']) && ($z['size'] < 1024 * $localfile[$extension]['minisize'])){//'超出该文件类型大小限制!'
				continue;
			}
			if(!empty($localfile[$extension]['maxsize']) && ($z['size'] > 1024 * $localfile[$extension]['maxsize'])){//'超出该文件类型大小限制!'
				continue;
			}
			$size+=$z['size'];
			if($this->capacity != -1 && $size > $capacity)break;
			$ret[]=$z['index'];
		}
		if(empty($ret)){
			$result['error'] = -2;
			return $result;
		}
		$tzip="$fuploadpath{$memberid}_".cls_string::Random(6).'/';
		$lst=$zip->Extract($tzip,$ret);
		@unlink($uploadfile['tmp_name']);
		$ret=array();
		foreach($lst as $k => $v){
			if(substr($k,-1)=='/')continue;
			$uploadfile['filename'] = preg_replace("/(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)(\.|$)/i", "_\\1\\2", date('dHis').substr(md5($k.microtime()),5,10).cls_string::Random(4,1).'.'.$extension);
			$uploadfile['url'] = $uploadpath.$uploadfile['filename'];
			$target = $fuploadpath.$uploadfile['filename'];
			if(!rename($tzip.$k,$target))continue;
			$uploadfile['thumbed'] = 0;
			if(in_array($extension, array('jpg','jpeg','gif','png','swf','bmp'))){
				if(!$infos = @getimagesize($target)){
					self::$_file->delFile($target);
					continue;
				}
				if(isset($infos[0]) && isset($infos[1])){
					$result['width'] = $infos[0];
					$result['height'] = $infos[1];
				}
				if($this->image_watermark($target,$wmid)){
					$uploadfile['size'] = filesize($target);
				}
			}
			if(cls_url::is_remote_atm($uploadfile)){
				include_once M_ROOT."include/ftp.fun.php";
				ftp_upload($target,$uploadfile);
			}
			$this->upload_size += ceil($uploadfile['size'] / 1024);
			if($this->capacity != -1){
				$this->capacity -= ceil($uploadfile['size'] / 1024);
				$this->capacity = max(0,$this->capacity);
			}
			$db->query("INSERT INTO {$tblprefix}userfiles SET
					filename='$uploadfile[filename]',
					url='$uploadfile[url]',
					type='$type',
					createdate='$timestamp',
					mid='$uploadfile[mid]',
					mname='$uploadfile[mname]',
					size='$uploadfile[size]',
					thumbed='$uploadfile[thumbed]'");
			if($ufid = $db->insert_id()) $this->ufids[] = $ufid;
			$ret[] = $uploadfile['url'];
		}
		unset($uploadfile);
		clear_dir($tzip,1);
		$result['remote']=$ret;
		return $result;
	}
	function thumb_pick($string,$datatype='htmltext',$rpid=0){//只处理已经stripslashes的文本。
		if(!$string) return '';
		$thumb = '';
		if(in_array($datatype,array('text','multitext','htmltext'))){
		/*	if(preg_match("/<img\b[^>]+\bsrc\s*=\s*(?:\"(.+?)\"|'(.+?)'|(.+?)(?:\s|\/?>))/is",$string,$matches)){*/
            # 获取编辑器里第一张图片作缩略图
			if(preg_match("/<img.*src\s*=\s*[\"|']*([^'\"]+)[\"|'].*>/isU",$string,$matches)){
				$thumb = @"$matches[1]$matches[2]$matches[3]";
				$thumb = cls_url::tag2atm($thumb);
				if(!cls_url::islocal($thumb,1) && $rpid){
					$filearr = $this->remote_upload($thumb,$rpid);
					$thumb = $filearr['remote'];
				}
				if(isset($filearr['width'])){
					$thumb .= '#'.$filearr['width'].'#'.$filearr['height'];
				}elseif($infos = @getimagesize(cls_url::local_file($thumb))){
					$thumb .= '#'.$infos[0].'#'.$infos[1];
				}
			}
		}elseif($datatype == 'images'){
			$images = @unserialize($string);
			if(is_array($images)){
				if(empty($images)) return '';
				$image = $images[min(array_keys($images))];
				$image['remote'] = cls_url::tag2atm($image['remote']);
				if(!cls_url::islocal($image['remote'],1) && $rpid){
					$image = $this->remote_upload($image['remote'],$rpid);
				}
				$thumb = $image['remote'];
				isset($image['width']) && $thumb .= '#'.$image['width'].'#'.$image['height'];
			}
		}elseif($datatype == 'image'){
			$image = array_filter(explode('#',$string));
			$image[0] = cls_url::tag2atm($image[0]);
			if(!cls_url::islocal($image[0],1) && $rpid){
				$filearr = $this->remote_upload($image[0],$rpid);
				$image[0] = $filearr['remote'];
				if(isset($filearr['width'])){
					$image[1] = $filearr['width'];
					$image[2] = $filearr['height'];
				}
			}
			$thumb = $image[0];
			isset($image[1]) && $thumb .= '#'.$image[1].'#'.$image[2];
		}
		return cls_url::save_atmurl($thumb);
	}
	function remotefromstr($string,$rpid,$wmid = 0){
		//将嵌在文本中的远程附件本地化
		if(!$this->capacity) return $string;
		$rprojects = cls_cache::Read('rprojects');
		if(empty($rpid) || empty($rprojects[$rpid]['rmfiles'])) return $string;
		if(!preg_match_all("/(href|src)\s*=\s*(\"(.+?)\"|'(.+?)'|(.+?)(\s|\/?>))/is",$string,$matches)){
			return $string;
		}
		$remoteurls = array_filter(array_merge($matches[3],$matches[4],$matches[5]));
		foreach($remoteurls as $k => $v){
			if(cls_url::islocal($v,1)){//排除本地或ftp上的链接
				unset($remoteurls[$k]);
			}elseif(!empty($rprojects[$rpid]['excludes'])){
				foreach($rprojects[$rpid]['excludes'] as $i){
					if(in_str($i,$v)){
						unset($remoteurls[$k]);
						break;
					}
				}
			}
		}
		$remoteurls = array_unique($remoteurls);
		$oldurls = $newurls = array();
		foreach($remoteurls as $oldurl){
			$filearr = $this->remote_upload($oldurl,$rpid,$wmid);
			$newurl = $filearr['remote'];
			if(strpos($newurl,':/') === false) $newurl = '<!cmsurl />'.$newurl;//本地路径的图片也要加上<!cmsurl />，这跟直接存数据库的附件是不一样的。
			if($newurl != $oldurl){
				$oldurls[] = $oldurl;
				$newurls[] = $newurl;
			}
		}
		return str_replace($oldurls,$newurls,$string);
	}
	function upload_path($type){//格式：userfiles/image/xxxx/
		global $dir_userfile,$path_userfile;
		$uploadpath = $dir_userfile.'/'.$type;
		if($this->current_dir){
			$uploadpath .= $this->current_dir;
		}else{
			if(empty($path_userfile)){
				$uploadpath .= '/';
			}elseif($path_userfile == 'month'){
				$uploadpath .= '/'.date('Ym').'/';
			}elseif($path_userfile == 'day'){
				$uploadpath .= '/'.date('Ymd').'/';
			}
		}
		mmkdir(M_ROOT.$uploadpath);
		return $uploadpath;
	}
	function saveuptotal($updatedb=0){//整个过程结束后再一次性的更新用户上传量
		$curuser = cls_UserMain::CurUser();
		if($this->upload_size) $curuser->updateuptotal($this->upload_size);
		$updatedb && $curuser->updatedb();
	}
	
    /**
	 * 生成指定大小的缩略图
	 *
	 * @param  string  $target    图片相对地址（数据库存储地址）
	 * @param  int  $to_w    指定缩略图宽度
	 * @param  int  $to_h    指定缩略图高度
     * @param  int $tofile   缩略图的显示url
	 * @param  int  $cutall    缩略图生成方式，1最佳剪裁，2保留全图
	 * @param	int  $padding  补白方式 1 补白  0 不补白
	 */
	function image_resize($target = '',$to_w,$to_h,$tofile = '',$cutall = 1,$padding=0){
		$tofile = !$tofile ? cls_url::thumb_local($target,$to_w,$to_h) : $tofile;
		mmkdir($tofile,0,1);
		$info = @getimagesize($target);
		$info_mime = $info['mime'];
		$thumbed = false;
		if(in_array($info_mime, array('image/jpeg','image/gif','image/png'))){
			$from_w = $info[0];
			$from_h = $info[1];
			$fto_w = $to_w;
			$fto_h = $to_h;
			$isanimated = 0;
			if($info['mime'] == 'image/gif'){
				$fp = fopen($target, 'rb');
				$im = fread($fp, filesize($target));
				fclose($fp);
				$isanimated = strpos($im,'NETSCAPE2.0') === FALSE ? 0 : 1;
			}
			// 判断是否会把内存耗尽
			$mmem = ini_get('memory_limit');
			if(strpos($mmem,'M')){
				$mmem = str_replace('M','',$mmem) * 1024 * 1024;	
			}elseif(strpos($mmem,'K')){
				$mmem = str_replace('K','',$mmem) * 1024;	
			}elseif(strpos($mmem,'G')){ //有不有?
				$mmem = str_replace('G','',$mmem) * 1024 * 1024 * 1024;			
			}else{ //有不有?
				$mmem = intval($mmem); //$mmem = str_replace('?','',ini_get('memory_limit'));			
			}
			// ($from_w * $from_h * 5) > //非官方找到的算法，自己测试和别人的经验与这个很接近。
			// 以下0.75因素和算法有待再精准...
			if($from_w * $from_h * 5 > $mmem * 0.75){
				@copy(M_ROOT.'images/common/error_thumb.jpg',$tofile);
				return; //adminlog('管理员管理','会员列表管理操作'); echo '<br>save-log<br>';
			}
			if(!$isanimated){
				switch($info['mime']) {
					case 'image/jpeg':
						$im = imagecreatefromjpeg($target);
						break;
					case 'image/gif':
						$im = imagecreatefromgif($target);
						break;
					case 'image/png':
						$im = imagecreatefrompng($target);
						break;
				}			
    		if($cutall==2){
    		    if($padding){
    				if($to_w>$from_w && $to_h>$from_h){
    						$m_x = round(($to_w - $from_w)/2);
    						$m_y = round(($to_h - $from_h)/2);
    						$cut_x = 0;
    						$cut_y = 0;
    						$fto_w = $from_w;
    						$fto_h = $from_h;
    						$cut_w = $from_w;
    						$cut_h = $from_h;	
    					}else{
    						$to_radio = $to_w/$to_h;
    						$from_radio = $from_w/$from_h;
    						if($to_radio>$from_radio){
    							$temp_h = $to_h;
    							$temp_w = $to_h * $from_radio; 
    						}else{
    							$temp_w = $to_w;
    							$temp_h = $to_w / $from_radio;
    						}
    						$m_x = round(($to_w - $temp_w)/2);
    						$m_y = round(($to_h - $temp_h)/2);
    						$cut_x = 0;
    						$cut_y = 0;
    						$fto_w = $temp_w;
    						$fto_h = $temp_h;
    						$cut_w = $from_w;
    						$cut_h = $from_h;
    					}
    			}else{
    				$m_x = 0;
    				$m_y = 0;
    				$cut_x = 0;
    				$cut_y = 0;
    				$fto_w = $to_w;
    				$fto_h = $to_h;
    				$cut_w = $from_w;
    				$cut_h = $from_h;
    			}		
    						
    		}elseif($cutall<=1){//最佳化裁剪(用默认值带过来此参数为0,如ajax里面直接调用...)
           
    			if($padding){//补白             
                 	if($from_w <= $to_w && $from_h <= $to_h){ //原图比缩略图都小
    					$m_x = round(($to_w - $from_w)/2);
    					$m_y = round(($to_h - $from_h)/2);
    					$cut_x = 0;
    					$cut_y = 0;
    					$fto_w = $from_w;
    					$fto_h = $from_h;
    					$cut_w = $from_w;
    					$cut_h = $from_h; 	
    				}else{
    					if($from_w>=$to_w && $from_h>=$to_h){ //原图比缩略图长宽都要大   
                        $to_radio = $to_w/$to_h;
    					$from_radio = $from_w/$from_h;                                        
    					if($to_radio<$from_radio){
    						$temp_h = $from_h;
    						$temp_w = round($temp_h * $to_radio); 
    					}else{
    						$temp_w = $from_w;
    						$temp_h = round($temp_w / $to_radio);
    					}   
                            $m_x = 0;
                            $m_y = 0;
                            if($from_radio>1){          //宽度大于高度从右上角裁
                                $cut_x = ($from_w-$temp_w)/2;
                                $cut_y = ($from_h-$temp_h)/2; 
                            }else{                      //宽度小于高度从中间
                                $cut_x = $cut_y = 0;
                            }                      								
    						$fto_w = $to_w;
    						$fto_h = $to_h;
    						$cut_w = $temp_w;
    						$cut_h = $temp_h;
                              
    					}elseif($from_w <= $to_w ){//缩略图宽度大于原图
    					    $m_x = ($to_w-$from_w)/2;
                            $m_y = 0;
                            $cut_x = 0;
                            $cut_y = ($from_h-$to_h)/2;                        								
    						$fto_w = $from_w;
    						$fto_h = $to_h;
    						$cut_w = $from_w;
    						$cut_h = $to_h;
    					}elseif($from_h <= $to_h){//缩略图高度大于原图
                            $m_x = 0;
                            $m_y = ($to_h-$from_h)/2;
                            $cut_x = ($from_w-$to_w)/2;
                            $cut_y = 0;                        								
    						$fto_w = $to_w;
    						$fto_h = $from_h;
    						$cut_w = $to_w;
    						$cut_h = $from_h;
    					}
    				}             
                
    			}else{//不补白
    				$m_x = 0;
    				$m_y = 0;
    				$cut_x = 0;
    				$cut_y = 0;
    				$fto_w = $to_w;
    				$fto_h = $to_h;
    				$cut_w = $from_w;
    				$cut_h = $from_h;
    			}
    		}
			
    		if(function_exists("imagecreatetruecolor")){
    			if($im_n = imagecreatetruecolor($to_w,$to_h)){
    				$white = imagecolorallocate($im_n, 255, 255, 255);
    				imagefill($im_n,0,0,$white); 
    				imagecopyresampled($im_n,$im,$m_x,$m_y,$cut_x,$cut_y,$fto_w,$fto_h,$cut_w,$cut_h); 
    			}elseif($im_n = imagecreate($to_w,$to_h)){
    				$white = imagecolorallocate($im_n, 255, 255, 255);
    				imagefill($im_n,0,0,$white);
    				imagecopyresized($im_n,$im,$m_x,$m_y,$cut_x,$cut_y,$fto_w,$fto_h,$cut_w,$cut_h); 
    			} 
    		}else{ 
    				$white = imagecolorallocate($im_n, 255, 255, 255);
    				imagefill($im_n,0,0,$white);
    				$im_n = imagecreate($to_w,$to_h); 
    				imagecopyresized($im_n,$im,$m_x,$m_y,$cut_x,$cut_y,$fto_w,$fto_h,$cut_w,$cut_h); 
    		}
    				imagejpeg($im_n,$tofile);
    				imagedestroy($im); 
    				imagedestroy($im_n); 
    				$thumbed = true;
    		}
        }
        if(!$thumbed) @copy($target,$tofile);
        return;
	}
	
	function image_watermark($target,$wmid = 0){
		$watermarks = cls_cache::Read('watermarks');
		if(@empty($watermarks[$wmid])) return false;
		if(!$watermarks[$wmid]['Available']) return false; 
		$wmark = $watermarks[$wmid];
		extract($wmark);
		if($watermarktype != 2){
			$watermark_file = $watermarktype ? M_ROOT.'images/common/watermark.png' : M_ROOT.'images/common/watermark.gif';
			if(!is_file($watermark_file)) return false;
		}else{
			if(empty($waterfontfile)) return false;
			$watermark_font = M_ROOT.'images/common/'.$waterfontfile;
			if(!is_file($watermark_font)) return false;
		}
		$imageinfo = getimagesize($target);
		$watermarked = false;
		if(in_array($imageinfo['mime'], array('image/jpeg', 'image/gif', 'image/png')) && $watermarkminwidth < $imageinfo[0] && $watermarkminheight < $imageinfo[1]) {
			if($watermarkstatus) {
				$wmstatus_arr = array_filter(explode(',',$watermarkstatus));
				if($watermarktype!=2){
					$watermarkinfo	= getimagesize($watermark_file);
					$watermark_logo = $watermarktype ? imagecreatefrompng($watermark_file) : imagecreatefromgif($watermark_file);
					$logo_w		= $watermarkinfo[0];
					$logo_h		= $watermarkinfo[1];
					$img_w		= $imageinfo[0];
					$img_h		= $imageinfo[1];
					$wmwidth	= $img_w - $logo_w;
					$wmheight	= $img_h - $logo_h;
		
					$isanimated = 0;
					if($imageinfo['mime'] == 'image/gif') {
						$fp = fopen($target, 'rb');
						$imagebody = fread($fp, filesize($target));
						fclose($fp);
						$isanimated = strpos($imagebody, 'NETSCAPE2.0') === FALSE ? 0 : 1;
					}
					
					if(is_readable($watermark_file) && $wmwidth > 10 && $wmheight > 10 && !$isanimated) {
						switch($imageinfo['mime']) {
							case 'image/jpeg':
								$dst_photo = imagecreatefromjpeg($target);
								break;
							case 'image/gif':
								$dst_photo = imagecreatefromgif($target);
								break;
							case 'image/png':
								$dst_photo = imagecreatefrompng($target);
								break;
						}
						foreach($wmstatus_arr as $wmstatus){
							$xy = $this->wmlocation($wmstatus,$img_w,$img_h,$logo_w,$logo_h,$watermarkoffsetx,$watermarkoffsety);
							if($watermarktype) {
								imagecopy($dst_photo, $watermark_logo, $xy[0], $xy[1], 0, 0, $logo_w, $logo_h);
							} else {
								imagealphablending($watermark_logo, true);
								imagecopymerge($dst_photo, $watermark_logo, $xy[0], $xy[1], 0, 0, $logo_w, $logo_h, $watermarktrans);
							}
						}
		
						switch($imageinfo['mime']) {
							case 'image/jpeg':
								imagejpeg($dst_photo, $target, $watermarkquality);
								break;
							case 'image/gif':
								imagegif($dst_photo, $target);
								break;
							case 'image/png':
								imagepng($dst_photo, $target);
								break;
						}
						$watermarked = true;
					}
				}else{					
					switch($imageinfo['mime']) {
						case 'image/jpeg':
							$im = imagecreatefromjpeg($target);
							break;
						case 'image/gif':
							$im = imagecreatefromgif($target);
							break;
						case 'image/png':
							$im = imagecreatefrompng($target);
							break;
					}
					$watermarktext = cls_string::iconv(cls_env::getBaseIncConfigs('mcharset'),"UTF-8",$watermarktext);
					$ar = imagettfbbox($watermarkfontsize, $watermarkangle, $watermark_font, $watermarktext);
					$img_w		= $imageinfo[0];
					$img_h		= $imageinfo[1];
					$logo_h = max($ar[1], $ar[3]) - min($ar[5], $ar[7]);
					$logo_w = max($ar[2], $ar[4]) - min($ar[0], $ar[6]);
					$white = imagecolorallocate($im, 255,255,255);
					$r1   =   hexdec(substr($watermarkcolor,1,2));
					$g1   =   hexdec(substr($watermarkcolor,3,2));
					$b1   =   hexdec(substr($watermarkcolor,5,2)); 
					$color = imagecolorclosestalpha($im,$r1,$g1,$b1,20);
					foreach($wmstatus_arr as $wmstatus){
						$xy = $this->wmlocation($wmstatus,$img_w,$img_h,$logo_w,$logo_h,$watermarkoffsetx,$watermarkoffsety);
						$xy[1] += $logo_h;
						imagettftext($im, $watermarkfontsize,$watermarkangle,$xy[0], $xy[1], $color, $watermark_font, $watermarktext);
					}
					switch($imageinfo['mime']) {
						case 'image/jpeg':
							imagejpeg($im, $target);
							break;
						case 'image/gif':
							imagegif($im, $target);
							break;
						case 'image/png':
							imagepng($im, $target);
							break;
					}
					imagedestroy($im);
				}
			}
		}
		return $watermarked;
	}
	
	function wmlocation($status,$img_w,$img_h,$logo_w,$logo_h,$offsetx = 5,$offsety = 5){
		switch($status) {
			case 1:
				$x = $offsetx;
				$y = $offsety;
				break;
			case 2:
				$x = ($img_w - $logo_w) / 2;
				$y = $offsety;
				break;
			case 3:
				$x = $img_w - $logo_w - $offsetx;
				$y = $offsety;
				break;
			case 4:
				$x = $offsetx;
				$y = ($img_h - $logo_h) / 2;
				break;
			case 5:
				$x = ($img_w - $logo_w) / 2;
				$y = ($img_h - $logo_h) / 2;
				break;
			case 6:
				$x = $img_w - $logo_w - $offsetx;
				$y = ($img_h - $logo_h) / 2;
				break;
			case 7:
				$x = + $offsetx;
				$y = $img_h - $logo_h - $offsety;
				break;
			case 8:
				$x = ($img_w - $logo_w) / 2;
				$y = $img_h - $logo_h - $offsety;
				break;
			case 9:
				$x = $img_w - $logo_w - $offsetx;
				$y = $img_h - $logo_h - $offsety;
				break;
		}
		return array(@$x,@$y);
	}
	
    // paras:文档id; 把一个单图字段转化为多图字段,上传时,分离保存成多个文档,需要加上url参数,格式为array('aid'=>123,'url'=>'/path/fname.ext')
	function closure($clear = 0, $paras = 0, $table = 'archives'){
		global $db, $tblprefix, $m_cookie;
		$curuser = cls_UserMain::CurUser();
		$ckey = @$curuser->info['msid'] . '_upload';
		$ids = implode(',', $this->ufids);
		empty($m_cookie[$ckey]) || $ids = $m_cookie[$ckey] . ($ids ? ",$ids" : '');
		if($clear){
			//表ID对应数组
			$tids = array(
					'archives' => 1,
					'farchives' => 2,
					'members' => 3,
					'marchives' => 4,
					'comments' => 16,
					'replys' => 17,
					'offers' => 18,
					'mcomments' => 32,
					'mreplys' => 33,
					'pushs' => 64,
			);
			$tid = $table && isset($tids[$table]) ? $tids[$table] : 0;
			//防止别人修改cookie注入MySQL
			if(preg_match('/^\d+(?:,\d+)*$/', $ids)){
				if($paras){//资料添加写功后，将上传的附件与资料id进行关联
					if(is_array($paras)){
					   $aid = $paras['aid'];
                       $url = $paras['url'];
                       if($pos=strpos($url,'#')) $url = substr($url,0,$pos);
					   if(strstr($url,"http://")) $url = cls_url::save_atmurl($url); 
                       $urlsql = " AND url = '$url' "; //echo $urlsql;
					}else{
					   $aid = $paras;
                       $urlsql = " ";
					}
                    $aid = intval($aid);
                    $tid && $db->query("UPDATE {$tblprefix}userfiles SET aid=$aid,tid=$tid WHERE aid=0 $urlsql AND ufid IN ($ids)", 'UNBUFFERED');
				}elseif($clear == 1){//资料添加失败，删除本次操作有关的附件及记录
					$query = $db->query("SELECT url,type FROM {$tblprefix}userfiles WHERE ufid IN ($ids) AND tid=0 AND aid=1");
					while($item = $db->fetch_array($query)){
						atm_delete($item['url'],$item['type']);
					}
					$db->query("DELETE FROM {$tblprefix}userfiles WHERE ufid IN ($ids) AND tid=0 AND aid=1", 'UNBUFFERED');
				}
			}
			msetcookie($ckey, '', -31536000);
		}else{//附件上传成功后，将附件id写入cookie
			msetcookie($ckey, $ids, 31536000);
		}
	}
    
    public function setter( $name, $argc )
    {
        if ( property_exists($this, $name) )
        {
            $this->$name = $argc;
        }
    }
    
    /**
     * 设置上传类型
     * 
     * @param string $type  当前上传的类型，目前有：upload、base64
     * 
     * @since nv50
     */
    public function setType( $type )
    {
        $this->type = strtolower( (string) $type );
    }
    
    /**
     * 设置配置信息
     * 
     * @param array $config 要设置的配置信息
     * 
     * @since nv50
     */
    public function setConfig( array $configs )
    {
        $this->configs = $configs;
    }
}