<?php
/**
 * 扩展
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_sms_msend extends _08_M_Ajax_sms_msend_Base
{
	/*
    public function __toString(){   
		$this->init(); 
		//安全综合检测
		$re = $this->check_all(); 
		if($re['error']) return $re;
		//执行操作
		$func = "sms_".$this->act;
		return $this->$func();
    }*/
	
	// getmsg:得到含有占位符的信息
    public function get_msgExt(){   
		$msg = '';
		if($this->mpar['type']=='a' && in_array($this->mpar['chid'],array(4,115,116))){
			$msg = '{subject}'; 
			$msg .= empty($this->dbarr['address']) ? '' : '，地址:{address}';
			$msg .= empty($this->dbarr['tel']) ? '' : '，电话:{tel}';
			$msg .= empty($this->dbarr['dj']) ? '' : '，均价：{dj}元';
		}elseif($this->mpar['type']=='a'){
			$msg = '{subject}';
			$msg .= empty($this->dbarr['address']) ? '' : '，地址:{address}';
			$msg .= empty($this->dbarr['lxdh']) ? '' : '，联系电话:{lxdh}';
			$msg .= empty($this->dbarr['xingming']) ? '' : '，联系人:{xingming}';
		}elseif($this->mpar['type']=='m' && $this->mpar['chid']=='2'){
			$msg = '姓名：{xingming}';
			$msg .= empty($this->dbarr['lxdh']) ? '' : '，联系方式:{lxdh}';
			$msg .= empty($this->dbarr['email']) ? '' : '，电子邮件:{email}';
		}elseif($this->mpar['type']=='m' && $this->mpar['chid']=='3'){
			$msg = '{cmane}';
			$msg .= empty($this->dbarr['lxdh']) ? '' : '，联系方式:{lxdh}';
			$msg .= empty($this->dbarr['caddress']) ? '' : '，地址:{caddress}';
			$msg .= empty($this->dbarr['email']) ? '' : '，电子邮件:{email}';
		}elseif($this->mpar['type']=='m'){
			$msg = '{companynm}';
			$msg .= empty($this->dbarr['lxdh']) ? '' : '，联系方式:{lxdh}';
			$msg .= empty($this->dbarr['dizhi']) ? '' : '，地址:{dizhi}';
			$msg .= empty($this->dbarr['email']) ? '' : '，电子邮件:{email}'; 
		}
		return $msg;
	}
	// fix_msg:修正信息
    public function fix_msgArr(){   
		$this->fix_msgArrBase();
	}
}

/*
{if empty($ismem)}
	{if in_array($chid,array(4,115,116))}
	{subject}{if $address}，地址：{address}{/if}{if $tel}，电话:{tel}{/if}{if $dj}，均价：{dj}元{/if}【{hostname}】
	{else}
	{subject}{if $address}，地址：{address}{/if}{if $lxdh}，联系电话:{lxdh}{/if}{if $xingming}，联系人：{xingming}{/if}【{hostname}】
	{/if}
{else}
	{if $mchid==2}
	姓名：{xingming}{if $lxdh}，联系方式：{lxdh}{/if}，电子邮件：{email}【{hostname}】
	{elseif $mchid==3}
	{cmane}{if $lxdh}，联系方式：{lxdh}{/if}{if $caddress}，地址：{caddress}{/if}，电子邮件：{email}【{hostname}】
	{else}
	{companynm}{if $lxdh}，联系方式：{lxdh}{/if}{if $dizhi}，地址:{dizhi}{/if}，电子邮件：{email}
	{/if}
{/if}
*/
