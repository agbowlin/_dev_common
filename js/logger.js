//=====================================================================
//=====================================================================
//
//		logger.js
//
//=====================================================================
//=====================================================================


"use strict";


module.exports = Logger;


function Logger()
{
	return;
}


//---------------------------------------------------------------------
Logger.LogGroup = '';
Logger.LogLevels = 'TDIWE';
Logger.LogAggregateServer = '';

Logger.OutputGroup = true;
Logger.OutputTime = true;
Logger.OutputLevel = true;


//---------------------------------------------------------------------
Logger.FormatTimestamp =
	function FormatTimestamp(date)
	{
		// var timestamp = date.toISOString(); //"2011-12-19T15:28:46.493Z"
		var timestamp =
			date.getFullYear() +
			"-" + ("0" + (date.getMonth() + 1)).slice(-2) +
			"-" + ("0" + date.getDate()).slice(-2) +
			" " + ("0" + date.getHours()).slice(-2) +
			":" + ("0" + date.getMinutes()).slice(-2) +
			":" + ("0" + date.getSeconds()).slice(-2) +
			"." + ("000" + date.getMilliseconds()).slice(-4);
		// " " + ("000" + date.getTimezoneOffset()).slice(-4);
		return timestamp;
	}


//---------------------------------------------------------------------
Logger.GetTimestamp =
	function GetTimestamp()
	{
		return Logger.FormatTimestamp(new Date());
	}


//---------------------------------------------------------------------
Logger.SendLogAggregator =
	function SendLogAggregator(Group, Level, Timestamp, Message)
	{
		if (!Logger.LogAggregateServer)
		{
			return;
		}
		if (Logger.LogAggregateServer.length == 0)
		{
			return;
		}

		//TODO:

		return;
	}


//---------------------------------------------------------------------
Logger.LogMessage =
	function LogMessage(Message, Level, ExtraData)
	{
		Level = Level || 'INFO';
		var this_timestamp = Logger.GetTimestamp();

		// Get the log level of the message.
		var log_level = Level.substr(0, 1).toUpperCase();
		if (Logger.LogLevels.indexOf(log_level) == -1)
		{
			// Ignore message.
			return null;
		}

		// Get the log level.
		if (log_level == 'T')
		{
			log_level = 'TRACE';
		}
		else if (log_level == 'D')
		{
			log_level = 'DEBUG';
		}
		else if (log_level == 'I')
		{
			log_level = 'INFO ';
		}
		else if (log_level == 'W')
		{
			log_level = 'WARN ';
		}
		else if (log_level == 'E')
		{
			log_level = 'ERROR';
		}
		else
		{
			log_level = Level;
		}

		// Construct the output message.
		var out_message = '';
		var left_side = ' | ';
		var right_side = '';
		if (Logger.OutputGroup)
		{
			out_message += left_side + Logger.LogGroup + right_side;
		}
		if (Logger.OutputTime)
		{
			out_message += left_side + this_timestamp + right_side;
		}
		if (Logger.OutputLevel)
		{
			out_message += left_side + log_level + right_side;
		}
		out_message += left_side + Message;

		// Add the extra data.
		if (ExtraData)
		{
			out_message += "\n" + JSON.stringify(ExtraData, undefined, "    ");
		}

		// Send message to the console.
		if ((log_level == 'WARN') || (log_level == 'ERROR'))
		{
			console.error(out_message);
		}
		console.log(out_message);

		// Send message to the log aggregator.
		Logger.SendLogAggregator(Logger.LogGroup, Level, this_timestamp, Message);

		// Return the message.
		return out_message;
	}


//---------------------------------------------------------------------
Logger.LogTrace =
	function LogTrace(Message, ExtraData)
	{
		Logger.LogMessage(Message, 'TRACE', ExtraData);
	}
Logger.LogDebug =
	function LogDebug(Message, ExtraData)
	{
		Logger.LogMessage(Message, 'DEBUG', ExtraData);
	}
Logger.LogInfo =
	function LogInfo(Message, ExtraData)
	{
		Logger.LogMessage(Message, 'INFO', ExtraData);
	}
Logger.LogWarn =
	function LogWarn(Message, ExtraData)
	{
		Logger.LogMessage(Message, 'WARN', ExtraData);
	}
Logger.LogWarning =
	function LogWarning(Message, ExtraData)
	{
		Logger.LogMessage(Message, 'WARN', ExtraData);
	}
Logger.LogError =
	function LogError(Message, ExtraData)
	{
		Logger.LogMessage(Message, 'ERROR', ExtraData);
	}


//---------------------------------------------------------------------
Logger.LogBlank =
	function LogBlank(Level)
	{
		Logger.LogMessage('', Level);
	}
Logger.LogSeparator =
	function LogSeparator(Level)
	{
		Logger.LogMessage('==========================================', Level);
	}


//---------------------------------------------------------------------
Logger.ObjectJson =
	function DebugObject(SomeObject)
	{
		return JSON.stringify(SomeObject, undefined, "    ");
	}


//---------------------------------------------------------------------
Logger.LogObject =
	function LogObject(SomeObject)
	{
		this.LogMessage("\n" + this.ObjectJson(SomeObject));
		return;
	}
