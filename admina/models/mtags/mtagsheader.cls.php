<?php
/**
 * 标识处理类公共头部
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
 
class cls_mtagsHeader extends cls_modelsHeader
{ 
    public function __construct()
    {
        parent::__construct();
        foreach(array('ttype', 'tname', 'fn', 'types', 'textid', 'caretpos', 'src_type', 'floatwin_id', 'ename') as $v)
        {
            empty($this->params[$v]) || $this->url .= "&$v={$this->params[$v]}";
        }
        
        if(!empty($this->params['tclass']))
        {
            $this->url .= "&tclass={$this->params['tclass']}";
        }
        else if(!empty($this->params['mtagnew']['tclass']))
        {
            $this->url .= "&tclass={$this->params['mtagnew']['tclass']}"; 
        }
    }
    
    public static function showTagTitle( $mtag, $mtagnew )
    {
        global $tname, $iscopy, $tclass, $fn,$cms_abs, $ttype;
		
        @$mtag = _tag_merge((array) $mtag,(array) $mtagnew);
		
        empty($_POST) || $tname = @$mtag['ename'];
		trbasic('标识标题','mtagnew[cname]',(isset($mtag['cname']) ? $mtag['cname'] : '').($iscopy ? '_复件' : ''),'text', array('validate' => makesubmitstr('mtagnew[cname]',0,0,3,30)));
		trbasic('*标识英文名称','mtagnew[ename]',$tname.($iscopy ? '_cp' : ''),'text', array('validate' => makesubmitstr('mtagnew[ename]',1,'tagtype',3,32)));
        $older = empty($iscopy)?(empty($mtag['ename'])?'':$mtag['ename']):'';
		$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC("ajax=check_mtagename&older={$older}&tag=$ttype&val=%1");
		if($ttype == 'rtag'){//复合标识暂没有用Ajax验证重复
			echo _08_HTML::AjaxCheckInput('mtagnew[ename]', $ajaxURL);
		}

        $mtagses = _08_factory::getMtagsInstance($tclass);
        if ( is_object($mtagses) )
        {
            $mtagses->showCotypesSelect($mtag);
            # 如果是编辑选中时让定义sclass
            if( !empty($fn) && empty($_POST) )
            {
                trhidden('_sclass', @$mtagses->getSclass((array)$mtag['setting']));
            }
        }
        return $mtag;
    }
}

