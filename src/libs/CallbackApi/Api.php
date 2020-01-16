<?php

    class Api
    {
        // TODO: implementar uma trava pra não permitir que o método get seja chamado mais de uma vez em uma unica execução para o mesmo endereço.
        public function get($path = '/', $args = '', $call = '')
        {
            if ($this->validateCurrentPath($path)) {

                if (!$this->validateRequestMethod('GET')) {
                    return '[001] Invalid request type.';
                }

                if ($call <> '') return $call($args);
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
                http_response_code(500);
                return false;
            }
            return true;
        }
    }