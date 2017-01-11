#!/bin/bash
# while sleep 1; do ps --no-headers -o '%cpu,%mem' -C "phantomjs"; done

# PROCESS_NAME="phantomjs"
# PROCESS_NAME="dropbear"
PROCESS_NAME="$1"

echo ' Monitoring:' $PROCESS_NAME
echo '  Timestamp           | CPU  MEM'
echo '  --------------------+-----------'

while true;
do
	TIMESTAMP=$( date +%Y-%m-%d,%H:%M:%S )
	RESULT=$(ps --no-headers -o '%cpu,%mem' -C "$PROCESS_NAME")
	if [ -n "$RESULT" ]
	then
		echo '>' $TIMESTAMP '|' $RESULT
	fi
	sleep 1
done
