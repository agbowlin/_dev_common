#!/usr/bin/php
<?php
//=====================================================================
//=====================================================================
//
//		build/versions.php
//
//=====================================================================
//=====================================================================


//---------------------------------------------------------------------
function LogText( $Text )
{
	echo($Text."\n");
	return;
}


//---------------------------------------------------------------------
function LogSeparator( $Width )
{
	echo('+'.str_pad( '', ($Width - 2), '-' ).'+'."\n");
	return;
}


//---------------------------------------------------------------------
function RunCommand( $Command )
{
	$script_stdout_lines = [];
	$script_return_value = '';
	exec( $Command.' '.$Argument, $script_stdout_lines, $script_return_value );
	$script_stdout = implode( "\n", $script_stdout_lines )."\n";
	return $script_stdout;
}


//---------------------------------------------------------------------
function NewVersion( $Group, $Command )
{
	$version = new stdClass();
	$version->Group = $Group;
	$version->Command = $Command;
	$version->Version = '~';
	return $version;
}


//---------------------------------------------------------------------
function UntilChar( $Text, $Char )
{
	$ich = strpos( $Text, $Char );
	if( $ich >= 0 )
	{
		$Text = substr( $Text, 0, $ich );
	}
	return $Text;
}


//---------------------------------------------------------------------
function PopLine( $Text )
{
	$ich = strpos( $Line, "\n" );
	if( $ich >= 0 )
	{
		$Line = substr( $Line, $ich + 2 );
	}
	return $Line;
}


//---------------------------------------------------------------------
function CleanVersion( $Line )
{
	$ich = strpos( $Line, " " );
	if( $ich )
	{
		$Line = substr( $Line, 0, $ich );
	}
	$ich = strpos( $Line, "(" );
	if( $ich )
	{
		$Line = substr( $Line, 0, $ich );
	}
	$ich = strpos( $Line, "-" );
	if( $ich )
	{
		$Line = substr( $Line, 0, $ich );
	}
	$ich = strpos( $Line, "," );
	if( $ich )
	{
		$Line = substr( $Line, 0, $ich );
	}
	$ich = strpos( $Line, "_" );
	if( $ich )
	{
		$Line = substr( $Line, 0, $ich );
	}
	$ich = strpos( $Line, "\"" );
	if( $ich )
	{
		$Line = substr( $Line, 0, $ich );
	}
	$ich = strpos( $Line, "\n" );
	if( $ich )
	{
		$Line = substr( $Line, 0, $ich );
	}
	if( strpos( $Line, 'v' ) === 0 )
	{
		$Line = substr( $Line, 1 );
	}
	return $Line;
}


//---------------------------------------------------------------------
function PrintVersions( $Versions )
{
	// Calculate the column widths
	$command_column_width = 0;
	$version_column_width = 0;
	foreach( $Versions as $version )
	{
		if( strlen( $version->Command ) > $command_column_width )
		{
			$command_column_width = strlen( $version->Command );
		}
		if( strlen( $version->Version ) > $version_column_width )
		{
			$version_column_width = strlen( $version->Version );
		}
	}
	
	// Report the versions
	$group = '';
	$row_width = 2 + $command_column_width + 3 + $version_column_width + 2;
	LogText( '' );
	LogText( 'Versions:');
	foreach( $Versions as $version )
	{
		if( $version->Group !== $group )
		{
			if( $group )
			{
				LogSeparator( $row_width );
			}
			LogText( '' );
			LogText( $version->Group );
			LogSeparator( $row_width );
			$group = $version->Group;
		}
		$line = '| ';
		$line .= str_pad( $version->Command, $command_column_width );
		$line .= ' | ';
		$line .= str_pad( $version->Version, $version_column_width );
		$line .= ' |';
		LogText( $line );
	}
	LogSeparator( $row_width );
	LogText( '' );
	LogText( 'Reported '.count( $Versions ).' versions.');

	return;
}


//---------------------------------------------------------------------


$versions = [];


//---------------------------------------------------------------------
//---------------------------------------------------------------------
//
//		System
//
//---------------------------------------------------------------------
//---------------------------------------------------------------------

$group = 'System';


