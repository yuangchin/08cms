<?php
!defined('M_COM') && exit('No Permission');
foreach(array('cotypes','channels','cnodes','catalogs') as $k) $$k = cls_cache::Read($k);
if($sitemap['ename'] == 'google'){
	include_once M_ROOT.'./include/archive.fun.php';
	$sqlstr = "WHERE checked=1";
	if(!empty($sitemap['setting']['indays'])){
		$sqlstr .= " AND createdate>".($timestamp - 86400 * $sitemap['setting']['indays']);
	}
	if(!empty($sitemap['setting']['chsource'])){
		$sqlstr .= " AND chid ".multi_str($sitemap['setting']['chids']);
	}
	if(!empty($sitemap['setting']['casource'])){
		$sqlstr .= " AND caid ".multi_str($sitemap['setting']['caids']);
	}
	foreach($cotypes as $coid => $cotype){
		if(!empty($sitemap['setting']['cosource'.$coid])){
			if($cnsql = cnsql($coid,$sitemap['setting']['ccids'.$coid],'')) $sqlstr .= " AND $cnsql";
		}
	}
	$datastr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
				"<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\n";
	$datastr .= "  <url>\n".
				"    <loc>".htmlspecialchars($cms_abs)."</loc>\n".
				"    <lastmod>".date('Y-m-d')."</lastmod>\n".
				"    <changefreq>daily</changefreq>\n".
				"    <priority>1.0</priority>\n".
				"  </url>\n";
				
	$query = $db->query("SELECT ename FROM {$tblprefix}cnodes WHERE cnlevel=1 AND closed=0 ORDER BY cnid LIMIT 0,1000");
	while($r = $db->fetch_array($query)){
		$cnode = cls_node::cnodearr($r['ename'],1);
		$datastr .= "  <url>\n".
					"    <loc>".htmlspecialchars($cnode['indexurl'])."</loc>\n".
					"    <lastmod>".date('Y-m-d')."</lastmod>\n".
					"    <changefreq>daily</changefreq>\n".
					"    <priority>0.8</priority>\n".
					"  </url>\n";
	}
	
	$query = $db->query("SELECT * FROM {$tblprefix}archives $sqlstr ORDER BY aid DESC LIMIT 0,5000");
	while($archive = $db->fetch_array($query)){
		$priority = $archive['clicks'] > 1000 ? '0.5' : '0.3';
		$datastr .= "  <url>\n".
					"    <loc>".htmlspecialchars(cls_ArcMain::Url($archive))."</loc>\n".
					"    <lastmod>".date('Y-m-d',$archive['createdate'])."</lastmod>\n".
					"    <changefreq>yearly</changefreq>\n".
					"    <priority>".($archive['clicks'] > 1000 ? '0.5' : '0.3')."</priority>\n".
					"  </url>\n";
	}
	$datastr .= "</urlset>";
}elseif($sitemap['ename'] == 'baidu'){
	include_once M_ROOT."./include/extends/arcedit.cls.php";
	$sqlstr = "WHERE checked=1";
	if(!empty($sitemap['setting']['indays'])){
		$sqlstr .= " AND createdate>".($timestamp - 86400 * $sitemap['setting']['indays']);
	}
	if(empty($sitemap['setting']['chsource'])){
		$sqlstr .= " AND chid ".multi_str($chids);
	}else{
		$sqlstr .= " AND chid ".multi_str($sitemap['setting']['chids']);
	}
	if(!empty($sitemap['setting']['casource'])){
		$sqlstr .= " AND caid ".multi_str($sitemap['setting']['caids']);
	}
	foreach($cotypes as $coid => $cotype){
		if(!empty($sitemap['setting']['cosource'.$coid])){
			if($cnsql = cnsql($coid,$sitemap['setting']['ccids'.$coid],'')) $sqlstr .= " AND $cnsql";
		}
	}
	$life = empty($sitemap['setting']['life']) ? 0 : $sitemap['setting']['life'];
	$datastr = "<?xml version=\"1.0\" encoding=\"$mcharset\"?>\n".
				"<document>\n".
				"  <webSite>".htmlspecialchars($cms_abs)."</webSite>\n".
				"  <webMaster>$adminemail</webMaster>\n".
				"  <updatePeri>".($life * 60)."</updatePeri>\n";

	$query = $db->query("SELECT aid FROM {$tblprefix}archives $sqlstr ORDER BY aid DESC LIMIT 0,100");
	$arc = new cls_arcedit;
	while($row = $db->fetch_array($query)){
		$aid = $row['aid'];
		$arc->init();
		$arc->set_aid($aid);
		$arc->detail_data(0);
		$datastr .= "  <item>\n".
					"    <title>".htmlspecialchars($arc->archive['subject'])."</title>\n".
					"    <link>".htmlspecialchars(cls_ArcMain::Url($arc->archive))."</link>\n".
					"    <text>".htmlspecialchars($arc->archive[$arc->channel['baidu']])."</text>\n".
					"    <image>".htmlspecialchars(cls_url::view_atmurl($arc->archive['thumb']))."</image>\n".
					"    <keywords>".htmlspecialchars($arc->archive['keywords'])."</keywords>\n".
					"    <category>".$catalogs[$arc->archive['caid']]['title']."</category>\n".
					"    <author>".htmlspecialchars($arc->archive['author'])."</author>\n".
					"    <source>".htmlspecialchars($arc->archive['source'])."</source>\n".
					"    <pubDate>".date('Y-m-d H:i:s',$arc->archive['createdate'])."</pubDate>\n".
					"  </item>\n";
	}
	$datastr .= "</document>";
}

?>