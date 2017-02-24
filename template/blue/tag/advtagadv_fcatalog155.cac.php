<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog155 = array (
  'ename' => 'adv_fcatalog155',
  'tclass' => 'advertising',
  'template' => '[row]
{if $v[\'sn_row\']%2==1}<?php $ad_height=70 ?>{else}<?php $ad_height=300 ?>{/if}
<div class="ppad{$v[\'sn_row\']} ad mb5" style="{if $v[\'sn_row\']%2==1}display: none;{/if}">
            {if $v[\'html\']}{html}{elseif $v[\'image\']}
            <a href="{link}">
                {c$ad_image1200_90 [tclass=image/] [tname=image/] [val=u/] [maxwidth=1200/] [maxheight={$ad_height}/]}
                <img src="{url}" width="1200" height="{$ad_height}" />
                {/c$ad_image1200_90}
            </a>
            {elseif $v[\'flash\']}{c$flash1200_90 [tclass=flash/] [tname=flash/] [val=u/] [width=1200/] [height={$ad_height}/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>
            {/if}{/c$flash1200_90}{/if}
</div>
[/row]

<script type="text/javascript">
    setTimeout(function(){$(\'.ppad2\').animate({height:\'hide\'},function(){$(this).prev().animate({height:\'show\'})})},5000);
</script>',
  'setting' => 
  array (
    'limits' => 4,
    'wherestr' => 'FIND_IN_SET(\'$farea\',a.farea)',
    'casource' => 'fcatalog155',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;