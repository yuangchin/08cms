<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog160 = array (
  'ename' => 'adv_fcatalog160',
  'tclass' => 'advertising',
  'template' => '<div class="ad mb5">
        {if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" >{c$ad_image1200_70 [cname=ad_image1200_70/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight=70/]}<img src="{url}" width="1200" height="70" />{/c$ad_image1200_70}</a>{elseif $v[\'flash\']}{c$flash1200_70 [cname=flash1200_70/] [tclass=flash/] [tname=flash/] [val=u/] [width=1200/] [height=70/]}{playbox}{if $v[\'link\']}<br><a href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash1200_70}{/if}
    </div>',
  'setting' => 
  array (
    'wherestr' => 'FIND_IN_SET(\'$farea\',a.farea)',
    'casource' => 'fcatalog160',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;