<?php

//************************************************************************
//
// eGeoVisor
// ============================================
//
// Copyright (c) 2008 by Geodata Sistemas S.L.
// http://www.geodata.es
// Written by Xose Manuel Pérez and Martí Pericay
//
// Generic functions file
//
// This program is free software. You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License.
//
//************************************************************************

function getIP() {
  if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
  return $ip;
}

function getFileContents($file) {
	$pipe = @fopen ($file, 'rb');
	if ($pipe) {
		while (!feof ($pipe)) {
			$line = fgets ($pipe, 2048);
			$buffer .= $line;
		}
		fclose($pipe);
    return $buffer;
	}
	return false;
}
/**
*	Gets file contents and stores it in a variable
* @param {boolean} $toDB Specifies if data is to be later transfered to an eGV database, to avoid %server% and %locale% substitutions (default is false)
* @returns $fileContent File contents
* @type string
*/
	function getContents($file) {
	
		// check file and download
		if (!$fileContent = getFileContents($file)) return false;
	  $fileContent = preg_replace("/(<!--)(.*)(-->)/e","",$fileContent);
	  
	  // download and substitute includes
	  $pattern = '/(<include([ ]+)file=([\'"]))(.+)(([\'"])([ ]*)\/>)/i';
		while (preg_match($pattern, $fileContent, $content)) {
			$includeFile = dirname($file).'/'.$content[4];
	    $includeContent = getContents($includeFile);
	    $fileContent = str_replace($content[0], $includeContent, $fileContent);
		}
		    
	  // return contents
	  return $fileContent;
	
	}

function getNodeValue($parent, $name, $default = false) {
  $node = $parent->getElementsByPath($name, 1);
	if ($node) return $node->getText();
	return $default;
}

function getAttributeValue($parent, $name, $default = false) {
  if ($parent->hasAttribute($name)) return $parent->getAttribute($name);
  return $default;
}

function getNodeAttributeValue($parent, $node, $name, $default = false) {
  $tmp = $parent->getElementsByPath($node, 1);
  if ($tmp) return getAttributeValue($tmp, $name, $default);
  return $default;
}

function removeApostrophe ($input) {
	$string = utf8_decode($input);
	$string = str_replace("'", "&#39;", $string);
	
  return $string;
}

function removeApostropheDB ($input) {
	//$string = utf8_decode($input);
	$string = str_replace("'", "&#39;", $input);
	
  return $string;
}

function str_contains($haystack, $needle, $ignoreCase = false) {
  if ($ignoreCase) {
    $haystack = strtolower($haystack);
    $needle  = strtolower($needle);
  }
  $needlePos = strpos($haystack, $needle);
  return ($needlePos === false ? false : ($needlePos+1));
}

function get_parameter($name, $default = null) {
  if (isset($_REQUEST[$name])) {
    return $_REQUEST[$name];
  } else {
    return $default;
  }
}

function get_url_parameter($url, $name, $default = null) {
  $parsed_url = parse_url($url);
  parse_str($parsed_url['query'],$parameters);
  if (isset($parameters[$name])) return $parameters[$name];
  return $default;
}

function set_url_parameter($url, $name, $value) {
  $parsed_url = parse_url($url);
  parse_str($parsed_url['query'],$parameters);
	$parameters[$name] = $value;
  $parsed_url['query'] = http_implode($parameters);
  $url = glue_url($parsed_url);
  return $url;
}

function http_implode($input) {
  if (!is_array($input)) return false;
  $url_query="";
  foreach ($input as $key=>$value) {
    $url_query .=(strlen($url_query)>1)?'&':"";
    $url_query .= urlencode($key).'='.urlencode($value);
  }
  return $url_query;
}

function glue_url($parsed) {
  if (! is_array($parsed)) return false;
  $uri = $parsed['scheme'] ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
  $uri .= $parsed['user'] ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
  $uri .= $parsed['host'] ? $parsed['host'] : '';
  $uri .= $parsed['port'] ? ':'.$parsed['port'] : '';
  $uri .= $parsed['path'] ? $parsed['path'] : '';
  $uri .= $parsed['query'] ? '?'.$parsed['query'] : '';
  $uri .= $parsed['fragment'] ? '#'.$parsed['fragment'] : '';
  return $uri;
}

function getBestFormat($formats, $raster = false) {

  if ($raster) {
    $sorted = array('image/jpeg', 'image/png', 'image/gif', 'image/wbmp');
  } else {
    $sorted = array('image/png', 'image/gif', 'image/jpeg', 'image/wbmp');
  }
  for ($i = 0; $i < count($sorted); $i++) {
    if (in_array($sorted[$i], $formats)) return $sorted[$i];
  }
  return false;

}

function getBestInfoFormat($formats) {

  $sorted = array('application/vnd.ogc.gml', 'text/plain', 'text/html', 'text/xml');
  for ($i = 0; $i < count($sorted); $i++) {
    if (in_array($sorted[$i], $formats)) return $sorted[$i];
  }
  return false;

}

function secure_tmpname($postfix = '.tmp', $prefix = 'tmp', $dir = null) {

	// validate arguments
	if (! (isset($postfix) && is_string($postfix))) return false;
	if (! (isset($prefix) && is_string($prefix))) return false;
	if (! (isset($dir))) $dir = getcwd();

  $tries = 1;
  do {
  
   	$filename = $dir.$prefix."_".$tries.$postfix;
   	if (!file_exists($filename)) return $filename;
   	$tries++;
  
  } while ($tries <= 1000);
  
  return false;

}

	/**
	*	Queries the DB with the SQL provided
	* @param {object} $db Database object
	* @param {string} $sql SQL query
	* @returns $out Array with data, false if no records or errors where found
	* @type array|boolean
	*/		
	function queryDB($db,$sql){
		
		if ($rs = $db->query($sql)) {
	    $records = array();
	    while ($record = $db->fetch_array($rs)) {
	    	$records[] = $record;
	    }
	    $db->free($rs);
	    
	    $out = (count($records)) ? $records : false;
	    
	    return $out;

		}
		return false;
	}
	
	function html2rgb($color){
		    if ($color[0] == '#')
		            $color = substr($color, 1);
		                if (strlen($color) == 6)
		                        list($r, $g, $b) = array($color[0].$color[1],
		                                                 $color[2].$color[3], 
		                                                 $color[4].$color[5]);
		               elseif (strlen($color) == 3)        
		               list($r, $g, $b) = array($color[0], $color[1], $color[2]);    
		               else        return false;    
		               $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);    
		               return array($r, $g, $b);
}

?>