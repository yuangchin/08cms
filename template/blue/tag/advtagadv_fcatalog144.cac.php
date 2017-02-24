<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog144 = array (
  'ename' => 'adv_fcatalog144',
  'tclass' => 'advertising',
  'template' => '{if $v[\'html\']}{html}{elseif $v[\'image\']}<a target="_blank" href="{link}"> {c$jzad_image790_70 [cname=jzad_image790_70/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=790/] [maxheight=70/]}<img src="{url}" width="790" height="70" alt="{$v[subject]}" />{/c$jzad_image790_70}</a>{elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=f/] [width=790/] [height=70/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"><img width="{width}" height="{height}" src="{$cms_abs}userfiles/notdel/blank.gif"/></a>{/if}{/c$flash}{/if}',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog144',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;