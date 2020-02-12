#!/bin/bash

echo "####################################"
QUERY="select current_timestamp"
echo "QUERY: ${QUERY}"
/usr/bin/php56 queryMSSQL.php --server "SMA" --port "1433" --user "mmauser@sma-mma" --password "teatinos280..sma" \
-q "${QUERY}"
echo "STATUS: $?"

