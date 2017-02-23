<?php

error_reporting(0);
ini_set('display_errors', 0);
set_time_limit (36000);
$start = microtime(true);


$hierarquia = 8;


$pdo = new PDO('mysql:host=localhost;dbname=LiveDB', 'usuario_db', 'senha_db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $select = "SELECT * FROM classificacao_import_itis"
        . " where id_taxonomia = {$hierarquia}";

    $consulta = $pdo->prepare($select);
    $consulta->execute();
    $retorno = $consulta->fetchAll(\PDO::FETCH_ASSOC);

debug2($retorno);


$retorno_update = [];

    foreach($retorno as $indice => $busca) {
$pdo = NULL;

$pdo = new PDO('mysql:host=localhost;dbname=LeafLiveDB', 'usuario_db', 'senha_db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $table = 'taxon';

        $insert_db = [
            'nome'             => $busca['nome'],
            'id_classificacao' => $busca['id_taxonomia'],
            'id_taxon'         => !empty($busca['id_pai']) ? $busca['id_pai'] : NULL
        ];

        debug2($insert_db);

        ksort($insert_db);

        $fieldNames = implode('`, `', array_keys($insert_db));
        $fieldValues = ':' . implode(', :', array_keys($insert_db));

        $sth = $pdo->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

        foreach($insert_db as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        debug2($sth);

        try{
            debug2($sth->execute());
            $id_pai = $pdo->lastInsertId();

            debug2($id_pai);
        }catch(Exception $e){
            debug2($e->getMessage());
        }


        // exit;

        $pdo = NULL;

        $pdo = new PDO('mysql:host=localhost;dbname=LiveDB', 'usuario_db', 'senha_db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $table = 'classificacao_import_itis';
        $where = $busca['tsn'];

        $data = ['id_pai' => $id_pai];

        ksort($data);
        $fieldDetails = NULL;

        foreach($data as $key => $value) {
            $fieldDetails .= "`$key` = :$key,";
        }

        $fieldDetails = rtrim($fieldDetails, ',');




        $new = $pdo->prepare("UPDATE $table SET $fieldDetails WHERE `id_classificacao` = $where");

        foreach($data as $key => $value) {
            $new->bindValue(":$key", $value);
        }

        try{
            $retorno_update[] = [
                $new->execute(),
                $new->errorCode(),
                $new->errorInfo()
            ];
        }catch(Exception $e){
            debug2($e->getMessage());
        }

        unset($id_pai);

    }

    debug2($retorno_update);


echo '<br><br><br>lerolero';



exit;




















































exit;


$taxonomia_id_pai = 7;
$taxonomia_id = 8;
$taxonomia_nome = 'Subspecies';

$buscar = get_itis_tsn_pais($taxonomia_id_pai);

// debug2($buscar);
// exit;



// h.Parent_TSN = 5414 OR h.Parent_TSN = 9417 OR h.Parent_TSN = 14189 OR h.Parent_TSN = 500000 OR h.Parent_TSN = 660046 OR h.Parent_TSN = 846119 OR h.Parent_TSN = 846495 OR h.Parent_TSN = 846496



// OR h.Parent_TSN = 5415 OR h.Parent_TSN = 9418 OR h.Parent_TSN = 15648 OR h.Parent_TSN = 15680 OR h.Parent_TSN = 15743 OR h.Parent_TSN = 15754 OR h.Parent_TSN = 18015 OR h.Parent_TSN = 18054 OR h.Parent_TSN = 18063 OR h.Parent_TSN = 500006 OR h.Parent_TSN = 500007 OR h.Parent_TSN = 500009 OR h.Parent_TSN = 846123 OR h.Parent_TSN = 846124 OR h.Parent_TSN = 846125 OR h.Parent_TSN = 846126 OR h.Parent_TSN = 846127 OR h.Parent_TSN = 846128 OR h.Parent_TSN = 846129 OR h.Parent_TSN = 846130 OR h.Parent_TSN = 846131 OR h.Parent_TSN = 846507 OR h.Parent_TSN = 846508 OR h.Parent_TSN = 846509 OR h.Parent_TSN = 846510 OR h.Parent_TSN = 846511 OR h.Parent_TSN = 846512 OR h.Parent_TSN = 846513 OR h.Parent_TSN = 846514 OR h.Parent_TSN = 846515 OR h.Parent_TSN = 846516 OR h.Parent_TSN = 846518 OR h.Parent_TSN = 846519 OR h.Parent_TSN = 846520 OR h.Parent_TSN = 846521 OR h.Parent_TSN = 846522 OR h.Parent_TSN = 846523 OR h.Parent_TSN = 846524 OR h.Parent_TSN = 846526 OR h.Parent_TSN = 846528 OR h.Parent_TSN = 954907

debug2($buscar);



$resultado = [];

foreach ($buscar as $indice => $busca) {
    $inserts = get_next_level(get_itis_classificacoes($busca['tsn']), $taxonomia_nome, $taxonomia_id, $busca['tsn']);

    debug2($inserts);
    // exit;


    foreach ($inserts as $indice => $insert) {
        $resultado[] = insert_table($insert);
    }
}




debug2($resultado);
exit;




$insert_db = [
    'tsn'              => $retorno[0]['TSN'],
    'nome'             => $retorno[0]['complete_name'],
    'id_classificacao' => 1,
];

debug2($insert_db);

$pdo = NULL;

$pdo = new PDO('mysql:host=localhost;dbname=LiveDB', 'usuario_db', 'senha_db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$table = 'classificacao_import_itis';

ksort($insert_db);

        $fieldNames = implode('`, `', array_keys($insert_db));
        $fieldValues = ':' . implode(', :', array_keys($insert_db));

        $sth = $pdo->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

        debug2($sth);

        foreach($insert_db as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        debug2($sth);


        try{
            $retorno_insert = [
                $sth->execute(),
                $pdo->lastInsertId(),
                $sth->errorCode(),
                $sth->errorInfo()
            ];
        }catch(Exception $e){
            debug2($e->getMessage());
        }

        $resultados = [
            "status"        => $retorno_insert[0] == true ? true : false,
            "id"            => $retorno_insert[1] != 0 ? $retorno_insert[1] : false,
            "error_code"    => $retorno_insert[2] != '00000' ? $retorno_insert[2] : false,
            "erros_info"    => !is_null($retorno_insert[3][2]) ? $retorno_insert[3][2] : false
        ];

    debug2($resultados);
exit;


$select_mem = memory_get_usage(true);



foreach ($retorno as $indice => $c) {

	$id_tsn = !empty($c['Parent_TSN']) ? $c['Parent_TSN'] : 'NULL';

	$insert_db = "INSERT INTO classificacao_import_itis"
		. " (`tsn`, `nome`, `id_classificacao`, `id_taxonomia`, `id_tsn`, `level`, `ativo`)"
		. "  VALUES"
		. " ({$c['TSN']}, '{$c['completename']}', NULL, NULL, {$id_tsn}, {$c['level']}, 1)";


	$insert = $pdo->prepare($insert_db);
	$insert->execute();

	// $retorno_novo[] = [
	// 	$insert->execute(),
	// 	$pdo->lastInsertId(),
	// 	$insert->errorCode(),
	// 	$insert->errorInfo()
	// ];

	unset($insert_db);
}
	$insert_mem = memory_get_usage(true);
	$duration = microtime(true) - $start;



	// $hours = intval(($duration / 3600000000));
 //    $minutes = intval(($duration - ($hour * 3600000000)) / 60000000);
 //    $seconds = intval(($duration - ($hour * 3600000000) - ($minutes * 60000000)) / 1000000);

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


	// outra memoria => memory_get_peak_usage()



// debug2($retorno_novo);
exit;

function insert_table($insert_db){

    $pdo = new PDO('mysql:host=localhost;dbname=LiveDB', 'usuario_db', 'senha_db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table = 'classificacao_import_itis';

    ksort($insert_db);

    $fieldNames = implode('`, `', array_keys($insert_db));
    $fieldValues = ':' . implode(', :', array_keys($insert_db));

    $sth = $pdo->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

    // debug2($sth);

    foreach($insert_db as $key => $value) {
        $sth->bindValue(":$key", $value);
    }

    // debug2($sth);


    try{
        $retorno_insert = [
            $sth->execute(),
            $pdo->lastInsertId(),
            $sth->errorCode(),
            $sth->errorInfo()
        ];
    }catch(Exception $e){
        debug2($e->getMessage());
    }

    return [
        "status"        => $retorno_insert[0] == true ? true : false,
        "id"            => $retorno_insert[1] != 0 ? $retorno_insert[1] : false,
        "error_code"    => $retorno_insert[2] != '00000' ? $retorno_insert[2] : false,
        "erros_info"    => !is_null($retorno_insert[3][2]) ? $retorno_insert[3][2] : false
    ];
}

function get_next_level($classificacoes, $taxonomia_nome, $taxonomia_id, $classificacao_pai = NULL){

    // debug2(get_defined_vars());
    // exit;
    $insert_db = [];

    foreach ($classificacoes as $indice => $classificacao) {

        if($classificacao['rank_name'] == $taxonomia_nome){
            $insert_db[] = [
                'tsn'              => $classificacao['TSN'],
                'nome'             => $classificacao['complete_name'],
                'id_classificacao' => isset($classificacao_pai) ? $classificacao_pai : NULL,
                'id_taxonomia'     => $taxonomia_id
            ];
        }else{

            $insert_db = array_merge($insert_db, get_next_level(get_itis_classificacoes($classificacao['TSN']), $taxonomia_nome, $taxonomia_id, $classificacao_pai));
        }

    }

    return $insert_db;
}

function get_itis_classificacoes($parent_tsn){
    $pdo = new PDO('mysql:host=localhost;dbname=ITIS', 'usuario_db', 'senha_db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $select = ' select'
    . '     h.TSN,'
    . '     h.Parent_TSN,'
    . '     tu.`usage`,'
    . '     tu.kingdom_id,'
    . '     tu.rank_id,'
    . '     tu.complete_name,'
    . '     tut.rank_name,'
    . '     k.kingdom_name'
    . ' FROM'
    . '     `hierarchy` h'
    . ' LEFT JOIN `taxonomic_units` tu ON'
    . '     tu.tsn = h.TSN'
    . ' LEFT JOIN `taxon_unit_types` tut ON'
    . '     tut.rank_id = tu.rank_id AND tut.kingdom_id = tu.kingdom_id'
    . ' LEFT JOIN `kingdoms` k ON '
    . '     k.kingdom_id = tut.kingdom_id'
    . ' WHERE'
    . '     h.Parent_TSN = ' . $parent_tsn;

    $consulta = $pdo->prepare($select);
    $consulta->execute();
    $retorno = $consulta->fetchAll(\PDO::FETCH_ASSOC);

    return $retorno;
}

function get_itis_tsn_pais($id_taxonomia){
    $pdo = new PDO('mysql:host=localhost;dbname=LiveDB', 'usuario_db', 'senha_db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $select = ' select'
    . '     tsn'
    . ' FROM'
    . '     `classificacao_import_itis`'
    . ' WHERE'
    . '     id_taxonomia = ' . $id_taxonomia;

    $consulta = $pdo->prepare($select);
    $consulta->execute();
    $retorno = $consulta->fetchAll(\PDO::FETCH_ASSOC);

    return $retorno;
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