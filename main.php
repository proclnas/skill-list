<?php

header('Content-type: application/json');

require_once __DIR__ . '/vendor/autoload.php';

var_dump(new \SkillList\SkillList);
/*$responseSkeleton = [
    'error' => false,
    'msg' => '',
    'result'
];

$requestedEngine = $_GET['engine'];
$requestedKeyword = $_GET['keyword'];

if (!in_array($requestedEngine, SkillList::getAllowedEngines())) {
    $responseSkeleton['error'] = true;
    $responseSkeleton['msg'] = 'Engine inválida';
} elseif (!isset($requestedKeyword)) {
    $responseSkeleton['error'] = true;
    $responseSkeleton['msg'] = 'Especifíque uma keyword';
}

try {
    $skillList = new SkillList($requestedEngine, $requestedKeyword);
    $skillList->start();
    $response = $skillList->getResults();

    if (!is_null($response)) {
        $responseSkeleton['msg'] = 'sucesso';
        $responseSkeleton['result'] = $response;
    }
} catch (Exception $e) {
    $responseSkeleton['error'] = true;
    $responseSkeleton['msg'] = $e->getMessage();
} finally {
    echo json_encode($responseSkeleton);
}*/