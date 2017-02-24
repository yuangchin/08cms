本目录文件汇集了一些系统内置的开发配置缓存
与核心完全同步，核心升级直接覆盖
与extend_sample/dynamic/syscache(扩展系统的开发配置缓存)相对应

**********************

* amfuncs.cac.php：管理角色中的功能权限项目
* arcinitfields.cac.php：添加文档模型时，新建文档主表的初始架构性字段
* bknavurls.cac.php：管理后台的顶部子导航配置
  btagnames.cac.php：原始标识查询列表中，需要手动添加的项
  btagnames_del.cac.php：原始标识查询列表中，需要排除的项
* cachedos.cac.php：系统架构性缓存的生成规则配置
* cfregcodes.cac.php：系统内需要的验证码类型
* crbases.cac.php：积分增减策略中的设置项目
  sysparams.cac.php：系统基本的开发性配置(目前暂不支持扩展系统定制，不能复制到extend_sample/dynamic/syscache)
* ugallows.cac.php：会员组允许权限的设置项
* ugforbids.cac.php：会员组禁止操作的设置项
* _mconfigs.cac.php：用于打包时，需要强制固定的系统参数配置，系统本身并不使用这个配置。

  _certvars.cac.php：授权文件样本(可用于08cms.com及本地测试，纳入svn中更新)
  certvars.cac.php：实际授权文件，在svn中忽略

[注] 以上带*的文件，可复制到extend_sample/dynamic/syscache/中，作为扩展系统中的优先配置