//------------------------------------------
// BASH
//------------------------------------------
$version = NewVersion( $group, 'bash' );
$stdout = RunCommand( 'bash --version' );
if( substr( $stdout, 0, 18 ) == 'GNU bash, version ' )
{
	$stdout = substr( $stdout, 18 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// RSYSLOGD
//------------------------------------------
$version = NewVersion( $group, 'rsyslogd' );
$stdout = RunCommand( 'rsyslogd -v' );
if( substr( $stdout, 0, 9 ) == 'rsyslogd ' )
{
	$stdout = substr( $stdout, 9 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//---------------------------------------------------------------------
//---------------------------------------------------------------------
//
//		Database
//
//---------------------------------------------------------------------
//---------------------------------------------------------------------

$group = 'Database';


//------------------------------------------
// MYSQL (Client)
//------------------------------------------
$version = NewVersion( $group, 'mysql (client)' );
$stdout = RunCommand( 'mysql --version' );
if( substr( $stdout, 0, 11 ) == 'mysql  Ver ' )
{
	$stdout = substr( $stdout, 11 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// MYSQL (Server)
//------------------------------------------
$version = NewVersion( $group, 'mysqld (server)' );
$stdout = RunCommand( 'sudo mysql -e "SELECT @@version" --skip-column-names' );
if( $stdout )
{
	$stdout = UntilChar( $stdout, "\n" );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// MONGOD
//------------------------------------------
$version = NewVersion( $group, 'mongod' );
$stdout = RunCommand( 'mongod --version' );
if( substr( $stdout, 0, 11 ) == 'db version ' )
{
	$stdout = substr( $stdout, 11 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;



//---------------------------------------------------------------------
//---------------------------------------------------------------------
//
//		Network
//
//---------------------------------------------------------------------
//---------------------------------------------------------------------

$group = 'Network';


//------------------------------------------
// APACHE2
//------------------------------------------
$version = NewVersion( $group, 'apache2' );
$stdout = RunCommand( 'apache2 -v' );
$stdout = UntilChar( $stdout, "\n" );
if( substr( $stdout, 0, 23 ) == 'Server version: Apache/' )
{
	$stdout = substr( $stdout, 23 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// NGINX
//------------------------------------------
$version = NewVersion( $group, 'nginx' );
$stdout = RunCommand( 'nginx -v' );
$stdout = UntilChar( $stdout, "\n" );
if( substr( $stdout, 0, 21 ) == 'nginx version: nginx/' )
{
	$stdout = substr( $stdout, 21 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// NODEJS
//------------------------------------------
$version = NewVersion( $group, 'node' );
$stdout = RunCommand( 'node --version' );
$stdout = UntilChar( $stdout, "\n" );
if( strlen( $stdout ) )
{
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//---------------------------------------------------------------------
//---------------------------------------------------------------------
//
//		Languages
//
//---------------------------------------------------------------------
//---------------------------------------------------------------------

$group = 'Languages';


//------------------------------------------
// JAVA
//------------------------------------------
$version = NewVersion( $group, 'java' );
$stdout = RunCommand( 'java -version' );
if( substr( $stdout, 0, 14 ) == 'java version "' )
{
	$stdout = substr( $stdout, 14 );
	$version->Version = CleanVersion( $stdout );
}
$version->Version = '?.?.?'; // Detection is broken.
$versions []= $version;


//------------------------------------------
// PHP
//------------------------------------------
$version = NewVersion( $group, 'php' );
$stdout = RunCommand( 'php --version' );
if( substr( $stdout, 0, 4 ) == 'PHP ' )
{
	$stdout = substr( $stdout, 4 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// PYTHON
//------------------------------------------
$version = NewVersion( $group, 'python' );
$stdout = RunCommand( 'python --version' );
if( substr( $stdout, 0, 7 ) == 'Python ' )
{
	$stdout = substr( $stdout, 7 );
	$version->Version = CleanVersion( $stdout );
}
$version->Version = '?.?.?'; // Detection is broken.
$versions []= $version;


//------------------------------------------
// RUBY
//------------------------------------------
$version = NewVersion( $group, 'ruby' );
$stdout = RunCommand( 'ruby --version' );
if( substr( $stdout, 0, 5 ) == 'ruby ' )
{
	$stdout = substr( $stdout, 5 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//---------------------------------------------------------------------
//---------------------------------------------------------------------
//
//		Tools
//
//---------------------------------------------------------------------
//---------------------------------------------------------------------

$group = 'Tools';


//------------------------------------------
// NPM
//------------------------------------------
$version = NewVersion( $group, 'npm' );
$stdout = RunCommand( 'npm --version' );
$stdout = UntilChar( $stdout, "\n" );
if( strlen( $stdout ) )
{
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// BOWER
//------------------------------------------
$version = NewVersion( $group, 'bower' );
$stdout = RunCommand( 'bower --version' );
$stdout = UntilChar( $stdout, "\n" );
if( strlen( $stdout ) )
{
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// PHANTOMJS
//------------------------------------------
$version = NewVersion( $group, 'phantomjs' );
$stdout = RunCommand( 'phantomjs --version' );
$stdout = UntilChar( $stdout, "\n" );
if( strlen( $stdout ) )
{
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// CASPERJS
//------------------------------------------
$version = NewVersion( $group, 'casperjs' );
$stdout = RunCommand( 'casperjs --version' );
$stdout = UntilChar( $stdout, "\n" );
if( strlen( $stdout ) )
{
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//------------------------------------------
// CURL
//------------------------------------------
$version = NewVersion( $group, 'curl' );
$stdout = RunCommand( 'curl --version' );
if( substr( $stdout, 0, 5 ) == 'curl ' )
{
	$stdout = substr( $stdout, 5 );
	$version->Version = CleanVersion( $stdout );
}
$versions []= $version;


//---------------------------------------------------------------------


PrintVersions( $versions );

