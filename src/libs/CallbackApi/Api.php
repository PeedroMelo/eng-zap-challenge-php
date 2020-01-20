<?php

    class Api
    {
        public function __construct()
        {
            header("Content-type: application/json; charset=UTF-8");
        }

        // TODO: implementar uma trava pra não permitir que o método get seja chamado mais de uma vez em uma unica execução para o mesmo endereço.
        public function get($path = '/', $call = '')
        {
            if ($this->validateCurrentPath($path)) {
                if (!$this->validateRequestMethod('GET')) {
                    $this->response(400, '[400] Invalid request type.');
                    return false;
                }

                $req = $_GET;

                if ($call <> '') return $call($req);
            }
        }

        private function validateCurrentPath($path)
        {
            $url = $_GET['url'];
            if ($path <> $url) return false;
            return true;
        }

        private function validateRequestMethod($requestMethod)
        {
            if ($_SERVER['REQUEST_METHOD'] <> $requestMethod) {
                return false;
            }
            return true;
        }

        public function response($response_code, $return)
        {
            http_response_code($response_code);
            print_r(json_encode($return));
        }
    }