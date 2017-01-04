<?php


//---------------------------------------------------------------------
function curl_url( $Url, $MaxRetries )
{
	$MaxRetries = $MaxRetries || 1;
	$curl = curl_init();
	
	// Setup headers - I used the same headers from Firefox version 2.0.0.6
	$header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$header[] = "Cache-Control: max-age=0";
	$header[] = "Connection: keep-alive";
	$header[] = "Keep-Alive: 300";
	$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$header[] = "Accept-Language: en-us,en;q=0.5";
	$header[] = "Pragma: "; // browsers keep this blank.
	
	curl_setopt($curl, CURLOPT_URL, $Url);
	// curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10) AppleWebKit/600.1.25 (KHTML, like Gecko) Version/8.0 Safari/600.1.25');
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_REFERER, 'http://www.google.com');
	curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
	curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	
	$try_number = 0;
	while( $try_number < $MaxRetries )
	{
		$try_number++;
		$html = curl_exec($curl); // execute the curl command
		if( $html )
		{
			break;
		}
		echo( "Retrying $Url\n" );
	}

	// $html = curl_exec($curl); // execute the curl command
	curl_close($curl); // close the connection
	return $html; // and finally, return $html
}


//---------------------------------------------------------------------
function curl_post_async( $url, $params )
{
	foreach( $params as $key => &$val )
	{
		if( is_array( $val ) )
		{
			$val = implode( ',', $val );
		}
		$post_params[] = $key.'='.urlencode( $val );
	}
	$post_string = implode( '&', $post_params );
	
	$parts=parse_url( $url );
	
	$fp = fsockopen( $parts['host'],
					isset( $parts['port']) ? $parts['port'] : 80,
					$errno, $errstr, 30 );
	
	$out = "POST ".$parts['path']." HTTP/1.1\r\n";
	$out.= "Host: ".$parts['host']."\r\n";
	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$out.= "Content-Length: ".strlen( $post_string )."\r\n";
	$out.= "Connection: Close\r\n\r\n";
	if( isset( $post_string ) )
	{
		$out.= $post_string;
	}
	
	fwrite( $fp, $out );
	fclose( $fp );
	return;
}


