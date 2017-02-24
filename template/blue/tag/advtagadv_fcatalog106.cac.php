<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog106 = array (
  'ename' => 'adv_fcatalog106',
  'tclass' => 'advertising',
  'template' => '{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank">{c$image [cname=image/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=247/] [maxheight=168/]}<img src="{url}" width="247" height="168" border="0" />{/c$image}</a>{elseif $v[\'flash\']}{c$flash [cname=flash/] [tclass=flash/] [tname=flash/] [val=u/] [width=247/] [height=168/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash}{/if}',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog106',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;