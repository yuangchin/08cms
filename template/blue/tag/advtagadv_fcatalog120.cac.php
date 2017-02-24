<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog120 = array (
  'ename' => 'adv_fcatalog120',
  'tclass' => 'advertising',
  'template' => '<div class="ad mb5">
            {if $v[\'html\']}{html}{elseif $v[\'image\']}
            <a href="{link}">
                {c$ad_image1200_90 [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight=70/]}
                <img src="{url}" width="1200" height="70" />
                {/c$ad_image1200_90}
            </a>
            {elseif $v[\'flash\']}{c$flash1200_90 [tclass=flash/] [tname=flash/] [val=u/] [width=1200/] [height=70/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>
            {/if}{/c$flash1200_90}{/if}
        </div>',
  'setting' => 
  array (
    'casource' => 'fcatalog120',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;