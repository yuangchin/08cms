<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog166 = array (
  'ename' => 'adv_fcatalog166',
  'tclass' => 'advertising',
  'template' => '{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank"><img {c$image [tclass=image/] [tname=image/] [val=u/] [maxwidth=760/] [maxheight=120/]} src="{url}"  height="120" width="760"
                {/c$image}/></a>{elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=f/] [width=760/] [height=120/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"><img width="{width}" height="{height}" src="{$cms_abs}userfiles/notdel/blank.gif"/></a>{/if}{/c$flash}{/if}',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog166',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;