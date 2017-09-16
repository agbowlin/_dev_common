"use strict";


var fs = require('fs');


module.exports = Utils_Casper;


function Utils_Casper(casper, logger, log_page_errors, log_remote_messages)
{
	casper.log_page_errors = log_page_errors; // || true;
	casper.log_remote_messages = log_remote_messages; // || true;


	//=====================================================================
	//=====================================================================
	//
	//  ╔═╗┌─┐┌─┐┌─┐┌─┐┬─┐  ╔═╗┬  ┬┌─┐┌┐┌┌┬┐┌─┐
	//  ║  ├─┤└─┐├─┘├┤ ├┬┘  ║╣ └┐┌┘├┤ │││ │ └─┐
	//  ╚═╝┴ ┴└─┘┴  └─┘┴└─  ╚═╝ └┘ └─┘┘└┘ ┴ └─┘
	//
	//=====================================================================
	//=====================================================================


	//=====================================================================
	casper.on("error", function(msg, trace)
	{
		if (logger)
		{
			logger.LogTrace("[Error] " + msg);
			logger.LogTrace("[Error trace] " + JSON.stringify(trace, undefined, 4));
		}
		return;
	});


	//=====================================================================
	casper.on("run.complete", function()
	{
		if (logger)
		{
			logger.LogTrace("Execution complete.");
		}
		this.exit(0);
		return;
	});


	//=====================================================================
	casper.on("page.error", function(msg, trace)
	{
		if (casper.log_page_errors)
		{
			if (logger)
			{
				logger.LogDebug("[Remote Page Error] " + msg);
				logger.LogDebug("[Remote Error trace] " + JSON.stringify(trace, undefined, 4));
			}
		}
		return;
	});


	//=====================================================================
	casper.on('remote.message', function(msg)
	{
		if (casper.log_remote_messages)
		{
			if (logger)
			{
				logger.LogDebug('[Remote Message] ' + msg);
			}
		}
		return;
	});


	//=====================================================================
	//=====================================================================
	//
	//  ┌─┐┌─┐┌─┐┌─┐┌─┐┬─┐  ┬ ┬┬─┐┌─┐┌─┐┌─┐┌─┐┬─┐┌─┐
	//  │  ├─┤└─┐├─┘├┤ ├┬┘  │││├┬┘├─┤├─┘├─┘├┤ ├┬┘└─┐
	//  └─┘┴ ┴└─┘┴  └─┘┴└─  └┴┘┴└─┴ ┴┴  ┴  └─┘┴└─└─┘
	//
	//=====================================================================
	//=====================================================================


	//=====================================================================
	casper.GetAttributeValue = function GetAttributeValue(Selector, AttributeName, Default)
	{
		if (!this.exists(Selector))
		{
			return Default;
		}
		return this.getElementAttribute(Selector, AttributeName);
	}


	//=====================================================================
	casper.GetElementText = function GetElementText(Selector)
	{
		if (!this.exists(Selector))
		{
			return '';
		}
		return this.fetchText(Selector);
	}


	//=====================================================================
	casper.ClickToDeath = function ClickToDeath(selector, timeout_ms)
	{
		this.waitForSelector(selector,
			function OnResource()
			{
				if (this.exists(selector))
				{
					if (logger)
					{
						logger.LogTrace('ClickToDeath [' + selector + ']');
					}
					this.click(selector);
					// this.wait(2000, ClickToDeath(selector));
					this.ClickToDeath(selector);
				}
			},
			function OnTimeout() {},
			timeout_ms);

		return;
	}


	//=====================================================================
	casper.GetPageSnapshot = function GetPageSnapshot(SnapshotName, DoSaveImage, DoSaveHtml)
	{
		SnapshotName = SnapshotName || 'snapshot';
		DoSaveImage = DoSaveImage || true;
		DoSaveHtml = DoSaveHtml || true;

		if (DoSaveImage)
		{
			this.capture(SnapshotName + '.jpg');
		}
		if (DoSaveHtml)
		{
			fs.write(SnapshotName + '.html', this.getPageContent(), 'w');
		}

		return;
	}


	//=====================================================================
	casper.ExitNow = function ExitNow(Status, Message)
	{
		if (logger)
		{
			if (Message)
			{
				logger.LogTrace(Message);
			}
			logger.LogTrace('CASPER WILL NOW EXIT!');
		}
		this.exit(Status);
		this.bypass(99999);
		return;
	}


}
