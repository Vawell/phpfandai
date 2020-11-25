<?php 

set_time_limit(60); 

if( !defined('__DIR__') ) 

{ 

define('__DIR__',dirname(__FILE__)) ; 

} 

$_REQUEST['url'] =gtRootUrl(); 

//改成网站正式服务器ip 

$ip= '127.0.0.1'; 

$aAccess = curl_init() ; 

// -------------------- 

// set URL and other appropriate options 

curl_setopt($aAccess, CURLOPT_URL, $_REQUEST['url']); 

curl_setopt($aAccess, CURLOPT_HEADER, true); 

curl_setopt($aAccess, CURLOPT_RETURNTRANSFER, true); 

curl_setopt($aAccess, CURLOPT_FOLLOWLOCATION, false); 

curl_setopt($aAccess, CURLOPT_SSL_VERIFYPEER, false); 

curl_setopt($aAccess, CURLOPT_SSL_VERIFYHOST, false); 

curl_setopt($aAccess, CURLOPT_TIMEOUT, 60); 

curl_setopt($aAccess, CURLOPT_BINARYTRANSFER, true); 

//curl_setopt($aAccess, CURLOPT_HTTPPROXYTUNNEL, 0); 

curl_setopt($aAccess,CURLOPT_PROXY,$ip.':80'); 

//curl_setopt($aAccess,CURLOPT_PROXY,'127.0.0.1:8888'); 

if(!empty($_SERVER['HTTP_REFERER'])) 

curl_setopt($aAccess,CURLOPT_REFERER,$_SERVER['HTTP_REFERER']) ; 

$headers=get_client_header(); 

curl_setopt($aAccess,CURLOPT_HTTPHEADER,$headers) ; 

if( $_SERVER['REQUEST_METHOD']=='POST' ) 

{ 

	curl_setopt($aAccess, CURLOPT_POST, 1); 

	curl_setopt($aAccess, CURLOPT_POSTFIELDS, http_build_query($_POST)); 

} 

// grab URL and pass it to the browser 

$sResponse = curl_exec($aAccess); 

list($headerstr,$sResponse)=parseHeader($sResponse); 

$headarr= explode("\r\n", $headerstr); 

foreach($headarr as $h){ 

	if(strlen($h)>0){ 

		if(strpos($h,'Content-Length')!==false) continue; 

		if(strpos($h,'Transfer-Encoding')!==false) continue; 

		if(strpos($h,'Connection')!==false) continue; 

		if(strpos($h,'HTTP/1.1 100 Continue')!==false) continue; 

		header($h); 

	} 

} 

function replace_html_path($arrMatche) 

{	 

	$sPath = makeUrl($arrMatche[4]) ; 

	if( strtolower($arrMatche[1])=='img' ) 

	{ 

		$sPath.= '&bin=1' ; 

	} 

	 

	return "<{$arrMatche[1]} {$arrMatche[2]} {$arrMatche[3]}=\"{$sPath}\"" ; 

} 

function get_client_header(){ 

	$headers=array(); 

	foreach($_SERVER as $k=>$v){ 

		if(strpos($k,'HTTP_')===0){ 

			$k=strtolower(preg_replace('/^HTTP/', '', $k)); 

			$k=preg_replace_callback('/_\w/','header_callback',$k); 

			$k=preg_replace('/^_/','',$k); 

			$k=str_replace('_','-',$k); 

			if($k=='Host') continue; 

			$headers[]="$k:$v"; 

		} 

	} 

	return $headers; 

} 

function header_callback($str){ 

	return strtoupper($str[0]); 

} 

function parseHeader($sResponse){ 

	list($headerstr,$sResponse)=explode("\r\n\r\n",$sResponse, 2); 

	$ret=array($headerstr,$sResponse); 

	if(preg_match('/^HTTP\/1\.1 \d{3}/', $sResponse)){ 

		$ret=parseHeader($sResponse); 

	} 

	return $ret; 

} 

function gtRootUrl() 

{ 

//缓存结果，同一个request不重复计算 

static $gtrooturl; 

if(empty($gtrooturl)){ 

	// Protocol 

	$s = !isset($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : ''; 

	$protocol = strtolower($_SERVER['SERVER_PROTOCOL']); 

	$protocol = substr($protocol,0,strpos($protocol,'/')).$s.'://'; 

	// Port 

	$port = ($_SERVER['SERVER_PORT'] == 80) ? '' : ':'.$_SERVER['SERVER_PORT']; 

	// Server name 

	$server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'].$port : getenv('SERVER_NAME').$port; 

	// Host 

	$host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : $server_name; 

	 $gtrooturl=$protocol.$host.$_SERVER['REQUEST_URI']; 

	} 

		return $gtrooturl; 

} 

// close cURL resource, and free up system resources 

curl_close($aAccess); 

echo $sResponse ;

