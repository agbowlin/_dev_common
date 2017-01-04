<?php


class Logger
{

	//---------------------------------------------------------------------
	public $LogGroup			= '';
	public $LogLevels			= 'TDIWE';
	public $LogTimestamp		= true;
	// public $LogAggregate		= true;
	public $LogAggregateServer	= '';


	//---------------------------------------------------------------------
	public function GetTimestamp()
	{
		// The ISO-8601 date (e.g. 2013-05-05T16:34:42+00:00)
		// return date('c');
		$microseconds = microtime();
		$microseconds = substr( $microseconds, 2, 4 );
		$date = date_create();
		return date_format( $date, 'Y-m-d H:i:s.'.$microseconds.' O' );
	}
	
	
	//---------------------------------------------------------------------
	public function SendLogAggregator( $Group, $Level, $Timestamp, $Message )
	{
		// $url = 'http://logagg.liquicode.com/submit.php';
		$params = array
				(
					"group"		=> $Group,
					"level"		=> $Level,
					"timestamp"	=> $Timestamp,
					"message"	=> $Message,
				);
		
		foreach( $params as $key => &$val )
		{
			if( is_array( $val ) )
			{
				$val = implode( ',', $val );
			}
			$post_params[] = $key.'='.urlencode( $val );
		}
		$post_string = implode( '&', $post_params );
		
		$parts=parse_url( $this->LogAggregateServer );
		
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
	
	
	//---------------------------------------------------------------------
	public function LogMessage($Message, $Level = 'INFO' )
	{
		$this_timestamp = $this->GetTimestamp();

		// Get the log level of the message.
		$log_level = strtoupper( substr( $Level, 0, 1 ) );
		if( !stristr( $this->LogLevels, $log_level ) )
		{
			// Ignore message.
			return null;
		}

		// Process message.
		if    ( $log_level == 'T' ) { $log_level = 'TRACE'; }
		elseif( $log_level == 'D' ) { $log_level = 'DEBUG'; }
		elseif( $log_level == 'I' ) { $log_level = 'INFO '; }
		elseif( $log_level == 'W' ) { $log_level = 'WARN '; }
		elseif( $log_level == 'E' ) { $log_level = 'ERROR'; }
		else { $log_level = $Level; }

		// Construct the output message.
		$out_message = '';
		if( $this->LogTimestamp )
		{
			$out_message .= '====[ '.$this_timestamp.' ] ';
		}
		$out_message .= '====[ '.$log_level.' ] ';
		$out_message .= $Message;
		
		// Send message to the console.
		echo $out_message."\n";
		
		if( $this->LogAggregateServer && (strlen( $this->LogAggregateServer ) > 0) )
		{
			$this->SendLogAggregator( $this->LogGroup, $Level, $this_timestamp, $Message );
		}
		
		// Return the message.
		return $out_message;
	}
	

	//---------------------------------------------------------------------
	public function LogTrace( $Message )
	{
		return $this->LogMessage( $Message, 'TRACE' );
	}
	public function LogDebug( $Message )
	{
		return $this->LogMessage( $Message, 'DEBUG' );
	}
	public function LogInfo( $Message )
	{
		return $this->LogMessage( $Message, 'INFO' );
	}
	public function LogWarning( $Message )
	{
		return $this->LogMessage( $Message, 'WARN' );
	}
	public function LogError( $Message )
	{
		return $this->LogMessage( $Message, 'ERROR' );
	}
	
	
}

