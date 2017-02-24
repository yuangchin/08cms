<?php
/**
 * HTML文档处理类
 * 该类依赖于：DOMDocument，详情请查看：{@link http://docs.php.net/manual/zh/class.domdocument.php}
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_Documents_HTML
{
    private $html = '';
    private $items = array();
    
    /**
     * 类似于JQuery的选择器，目前只支持选择DOM名称
     * 
     * @param mixed $items DOM名称（可用字符串或数组传递方式，字符串以英文逗号分隔）
     **/
    public function pQuery($items)
    {
        if (is_string($items))
        {
            $item = explode(',', $item);
        }
        else
        {
            $items = (array) $items;
        	foreach ($items as &$item)
            {
                if (is_string($item) && (false !== strpos($item, ',')))
                {
                    $item = explode(',', $item);
                }
                
                if (is_string($item) && (strtolower($item) === 'h'))
                {
                    $item = array();
                    for ($i = 1; $i <= 7; ++$i)
                    {
                        $item[] = "h{$i}";
                    }
                }
            }
            
            $items = cls_Array::_array_multi_to_one($items);
        }        
        
        $this->items = array_unique(array_map('strtolower', array_map('trim', $items)));
        
        return $this;
    }
    
    /**
     * 删除节点
     * 
     * @param  bool   $clearText 当前清除一个标签时，是否清除标签里面的内容，TRUE为清除，FALSE为不清除
     * @return string $text      返回清除后的内容
     **/
    public function remove($clearText = false)
    {
        $text = $this->html;
        
        if (in_array('all', $this->items))
        {
            $text = cls_string::HtmlClear($text);
        }
        else
        {
        	if ($trimKey = array_keys($this->items, 'trim'))
            {
                $text = trim($text);
                unset($this->items[$trimKey[0]]);
            }
            
            if ($tabKey = array_keys($this->items, 'tab'))
            {
                $text = str_replace(array("\n", "\r", "\t"), '', $text);
                unset($this->items[$tabKey[0]]);
            }
            
            if ($nbspKey = array_keys($this->items, 'nbsp'))
            {
                $text = str_replace('&nbsp;', '', $text);
                unset($this->items[$tabKey[0]]);
            }
            
            $regexp = '';
            foreach ($this->items as $item)
            {
                if (empty($item))
                {
                    continue;
                }
                
                if ($regexp)
                {
                    $regexp .= '|';
                }
                
                if (in_array($item, array('br', 'img', 'hr', 'input', 'meta', 'link')))
                {
                    $regexp .= "<{$item}.*>";
                }
                else
                {
                    if ($clearText || ($item === 'script'))
                    {
                        $split = '.*';
                    }
                    else
                    {
                    	$split = '|';
                    }
                    
                	$regexp .= "<{$item}.*>$split</$item>";
                }                
            }
            
            $text = str_replace(array('<!cmsurl />', '<!ftpurl />'), array('[--08CMS-cmsurl-]', '[--08CMS-ftpurl-]'), $text);
            $text = preg_replace("@{$regexp}@isU", '', $text);
            $text = str_replace(array('[--08CMS-cmsurl-]', '[--08CMS-ftpurl-]'), array('<!cmsurl />', '<!ftpurl />'), $text);
        }        
        
       # $this->html = $text;
        
        return $text;
    }
    
    public static function getInstance($html)
    {        
        return new self($html);
    }
    
    public function __construct($html)
    {
        $this->html = $html;
    }
}