<?php
    class Imovel
    {
        private $curl;
        private $config = [];
        private $httpParams = [];
        private $test = false;

        function __construct($test = false)
        {
            require '../libs/Curl/Curl.php';
            $this->curl = new Curl();

            $this->config = $this->setConfig();
            
            $this->test    = $test;

            $this->httpParams = [
                'source'     => isSet($_GET['source']) ? $_GET['source'] : '',
                'pageNumber' => isSet($_GET['pageNumber']) ? $_GET['pageNumber'] : '',
                'pageSize'   => isSet($_GET['pageSize']) ? $_GET['pageSize'] : '',
            ];
        }

        private function setConfig()
        {
            return json_decode(file_get_contents("../config/config.json"), true);
        }

        public function getImovel($test = false)
        {
            if ($this->httpParams['source'] == '' || !in_array($this->httpParams['source'], ['ZAP', 'VIVA'])) {
                http_response_code(500);
                return '[002] Invalid source data (ZAP/VIVA)';
            }

            if (!$test) {
                $options = [
                    'url'    => isset($this->config['sourceFileUrl']) ? $this->config['sourceFileUrl'] : '',
                    'filter' => $this->httpParams,
                ];
                $response = $this->curl->get($options);
                $result = $this->handleImovel($response, $options['filter']);
            } else {
                $options = [
                    'url'    => isset($this->config['sourceFileUrlTest']) ? $this->config['sourceFileUrlTest'] : '',
                    'filter' => $this->httpParams,
                ];
                $response = $this->curl->getTest($options);
                $result = $this->handleImovel($response, $options['filter']);
            }

            return $result;
        }

        // TODO: Aplicar regras de negócio
        private function handleImovel($response, $filter = [])
        {
            return $this->handleResponse($response, $filter);
        }

        private function handleResponse($response, $filter = [])
        {
            $response = json_decode($response, true);

            $total_count = count($response);
            $page_number = isSet($filter['pageNumber']) ? $filter['pageNumber'] : 1;
            $page_size   = $total_count > 50 ? 50 : $total_count;

            $current_index = ($page_number > 1) ? ($page_number * $page_size) - 1 : 0;
            
            $result = array_slice($response, $current_index, $page_size);

            return [
                'pageNumber' => (int) $page_number,
                'pageSize'   => (int) $page_size,
                'totalCount' => (int) $total_count,
                'listings'   => $result
            ];
        }
    }
?>