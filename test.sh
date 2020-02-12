#!/bin/bash
echo ""
echo "####################################"
QUERY=" SELECT LEFT(CONVERT(VARCHAR(33), o.WhenStartedUtc, 126), 19)  AS AirDatetime, mc.Concentration AS Concentracion FROM OperationMeasuredConcentration omc INNER JOIN  MeasuredConcentration mc ON omc.MeasuredConcentration_FK = mc.RecId INNER JOIN CalibrationCurve cc ON mc.CalibrationCurve_FK = cc.RecId INNER JOIN ChemCompGcAnalyticalMethod ccgam ON cc.ChemCompGcAnalyticalMethod_FK = ccgam.RecId INNER JOIN ChemicalComponentGlobal ccg ON ccgam.ChemicalComponentGlobal_FK = ccg.RecId INNER JOIN Operation o ON omc.Operation_FK = o.RecId WHERE (o.WhenStartedUtc > convert(datetime, convert(varchar(10),LEFT('@TIME@', 6),20)  + ' ' + stuff(RIGHT('@TIME@', 4),3,0,':'))) AND (o.WhenStartedUtc <= convert(datetime, convert(varchar(10),LEFT('@ETIME@', 6),20)  + ' ' + stuff(RIGHT('@ETIME@', 4),3,0,':'))) AND (ccg.Name = 'Benzene') AND (datepart(minute, o.WhenStartedUtc) = 30 OR datepart(minute, o.WhenStartedUtc) = 0) ORDER BY o.WhenStartedUtc ASC"
echo "QUERY: ${QUERY}"
/usr/bin/php queryMSSQL.php --server "172.20.90.215" --port "1433" --user "sa" --password "sa2013&" --dbname "MeasSysDb0504_Btx304" \
-q "${QUERY}"
echo "STATUS: $?"

