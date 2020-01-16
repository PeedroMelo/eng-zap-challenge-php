<?php
    header("Content-type: application/json; charset=UTF-8");
    
    require_once __DIR__ . "/../libs/CallbackApi/Api.php";
    require_once __DIR__ . "/../controllers/Imovel.php";

    $api = new Api();
    $imovel = new Imovel();

    $api->get('imovel', $imovel, function($im) {
        echo json_encode($im->getImovel(false));
    });

    $api->get('imovel/teste', $imovel, function($im) {
        $debug = true;
        echo json_encode($im->getImovel($debug));
    });