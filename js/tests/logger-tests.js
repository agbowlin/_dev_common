var Logger = require('../logger');

Logger.LogGroup = 'Test Group';

Logger.LogTrace("This is an Trace message.");
Logger.LogDebug("This is an Debug message.");
Logger.LogInfo("This is an Info message.");
Logger.LogWarning("This is an Warn message.");
Logger.LogError("This is an Error message.");

Logger.LogSeparator();
Logger.LogLevels = 'IWE';

Logger.LogTrace("This is an Trace message. SHOULD NOT SEE THIS MESSAGE.");
Logger.LogDebug("This is an Debug message. SHOULD NOT SEE THIS MESSAGE.");
Logger.LogInfo("This is an Info message.");
Logger.LogWarning("This is an Warn message.");
Logger.LogError("This is an Error message.");

Logger.LogSeparator();
var obj = {
	Field1: 'Foo',
	Field2: 'Bar'
};
Logger.LogInfo("Here is some data:", obj);

Logger.LogSeparator();
Logger.LogLevels = 'TDIWE';

Logger.OutputGroup = false;
Logger.OutputTime = false;
Logger.OutputLevel = false;
Logger.LogInfo("This message has no output header fields.");

Logger.OutputGroup = true;
Logger.OutputTime = false;
Logger.OutputLevel = false;
Logger.LogInfo("This message has: Group.");

Logger.OutputGroup = false;
Logger.OutputTime = true;
Logger.OutputLevel = false;
Logger.LogInfo("This message has: Time.");

Logger.OutputGroup = false;
Logger.OutputTime = false;
Logger.OutputLevel = true;
Logger.LogInfo("This message has: Level.");

Logger.OutputGroup = true;
Logger.OutputTime = true;
Logger.OutputLevel = false;
Logger.LogInfo("This message has: Group, Time.");

Logger.OutputGroup = false;
Logger.OutputTime = true;
Logger.OutputLevel = true;
Logger.LogInfo("This message has: Time, Level.");

Logger.OutputGroup = true;
Logger.OutputTime = true;
Logger.OutputLevel = true;
Logger.LogInfo("This message has: Group, Time, Level.");

Logger.LogSeparator();
try
{
	throw "This is an error!";
}
catch (exception)
{
	Logger.LogError(exception, exception);
}

Logger.LogSeparator();
Logger.LogInfo("Its all good, exiting now.");

process.exit();
