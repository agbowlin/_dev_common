<?php


$Logger = new Logger();


class Logger
{

	//---------------------------------------------------------------------
	public $LogGroup			= '';
	public $LogLevels			= 'TDIWE';
	public $LogAggregateServer	= '';

	public $OutputGroup			= true;
	public $OutputTime			= true;
	public $OutputLevel			= true;


	//---------------------------------------------------------------------
	public function GetTimestamp()
	{
		// The ISO-8601 date (e.g. 2013-05-05T16:34:42+00:00)
		// return date('c');
		$date = date_create();
		$microseconds = microtime();
		$microseconds = substr( $microseconds, 2, 4 );
		$date_format_string = 'Y-m-d H:i:s.'.$microseconds.' O';
		return date_format( $date, $date_format_string );
	}
	
	
	//---------------------------------------------------------------------
	public function SendLogAggregator( $Group, $Level, $Timestamp, $Message )
	{
		if( !$this->LogAggregateServer ) { return; }
		if( strlen( $this->LogAggregateServer ) == 0 ) { return; }
		
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
	public function LogMessage($Message, $Level = 'INFO', $ExtraData = null )
	{
		$this_timestamp = $this->GetTimestamp();

		// Get the log level of the message.
		$log_level = strtoupper( substr( $Level, 0, 1 ) );
		if( !stristr( $this->LogLevels, $log_level ) )
		{
			// Ignore message.
			return null;
		}

		// Get the log level.
		if    ( $log_level == 'T' ) { $log_level = 'TRACE'; }
		elseif( $log_level == 'D' ) { $log_level = 'DEBUG'; }
		elseif( $log_level == 'I' ) { $log_level = 'INFO '; }
		elseif( $log_level == 'W' ) { $log_level = 'WARN '; }
		elseif( $log_level == 'E' ) { $log_level = 'ERROR'; }
		else { $log_level = $Level; }

		// Construct the output message.
		$out_message = '';
		$left_side = ' | ';
		$right_side = '';
		if( $this->OutputGroup )
		{
			$out_message .= $left_side.$this->LogGroup.$right_side;
		}
		if( $this->OutputTime )
		{
			$out_message .= $left_side.$this_timestamp.$right_side;
		}
		if( $this->OutputLevel )
		{
			$out_message .= $left_side.$log_level.$right_side;
		}
		$out_message .= $left_side.$Message;

		// Add the extra data.
		if( $ExtraData )
		{
			$out_message .= "\n".json_encode( $ExtraData, JSON_PRETTY_PRINT );
		}

		// Send message to the console.
		if( ($log_level == 'WARN') || ($log_level == 'ERROR') )
		{
			console.error_log( $out_message );
		}
		echo $out_message."\n";
		
		// Send message to the log aggregator.
		$this->SendLogAggregator( $this->LogGroup, $Level, $this_timestamp, $Message );

		// Return the message.
		return $out_message;
	}
	

	//---------------------------------------------------------------------
	public function LogTrace( $Message, $ExtraData = null )
	{
		return $this->LogMessage( $Message, 'TRACE', $ExtraData );
	}
	public function LogDebug( $Message, $ExtraData = null )
	{
		return $this->LogMessage( $Message, 'DEBUG', $ExtraData );
	}
	public function LogInfo( $Message, $ExtraData = null )
	{
		return $this->LogMessage( $Message, 'INFO', $ExtraData );
	}
	public function LogWarn( $Message, $ExtraData = null )
	{
		return $this->LogMessage( $Message, 'WARN', $ExtraData );
	}
	public function LogWarning( $Message, $ExtraData = null )
	{
		return $this->LogMessage( $Message, 'WARN', $ExtraData );
	}
	public function LogError( $Message, $ExtraData = null )
	{
		return $this->LogMessage( $Message, 'ERROR', $ExtraData );
	}


	//---------------------------------------------------------------------
	public function LogBlank( $Level = 'INFO' )
	{
		return $this->LogMessage( '', $Level );
	}
	public function LogSeparator( $Level = 'INFO' )
	{
		return $this->LogMessage( '==========================================', $Level );
	}
	
	
}

