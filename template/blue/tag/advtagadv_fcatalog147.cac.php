<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog147 = array (
  'ename' => 'adv_fcatalog147',
  'tclass' => 'advertising',
  'template' => '<div class="mb5 clearfix">
             {if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank">{c$08cms2 [tclass=image/] [tname=image/] [val=u/] [maxwidth=743/] [maxheight=100/]}<img src="{url}"  height="100" width="743"/>
{/c$08cms2}</a>{elseif $v[\'flash\']}{c$fsdfs [cname=fsdfs/] [tclass=flash/] [tname=flash/] [val=f/] [width=743/] [height=100/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>{/if}{/c$fsdfs}{/if}
        </div>',
  'setting' => 
  array (
    'casource' => 'fcatalog147',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;