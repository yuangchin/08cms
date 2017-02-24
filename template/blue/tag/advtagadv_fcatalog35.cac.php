<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog35 = array (
  'ename' => 'adv_fcatalog35',
  'tclass' => 'advertising',
  'template' => '    <div class="blank30"></div>
{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank">{c$ad_image1200_60 [cname=ad_image1200_60/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight=60/]}<img src="{url}" width="1200" height="60" border="0" />{/c$ad_image1200_60}</a>{elseif $v[\'flash\']}{c$flash1200_60 [cname=flash1200_60/] [tclass=flash/] [tname=flash/] [val=u/] [width=1200/] [height=60/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash1200_60}{/if}
<div class="blank30"></div>
    ',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog35',
    'validperiod' => '1',
    'orderstr' => 'a.vieworder DESC ',
  ),
) ;