<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog145 = array (
  'ename' => 'adv_fcatalog145',
  'tclass' => 'advertising',
  'template' => '<div class="imgbox jqDuang"  data-obj=".bigpic li" data-cell=".num">
                <div class="bigpic">
                    <ul>
                    {c$farc [tclass=farchives/] [casource=fcatalog145/] [limits=5/] [validperiod=1/]}
                     <li><a href="{link}" target="_blank" >{c$ad_image368_245 [tclass=image/] [tname=image/] [val=u/] [maxwidth=354/] [maxheight=246/]}<img src="{url}" width="354" height="246" alt="{$v[\'subject\']}" data-url="{$v[\'link\']}" />{/c$ad_image368_245}</a>
                      <a class="txt" target="_blank" href="">{subject}</a>
                     </li>
                     
                     {/c$farc}
                    </ul>
                </div>
                <ul class="num" id="num">
                    {c$farc [tclass=farchives/] [casource=fcatalog145/] [limits=5/] [validperiod=1/]}
                    <li>{sn_row}</li>
                  {/c$farc}
                </ul>
            
            </div>',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog145',
    'validperiod' => '1',
    'orderstr' => 'a.vieworder DESC ',
  ),
) ;