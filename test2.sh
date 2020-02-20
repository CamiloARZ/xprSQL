#!/bin/bash
echo ""
echo "####################################"
QUERY="[dbo].[SPS_CalculoEmisionesAESQuintero] 'AESQuinteroU2', 'MP', '2002111100"
echo "QUERY: ${QUERY}"
/usr/bin/php71 queryMSSQL.php --server "sma-mma.database.windows.net" --port "1433" \
--user "mmauser" --password "teatinos280..sma" --dbname "sma-mma" -q "${QUERY}"
echo "STATUS: $?"

