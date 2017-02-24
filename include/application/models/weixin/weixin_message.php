<?php
/**
 * 微信消息接口管理模型
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
abstract class _08_M_Weixin_Message extends _08_M_Weixin_Base
{
	
    /**
     * 登录事件eventKey
     * 
     * @var   int
     */
    const SCENE_ID_LOGIN = 1; 
	
    /**
     * 注册事件eventKey
     * 
     * @var   int
     */
    const SCENE_ID_REGISTER = 2; 
	
    /**
     * 文本消息 / 回复文本消息 结构
     * 具体结构请看：
     * 文本消息：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E6.96.87.E6.9C.AC.E6.B6.88.E6.81.AF}
     * 回复文本消息：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E5.9B.9E.E5.A4.8D.E6.96.87.E6.9C.AC.E6.B6.88.E6.81.AF}
     */
    protected $_ToUserName = '<ToUserName><![CDATA[%s]]></ToUserName>';  # 开发者微信号 
    
    protected $_FromUserName = '<FromUserName><![CDATA[%s]]></FromUserName>'; # 发送方帐号（一个OpenID） 
    
    protected $_CreateTime = '<CreateTime>%s</CreateTime>'; # 消息创建时间 （整型） 
    
    protected $_MsgType = '<MsgType><![CDATA[%s]]></MsgType>'; # 消息类型
    
    protected $_Content = '<Content><![CDATA[%s]]></Content>'; # 文本消息内容
    
    protected $_MsgId = '<MsgId>%s</MsgId>'; # 消息id，64位整型 
    
    /**
     * 图片消息结构（在文本消息结构上增加以下结构）
     * 
     * 具体结构请看：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E5.9B.BE.E7.89.87.E6.B6.88.E6.81.AF}
     */
    protected $_PicUrl = '<PicUrl><![CDATA[%s]]></PicUrl>'; #  	图片链接 
    
    /**
     * 地理位置消息结构（在文本消息结构上增加以下结构）
     * 
     * 具体结构请看：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E5.9C.B0.E7.90.86.E4.BD.8D.E7.BD.AE.E6.B6.88.E6.81.AF}
     */
    protected $_Location_X = '<Location_X>%s</Location_X>'; # 地理位置纬度 
    
    protected $_Location_Y = '<Location_Y>%s</Location_Y>'; # 地理位置经度 
    
    protected $_Scale = '<Scale>%s</Scale>'; # 地图缩放大小
    
    protected $_Label = '<Label><![CDATA[%s]]></Label>'; # 地理位置信息 
    
    /**
     * 链接消息结构（在文本消息结构上增加以下结构）
     * 
     * 具体结构请看：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E9.93.BE.E6.8E.A5.E6.B6.88.E6.81.AF}
     */
    protected $_Title = '<Title><![CDATA[%s]]></Title>'; # 消息标题 
    
    protected $_Description = '<Description><![CDATA[%s]]></Description>'; # 消息描述 
    
    protected $_Url = '<Url><![CDATA[%s]]></Url>'; # 消息链接
    
    /**
     * 事件推送结构（在文本消息结构上增加以下结构）
     * 
     * 具体结构请看：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E4.BA.8B.E4.BB.B6.E6.8E.A8.E9.80.81}
     */
    protected $_Event = '<Event><![CDATA[%s]]></Event>'; # 事件类型，subscribe(订阅)、unsubscribe(取消订阅)、CLICK(自定义菜单点击事件) 
    
    protected $_EventKey = '<EventKey><![CDATA[%s]]></EventKey>'; # 事件KEY值，与自定义菜单接口中KEY值对应
    
    /**
     * 回复音乐消息结构（在文本消息结构上增加以下结构）
     * 
     * 具体结构请看：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E5.9B.9E.E5.A4.8D.E9.9F.B3.E4.B9.90.E6.B6.88.E6.81.AF}
     */
    protected $_MusicUrl = '<MusicUrl><![CDATA[%s]]></MusicUrl>'; # 音乐链接 
     
    protected $_HQMusicUrl = '<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>'; # 高质量音乐链接，WIFI环境优先使用该链接播放音乐 
    
    /**
     * 回复图文消息结构（在图片消息和链接消息结构上增加以下结构）
     * 
     * 具体结构请看：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97#.E5.9B.9E.E5.A4.8D.E5.9B.BE.E6.96.87.E6.B6.88.E6.81.AF}
     */
    protected $_ArticleCount = '<ArticleCount>%s</ArticleCount>'; # 图文消息个数，限制为10条以内
    
    protected $_post = null;
    
    private $replyItem = "<xml>\n";
    
    public function __construct( SimpleXMLElement $postObj )
    {
        parent::__construct();
        $this->_post = $postObj;
    }
    
    /**** 以下对于每一个POST请求，开发者在响应包中返回特定xml结构，对该消息进行响应（现支持回复文本、图文、语音、视频、音乐）。 ****/
    
    /**
     * 回复文本信息
     * 
     * @example $this->_ReplyText('测试回复文本消息。'); 
     * 
     * @param   string $content 要回复的文本数据
     * @return  string          返回一个XML结构
     * @since   nv50
     */
    public function _ReplyText( $content )
    {        
        if (empty($this->_mconfigs['weixin_enable']))
        {
            $content = '微信公众平台未开启。';
        }
        elseif ( empty($content) )
        {
            $content = '暂无相关数据！';
        }
        
        $items = array(
            'ToUserName' => $this->_post->FromUserName,
            'FromUserName' => $this->_post->ToUserName,
            'CreateTime' => time(),
            'MsgType' => 'text',
            'Content' => trim( (string) $content )
        );        
        $this->_setXmlItem($items);
        
        $this->replyItem .= '</xml>';
        
        return $this->replyItem;
    }
    
    /**
     * 回复音乐消息
     * 
     * @example
        # 回复音乐消息
        return $this->_ReplyMusic(
            array(
                'Title' => '消息标题',
                'Description' => '消息描述',
                'MusicUrl' => '音乐链接',
                'HQMusicUrl' => '高质量音乐链接，WIFI环境优先使用该链接播放音乐'
            )
        ); 
     * 
     * @param  array $musicInfo 要回复的音乐消息
     * @return string           返回一个XML结构
     * @since  nv50
     */
    protected function _ReplyMusic( array $musicInfo )
    {     
        if (empty($this->_mconfigs['weixin_enable']))
        {
            return $this->_ReplyText('微信公众平台未开启。');
        }
        elseif ( empty($musicInfo) )
        {
            return $this->_ReplyText('');
        }
        
        $items = array(
            'ToUserName' => $this->_post->FromUserName,
            'FromUserName' => $this->_post->ToUserName,
            'CreateTime' => time(),
            'MsgType' => 'music'
        );
        $this->_setXmlItem($items);
        $this->replyItem .= "<Music>\n";
        $this->_setXmlItem($musicInfo);
        $this->replyItem .= "</Music>\n";
        
        $this->replyItem .= '</xml>';
        
        return $this->replyItem;
    }
    
    
    /**
     * 回复图文信息
     * 
     * @example
        
        # 回复图文消息（多条）
        return $this->_ReplyNews(
            array(
                array('Title' => '消息标题一', 'Description' => '消息简述一', 'PicUrl' => '消息图片地址一', 'Url' => '消息链接一'),
                array('Title' => '消息标题二', 'Description' => '消息简述二', 'PicUrl' => '消息图片地址二', 'Url' => '消息链接二'),
                array('Title' => '消息标题三', 'Description' => '消息简述三', 'PicUrl' => '消息图片地址三', 'Url' => '消息链接三')
            )
        );
        
        # 回复图文消息（单条）
        return $this->_ReplyNews(
            array('Title' => '消息标题', 'Description' => '消息描述', 'PicUrl' => '消息图片地址', 'Url' => '消息链接')
        );
     * 
     * @param  string $articles 要回复的图文数据数组，
     *                          注：该数组一次发送的元素个数不能大于10（即一次不能回复超过10条的图文消息）,多条图文消息信息，默认第一个item为大图 
     * @return string           返回一个XML结构
     * @since  nv50
     */
    protected function _ReplyNews( array $articles )
    {     
        if (empty($this->_mconfigs['weixin_enable']))
        {
            return $this->_ReplyText('微信公众平台未开启。');
        }
        elseif ( empty($articles) )
        {
            return $this->_ReplyText('');
        }
        
        $items = array(
            'ToUserName' => $this->_post->FromUserName,
            'FromUserName' => $this->_post->ToUserName,
            'CreateTime' => time(),
            'MsgType' => 'news',
            'ArticleCount' => cls_Array::array_dimension($articles) == 1 ? 1 : count($articles)
        );
        $this->_setXmlItem($items);
        $this->replyItem .= "<Articles>\n";
        
        foreach ($articles as $key => $items) 
        {
            if ( !empty($items) )
            {
                $this->replyItem .= "<item>\n";
                if ( is_array($items) )
                {
                    $this->_setXmlItem($items);
                }
                else
                {
                	$this->_setXmlItem($articles);
                    $break = true;
                }
                $this->replyItem .= "</item>\n";
            }
            
            if ( isset($break) && $break )
            {
                break;
            }
        }
        
        $this->replyItem .= "</Articles>\n</xml>";
        
        return $this->replyItem;
    }
    
    /**
     * 设置XML节点
     * 
     * @param string $name  节点名称
     * @param mixed  $value 节点值
     * @since nv50
     */
    protected function _setXmlItem( array $items )
    {
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
        foreach ($items as $key => $itemValue) 
        {
            $format = $this->{'_' . $key};
            if ( !empty($format) )
            {
                $this->replyItem .= sprintf($format . "\n", cls_string::iconv($mcharset, 'UTF-8', $itemValue));
            }  
        }
    }
    
    public function __call( $name, $argc )
    {
         return $this->_ReplyText('您发送的信息有误，请检查后再试。');
    }
}