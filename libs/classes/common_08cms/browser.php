<?php
/**
 * 浏览器类，提供有关当前Web客户端的信息。
 * 浏览器识别是通过检查HTTP_USER_AGENT进行，由Web服务器提供的环境变量
 * 
 * @example    $browser = _08_Browser::getInstance();
               var_dump('isMobile: ' . $browser->isMobile());
                  echo '<br />';
               var_dump('Browser: ' . $browser->getBrowser());
                   echo '<br />';
               var_dump('Version: ' . $browser->getVersion());
                   echo '<br />';
               var_dump('Platform: ' . $browser->getPlatform());
                   echo '<br />';
               var_dump('isAndroid: ' .$browser->isAndroid());
                   echo '<br />';
               var_dump('isIPad: ' . $browser->isIPad());
                   echo '<br />';
               var_dump('isIPhone: ' . $browser->isIPhone());
                   echo '<br />';
               var_dump('PlatformVersion: ' . $browser->getPlatformVersion());
 * @package    08CMS.Platform
 * @subpackage common_08cms
 * 
 * @author     Wilson <Wilsonnet@163.com>
 * @copyright  Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_Browser
{
	/**
	 * @var   integer 主版本号
	 * @since nv50
	 */
	protected $_majorVersion = 0;

	/**
	 * @var   integer 次版本号
	 * @since nv50
	 */
	protected $_minorVersion = 0;
    
	/**
	 * @var   string 完整的用户代理字符串
	 * @since nv50
	 */
	protected $_agent = '';

	/**
	 * @var    string  HTTP_ACCEPT 字符串.
	 * @since  nv50
	 */
	protected $_accept = '';

	/**
	 * @var   string 小写的用户代理字符串
	 * @since nv50
	 */
	protected $_lowerAgent = '';

    /**
     * 移动设备
     * 
	 * @since nv50
     */
    protected $_mobile = false;
    
    /**
     * IOS系统
     */
    protected $_iphone = false;
    
    /**
     * iPad
     */
    protected $_ipad = false;
    
    /**
     * Android系统
     */
    protected $_android = false;

    /**
     * 搜索引擎
     * 
	 * @since nv50
     */
    protected $_robots = false;
    
    /**
     * 浏览器
     * 
	 * @since nv50
     */
    protected $_browser = false;

	/**
	 * @var   string 目前浏览器上运行的平台
	 * @since nv50
	 */
	protected $_platform = '';
    
	/**
	 * @var   integer 平台主版本号
	 * @since nv50
	 */
	protected $_platformMajorVersion = 0;

	/**
	 * @var   integer 平台次版本号
	 * @since nv50
	 */
	protected $_platformMinorVersion = 0;

	/**
	 * MIME图片类型列表
	 * 该列表可用于： IE、Netscape、Mozilla.
	 *
	 * @var   array
	 * @since nv50
	 */
	protected $_images = array('jpeg', 'gif', 'png', 'pjpeg', 'x-png', 'bmp');
    
    /**
     * @var   array 常见的爬虫名称列表
	 * @since nv50
     */
    protected $_robots_tables = array(
		'Googlebot',     # Google
        'Baiduspider',   # 百度
		'msnbot',        # MSN
		'bingbot',       # Bing
		'Yahoo',         # 雅虎
        'YodaoBot',      # 有道
        'Sosospider',    # 搜搜
        '360Spider',     # 360
        'Sogou',         # 搜狗
        'iaskspider',    # 新浪
        
        # 其它不知名的
        'AhrefsBot',        
		'Arachnoidea',
		'ArchitextSpider',
		'Ask Jeeves',
		'B-l-i-t-z-Bot',
		'BecomeBot',
		'cfetch',
		'ConveraCrawler',
		'ExtractorPro',
        'EasouSpider',   # 宜搜
		'FAST-WebCrawler',
		'FDSE robot',
		'fido',
		'geckobot',
		'Gigabot',
		'Girafabot',
		'grub-client',
		'Gulliver',
		'HTTrack',
		'ia_archiver',   # alexa
		'InfoSeek',
		'kinjabot',
		'KIT-Fireball',
		'larbin',
		'LEIA',
		'lmspider',
		'Lycos_Spider',
		'Mediapartners-Google',
		'MuscatFerret',
		'NaverBot',
		'OmniExplorer_Bot',
		'polybot',
		'Pompos',
		'Scooter',
		'Teoma',
		'TheSuBot',
		'TurnitinBot',
		'Ultraseek',
		'ViolaBot',
		'webbandit',
		'www.almaden.ibm.com/cs/crawler',
        'YisouSpider',
		'ZyBorg'
    );
    
	/**
	 * @var   array 浏览器对象数组
	 * @since nv50
	 */
	protected static $_instances = array();
    
    /**
	 * 创建一个浏览器实例（构造函数）
	 *
	 * @param string $userAgent 浏览器字符串解析
	 * @param string $accept    该HTTP_ACCEPT设置使用
	 *
	 * @since nv50
	 */
	protected function __construct($userAgent = null, $accept = null)
	{
		$this->match($userAgent, $accept);
	}
    
    protected function __clone(){}
    
    /**
	 * 获取浏览器对象，如果它不存在则创建
	 *
	 * @param  string $userAgent 浏览器字符串解析
	 * @param  string $accept    该HTTP_ACCEPT设置使用
	 * @return object            返回获取到的浏览器对象
	 *
	 * @since  nv50
	 */
	public static function getInstance($userAgent = null, $accept = null)
	{
		$signature = serialize(array($userAgent, $accept));

		if (empty(self::$_instances[$signature]))
		{
			self::$_instances[$signature] = new self($userAgent, $accept);
		}

		return self::$_instances[$signature];
	}
    
    /**
     * 匹配环境
     * 
	 * @param string $userAgent 浏览器字符串解析
	 * @param string $accept    该HTTP_ACCEPT设置使用
     * 
     * @since nv50
     */
    public function match($userAgent = null, $accept = null)
    {
        if ( is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT']) )
		{
			$this->_agent = trim($_SERVER['HTTP_USER_AGENT']);
		}
		else
		{
			$this->_agent = $userAgent;
		}
        
        $this->_lowerAgent = strtolower($this->_agent);
        
        if ( is_null($accept) && isset($_SERVER['HTTP_ACCEPT']) )
		{
		    $this->_accept = trim($_SERVER['HTTP_ACCEPT']);
		}
		else
		{
			$this->_accept = $accept;
		}
        
        $this->_accept = strtolower($this->_accept);
        
        if ( !empty($this->_agent) )
		{
			$this->_setPlatform();

			if ( (strpos($this->_lowerAgent, 'mobileexplorer') !== false) || (strpos($this->_lowerAgent, 'openwave') !== false) ||
                 (strpos($this->_lowerAgent, 'opera mini') !== false) || (strpos($this->_lowerAgent, 'opera mobi') !== false) ||
                 (strpos($this->_lowerAgent, 'operamini') !== false) )
			{
				$this->_mobile = true;
			}
			else if (preg_match('|Opera[/ ]([0-9.]+)|', $this->_agent, $version))
			{
				$this->setBrowser('opera');
				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);

                /**
                 * 由于改变了Opera的UA信息, 所以我们需要检查版本：XX.YY（但只有当版本> 9.80时）
                 * 详情请查看：{@link http://dev.opera.com/articles/view/opera-ua-string-changes/}
                 */
				if ($this->_majorVersion == 9 && $this->_minorVersion >= 80)
				{
					$this->_identifyBrowserVersion();
				}
			}
			else if ( preg_match('|Chrome[/ ]([0-9.]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('chrome');
				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
			}
			else if ( preg_match('|CrMo[/ ]([0-9.]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('chrome');
				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
			}
			else if ( preg_match('|CriOS[/ ]([0-9.]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('chrome');
				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
				$this->_mobile = true;
			}
			else if ( (strpos($this->_lowerAgent, 'elaine/') !== false) || (strpos($this->_lowerAgent, 'palmsource') !== false) ||
                      (strpos($this->_lowerAgent, 'digital paths') !== false) )
			{
				$this->setBrowser('palm');
				$this->_mobile = true;
			}
			else if ( (preg_match('|MSIE ([0-9.]+)|', $this->_agent, $version)) || 
					  (preg_match('|Trident/[0-9.]+\; rv\:([0-9.]+)|', $this->_agent, $version)) || // ie11: Trident/7.0; rv:11.0
                      (preg_match('|Internet Explorer/([0-9.]+)|', $this->_agent, $version)) )
			{
				$this->setBrowser('msie');

				if (strpos($version[1], '.') !== false)
				{
					list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
				}
				else
				{
					$this->_majorVersion = $version[1];
					$this->_minorVersion = 0;
				}

				# 匹配其它移动设备信息
				if (preg_match('/; (120x160|240x280|240x320|320x320)\)/', $this->_agent))
				{
					$this->_mobile = true;
				}
			}
			else if ( preg_match('|amaya/([0-9.]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('amaya');
				$this->_majorVersion = $version[1];

				if (isset($version[2]))
				{
					$this->_minorVersion = $version[2];
				}
			}
			else if ( preg_match('|ANTFresco/([0-9]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('fresco');
			}
			else if ( strpos($this->_lowerAgent, 'avantgo') !== false )
			{
				$this->setBrowser('avantgo');
				$this->_mobile = true;
			}
			else if ( preg_match('|Konqueror/([0-9]+)|', $this->_agent, $version) ||
                      preg_match('|Safari/([0-9]+)\.?([0-9]+)?|', $this->_agent, $version) )
			{
				$this->setBrowser('konqueror');
				$this->_majorVersion = $version[1];

				if (isset($version[2]))
				{
					$this->_minorVersion = $version[2];
				}

				// Safari.
				if (strpos($this->_agent, 'Safari') !== false )
				{
				    if (strpos($this->_agent, 'Mobile') !== false )
                    {
                        $this->_mobile = true;
                    }
                    
                    if ( $this->_majorVersion >= 60 )
                    {
    					$this->setBrowser('safari');
    					$this->_identifyBrowserVersion();
                    }
				}
			}
			else if ( preg_match('|Mozilla/([0-9.]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('mozilla');

				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
                
			    if (strpos($this->_agent, 'Mobile') !== false )
                {
                    $this->_mobile = true;
                }
			}
			else if ( preg_match('|Lynx/([0-9]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('lynx');
			}
			else if ( preg_match('|Links \(([0-9]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('links');
			}
			else if ( preg_match('|HotJava/([0-9]+)|', $this->_agent, $version) )
			{
				$this->setBrowser('hotjava');
			}
			else if ( (strpos($this->_agent, 'UP/') !== false) || (strpos($this->_agent, 'UP.B') !== false) ||
                      (strpos($this->_agent, 'UP.L') !== false) )
			{
				$this->setBrowser('up');
				$this->_mobile = true;
			}
			else if (strpos($this->_agent, 'Xiino/') !== false)
			{
				$this->setBrowser('xiino');
				$this->_mobile = true;
			}
			else if (strpos($this->_agent, 'Palmscape/') !== false)
			{
				$this->setBrowser('palmscape');
				$this->_mobile = true;
			}
			else if (strpos($this->_agent, 'Nokia') !== false)
			{
				$this->setBrowser('nokia');
				$this->_mobile = true;
			}
			else if (strpos($this->_agent, 'Ericsson') !== false)
			{
				$this->setBrowser('ericsson');
				$this->_mobile = true;
			}
			else if (strpos($this->_lowerAgent, 'wap') !== false)
			{
				$this->setBrowser('wap');
				$this->_mobile = true;
			}
			else if (strpos($this->_lowerAgent, 'docomo') !== false || strpos($this->_lowerAgent, 'portalmmm') !== false)
			{
				$this->setBrowser('imode');
				$this->_mobile = true;
			}
			else if ( strpos($this->_agent, 'BlackBerry') !== false )
			{
				$this->setBrowser('blackberry');
				$this->_mobile = true;
			}
			else if ( strpos($this->_agent, 'MOT-') !== false )
			{
				$this->setBrowser('motorola');
				$this->_mobile = true;
			}
			else if ( strpos($this->_lowerAgent, 'j-') !== false )
			{
				$this->setBrowser('mml');
				$this->_mobile = true;
			}
		}
    }
    
    /**
	 * 设置当前浏览器的系统平台
	 *
	 * @since nv50
	 */
	protected function _setPlatform()
	{
		if (strpos($this->_lowerAgent, 'wind') !== false)
		{
			$this->_platform = 'win';
		}
		else if (strpos($this->_lowerAgent, 'mac') !== false)
		{
			$this->_platform = 'mac';
            if ( false !== strpos($this->_lowerAgent, 'ipad') )
            {
                $this->_ipad = true;
            }
            else if ( false !== strpos($this->_lowerAgent, 'iphone') )
            {
                $this->_iphone = true;
            }
            
            if ( preg_match('| os[/ ]([\d_]+)|', $this->_lowerAgent, $version) )
            {
                list ($this->_platformMajorVersion, $this->_platformMinorVersion) = explode('_', $version[1]);
            }
		}
		else
		{
			$this->_platform = 'unix';
            
            if ( preg_match('| android[/ ]([\d.]+)|', $this->_lowerAgent, $version) )
            {
                $this->_android = true;
                list ($this->_platformMajorVersion, $this->_platformMinorVersion) = explode('.', $version[1]);
            }
		}
	}

	/**
	 * Return the currently matched platform.
	 *
	 * @return  string  The user's platform.
	 *
	 * @since   nv50
	 */
	public function getPlatform()
	{
		return $this->_platform;
	}

	/**
	 * 设置浏览器的版本，而不是由引擎版本降速使用时没有其他方法识别引擎版本
	 *
	 * @since nv50
	 */
	protected function _identifyBrowserVersion()
	{
		if (preg_match('|Version[/ ]([0-9.]+)|', $this->_agent, $version))
		{
			list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
		}
        else
        {
    		// 无法识别浏览器版本时均设为0
    		$this->_majorVersion = 0;
    		$this->_minorVersion = 0;
        }
	}

	/**
	 * 设置当前浏览器信息
	 *
	 * @param  string $browser 要设置的浏览器信息
	 *
	 * @since  nv50
	 */
	public function setBrowser($browser)
	{
		$this->_browser = $browser;
	}

	/**
	 * 获取当前浏览器信息
	 *
	 * @return string 返回当前浏览器信息
	 * @since  nv50
	 */
	public function getBrowser()
	{
		return $this->_browser;
	}

	/**
	 * 获取主版本号
	 *
	 * @return  integer 返回主版本号
	 * @since   nv50
	 */
	public function getMajor()
	{
		return $this->_majorVersion;
	}

	/**
	 * 获取次版本号
	 *
	 * @return  integer 返回次版本号
	 * @since   nv50
	 */
	public function getMinor()
	{
		return $this->_minorVersion;
	}

	/**
	 * 获取当前客户端浏览器版本
	 *
	 * @return  string 返回当前客户端版本信息
	 * @since   nv50
	 */
	public function getVersion()
	{
		return $this->_majorVersion . '.' . $this->_minorVersion;
	}

	/**
	 * 获取当前平台版本
	 *
	 * @return  string 返回当前客户端版本信息
	 * @since   nv50
	 */
	public function getPlatformVersion()
	{
		return $this->_platformMajorVersion . '.' . $this->_platformMinorVersion;
	}

	/**
	 * 获取完整的浏览器代理字符串
	 *
	 * @return string 返回完整的浏览器代理字符串
	 * @since  nv50
	 */
	public function getAgentString()
	{
		return $this->_agent;
	}

	/**
	 * 获取当前服务器上使用的HTTP协议信息
	 *
	 * @return  string  返回当前服务器上使用的HTTP协议信息
	 * @since   nv50
	 */
	public function getHTTPProtocol()
	{
		if ( isset($_SERVER['SERVER_PROTOCOL']) )
		{
			if ( $pos = strrpos($_SERVER['SERVER_PROTOCOL'], '/') )
			{
				return substr($_SERVER['SERVER_PROTOCOL'], $pos + 1);
			}
		}

		return null;
	}

	/**
	 * 确定一个浏览器可否显示给定的MIME类型
	 *
	 * 注意： image / jpeg文件和 image/pjpeg 是相同的，但是Mozilla并不想接受后者。所以我们将把它们看成是相同的。
	 *
	 * @param   string  $mimetype 要检查的MIME类型
	 * @return  boolean           如果可以显示该MIME类型返回TRUE，否则返回FALSE
	 *
	 * @since   nv50
	 */
	public function isViewable($mimetype)
	{
		$mimetype = strtolower($mimetype);
		list ($type, $subtype) = explode('/', $mimetype);

		if (!empty($this->_accept))
		{
			$wildcard_match = false;

			if (strpos($this->_accept, $mimetype) !== false)
			{
				return true;
			}

			if (strpos($this->_accept, '*/*') !== false)
			{
				$wildcard_match = true;

				if ($type != 'image')
				{
					return true;
				}
			}

			// 处理Mozilla的PJPEG / jpeg文件的问题
			if ($this->isBrowser('mozilla') && ($mimetype == 'image/pjpeg') && (strpos($this->_accept, 'image/jpeg') !== false))
			{
				return true;
			}

			if (!$wildcard_match)
			{
				return false;
			}
		}
        
		if ( $type != 'image')
		{
			return false;
		}

		return (in_array($subtype, $this->_images));
	}

	/**
	 * 判断客户端当前使用的是不是浏览器并与$browser是否相同
	 *
	 * @param  string  $browser 要检查的浏览器信息
	 * @return boolean          如果相同返回TRUE，否则返回FALSE
	 *
	 * @since   nv50
	 */
	public function isBrowser($browser)
	{
		return ($this->_browser === $browser);
	}

	/**
	 * 判断客户端是否为搜索引擎蜘蛛爬虫
	 *
	 * @return  boolean 如果是搜索引擎蜘蛛爬虫返回TRUE，否则返回FALSE
	 *
	 * @since   nv50
	 */
	public function isRobot()
	{
		foreach ($this->_robots_tables as $robot)
		{
			if ( false !== strpos($this->_agent, $robot) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * 判断客户端是否为移动设备
	 *
	 * @return boolean 如果是移动设备返回TRUE，否则返回FALSE
	 *
	 * @since  nv50
	 */
	public function isMobile()
	{
	    if ( $this->isAndroid() || $this->isIPhone() )
        {
            $this->_mobile = true;
        }
        
	    return $this->_mobile;
	}

	/**
	 * 判断客户端是否为iPhone
	 *
	 * @return boolean 如果是移动设备返回TRUE，否则返回FALSE
	 *
	 * @since  nv50
	 */
	public function isIPhone()
	{
		return $this->_iphone;
	}

	/**
	 * 判断客户端是否为iPad
	 *
	 * @return boolean 如果是移动设备返回TRUE，否则返回FALSE
	 *
	 * @since  nv50
	 */
	public function isIPad()
	{
		return $this->_ipad;
	}

	/**
	 * 判断客户端是否为Android
	 *
	 * @return boolean 如果是移动设备返回TRUE，否则返回FALSE
	 *
	 * @since  nv50
	 */
	public function isAndroid()
	{
		return $this->_android;
	}

	/**
	 * 判断是否来SSL链接
	 *
	 * @return  boolean 如果是返回TRUE，否则返回FALSE
	 *
	 * @since   nv50
	 */
	public function isSSLConnection()
	{
		return ( (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION') );
	}
    
    /**
     * 获取HTTP链接类型
     * 
     * @return string 返回获取到的类型
     * @since  nv50
     */
    public function getHttpConnection()
    {
        if ( $this->isSSLConnection() )
        {
            return 'https://';
        }
        
        return 'http://';
    }
}

