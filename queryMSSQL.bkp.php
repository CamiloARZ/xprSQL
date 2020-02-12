<?php
/**************************
 *
 * R9 Ingeniería
 * nh@r9.cl
 * jueves 5 de abril de 2012 
 *
 *************************/

/******************************************************************
 *
 *  Emula la función de cliente MSSQL para uso posterior en xprSQL
 *  Entrega el resultado de una query en una salida tabulada
 *
 *  Requiere el módulo php: mssql
 *
 *  Parámetros:
 *    -s <server> OR
 *    --server <server>
 *
 *    -u <user> OR
 *     --user <user>
 *
 *    -P <password> OR
 *    --password <password>
 *
 *    -d <dbname> OR
 *    --dbname <dbname>
 *
 *    -q <querySQL> OR
 *    --query <querySQL>
 *
 *    -D OR
 *    --debug
 ******************************************************************/

############# captura los warnings y los transforma en excepciones  #############
function handleError($errno, $errstr, $errfile, $errline, array $errcontext){
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

#################################################################################

$options = getopt('s:u:p:d:q:DP:',array('server:','user:','password:','dbname:','query:','debug','port:','ct:','qt:','ansi'));

$server_mssql 	= null;
$server_port 	= null;
$user_mssql 	= null;
$pass_mssql 	= null;
$dbname_mssql 	= null;
$query_string 	= null;
$debug 			= false;
$separador 		= ";";
$ansi 			= false;
$ct 			= false;
$qt 			= false;

$connect_timeout 	= 0;
$query_timeout 		= 0;


foreach($options as $option => $value) {
	switch($option) {
		case 'ansi':
			if(!$ansi) $ansi = true;
			else {
				fwrite(STDERR, "opcion --ansi ya introducida\n");
				exit(-1);
		    }
			break;
		case 'server':
			if(is_null($server_mssql)) $server_mssql = $value;
			else {
				fwrite(STDERR, "opcion --server ya introducida\n");
				exit(-1);
		    }
			break;
		case 's':
			if(is_null($server_mssql)) $server_mssql = $value;
			else {
				fwrite(STDERR, "opcion --s ya introducida\n");
				exit(-1);
		    }
		break;
		case 'port':
			if(is_null($server_port)) $server_port = $value;
			else {
				fwrite(STDERR, "opcion --password ya introducida\n");
				exit(-1);
		    }
			break;
		case 'p':
			if(is_null($server_port)) $server_port = $value;
			else {
				fwrite(STDERR, "opcion --p ya introducida\n");
				exit(-1);
		    }
		break;
		case 'user':
			if(is_null($user_mssql)) $user_mssql = $value;
			else {
				fwrite(STDERR, "opcion --user ya introducida\n");
				exit(-1);
		    }
		break;
		case 'u':
			if(is_null($user_mssql)) $user_mssql = $value;
			else {
				fwrite(STDERR, "opcion --u ya introducida\n");
				exit(-1);
		    }
		break;
		case 'password':
			if(is_null($pass_mssql)) $pass_mssql = $value;
			else {
				fwrite(STDERR, "opcion --password ya introducida\n");
				exit(-1);
		    }
			break;
		case 'P':
			if(is_null($pass_mssql)) $pass_mssql = $value;
			else {
				fwrite(STDERR, "opcion --p ya introducida\n");
				exit(-1);
		    }
		break;
		case 'dbname':
			if(is_null($dbname_mssql)) $dbname_mssql = $value;
			else {
				fwrite(STDERR, "opcion --dbname ya introducida\n");
				exit(-1);
		    }
		break;
		case 'd':
			if(is_null($dbname_mssql)) $dbname_mssql = $value;
			else {
				fwrite(STDERR, "opcion --d ya introducida\n");
				exit(-1);
		    }
		break;
		case 'query':
			if(is_null($query_string)) $query_string = $value;
			else {
				fwrite(STDERR, "opcion --query ya introducida\n");
				exit(-1);
		    }
		break;
		case 'q':
			if(is_null($query_string)) $query_string = $value;
			else {
				fwrite(STDERR, "opcion --q ya introducida\n");
				exit(-1);
		    }
		break;
		case 'debug':
			if(!$debug) $debug = true;
			else {
				fwrite(STDERR, "opcion --debug ya introducida\n");
				exit(-1);
		    }
		break;
		case 'D':
			if(!$debug) $debug = true;
			else {
				fwrite(STDERR, "opcion --D ya introducida\n");
				exit(-1);
		    }
		break;
		case 'ct':
			$ct = true;
			$connect_timeout = $value;
		break;
		case 'qt':
			$qt = true;
			$query_timeout = $value;
		break;
	}
}

/* Manejo de errores */
if(is_null($server_mssql))	{ fwrite(STDERR, "opcion --server sin especificar\n"); exit(-2); }
if(is_null($user_mssql))	{ fwrite(STDERR, "opcion --user sin especificar\n"); exit(-2); }
if(is_null($pass_mssql))	{ fwrite(STDERR, "opcion --password sin especificar\n"); exit(-2); }
//if(is_null($dbname_mssql))	{ fwrite(STDERR, "opcion --dbname sin especificar\n"); exit(-2); }
if(is_null($query_string))	{ fwrite(STDERR, "opcion --query sin especificar\n"); exit(-2); }

//mssql_min_error_severity(0);
//timeouts, si no se definen, se usan valores por defecto
if($ct) ini_set('mssql.connect_timeout', $connect_timeout);
if($qt) ini_set('mssql.timeout', $query_timeout);

$pos = strpos($server_mssql, "@");
if($pos === false)
	;
else {
	fwrite(STDERR, "Servername have @: ".$server_mssql."\n");
	$nsn = substr($server_mssql, 0, $pos);
	$nsn .= "\\";
	$nsn .= substr($server_mssql, $pos+1);
	$server_mssql = $nsn;
	fwrite(STDERR, "Servername: ".$server_mssql."\n");
}


############### Abrir conexión y utilizar DBname ####################################

// $serverName 	= $server_mssql.",".$server_port;
$serverName = "172.16.1.106,1433"; 
fwrite(STDERR, "serverName: ".$serverName." \n");

// $connectionInfo = array( "Database" => $dbname_mssql , "UID"=> $user_mssql , "PWD"=> $pass_mssql);
$connectionInfo = array( "Database" => "MEDIO_AMBIENTE" , "UID"=> "Adminstock" , "PWD"=> "HA0lfBW7");

$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
	fwrite(STDERR, "Connection established\n");
}else{
	fwrite(STDERR, "Connection could not be established! \n");
	exit(1);
}


