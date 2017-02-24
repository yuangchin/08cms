<?php
/**
 * 微信消息管理扩展模型（对普通微信用户向公众账号发来的消息进行回复）
 * 注：该文件在升级核心时请不要替换
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_Extends_Message extends _08_M_Weixin_Message
{
    /**
     * 出租模型ID
     */
    const RENTING_CHID = 2;
    
    /**
     * 二手房模型ID
     */
    const SECOND_HAND_HOUSING_CHID = 3;
    
    /**
     * 楼盘模型ID
     */
    const PROPERTY_CHID = 4;
    
    /**
     * 响应文本消息 （该函数保留给开发扩展功能回复特定的消息使用）
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function responseText()
    {
        # var_dump($this->_post);  可根据用户发送的消息进行回复
        if ( isset($this->_post->Content) )
        {
            $this->_post->Content = strtoupper(trim($this->_post->Content));        
            #@list($prefixes, $ccid) = preg_split('/(?<=[a-zA-Z])(?=[\d])/x', $this->_post->Content);
            $prefixes = substr($this->_post->Content, 0, 2);
            $ccid = substr($this->_post->Content, 2);
            
            if ( empty($prefixes) || empty($ccid) )
            {
                return $this->_ReplyText('回复有误，请重新回复。');
            }
            
            $Weixin_Extends_Message_Text = parent::getModels('Weixin_Extends_Message_Text', $this->_post);
            $method = ('responseText' . strtoupper($prefixes));   
        	$datas = call_user_func(array($Weixin_Extends_Message_Text, $method), $ccid);
                
            if ( empty($datas) )
            {
                return $this->_ReplyText('暂无相关数据。');
            }
            
            return $datas;
        }        
        
        return $this->_ReplyText('回复有误，请重新回复。');
    }
        
    /**
     * 响应图片消息 （该函数保留给开发扩展功能回复特定的消息使用）
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function responseImage()
    {        
    }
        
    /**
     * 响应语音消息 （该函数保留给开发扩展功能回复特定的消息使用）
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function responseVoice()
    {
        $this->_post->Content = '';
        if ( isset($this->_post->Recognition) )
        {
            $this->_post->Content = $this->_post->Recognition;
        }
        
        return $this->responseText();
    }
        
    /**
     * 响应视频消息 （该函数保留给开发扩展功能回复特定的消息使用）
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function responseVideo()
    {        
    }
        
    /**
     * 响应地理位置消息 （该函数保留给开发扩展功能回复特定的消息使用）
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function responseLocation()
    {        
    }
        
    /**
     * 响应链接消息 （该函数保留给开发扩展功能回复特定的消息使用）
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function responseLink()
    {        
    }
}