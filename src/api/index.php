<?php
    require_once __DIR__ . "/../libs/CallbackApi/Api.php";

    $api = new Api();

    $api->get('listarImoveis', function($req) {

        require_once __DIR__ . "/../controllers/Imovel.php";
        $imovel = new Imovel($req);

        print_r(json_encode($imovel->getImovel()));
    });

    $api->get('listarImoveis/test', function($req) {

        require_once __DIR__ . "/../controllers/ImovelTest.php";
        $imovel = new ImovelTest($req);

        print_r(json_encode($imovel->getImovel()));
    });