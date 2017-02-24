<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog114 = array (
  'ename' => 'adv_fcatalog114',
  'tclass' => 'advertising',
  'template' => '<div class="ad mb5">{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank"><img {c$image [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight=70/]} src="{url}"  height="70" width="1200"
{/c$image}/></a>{elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=f/] [width=1200/] [height=70/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash}{/if}</div>',
  'setting' => 
  array (
    'casource' => 'fcatalog114',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;