<?php
/**
 * 生成带参数的二维码接口
 * {@link http://mp.weixin.qq.com/wiki/index.php?title=%E7%94%9F%E6%88%90%E5%B8%A6%E5%8F%82%E6%95%B0%E7%9A%84%E4%BA%8C%E7%BB%B4%E7%A0%81}
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_Qrcode extends _08_M_Weixin_Base
{
    protected $_createUrlFormat = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s';
    
    protected $_showUrlFormat = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s';
    
    public function __construct()
    {
        parent::__construct();
        $config = $this->getAppIDAndAppSecret();
        $this->_requestGetAccessToken($config['weixin_appid'], $config['weixin_appsecret']);
    }
    
    /**
     * 创建二维码ticket
     * 
     * @param array $configs 创建二维码ticket所需要的参数
     */
    protected function _create_ticket( array $configs )
    {
        $configs = $this->formatPostDatasToJSON($configs);
        $url = sprintf($this->_createUrlFormat, $this->_access_token);
        $returnInfo = _08_Http_Request::getResources(array('urls' => $url, 'method' => 'POST', 'postData' => $configs), 5);
        return json_decode($returnInfo);
    }
    
    /**
     * 通过ticket换取二维码
     * 
     * @param array $configs 创建二维码ticket所需要的参数
     */
    public function show_qrcode( array $configs )
    {
        $postDatas = array();
        # 该二维码有效时间，以秒为单位。 最大不超过1800
        if ( isset($configs['expire_seconds']) )
        {
            $configs['expire_seconds'] = (int) $configs['expire_seconds'];
            if ( $configs['expire_seconds'] > 0 )
            {
                $postDatas['expire_seconds'] = $configs['expire_seconds'];
                $configs['action_name'] = 'QR_SCENE';
            }
        }
        
        # 二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久
        if ( empty($configs['action_name']) )
        {
            $postDatas['action_name'] = 'QR_LIMIT_SCENE';
            if ( isset($postDatas['expire_seconds']) )
            {
                unset($postDatas['expire_seconds']);
            }
        }
        else
        {
        	$postDatas['action_name'] = strtoupper(trim($configs['action_name']));
        }
        
        # 二维码详细信息
//        if ( isset($configs['action_info']) )
//        {
//            $postDatas['action_info'] = trim($configs['action_info']);
//        }
        
        # 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为10000（目前参数只支持1--10000）        
        if ( isset($configs['scene_id']) )
        {
            # 只验证下限，不处理上限，让微信官方处理，预防官方改规则(注意:如果是32位,根本不能用int)
            $postDatas['action_info']['scene']['scene_id'] = max(1, (int) $configs['scene_id']); 
        }
        ksort($postDatas);
        
        $qrcode_path = M_ROOT . $this->_mconfigs['dir_userfile'] . DS . 'qrcode' . DS;
        _08_FileSystemPath::checkPath($qrcode_path, true);
        $qrcode_file = $qrcode_path . md5(serialize($postDatas)) . '.jpg';
        if ( is_file($qrcode_file) )
        {
            if ( !isset($configs['expire_seconds']) || (TIMESTAMP - filemtime($qrcode_file) < $configs['expire_seconds']) )
            {
                $qrcode = file_get_contents($qrcode_file);
                return $qrcode;
            }
        }
        
        $ticket_info = $this->_create_ticket( $postDatas );
        if ( isset($ticket_info->ticket) )
        {
            $url = sprintf($this->_showUrlFormat, urlencode($ticket_info->ticket));
            $qrcode = _08_Http_Request::getResources($url);
            file_put_contents($qrcode_file, $qrcode);
            return $qrcode;
        }
        else
        {
        	cls_message::ajax_info(_08_M_Weixin_Error_Message::get(@$ticket_info->errcode), 'CONTENT');
        }
    }
	
	/**
     * 获取临时二维码 场景ID值（32位非0整型）
     * 
	 * @param  int   $timegap 时间间隔(分钟), $timegap分钟内未使用过的随机景值ID
     * 
     * @return string $hash 返回未使用过的随机景值ID
     * @08cms  1.0
     */
	public static function getSceneID($timegap=10)
    {
		global $m_cookie; //$cookies = self::_COOKIE();
		$db = _08_factory::getDBO();
		$reval = mt_rand(100001,2147483123); //2,147,483,648
		$time = TIMESTAMP - $timegap*60;
		while($db->select('scene_id')->from('#__msession')->where("mslastactive>$time")->_and(array('scene_id'=>$reval))->exec()->fetch())
		{
			$reval = mt_rand(100001,2147483123); 
		}
		$db->update('#__msession', array('scene_id'=>$reval))->where(array('msid'=>$m_cookie['msid']))->exec();
        return $reval;
    }
	
}