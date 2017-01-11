#!/usr/bin/php
<?php
//=====================================================================
//=====================================================================
//
//		build/push.php
//
//=====================================================================
//=====================================================================


//---------------------------------------------------------------------
function LogText($Text)
{
	echo('| '.$Text."\n");
	return;
}


//---------------------------------------------------------------------
function LogSeparator()
{
	echo('+---------------------------------------------------------------------'."\n");
	return;
}


//---------------------------------------------------------------------
function PrintUsage()
{
	LogText( 'Usage: push [-t] [-i] [-r] -m "commit message"' );
	LogText( "\t".'-t : Run push in test mode. No changes are made.' );
	LogText( "\t".'-i : Print the project info and exit. No changes are made.' );
	LogText( "\t".'-r : Increment the version number and create a new release.' );
	LogText( "\t".'-m : Specify the message to be used for the commit. (required)' );
	return;
}


//---------------------------------------------------------------------
function GetFileContents($Filename, $Log)
{
	if (file_exists($Filename))
	{
		$file_content = file_get_contents($Filename);
		return $file_content;
	}
	else
	{
		if ($Log)
		{
			LogText('WARNING: ' . $Filename . ' file is missing!');
		}
		return null;
	}
}


//---------------------------------------------------------------------
function ExecuteCommand( $Command )
{
	$result_lines = [];
	$script_stdout_lines = [];
	$script_return_value = '';
	exec( $Command, $script_stdout_lines, $script_return_value );
	foreach( $script_stdout_lines as $line )
	{
		if(strlen( $line ))
		{
			$result_lines []= $line;
			LogText( "\t| ".$line );
		}
	}
	return $result_lines;
}


//=====================================================================
//		Initialize Script
//=====================================================================


LogSeparator();
LogText('Initializing push ...');


//---------------------------------------------------------------------
// Initialize some working variables.
$working_directory = getcwd();


//---------------------------------------------------------------------
// Get the project version.
$project_name = '';
$project_version = '';
$file_content = GetFileContents('VERSION', true);
if ($file_content)
{
	$project_version = $file_content;
}
else
{
	$project_version = '0.0.0';
}


//---------------------------------------------------------------------
// Load the npm config.
$npm_config = null;
$file_content = GetFileContents('package.json', true);
if ($file_content)
{
	$npm_config = json_decode($file_content);
	$project_name = $npm_config->name;
	if ($npm_config->version != $project_version)
	{
		LogText('WARNING: The npm project version does not match the version file!');
	}
}
else
{
	$npm_config = new stdClass();
	$npm_config->name = $project_name;
	$npm_config->version = $project_version;
}


//---------------------------------------------------------------------
// Load the bower config.
$bower_config = null;
$file_content = GetFileContents('bower.json', true);
if ($file_content)
{
	$bower_config = json_decode($file_content);
	if ($bower_config->name != $npm_config->name)
	{
		LogText('WARNING: The bower project name does not match the npm project name!');
	}
	if ($bower_config->version != $project_version)
	{
		LogText('WARNING: The bower project version does not match the version file!');
	}
}
else
{
	$bower_config = new stdClass();
	$bower_config->name = $project_name;
	$bower_config->version = $project_version;
}


//---------------------------------------------------------------------
// Get the parameters.
$test_mode = false;
$info_only = false;
$do_release = false;
$commit_message = '';
$arg_index = 1;
while( $arg_index < count( $argv ) )
{
	$arg = strtolower( $argv[$arg_index] );
	if($arg == '-t')
	{
		$test_mode = true;
	}
	elseif($arg == '-i')
	{
		$info_only = true;
	}
	elseif($arg == '-r')
	{
		$do_release = true;
	}
	elseif($arg == '-m')
	{
		$arg_index++;
		if( $arg_index < count( $argv ) )
		{
			$commit_message = $argv[$arg_index];
		}
	}
	else
	{
		LogText('ERROR: Unknown parameter: ['.$arg.']');
		PrintUsage();
		exit();
	}
	$arg_index++;
}
if (!$commit_message)
{
	LogText('WARNING: Commit message is missing!');
}


//---------------------------------------------------------------------
// Increment the version.
if( $do_release )
{
	$version_parts = explode( '.', $project_version );
	$version_parts[count($version_parts) - 1]++;
	$new_version = implode( '.', $version_parts );
}


