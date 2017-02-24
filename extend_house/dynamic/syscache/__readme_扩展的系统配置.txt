# 本目录存放当前扩展系统的开发性配置缓存，其中_xxx.cac.php是配置样本文件(通过核心升级)

# 实际使用的扩展开发性配置文件，有两种来源途径

  1、本路径下_xxx.cac.php格式的文件(带下划_)，复制为xxx.cac.php，则该配置实际生效

    _btagnames_del_son.cac.php：原始标识查询列表中，需要手动添加的项
    _btagnames_son.cac.php：原始标识查询列表中，需要排除的项
    _cocsmenus.cac.php：管理后台管理节点区的模块扩展
    _idkeeps.cac.php：官方升级模式下的架构性id保护区的配置，避免官方升级预留的部分架构性id为客户占用
    _dbst_keys.cac.php：系统用于对照的数据表索引分布，不需要手动维护，通过程序生成
    _extendscripts.cac.php：官方扩展系统的脚本定制入口配置
    _exconfigs.cac.php:扩展系统的系统参数配置，类似于mconfigs.cac.php
    _customscripts.cac.php：客户定制系统的脚本定制入口配置
    _exscripts.cac.php，旧的扩展脚本入口配置，暂时保留以兼容旧版本  
   
  2、复制dynamic/syscache中的同名文件，来生成当前扩展系统的配置
    本目录的配置优先于dynamic/syscache下的同名配置，扩展系统中不需要个性化的配置不需要复制，直接使用核心配置。

    amfuncs.cac.php：管理角色中的功能权限项目
    arcinitfields.cac.php：添加文档模型时，新建文档主表的初始架构性字段
    bknavurls.cac.php：管理后台的顶部子导航配置
    cachedos.cac.php：系统架构性缓存的生成规则配置
    cfregcodes.cac.php：系统内需要的验证码类型
    crbases.cac.php：积分增减策略中的设置项目
    tagconfigs.cac.php：模板复合标识的一些项目的配置
    ugallows.cac.php：会员组允许权限的设置项
    ugforbids.cac.php：会员组禁止操作的设置项
    _mconfigs.cac.php：用于打包时，需要强制固定的系统参数配置，系统本身并不使用这个配置，(请注意按同名规则命名)。

***************************
以下官方团队的开发备注：

# 核心系统中本目录svn版本控制处理方法
  只有 "说明文档" 及 "样本文件(带_)" 会通过核心升级覆盖，纳入svn版本控制。
  核心中extendcache下的调试性配置，各开发者在自已的系统中建立，不需要纳入svn控制，以免核心升级后覆盖扩展系统的该配置。

# 扩展系统中 "非样本文件" 的svn版本控制及升级方法
  完全纳入svn
  需要手动升级：参照样本文件或核心同名配置文件(dynamic/syscache)的升级内容，手动升级扩展系统的相应配置。






