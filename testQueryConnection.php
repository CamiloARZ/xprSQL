<?php
    echo "step 1. conecction <br><br>";

    $serverName     = "oloresbd.r9.cl,1433"; //serverName\instanceName
    $connectionInfo = array( "Database"=>"demo", "UID"=>"sa", "PWD"=>"oloresr9_2019");

    $conn = sqlsrv_connect( $serverName, $connectionInfo);

    if( $conn ) {
        echo "&nbsp;&nbsp;&nbsp; Connection established.<br><br>";
    }else{
        echo "&nbsp;&nbsp;&nbsp; Connection could not be established.<br><br>";
        die( print_r( sqlsrv_errors(), true));
        exit;

    }

    echo "step 2. Query <br><br>";

    $tsql = "SELECT TOP (10) [DeviceId], [estampatiempo], [Timestamp], [TimestampUTC], [SO2] FROM [demo].[dbo].[DatosMinutalesVentanasP1]";
    $params = array(75123, 5, 741, 1, 818.70, 0.00); 
    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

    $data = sqlsrv_query($conn, $tsql, $params, $options);

    if ($conn === false) {
        echo "&nbsp;&nbsp;&nbsp; Connection could not be established. <br><br>";
        die( print_r( sqlsrv_errors(), true));
        exit;
    }

    $response       = sqlsrv_fetch_array($data);
    $Nresultados 	= sqlsrv_num_rows($data);
    $Ncampos 		= sqlsrv_num_fields($data);

echo "&nbsp;&nbsp;&nbsp;&nbsp; Nº Registros: ".$Nresultados;
echo "&nbsp;&nbsp;&nbsp;&nbsp; Nº Campos: ".$Ncampos."<br><br>";

for($j = 0; $j < $Nresultados; ++$j) {
    $row = sqlsrv_fetch_array( $data, SQLSRV_FETCH_NUMERIC);

    // echo $row[0].", ".$row[1].", ".$row[2]->format('Y-m-d H:i:s').", ".$row[3]->format('Y-m-d H:i:s').", ".$row[4]."<br/>";

    for($i = 0; $i < $Ncampos; ++$i) {
        if($i > 0) echo ";";
        if(is_object( $row[$i] )){
            echo $row[$i]->format('Y-m-d H:i:s');
        }else{
            echo $row[$i];
        }
        
    }
    
    echo "<br>";
}

sqlsrv_close($conn); 

echo "<br><br> Close Connect ";

?>