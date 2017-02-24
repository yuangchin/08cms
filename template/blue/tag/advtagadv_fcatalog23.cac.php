<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog23 = array (
  'ename' => 'adv_fcatalog23',
  'tclass' => 'advertising',
  'template' => '<div class="mb5"> {if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank">{c$ad_image280 [cname=ad_image280/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=280/][maxheight=300/]}<img src="{url}" width="280" height="300" border="0" />{/c$ad_image280}</a>{elseif $v[\'flash\']}{c$flash280 [cname=flash280/] [tclass=flash/] [tname=flash/] [val=u/] [width=280/] [height=300/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;"></a>
            {/if}{/c$flash280}{/if} </div>
<div class="blank5"></div>',
  'setting' => 
  array (
    'casource' => 'fcatalog23',
    'validperiod' => '1',
    'orderstr' => 'a.vieworder DESC ',
  ),
) ;