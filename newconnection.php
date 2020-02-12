<?php

    $serverName = "oloresbd.r9.cl,1433"; //serverName\instanceName
    $connectionInfo = array( "Database"=>"demo", "UID"=>"sa", "PWD"=>"oloresr9_2019");

    $conn = sqlsrv_connect( $serverName, $connectionInfo);

    if( $conn ) {
        echo "Connection established.\n";
    }else{
        echo "Connection could not be established.\n";
        die( print_r( sqlsrv_errors(), true));
    }

?>
