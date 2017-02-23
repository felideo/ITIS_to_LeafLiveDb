<?php

error_reporting(0);
ini_set('display_errors', 0);
set_time_limit (36000);
$start = microtime(true);

// INSERT INTO LiveDB.classificacao_import_itis
// (tsn, nome, id_classificacao, id_taxonomia, id_tsn, `level`, ativo, id, id_pai)
// VALUES(202422, 'Plantae', NULL, 1, NULL, NULL, 1, NULL, NULL);


// $classificacao_ascendente  = 1;
// $classificacao_descendente = 2;
// $classificacao_nome        = 'Division';

// $classificacao_ascendente  = 2;
// $classificacao_descendente = 3;
// $classificacao_nome        = 'Class';

// $classificacao_ascendente  = 3;
// $classificacao_descendente = 4;
// $classificacao_nome        = 'Order';

// $classificacao_ascendente  = 4;
// $classificacao_descendente = 5;
// $classificacao_nome        = 'Family';

// $classificacao_ascendente  = 5;
// $classificacao_descendente = 6;
// $classificacao_nome        = 'Genus';

// $classificacao_ascendente  = 6;
// $classificacao_descendente = 7;
// $classificacao_nome        = 'Species';

$classificacao_ascendente  = 7;
$classificacao_descendente = 8;
$classificacao_nome        = 'Subspecies';

$buscar = get_ascendentes($classificacao_ascendente);

debug2($buscar);

$resultado = [];

foreach ($buscar as $indice => $busca) {
    $inserts = get_next_level(get_itis_classificacoes($busca['tsn']), $classificacao_nome, $classificacao_descendente, $busca['tsn']);

    foreach ($inserts as $indice => $insert) {
        $resultado[] = insert_table($insert);
    }
}


debug2($resultado);

	$duration = microtime(true) - $start;

    $hours        = (int) ($duration / 60 / 60);
    $minutes      = (int) ($duration / 60) - $hours * 60;
    $seconds      = (int) $duration - $hours * 60 * 60 - $minutes * 60;
    $microseconds = ($duration - $seconds - ($minutes * 60));

	echo "Total registros => " . count($resultado) . "<br>";
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


echo '<br><br><br> Fim Importação';
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

function get_next_level($classificacoes, $classificacao_nome, $classificacao_descendente, $id_ascendente = NULL){
    $insert_db = [];

    foreach ($classificacoes as $indice => $classificacao) {

        if($classificacao['rank_name'] == $classificacao_nome){
            $insert_db[] = [
                'tsn'              => $classificacao['TSN'],
                'nome'             => $classificacao['complete_name'],
                'id_classificacao' => isset($id_ascendente) ? $id_ascendente : NULL,
                'id_taxonomia'     => $classificacao_descendente
            ];
        }else{
            $insert_db = array_merge($insert_db, get_next_level(get_itis_classificacoes($classificacao['TSN']), $classificacao_nome, $classificacao_descendente, $id_ascendente));
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

function get_ascendentes($id_taxonomia){
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