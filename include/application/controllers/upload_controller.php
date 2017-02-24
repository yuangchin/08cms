<?php
/**
 * 上传接口控制器
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
@set_time_limit(0);
class _08_C_Upload_Controller extends _08_Controller_Base
{
    private $type = '';
    private $lfile = '';
    private $browser = null;
    private $isExit;
    private $result;
    private $localname;
	private $encode;
    
    /**
     * 在构造方法里检测上传权限，如果无权限时终止上传
     */
    public function __construct()
    {
        parent::__construct();
//        if ( empty($this->_curuser->info['mid']) )
//        {
//            die('请先登录');
//        }
        $this->type = empty($this->_get['type']) ? 'images' : preg_replace('/[^\w\[\]]/', '', $this->_get['type']);
        $this->lfile = (substr($this->type,-1) == 's' ? substr($this->type,0,-1) : $this->type);
        
        $this->browser = _08_Browser::getInstance();
		$this->encode = empty($this->_get['encode']) ? '' : strtolower($this->_get['encode']);
        $this->result = array();
        $this->isExit = true;
        $this->_get['wmid'] = (empty($this->_get['wmid']) ? 0 : (int) $this->_get['wmid']);
        $this->_get['auto_compression_width'] = (empty($this->_get['auto_compression_width']) ? 0 : (int) $this->_get['auto_compression_width']);
        $this->localname = 'Filedata';
    }
    
    /**
     * 选中文件后开始执行上传操作
     */
    public function post()
    {
        ob_end_clean();
        cls_env::mob_start();
		$post = cls_env::_POST();
        $up=cls_upload::OneInstance();
        if ( isset($post['pic1']) )
        {
            $up->setType('base64');
            if ( isset($post['file_name']) )
            {
                $up->setConfig(array("oriName" => $post['file_name']));
            }
            else
            {
            	$up->setConfig(array("oriName" => "1.jpg"));
            }
            $this->localname = 'pic1';
        }
        $this->result = $up->local_upload($this->localname, $this->lfile, 
            array('wmid' => $this->_get['wmid'], 'auto_compression_width' => $this->_get['auto_compression_width']));
        
		if(empty($this->result['error']) && !empty($this->result['remote']))
        {
			$up->closure();
            # 该处用多个KEY值存只为保持兼容
            $this->result['name'] = $this->result['remote'] = cls_url::tag2atm($this->result['remote']);
            $up->saveuptotal(1);
		}
        else
        {
        	$this->result['error_message'] = $up->getErrorMessage();
        }
        
        $this->result['status'] = 1;
        
        if ( $this->isExit )
        {
            #exit(json_encode($this->result));
			exit(_08_Documents_JSON::encode($this->result, true));
        }        
    }
        
    /**
     * 删除附件
     */
    public function delete()
    {
        # 暂时限制只有管理员才有删除权限
        if ( !$this->_curuser->isadmin() )
        {
            return false;
        }
        
        $post = cls_env::_POST();
        # 为了要兼容之前的图片所以当ID不为数字时不删除
        if ( isset($post['ufid']) && is_numeric($post['ufid']) )
        {
            $userfiles = self::getModels('UserFiles_Table');
            $row = $userfiles->where(array('ufid' => $post['ufid']))->read('url', false);
            if ( $row )
            {
                // 如果成功删除数据库信息则删除上传文件
                if ( $userfiles->delete() )
                {
                    cls_atm::atm_delete($row['url'], $this->type);
                }
            }
        }
    }
    
    /**
     * 百度编辑器数据上传接口
     */
    public function ueditor()
    {	global $cms_top;
        isset($this->_get['action']) || $this->_get['action'] = '';
        $ueditor = _08_factory::getInstance('_08_Ueditor');
        $configs = $ueditor->getConfigs();
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
        switch ($this->_get['action'])
        {        
            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $up = cls_upload::OneInstance();
                if ( in_array($this->_get['action'], array('uploadimage', 'uploadscrawl')) )
                {
                    $this->_get['type'] = 'images';
                    if ( $this->_get['action'] == 'uploadimage' )
                    {
                        $this->localname = $configs['imageFieldName'];
                    }
                    else
                    {
                    	$this->localname = $configs['scrawlFieldName'];
                    }
                }
                else if ($this->_get['action'] == 'uploadvideo')
                {
                    $this->_get['type'] = 'flashs';
                    $this->localname = $configs['videoFieldName'];
                    # 百度编辑器里只有视频，所以要把本系统的FLASH与视频合并
                    $up->setter('lfile_types', array('media'));
                }
                else
                {
                	$this->_get['type'] = 'files';
                    $this->localname = $configs['fileFieldName'];
                }
                if ( isset($_FILES['upfile']) ) # 剪切默认的名称
                {
                    $this->localname = 'upfile';
                }
                $this->lfile = substr($this->_get['type'], 0, -1);
                if ( $this->_get['action'] == 'uploadscrawl' )
                {
                    $up->setType('base64');
                    $up->setConfig(array("oriName" => "scrawl.png"));
                }
                $this->isExit = false;
                $this->post();
                $result = array();
                if ( !empty($this->result['error']) || empty($this->result['name']) )
                {
                    $result['state'] = $up->getErrorMessage();
                }
                else
                {
                	$result = @mhtmlspecialchars(array(
                        'state' => 'SUCCESS', //上传状态，上传成功时必须返回"SUCCESS"
                        'url' => $this->result['name'],  //返回的地址
                        'title' => substr(strrchr($this->result['name'], '/'), 1), //新文件名
                        "original" =>  $this->encode == 'gbk' ? cls_string::iconv('UTF-8', $mcharset, $this->result['original']) : $this->result['original'],       //原始文件名
                        "type" => $this->result['extension'],           //文件类型
                        "size" => $this->result['size']            //文件大小
                    ));
                }
                
                $result = _08_Documents_JSON::encode($result, true);
                break;
        
            /* 抓取远程文件 */
            case 'catchimage':
                $up = cls_upload::OneInstance();
                $up->setConfig(array("oriName" => "remote.png"));
                $this->_get['rpid'] = (isset($this->_get['rpid']) ? (int) $this->_get['rpid'] : 0);
                $request = cls_env::_GET_POST();
                $list = array();
                if (isset($request[$configs['catcherFieldName']]))
                {
                    $request[$configs['catcherFieldName']] = (array) $request[$configs['catcherFieldName']];
                    foreach ( $request[$configs['catcherFieldName']] as $imgUrl ) 
                    {
                        $this->result = $up->remote_upload($imgUrl, $this->_get['rpid'], 
                            array('wmid' => $this->_get['wmid'], 'auto_compression_width' => $this->_get['auto_compression_width']));
                        if ( empty($this->result['error']) )
                        {
                            $list[] = @mhtmlspecialchars(array(
                                "state" => 'SUCCESS',
                                "url" => $this->result['remote'],
                                "size" => $this->result['size'],
                                "title" => substr(strrchr($this->result['name'], '/'), 1),
                                "original" => cls_string::iconv('UTF-8', $mcharset, $this->result['original']),
                                "source" => $imgUrl
                            ));
                        }
                    }
                }
                
                $result = _08_Documents_JSON::encode(array(
                    'state'=> count($list) ? 'SUCCESS':'ERROR',
                    'list'=> $list
                ), true);
                break;
        
            default:
                $method = $this->_get['action'];
                $result = $ueditor->$method();
                break;
        }
        
        /* 输出结果 */
        if (isset($this->_get["callback"]))
        {
            if (preg_match("/^[\w_]+$/", $this->_get["callback"]))
            {
                echo mhtmlspecialchars($this->_get["callback"]) . '(' . $result . ');';
            }
            else
            {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        }
        else
        {
			//针对百度编辑器的单图上传跨域解决办法
            echo isset($this->_get["crossdomain"]) ? "<script type='text/javascript'>document.domain= '{$cms_top}'||document.domain;</script>".$result : $result;
        }
    }
    
    /**
     * 显示上传按钮
     */
    public function select_button()
    {
        @$maxcount = intval($this->_get['maxcount']);
		$maxcount = empty($maxcount) ? 50 : $maxcount; // 一次最多让上传50个文件

    	$tmp = cls_atm::getLocalFilesExts($this->type);
    	$ftypes='';$otype='';
        foreach($tmp as $v)
        {
            $v['extname'] = strtolower($v['extname']);
            if($v['islocal']) $otype.=",\"$v[extname]\":[$v[minisize],$v[maxsize]]";
            $ftypes .= ((empty($ftypes) ? '' : ';') . '*.' . $v['extname']);
        }
        $otype=substr($otype,1);
        
        $configs = array('base_inc_configs' => cls_env::getBaseIncConfigs('mcharset, ckpre'), 
                         '_get' => @$this->_get,
                         'ftypes' => $ftypes,
                         'otype' => $otype,
                         'type' => $this->type,
                         'maxcount' => $maxcount,
                         'timestamp' => TIMESTAMP,
                         'mconfigs' => $this->_mconfigs );
        
        $this->_view->assign($configs);
        # 手机浏览时
        if ( $this->browser->isMobile() )
        {
            $accept = '';
            if ( in_array($this->type, array('image', 'images')) )
            {
                $accept = 'image/*';
            }
            else if( in_array($this->type, array('flash', 'flashs')) )
            {
            	$accept = 'application/x-shockwave-flash';
            }
            else if( in_array($this->type, array('media', 'medias')) )
            {
            	$accept = 'audio/*,video/*';
            }
            else if( in_array($this->type, array('file', 'files')) )
            {
            	$accept = 'application/*';
            }
            
            $this->_view->assign(array('accept' => $accept));            
            $this->_view->display('upload:select_button_in_html5', '.php');
            exit;
        }
    }
    
    public function edit_image_url()
    {        
        $configs = array('base_inc_configs' => cls_env::getBaseIncConfigs('mcharset, ckpre'), 
                         '_get' => @$this->_get,
                         'mconfigs' => $this->_mconfigs );
                         
        $this->_view->assign($configs);
    }
    
    /**
     * 裁剪组件
     */
    public function cut()
    {
        if ( isset($this->_get['imgsrc']) )
        {
            $this->_get['wmid'] = (empty($this->_get['wmid']) ? 0 : (int)$this->_get['wmid']);
            $this->_get['imgsrc'] = base64_decode($this->_get['imgsrc']);
            $imgurl = cls_url::tag2atm($this->_get['imgsrc']);            
            $configs = array('base_inc_configs' => array('mcharset' => cls_env::getBaseIncConfigs('mcharset')), 
                             'imgurl' => $imgurl,
                             'timestamp' => TIMESTAMP,
                             '_get' => $this->_get,
                             'mconfigs' => $this->_mconfigs );
            
            $this->_view->assign($configs);
        }
        else
        {
            die('请求参数不正确。');
        }
    }
}
