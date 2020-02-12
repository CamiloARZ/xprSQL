<?php
/**************************
 *  
 * 
 * R9 Ingeniería
 * nh@r9.cl
 * jueves 5 de abril de 2012 
 *
 *************************/

 /**************************
 * Update
 * R9 Ingeniería
 * camilo.rodriguez@r9.cl
 * Jueves 30 de enero de 2020
 *
 *************************/

/******************************************************************
 *
 *  Emula la función de cliente MSSQL para uso posterior en xprSQL
 *  Entrega el resultado de una query en una salida tabulada
 *
 *  Requiere el módulo php: sqlsrv y pdo_sqlsrv.
 *
 *  Parámetros de entrada:
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
function handleError($errno, $errstr, $errfile, $errline, array $errcontext){
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

##############################################################################################################################

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
if(is_null($query_string))	{ fwrite(STDERR, "opcion --query sin especificar\n"); exit(-2); }

/* timeouts, si no se definen, se usan valores por defecto */
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


/* Abrir conexión */
fwrite(STDERR, "-> Conexion base de datos \n");
fwrite(STDERR, "   Server: ".$server_mssql."\n");
fwrite(STDERR, "   Port: ".$server_port."\n");
fwrite(STDERR, "   User: ".$user_mssql."\n");
fwrite(STDERR, "   Password: ".$pass_mssql."\n\n");


$serverName     = $server_mssql.",".$server_port; //serverName\instanceName
$connectionInfo = array( "Database" => $dbname_mssql, "UID" => $user_mssql, "PWD" => $pass_mssql);

$conn = sqlsrv_connect( $serverName, $connectionInfo);
fwrite(STDERR, "-> Estado conexion \n");

  if( $conn ) {
    fwrite(STDERR, "   Connection established \n\n");
  }else{
    fwrite(STDERR, "   Connection could not be established! \n\n");
    exit(1);
  }

if ($ansi){ 
    sqlsrv_query("set ansi_nulls ON");
    sqlsrv_query("set ANSI_warnings ON");
 }

$lastmsg = sqlsrv_errors ();

/* Obtener Queries  */

$multi_queries = explode(";", $query_string);
fwrite(STDERR, "-> Numero de Queries: ".count($multi_queries)."\n");
fwrite(STDERR, "   Queries: ".count($multi_queries)."\n\n");


foreach ($multi_queries as $query) {
  set_error_handler('handleError');

  try {

	$params = array(75123, 5, 741, 1, 818.70, 0.00); 
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	$data = sqlsrv_query($conn, $query, $params, $options);

    if ($conn === false) {
     fwrite(STDERR, "-> Connection could not be established\n\n");
         die( print_r( sqlsrv_errors(), true));
        exit(1);
    }
  }

  catch (ErrorException $e) {
	  fwrite(STDERR, "Error: ".$e->getMessage()."\n");
	  if($lastmsg != sqlsrv_errors())
		  fwrite(STDERR, "Last Mssql msg: ".sqlsrv_errors ()."\n");
	    exit(1);
  }
  
 restore_error_handler();

/* Obtener datos de consulta  */
$response 	= sqlsrv_fetch_array($data);
$numero		= sqlsrv_num_rows($data);
$campos		= sqlsrv_num_fields($data);

if($numero == 0){
	fwrite(STDERR, "-> Sin registros\n");
	exit(0);
}

fwrite(STDERR, "-> Informacion query:\n");
fwrite(STDERR, "   Nº registros: ".$numero." \n");
fwrite(STDERR, "   Nº campos: ".$campos." \n\n");

if($debug) {
	fwrite(STDERR, "server: ".$server_mssql."\n");
	fwrite(STDERR, "port  : ".$server_port."\n");
	fwrite(STDERR, "user  : ".$user_mssql."\n");
	fwrite(STDERR, "pass  : ".$pass_mssql."\n");
	fwrite(STDERR, "dbname: ".$dbname_mssql."\n");
	fwrite(STDERR, "query : ".$query."\n");
	fwrite(STDERR, "\n");
	fwrite(STDERR, "resultados : ".$numero."\n");
	fwrite(STDERR, "columnas   : ".$campos."\n");
	exit(0);
}

/* Imprimir datos  */
for($j = 0; $j < $numero; ++$j) {
	$row = sqlsrv_fetch_array( $data, SQLSRV_FETCH_NUMERIC);
	for($i = 0; $i < $campos; ++$i) {
		if($i > 0) echo $separador;
		if(is_object( $row[$i] )){
            echo $row[$i]->format('Y-m-d H:i:s');
        }else{
            echo $row[$i];
        }
	}
	echo "\n";
}

/* Limpiar  */

sqlsrv_free_stmt($data);
}

/* Cerrar conexión   */

sqlsrv_close($conn);


