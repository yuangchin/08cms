<?php
defined('M_COM') || exit('No Permission');
$mnmenus = array (
  1 => 
  array (
    'title' => '常规管理',
    'childs' => 
    array (
      1 => 
      array (
        'title' => '文档合辑交互',
        'url' => 'javascript://content',
      ),
    ),
  ),
  51 => 
  array (
    'title' => '推送管理',
    'childs' => 
    array (
      303 => 
      array (
        'title' => '推送管理',
        'url' => 'javascript://pcontent',
      ),
    ),
  ),
  3 => 
  array (
    'title' => '广告管理',
    'childs' => 
    array (
      3 => 
      array (
        'title' => '副件管理',
        'url' => 'javascript://fcontent',
      ),
    ),
  ),
  4 => 
  array (
    'title' => '会员管理',
    'childs' => 
    array (
      4 => 
      array (
        'title' => '会员管理',
        'url' => 'javascript://mcontent',
      ),
    ),
  ),
  17 => 
  array (
    'title' => '其他内容',
    'childs' => 
    array (
      44 => 
      array (
        'title' => '页面静态',
        'url' => '?entry=static&action=index',
      ),
      302 => 
      array (
        'title' => '网站提问管理',
        'url' => '?entry=extend&extend=webtw&action=list',
      ),
      311 => 
      array (
        'title' => '碎片管理',
        'url' => '?entry=fragments',
      ),
      27 => 
      array (
        'title' => '采集管理',
        'url' => '?entry=gmissions&action=gmissionsedit',
      ),
      304 => 
      array (
        'title' => '手机短信',
        'url' => '?entry=sms_admin',
      ),
      312 => 
      array (
        'title' => '400电话管理',
        'url' => '?entry=webcall',
      ),
      59 => 
      array (
        'title' => '投票管理',
        'url' => '?entry=votes&action=votesedit',
      ),
      501 => 
      array (
        'title' => 'union400',
        'url' => '?entry=webcall400',
      ),
      60 => 
      array (
        'title' => '站内短信',
        'url' => '?entry=pms&action=pmsmanage',
      ),
      88 => 
      array (
        'title' => '附件管理',
        'url' => '?entry=userfiles&action=userfilesedit',
      ),
      313 => 
      array (
        'title' => '微信设置',
        'url' => '?entry=weixin',
      ),
      21 => 
      array (
        'title' => '积分与支付',
        'url' => '?entry=pays&action=paysedit',
      ),
      78 => 
      array (
        'title' => '不良词管理',
        'url' => '?entry=badwords',
      ),
      79 => 
      array (
        'title' => '热门关键词',
        'url' => '?entry=wordlinks',
      ),
      37 => 
      array (
        'title' => 'SiteMap地图',
        'url' => '?entry=sitemaps&action=sitemapsedit',
      ),
      26 => 
      array (
        'title' => '站点日志',
        'url' => '?entry=records&action=badlogin',
      ),
    ),
  ),
  15 => 
  array (
    'title' => '网站架构',
    'childs' => 
    array (
      35 => 
      array (
        'title' => '文档模型',
        'url' => '?entry=channels&action=channeledit',
      ),
      36 => 
      array (
        'title' => '栏目管理',
        'url' => '?entry=catalogs&action=catalogedit',
      ),
      305 => 
      array (
        'title' => '类目管理',
        'url' => '?entry=cotypes&action=cotypesedit',
      ),
      42 => 
      array (
        'title' => '扩展架构',
        'url' => '?entry=commus&action=commusedit',
      ),
      28 => 
      array (
        'title' => '积分设置',
        'url' => '?entry=currencys&action=currencysedit',
      ),
      29 => 
      array (
        'title' => '会员架构',
        'url' => '?entry=grouptypes&action=grouptypesedit',
      ),
    ),
  ),
  16 => 
  array (
    'title' => '模板风格',
    'childs' => 
    array (
      97 => 
      array (
        'title' => '模板设置',
        'url' => '?entry=tplconfig&action=tplbase',
      ),
      309 => 
      array (
        'title' => '模板绑定',
        'url' => '?entry=tplconfig&action=system',
      ),
      40 => 
      array (
        'title' => '类目节点管理',
        'url' => '?entry=cnodes&action=cnodescommon',
      ),
      116 => 
      array (
        'title' => '会员频道节点',
        'url' => '?entry=mcnodes',
      ),
      107 => 
      array (
        'title' => '会员空间模板',
        'url' => '?entry=mtconfigs&action=mtconfigsedit',
      ),
      310 => 
      array (
        'title' => '手机版设置',
        'url' => '?entry=o_tplconfig',
      ),
      51 => 
      array (
        'title' => '原始标识',
        'url' => '?entry=btags',
      ),
      54 => 
      array (
        'title' => '复合标识',
        'url' => '?entry=mtags&action=mtagsedit&ttype=ctag',
      ),
      57 => 
      array (
        'title' => '区块标识',
        'url' => '?entry=mtags&action=mtagsedit&ttype=rtag',
      ),
      308 => 
      array (
        'title' => '标识还原',
        'url' => '?entry=tags_restore',
      ),
    ),
  ),
  18 => 
  array (
    'title' => '系统设置',
    'childs' => 
    array (
      43 => 
      array (
        'title' => '网站参数',
        'url' => '?entry=mconfigs&action=cfsite',
      ),
      39 => 
      array (
        'title' => '管理后台',
        'url' => '?entry=backparams&action=bkparams',
      ),
      61 => 
      array (
        'title' => '会员中心',
        'url' => '?entry=backparams&action=mcparams',
      ),
      128 => 
      array (
        'title' => '房产参数',
        'url' => '?entry=extend&extend=exconfigs',
      ),
      32 => 
      array (
        'title' => '方案管理',
        'url' => '?entry=permissions&action=permissionsedit',
      ),
      126 => 
      array (
        'title' => '附属设置',
        'url' => '?entry=misc&action=cronedit',
      ),
      301 => 
      array (
        'title' => '禁止IP',
        'url' => '?entry=bannedips',
      ),
      22 => 
      array (
        'title' => '数据库相关',
        'url' => '?entry=database&action=dbexport',
      ),
      106 => 
      array (
        'title' => '系统缓存',
        'url' => '?entry=rebuilds',
      ),
    ),
  ),
) ;