<?php


class PdoDatabase
{

	//---------------------------------------------------------------------
	public $EngineName			= 'mysql';
	public $ServerName			= 'localhost';
	public $DatabaseName		= null;
	public $Username			= null;
	public $Password			= null;


	//---------------------------------------------------------------------
	public function Execute( $SqlStatement, $Parameters )
	{
		try
		{
			$connection_string = $this->EngineName;
			$connection_string .= ":host=".$this->ServerName.";";
			if( $this->DatabaseName )
			{
				$connection_string .= "dbname=".$this->DatabaseName.";";
			}
			
			$pdo = new PDO( $connection_string, $this->Username, $this->Password );
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$query = $pdo->prepare( $SqlStatement );
			$result = $query->execute( $Parameters );
	
			unset( $query );
			unset( $pdo );
	
			return $result;
		}
		catch( PDOException $pdo_exception )
		{
			echo "PDO Exception: " . $pdo_exception->getMessage() . "\n";
			exit( 1 );
		}
		catch( Exception $exception )
		{
			echo "Exception: " . $exception->getMessage() . "\n";
			exit( 1 );
		}
	}


	//---------------------------------------------------------------------
	public function QueryRows( $SqlStatement, $Parameters )
	{
		try
		{
			$connection_string = $this->EngineName;
			$connection_string .= ":host=".$this->ServerName.";";
			if( $this->DatabaseName )
			{
				$connection_string .= "dbname=".$this->DatabaseName.";";
			}
			
			$pdo = new PDO( $connection_string, $this->Username, $this->Password );
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$query = $pdo->prepare( $SqlStatement );
			$query->execute( $Parameters );
	
			$rows = array();
			for( $index = 0; $row = $query->fetch(); $index++ )
			{
				$rows []= $row;
			}
	
			unset( $query );
			unset( $pdo );
	
			return $rows;
		}
		catch( PDOException $pdo_exception )
		{
			echo "PDO Exception: " . $pdo_exception->getMessage() . "\n";
			exit( 1 );
		}
		catch( Exception $exception )
		{
			echo "Exception: " . $exception->getMessage() . "\n";
			exit( 1 );
		}
	}
	
	
	//---------------------------------------------------------------------
	public function QueryFirstRow( $SqlStatement, $Parameters )
	{
		$rows = $this->QueryRows( $SqlStatement, $Parameters );
		if( count( $rows ) > 0 )
		{
			return $rows[ 0 ];
		}
		return null;
	}
	
	
	//---------------------------------------------------------------------
	public function QueryFirstValue( $SqlStatement, $Parameters )
	{
		$row = $this->QueryFirstRow( $SqlStatement, $Parameters );
		if( count( $row ) > 0 )
		{
			return $row[ 0 ];
		}
		return null;
	}


}

