<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog163 = array (
  'ename' => 'adv_fcatalog163',
  'tclass' => 'advertising',
  'template' => '<div class="ad">
{if $v[\'html\']}{html}{elseif $v[\'image\']}<a href="{link}" >
                            {c$ad_image380_30 [cname=ad_image380_30/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=380/] [maxheight=30/]}
                            <img src="{url}" width="380" height="30"  />
                            {/c$ad_image380_30}
                        </a>
                        {elseif $v[\'flash\']}{c$flash380_30 [cname=flash380_30/] [tclass=flash/] [tname=flash/] [val=u/] [width=380/] [height=30/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>
                        {/if}{/c$flash380_30}{/if}
</div>',
  'setting' => 
  array (
    'limits' => 1,
    'wherestr' => 'FIND_IN_SET(\'$farea\',a.farea)',
    'casource' => 'fcatalog163',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;