<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog139 = array (
  'ename' => 'adv_fcatalog139',
  'tclass' => 'advertising',
  'template' => '<div class="mb10">
                {if $v[\'html\']}{html}{elseif $v[\'image\']}<a target="_blank" href="{link}"> {c$image [cname=image/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=238/] [maxheight=150/]}<img src="{url}" width="238" height="150" />{/c$image} </a>{elseif $v[\'flash\']}{c$flash [cname=flash/] [tclass=flash/] [tname=flash/] [val=f/] [width=238/] [height=150/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash}{/if}
            </div>',
  'setting' => 
  array (
    'casource' => 'fcatalog139',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;