<?php

/**
 * 系统默认全局filter
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-2
 * @copyright 2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwFilter.php 24859 2013-02-25 03:19:55Z jieyin $
 * @package src
 * @subpackage library.filter
 */
class PwFilter extends PwBaseFilter {
	
	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		/* 模板变量设置 */

		$url = array();
		$var = Wekit::url();
		$url['base'] = $var->base;
		$url['res'] = $var->res;
		$url['css'] = $var->css;
		$url['images'] = $var->images;
		$url['js'] = $var->js;
		$url['attach'] = $var->attach;
		$url['themes'] = $var->themes;
		$url['extres'] = $var->extres;
		Wekit::setGlobal($url, 'url');

		$request = array(
			'm' => $this->router->getModule(),
			'c' => $this->router->getController(),
			'a' => $this->router->getAction(),
		);
		$request['mc'] = $request['m'] . '/' . $request['c'];
		$request['mca'] = $request['mc'] . '/' . $request['a'];
		Wekit::setGlobal($request, 'request');

		$this->_setPreCache($request['m'], $request['mc'], $request['mca']);
		$loginUser = Wekit::getLoginUser();

		$config = Wekit::C('site');
		if ($config['visit.state'] > 0) {
			$service = Wekit::load('site.srv.PwSiteStatusService');
			$resource = $service->siteStatus($loginUser, $config);
			if ($resource instanceof PwError) {
				if (!($config['visit.state'] == 1 && $request['mc'] == 'u/login')) {
					$this->showError($resource->getError());
				}
			}
		}
		if (!in_array($request['mc'], array('u/login', 'u/register', 'u/findPwd')) && !$loginUser->getPermission('allow_visit')) {
			if ($loginUser->isExists()) {
				$this->showError(array('permission.visit.allow', array('{grouptitle}' => $loginUser->getGroupInfo('name'))));
			} else {
				$this->forwardRedirect(WindUrlHelper::createUrl('u/login/run'));
			}
		}
		if ($config['refreshtime'] > 0 && Wind::getApp()->getRequest()->isGet() && !Wind::getApp()->getRequest()->getIsAjaxRequest()) {
			if (Wekit::V('lastvist')->lastRequestUri == Wekit::V('lastvist')->requestUri && (Wekit::V('lastvist')->lastvisit + $config['refreshtime']) > Pw::getTime()) {
				$this->showError('SITE:refresh.fast');
			}
		}
		$this->_setPreHook($request['m'], $request['mc'], $request['mca']);

		$debug = $config['debug'] || !$config['css.compress'];
		Wekit::setGlobal(array('debug' => $debug ? '/dev' : '/build'), 'theme');
	}
	
	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {
		//门户管理模式 编译目录切换
		if ($this->getRequest()->getPost('design')) {
			$loginUser = Wekit::getLoginUser();
			$designPermission = $loginUser->getPermission('design_allow_manage.push');
			if ($designPermission > 0) {
				$dir = Wind::getRealDir('DATA:design.template');
				if (is_dir($dir)) WindFolder::rm($dir, true);
				$this->forward->getWindView()->compileDir = 'DATA:design.template';
			}
		}
		
		// SEO settings
		Wekit::setGlobal(NEXT_VERSION . ' ' . NEXT_RELEASE, 'version');
		$seo = Wekit::V('seo');
		Wekit::setGlobal($seo ? $seo->getData() : array('title' => Wekit::C('site', 'info.name')), 'seo');
		
		$this->setOutput($this->getRequest()->getIsAjaxRequest() ? '1' : '0', '_ajax_');
		
		/*[设置给PwGlobalFilters需要的变量]*/
		$_var = array(
			'current' => $this->forward->getWindView()->templateName,
			'a' => $this->router->getAction(),
			'c' => $this->router->getController(),
			'm' => $this->router->getModule());
		$this->getResponse()->setData($_var, '_aCloud_');
	}

	protected function _setPreCache($m, $mc, $mca) {
		$precache = Wekit::V('precache');
		if (isset($precache[$m])) Wekit::cache()->preset($precache[$m]);
		if (isset($precache[$mc])) Wekit::cache()->preset($precache[$mc]);
		if (isset($precache[$mca])) Wekit::cache()->preset($precache[$mca]);
	}

	protected function _setPreHook($m, $mc, $mca) {
		$prehook = Wekit::V('prehook');
		PwHook::preset($prehook['ALL']);
		PwHook::preset($prehook[Wekit::getLoginUser()->isExists() ? 'LOGIN' : 'UNLOGIN']);
		if (isset($prehook[$m])) PwHook::preset($prehook[$m]);
		if (isset($prehook[$mc])) PwHook::preset($prehook[$mc]);
		if (isset($prehook[$mca])) PwHook::preset($prehook[$mca]);
	}
}
?>