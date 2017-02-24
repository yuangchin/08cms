<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog165 = array (
  'ename' => 'adv_fcatalog165',
  'tclass' => 'advertising',
  'template' => '<div id="ad{sn_row}">
    {if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" target="_blank"><img{c$image [tclass=image/] [tname=image/] [val=u/] [maxwidth=100/] [maxheight=300/]} src="{url}"  height="300" width="100"
{/c$image}/></a>{elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=f/] [width=100/] [height=300/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"><img width="{width}" height="{height}" src="{$cms_abs}userfiles/notdel/blank.gif"></a>
            {/if}{/c$flash}{/if}
    <div class="adclose tc" style="cursor:pointer" onclick="this.parentNode.style.display=\'none\'">关闭广告</div>
</div>',
  'setting' => 
  array (
    'limits' => 4,
    'casource' => 'fcatalog165',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;