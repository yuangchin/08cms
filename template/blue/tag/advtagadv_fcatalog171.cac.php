<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog171 = array (
  'ename' => 'adv_fcatalog171',
  'tclass' => 'advertising',
  'template' => '<div class="ad">
{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank"><img {c$image [tclass=image/] [tname=image/] [val=u/] [maxwidth=280/] [maxheight=300/]} src="{url}"  height="300" width="280"
{/c$image}/></a>{elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=f/] [width=280/] [height=300/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$flash}{/if}
</div>',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog171',
    'validperiod' => '1',
    'orderstr' => 'a.vieworder DESC ',
  ),
) ;