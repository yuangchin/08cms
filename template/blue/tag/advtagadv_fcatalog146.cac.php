<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog146 = array (
  'ename' => 'adv_fcatalog146',
  'tclass' => 'advertising',
  'template' => '<div class="mb5 wrap">{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank"><img {c$image [tclass=image/] [tname=image/] [val=u/] [maxwidth=1000/] [maxheight=70/]} src="{url}"  height="70" width="1000"
{/c$image}/></a>{elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=f/] [width=1000/] [height=70/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash}{/if}</div>',
  'setting' => 
  array (
    'casource' => 'fcatalog146',
    'validperiod' => '1',
    'orderstr' => 'a.vieworder DESC ',
  ),
) ;