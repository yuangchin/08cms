<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog119 = array (
  'ename' => 'adv_fcatalog119',
  'tclass' => 'advertising',
  'template' => ' <div class="ad r">{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank">{c$ad_image_70 [cname=ad_image_70/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=788/] [maxheight=70/]}<img src="{url}" width="788" height="70" border="0" />{/c$ad_image_70}</a>{elseif $v[\'flash\']}{c$flash_788_70 [cname=flash_788_70/] [tclass=flash/] [tname=flash/] [val=u/] [width=788/] [height=70/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>
            {/if}{/c$flash_788_70}{/if}</div>',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog119',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;