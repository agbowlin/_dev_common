"use strict";


module.exports = Logger;


function Logger()
{
	return;
}


//---------------------------------------------------------------------
Logger.LogTimestamp = true;


//---------------------------------------------------------------------
Logger.GetTimestamp =
	function GetTimestamp()
	{
		var date = new Date();
		var timestamp = date.toISOString(); //"2011-12-19T15:28:46.493Z"
		return timestamp;
	}


//---------------------------------------------------------------------
Logger.LogMessage =
	function LogMessage(Message)
	{
		var head = '========[';
		var tail = '] ' + Message;
		var stats = '';
		if (Logger.LogTimestamp)
		{
			stats += this.GetTimestamp();
		}
		console.log(head + ' ' + stats + ' ' + tail);
		return;
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
