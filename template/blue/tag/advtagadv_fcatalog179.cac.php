<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog179 = array (
  'ename' => 'adv_fcatalog179',
  'tclass' => 'advertising',
  'template' => '<div class="ad mb5">
{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank"><img {c$image [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight=70/]} src="{url}"  height="70" width="1200"
{/c$image}/></a>{elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=f/] [width=1200/] [height=70/]}{playbox}{if $v[\'link\']}<a href="{$v[\'link\']}" style="position: relative;margin-top:-{height}px;width:{width}px;height:{height}px;display:block" target="_blank"><img width="{width}" height="{height}" src="{$cms_abs}userfiles/notdel/blank.gif"/></a>{/if}{/c$flash}{/if}
</div>',
  'setting' => 
  array (
    'casource' => 'fcatalog179',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;