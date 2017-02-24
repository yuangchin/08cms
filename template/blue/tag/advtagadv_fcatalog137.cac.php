<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog137 = array (
  'ename' => 'adv_fcatalog137',
  'tclass' => 'advertising',
  'template' => '<div class="ad mb5">
        {if $v[\'html\']}{html}{elseif $v[\'image\']}
        <a target="_blank" href="{link}">
            {c$image [cname=image/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight=70/]}
            <img src="{url}" width="1200" height="70" />
            {/c$image}
        </a>
        {elseif $v[\'flash\']}{c$flash_1200_60 [cname=flash_1200_60/] [tclass=flash/] [tname=flash/] [val=f/] [width=1200/] [height=70/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>
        {/if}{/c$flash_1200_60}{/if}
    </div>',
  'setting' => 
  array (
    'casource' => 'fcatalog137',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;