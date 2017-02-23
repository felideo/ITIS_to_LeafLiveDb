<?php

error_reporting(E_ALL);
// ini_set('display_errors', 0);
set_time_limit (36000);
$start = microtime(true);


echo 'lerolero';

	$pdo = new PDO('mysql:host=localhost;dbname=LeafLiveDB', 'usuario_db', 'senha_db');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $select = "SELECT id FROM taxon"
        . " where id_classificacao = 8";

    $consulta = $pdo->prepare($select);

    try{
    	$consulta->execute();
    	$retorno = $consulta->fetchAll(\PDO::FETCH_ASSOC);
    }catch(Exception $e){
        debug2($e->getMessage());
    }



   	foreach ($retorno as $indice => $value) {
		$hierarquia = buscar_hierarquia($value['id']);

		// debug2($hierarquia);
		// exit;

		$organismo = '';

		foreach ($hierarquia as $indice => $taxon) {
			$organismo .= $taxon['id'] . ';';
		}

		$insert_db = [
			'nome'          => $hierarquia[7]['nome'],
			'id_last_taxon' => $hierarquia[7]['id'],
			'organismo'     => $organismo
		];

		// debug2($insert_db);

		insert($insert_db);

   	}

	debug2("Lerolero Fim");

	echo '<br><br><br>';

	$insert_mem = memory_get_usage(true);
	$duration = microtime(true) - $start;

    $hours        = (int) ($duration / 60 / 60);
    $minutes      = (int) ($duration / 60) - $hours * 60;
    $seconds      = (int) $duration - $hours * 60 * 60 - $minutes * 60;
    $microseconds = ($duration - $seconds - ($minutes * 60));

	echo "Total registros => " . count($retorno) . "<br>";
	echo "<br><br>";
	echo "Horas => " . $hours . "<br>";
	echo "Minutos => " . $minutes . "<br>";
	echo "Segundos => " . $seconds . "<br>";
	echo "Microssegundos => " . $microseconds . "<br>";
	echo "Duração => " . $duration . "<br>";
	echo "<br><br>";
	echo "Memoria Select => " . $select_mem / 1048576 . "<br>";
	echo "Memoria Inserts => " . $insert_mem / 1048576 . "<br>";
	echo "Memoria Total => " . (memory_get_usage(true) / 1048576) . "<br>";
	echo "Memoria Outra => " . (memory_get_peak_usage() / 1048576) . "<br>";

	exit;




exit;

function buscar_hierarquia($id){

	$pdo = new PDO('mysql:host=localhost;dbname=LeafLiveDB', 'usuario_db', 'senha_db');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $select = "SELECT * FROM taxon"
        . " where id = {$id}";

    $consulta = $pdo->prepare($select);

    try{
    	$consulta->execute();
    	$retorno = $consulta->fetchAll(\PDO::FETCH_ASSOC);
    }catch(Exception $e){
        debug2($e->getMessage());
    }

    if(!empty($retorno[0]['id_taxon'])){
    	$recursividade = buscar_hierarquia($retorno[0]['id_taxon']);
    }else{
    	return $retorno;
    	exit;
    }

    return array_merge($recursividade, $retorno);
}

function insert($insert_db){
	$pdo = new PDO('mysql:host=localhost;dbname=LeafLiveDB', 'usuario_db', 'senha_db');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table = 'organismo';

    ksort($insert_db);

    $fieldNames = implode('`, `', array_keys($insert_db));
    $fieldValues = ':' . implode(', :', array_keys($insert_db));

    $sth = $pdo->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

    foreach($insert_db as $key => $value) {
        $sth->bindValue(":$key", $value);
    }

    try{
        return $sth->execute();
    }catch(Exception $e){
        debug2($e->getMessage());
    }
}


















//Por Felideo Oficial!
function debug2($var, $legenda = false, $exit = false) {
    //Se for ajax deve ser exibido em JSON FORMAT
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

        if(is_array(carregar_UTF8($var))){
            echo json_encode(carregar_UTF8($var));
        }else{
            echo json_encode(array(carregar_UTF8($var)));
        }

    }else{

        echo "\n<pre style='position: relative; z-index: 99999;''>";
        echo "============================ DEBUG2 OFICIAL ==========================\n";



        foreach($GLOBALS as $var_name => $value) {
            if ($value === $var) {

                $variavel = "Variavel => $" . $var_name;

                $tamanho = strlen ($variavel);
                $tabs = str_repeat('&nbsp;', (70 - $tamanho) / 2);
                echo $tabs . $variavel . "\n";
            }
        }

        if ($legenda){
            $legenda = strtoupper($legenda);
            $tamanho = strlen ($legenda);
            $tabs = str_repeat('&nbsp;', (70 - $tamanho) / 2);
            echo $tabs . $legenda . "\n\n";
        }
        if (is_array($var) || is_object($var)) {
            echo htmlentities(print_r($var, true));
        } elseif (is_string($var)) {
            echo "string(" . strlen($var) . ") \"" . htmlentities($var) . "\"\n";
        } else {
            var_dump($var);
        }
        // echo "\n=============== FIM ===============\n";
        echo "\n";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        echo "</pre>";
    }

    if ($exit) {
        die;
    }
}