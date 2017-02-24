<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog22 = array (
  'ename' => 'adv_fcatalog22',
  'tclass' => 'advertising',
  'template' => '<div class="tc ptb10">{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank">{c$image [cname=image/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=270/] [maxheight=250/]}<img src="{url}" width="270" height="250" />{/c$image}</a>{elseif $v[\'flash\']}{c$flash [cname=flash/] [tclass=flash/] [tname=flash/] [val=u/] [width=270/] [height=250/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash}{/if}
        </div>',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog22',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;