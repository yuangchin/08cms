<?php
function xml_unserialize(&$xml) {
	$xml_parser = new XML();
	$data = $xml_parser->parse($xml);
	$xml_parser->destruct();
	$arr = xml_format_array($data);
	return $arr['root'];
}

function xml_serialize(&$data, $htmlon = 0, $level = 1) {
	$space = str_repeat("\t", $level);
	$cdatahead = $htmlon ? '<![CDATA[' : '';
	$cdatafoot = $htmlon ? ']]>' : '';
	$s = '';
	if(!empty($data)) {
		foreach($data as $key => $val) {
			if(!is_array($val)) {
				$val = "$cdatahead$val$cdatafoot";
				if(is_numeric($key)) {
					$s .=  "$space<item_$key>$val</item_$key>";
				} elseif($key === '') {
					$s .= '';
				} else {
					$s .= "$space<$key>$val</$key>";
				}
			} else {
				if(is_numeric($key)) {
					$s .=  "$space<item_$key>".xml_serialize($val, $htmlon, $level+1)."$space</item_$key>";
				} elseif($key === '') {
					$s .= '';
				} else {
					$s .= "$space<$key>".xml_serialize($val, $htmlon, $level+1)."$space</$key>";
				}
			}
		}
	}
	$s = preg_replace("/([\x01-\x09\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
	return ($level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><root>" : '').$s.($level == 1 ? '</root>' : '');
}

function xml_format_array($arr, $level = 0) {
	foreach((array)$arr as $key => $val) {
		if(is_array($val)) {
			$val = xml_format_array($val, $level + 1);
		}
		if(is_string($key) && strpos($key, 'item_') === 0) {
			$arr[intval(substr($key, 5))] = $val;
			unset($arr[$key]);
		} else {
			$arr[$key] = $val;
		}
	}
	return $arr;
}

class XML {
	var $parser;
	var $document;
	var $parent;
	var $stack;
	var $last_opened_tag;

	function XML() {
		$this->parser = xml_parser_create('ISO-8859-1');
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}

	function destruct() {
		xml_parser_free($this->parser);
	}

	function parse(&$data) {
		$this->document = array();
		$this->stack	= array();
		$this->parent   = &$this->document;
		return xml_parse($this->parser, $data, true) ? $this->document : NULL;
	}

	function open(&$parser, $tag, $attributes) {
		$this->data = '';
		$this->last_opened_tag = $tag;
		if(is_array($this->parent) and array_key_exists($tag,$this->parent)) {
			if(is_array($this->parent[$tag]) and array_key_exists(0,$this->parent[$tag])) {
				$key = count_numeric_items($this->parent[$tag]);
			}else{
				if(array_key_exists($tag.'_attr',$this->parent)) {
					$arr = array('0_attr'=>&$this->parent[$tag.'_attr'], &$this->parent[$tag]);
					unset($this->parent[$tag.'_attr']);
				} else {
					$arr = array(&$this->parent[$tag]);
				}
				$this->parent[$tag] = &$arr;
				$key = 1;
			}
			$this->parent = &$this->parent[$tag];
		} else {
			$key = $tag;
		}
		if($attributes) {
			$this->parent[$key.'_attr'] = $attributes;
		}
		$this->parent  = &$this->parent[$key];
		$this->stack[] = &$this->parent;
	}

	function data(&$parser, $data) {
		if($this->last_opened_tag != NULL)
			$this->data .= $data;
	}

	function close(&$parser, $tag) {
		if($this->last_opened_tag == $tag) {
			$this->parent = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) $this->parent = &$this->stack[count($this->stack)-1];
	}
}

function count_numeric_items(&$array) {
	return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
}
function mfsockopen($url,$limit = 0,$post = '',$cookie = '',$bysocket = FALSE,$ip = '',$timeout = 15,$block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

?>