//---------------------------------------------------------------------
// Report.
LogSeparator();
LogText("Push: " . $project_name);
LogSeparator();
LogText("   Working Folder : " . $working_directory);
LogText("     Project Name : " . $project_name);
LogText("  Current Version : " . $project_version);
if( $do_release )
{
	LogText("      New Version : " . $new_version);
}
LogText("   Commit Message : " . $commit_message);
if( $test_mode )
{
	LogText("        Test Mode : ENABLED");
}


//=====================================================================
//		GIT STATUS
//=====================================================================


//---------------------------------------------------------------------
// Run 'git status' to see what changed.
LogSeparator();
LogText("Getting project status ...");
$result_lines = ExecuteCommand( 'git status --porcelain' );
if (count($result_lines))
{
	LogText('Found ' . count($result_lines) . ' changes.');
}
else
{
	LogText('No changes found.');
}


if( $info_only )
{
	LogSeparator();
	LogText('Printing project information only. Now exiting.');
	LogSeparator();
	exit();
}


if (!$commit_message)
{
	LogSeparator();
	LogText('Pushing will NOT be completed because the commit message is missing.');
	LogText('Supply a commit message when calling push:');
	LogText("\t" . 'push -m "commit message"');
	PrintUsage();
}
else
{


	//=====================================================================
	//		APPLY NEW VERSION NUMBER
	//=====================================================================
	
	
	if( $do_release )
	{
		LogSeparator();
		if( $test_mode )
		{
			LogText("Applying new version number ... (in test mode)");
			LogText("\tWould write package.json");
			LogText("\tWould write bower.json");
			LogText("\tWould write VERSION");
		}
		else
		{
			LogText("Applying new version number ...");
			
			LogText("\tWriting package.json");
			$npm_config->version = $new_version;
			file_put_contents( 'package.json', json_encode( $npm_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			
			LogText("\tWriting bower.json");
			$bower_config->version = $new_version;
			file_put_contents( 'bower.json', json_encode( $bower_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			
			LogText("\tWriting VERSION");
			$version = $new_version;
			file_put_contents( 'VERSION', $version );
		}

	}
	
	
	//=====================================================================
	//		GIT ADD .
	//=====================================================================


	LogSeparator();
	if( $test_mode )
	{
		LogText('Updating the index ... (in test mode)');
		$result_lines = ExecuteCommand( 'git add --all --dry-run' );
	}
	else
	{
		LogText('Updating the index ...');
		$result_lines = ExecuteCommand( 'git add --all' );
	}


	//=====================================================================
	//		GIT COMMIT -m Message
	//=====================================================================


	LogSeparator();
	if( $test_mode )
	{
		LogText('Packaging the commit ... (in test mode)');
		$result_lines = ExecuteCommand( 'git commit --porcelain --message "'.$commit_message.'"' );
	}
	else
	{
		LogText('Packaging the commit ...');
		$result_lines = ExecuteCommand( 'git commit --message "'.$commit_message.'"' );
	}


	//=====================================================================
	//		GIT TAG
	//=====================================================================


	if( $do_release )
	{
		LogSeparator();
		if( $test_mode )
		{
			LogText('Tagging a new release ... (in test mode)');
			// $result_lines = ExecuteCommand( 'git tag --porcelain --annotate v'.$new_version.' --message "Release v'.$new_version.'"' );
		}
		else
		{
			LogText('Tagging a new release ...');
			$result_lines = ExecuteCommand( 'git tag --annotate v'.$new_version.' --message "Release v'.$new_version.'"' );
		}
	}


	//=====================================================================
	//		GIT PUSH ORIGIN MASTER
	//=====================================================================


	LogSeparator();
	if( $test_mode )
	{
		LogText('Pushing the commit ... (in test mode)');
		$result_lines = ExecuteCommand( 'git push origin master --tags --dry-run --porcelain' );
	}
	else
	{
		LogText('Pushing the commit ...');
		if( $do_release )
		{
			$result_lines = ExecuteCommand( 'git push origin master --tags' );
		}
		else
		{
			$result_lines = ExecuteCommand( 'git push origin master' );
		}
	}


	//=====================================================================
	//		NPM PUBLISH
	//=====================================================================


	if( $do_release )
	{
		LogSeparator();
		if( $test_mode )
		{
			LogText('Publishing to npm ... (in test mode)');
			// $result_lines = ExecuteCommand( 'git push origin master --tags --dry-run --porcelain' );
		}
		else
		{
			LogText('Publishing to npm ...');
			$result_lines = ExecuteCommand( 'npm publish' );
		}
	}


}


//=====================================================================
//		Exit Script
//=====================================================================


//---------------------------------------------------------------------
LogSeparator();
LogText('Finished.');
LogSeparator();

