<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog127 = array (
  'ename' => 'adv_fcatalog127',
  'tclass' => 'advertising',
  'template' => '        <div class="ad">{if $v[\'html\']}{html}{elseif $v[\'image\']}
                    <a href="{link}" >
                        {c$image [tclass=image/] [tname=image/] [val=u/] [maxwidth=658/] [maxheight=88/]}
                        <img src="{url}" width="658" height="88"/>
                        {/c$image}
                    </a>
                    {elseif $v[\'flash\']}{c$flash [tclass=flash/] [tname=flash/] [val=u/] [width=658/] [height=88/]}{playbox}{if $v[\'link\']}<a class="ad-link" href="{$v[\'link\']}" style="margin-top:-{height}px;width:{width}px;height:{height}px;" target="_blank"></a>
                    {/if}{/c$flash}{/if}
        </div>',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog127',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;