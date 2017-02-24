<?php
/**
 * Rewrite操作类
 * 目前只支持三种服务器软件：IIS、APACHE、NGINX
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class _08_Rewrite
{    
    protected $_virtualfile;
    
    /**
     * 是否开启伪静态
     **/
    protected $_virtualurl = false;
    
    private $mconfigs = array();
        
    /**
     * 获取要使用伪静态的入口文件
     */
    public static function getVirtualFiles()
    {
        $virtualfiles = array();
        # 定义要使用伪静态的入口脚本名称
        $files = array('/mspace/archive.php', '/mspace/index.php', '/wap/login.php', '/member/index.php',
                       '/index.php', '/archive.php', '/info.php');
        foreach($files as $file)
        {
            $virtualfiles[md5($file)] = $file;
        }
        
        return $virtualfiles;
    }
    
    /**
     * 创建伪静态规则
     */
    public function create( $server = '' )
    {
        $system = _08_SystemInfo::getInstance();
        $sysinfo = & $system->getInfo();
        $rule = $sysinfo['copyright'] . <<<EOT
        
##
# 使用该文件时请先阅读该内容
# 要使用该文件，请在服务器配置里开启Rewrite模块，具体配置方法请查看：http://www.08cms.com/html/tech/1157-1.html
##
EOT;
        $file = '.htaccess';
        
        # 自动获取
        if ( empty($server) )
        {            
            # 预防使用了反向代理的情况（注：该方法如果服务器未开启该返回信息，有可能会获取不成功）
            $header = $this->getProxyHeader();
            if ( isset($header['Server']) )
            {
                $sysinfo['server'] = $header['Server'];
            }
        }
        else # 手动指定 
        {
            $sysinfo['server'] = $server;
        }
        
        if ( false !== stripos($sysinfo['server'], 'IIS') )
        {
            @list(, $version) = explode('_', $sysinfo['server']);
            $version < 3 && $file = 'httpd.ini';
            $rule .= $this->getIISRule($sysinfo['server']);
        }
        else if ( false !== stripos($sysinfo['server'], 'APACHE') )
        {
        	$rule .= $this->getApacheRule();
        }
        else
        {
        	$rule .= $this->getNginxRule();
        }
        
        if ($this->_virtualurl)
        {
        	_08_FilesystemFile::getInstance()->_fwrite(
                array('file' => $file, 'string' => $rule, 'close' => true)
            );
        }
        else
        {
        	_08_FilesystemFile::getInstance()->delFile($file);
        }
        
    }
    
    /**
     * 获取IIS规则
     */
    public function getIISRule( $server_info )
    {
        @list(, $version) = explode('_', $server_info);
        if ( empty($version) )
        {
            $version = 3;
        }
        # ISAPI Rewrite 3 或以上的规则兼容于APACHE
        if ( $version > 2 )
        {
            $rule = $this->getApacheRule();
            $rule = str_replace(
                array('RewriteEngine'), 
                array("# ISAPI Rewrite3+\r\nRewriteEngine"),
                $rule
            );
        }
        else
        {
            $rule = <<<EOT

[ISAPI_Rewrite]
# Version 2.x-
# 3600 = 1 hour 
# CacheClockRate 3600
RepeatLimit 32

EOT;
            foreach( (array) $this->_virtualfile as $file)
            {
                $key = md5($file);
                $RewriteRule = str_replace('.php', '', $file);
                # 如果多个站共用一个Rewrite文件时要使用RewriteCond，以免影响其它网站
                $rule .= "RewriteCond Host: ^{$_SERVER['HTTP_HOST']}\$\r\n";
                $rule .= 'RewriteRule ^' . $RewriteRule . $this->mconfigs['rewritephp'] . '(.+)$ ' . $file . "?\$1 \r\n";
            }
        
          #  $rule .= "RewriteRule ^/_/(.*)\$ ?/\$1 \r\n";
        }
        
        return $rule;
    }
    
    /**
     * 获取APACHE规则
     */
    public function getApacheRule()
    {
        $rule = <<<EOT

RewriteEngine On\r\n
RewriteBase {$this->mconfigs['cmsurl']}\r\n
EOT;
        foreach( (array) $this->_virtualfile as $file)
        {
            $file = substr($file, 1);
            $key = md5($file);
            $RewriteRule = str_replace('.php', '', $file);
            $rule .= 'RewriteRule ^' . $RewriteRule . $this->mconfigs['rewritephp'] . '(.+)$ ' . $file . "?\$1 [L]\r\n";
        }
        
        return $rule;
    }
    
    /**
     * 获取NGINX规则
     */
    public function getNginxRule()
    {
        $rule = "\r\nrewrite /_/(.*)\$ /?/\$1 last;\r\n";
        $RewriteCond = array();
        foreach( (array) $this->_virtualfile as $file)
        {
            $key = md5($file);
            $RewriteCond[$key] = str_replace('.php', '', $file);
            $rule .= 'rewrite ' . $RewriteCond[$key] . $this->mconfigs['rewritephp'] . '(.+)$ ' . $file . "?\$1 last;\r\n";
        }
        
        return $rule;
    }
    
    /**
     * 获取代理的HTTP头
     */
    public function getProxyHeader()
    {
        _08_Loader::import('include:http.cls');
        $check_temp_file = 'check_server_temp.html';
        $file = _08_FilesystemFile::getInstance();
        $file->_fwrite( array('file' => $check_temp_file, 'close' => true) );
        list($server_protocol, $verison) = explode('/', @$_SERVER['SERVER_PROTOCOL']);
        if ( isset($_SERVER['HTTP_HOST']) )
        {
            $host = strtolower($server_protocol . '://' . $_SERVER['HTTP_HOST']) . '/';
        }
        else
        {
        	$host = @$this->mconfigs['cms_abs'];
        }
        # （注：当网络有很大的延时时请使用手动指定。）
        $header = http::getHeaders($host . $check_temp_file, 1);
        $file->delFile(M_ROOT . $check_temp_file, 'html');
        return $header;
    }
    
    public function __construct( $rewritephp = '', $virtualurl = false )
    {
        $this->mconfigs = (array) cls_cache::Read('mconfigs');
        empty($this->mconfigs) && cls_message::show('系统配置信息错误！');
        $this->_virtualfile = self::getVirtualFiles();
        $this->_virtualurl = (bool) $virtualurl;
        if ( empty($rewritephp) )
        {
            if ( isset($this->mconfigs['rewritephp']) )
            {
                $this->mconfigs['rewritephp'] = preg_quote($this->mconfigs['rewritephp']);
            }
        }
        else
        {
        	$this->mconfigs['rewritephp'] = preg_quote($rewritephp);
        }
    }
}