if ($ansi) {
	sqlsrv_query("set ansi_nulls ON");
	sqlsrv_query("set ANSI_warnings ON");
}

$lastmsg = sqlsrv_errors ();

// mssql_query no informa de los errores, bug en php.

####################### Obtener datos de consulta  ############################

$multi_queries = explode(";", $query_string);

foreach ($multi_queries as $q) {
  set_error_handler('handleError');

  try {

	$params = array(75123, 5, 741, 1, 818.70, 0.00); 
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	$data = sqlsrv_query($conn, $q, $params, $options);

  }

  catch (ErrorException $e) {
	fwrite(STDERR, "Error: ".$e->getMessage()."\n");
	if($lastmsg != sqlsrv_errors())
		fwrite(STDERR, "Last Mssql msg: ".sqlsrv_errors ()."\n");
	exit(1);
  }
  restore_error_handler();

// Esto se ejecuta solo en insert
if($data === TRUE) {
	if(mssql_rows_affected($conn)==0)
		fwrite(STDERR, "T-Sin resultados\n");
	continue;
} elseif($data === FALSE) {
	fwrite(STDERR, "Error en consulta \"".sqlsrv_errors ()."\"\n");
	continue;
}

####################### Obtener datos de consulta  ############################
$Nresultados 	= sqlsrv_num_rows($data);
$Ncampos 		= sqlsrv_num_fields($data);

if($Nresultados == 0){
	fwrite(STDERR, "N-Sin resultados\n");
	exit(0);
}
################################################################################

if($debug) {
	fwrite(STDERR, "server: ".$server_mssql."\n");
	fwrite(STDERR, "port  : ".$server_port."\n");
	fwrite(STDERR, "user  : ".$user_mssql."\n");
	fwrite(STDERR, "pass  : ".$pass_mssql."\n");
	fwrite(STDERR, "dbname: ".$dbname_mssql."\n");
	fwrite(STDERR, "query : ".$query_string."\n");
	fwrite(STDERR, "\n");
	fwrite(STDERR, "resultados : ".$Nresultados."\n");
	fwrite(STDERR, "columnas   : ".$Ncampos."\n");
	exit(0);
}

################################ Imprimir datos  #################################

for($j = 0; $j < $Nresultados; ++$j) {
	$row = odbc_fetch_row()($data);
	for($i = 0; $i < $Ncampos; ++$i) {
		if($i > 0) echo $separador;
		echo $row[$i];
	}
	echo "\n";
}

####################################################################################


################################### Limpiar  #######################################

odbc_free_result($query);
}

####################################################################################


################################# Cerrar conexión   #################################

sqlsrv_close($conn);

####################################################################################
