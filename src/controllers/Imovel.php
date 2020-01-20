<?php
    class Imovel
    {
        private $curl;
        private $config = [];
        private $httpParams = [];
        private $curlResponse = [];

        function __construct($req = [])
        {
            require '../libs/Curl/Curl.php';
            $this->curl = new Curl();

            $this->config = json_decode(file_get_contents("../config/config.json"), true);

            $this->httpParams = [
                'source'     => isSet($req['source'])     ? $req['source']     : '',
                'pageNumber' => isSet($req['pageNumber']) ? $req['pageNumber'] : 1,
            ];

            $this->customParams = [
                'businessType'  => isSet($req['businessType'])  ? $req['businessType']  : '',
                'bathrooms'     => isSet($req['bathrooms'])     ? $req['bathrooms']     : '',
                'bedrooms'      => isSet($req['bedrooms'])      ? $req['bedrooms']      : '',
                'usableAreas'   => isSet($req['usableAreas'])   ? $req['usableAreas']   : '',
                'parkingSpaces' => isSet($req['parkingSpaces']) ? $req['parkingSpaces'] : '',
                'listingType'   => isSet($req['listingType'])   ? $req['listingType']   : '',
            ];
        }

        public function getImovel()
        {
            if ($this->httpParams['source'] == '' || !in_array($this->httpParams['source'], ['ZAP', 'VIVA'])) {
                http_response_code(500);
                return '[002] Invalid source data (ZAP/VIVA)';
            }

            // $this->curlResponse = json_decode(file_get_contents($this->config['sourceFileUrlTest']), true);
            $options = [
                'url'    => isset($this->config['sourceFileUrl']) ? $this->config['sourceFileUrl'] : '',
                'filter' => $this->httpParams,
            ];
            $this->curlResponse = $this->curl->get($options);
            $imoveis = $this->handleImovel();

            return $imoveis;
        }

        private function handleImovel()
        {
            switch ($this->httpParams['source']) {
                case 'ZAP':
                    $result = $this->filterZap();
                    break;
                case 'VIVA':
                    $result = $this->filterViva();
                    break;
            }
            return $result;
        }

        private function filterZap()
        {
            $response = array_filter($this->curlResponse, function($imovel) {
                $result = [];
                $gravar_resultado = 0;

                $longitude     = $imovel['address']['geoLocation']['location']['lon'];
                $latitude      = $imovel['address']['geoLocation']['location']['lat'];
                $tipo          = $imovel['pricingInfos']['businessType'];

                if ($longitude <> 0 && $latitude <> 0) {
                    switch ($tipo) {
                        case 'RENTAL':
                            if ($imovel['pricingInfos']['rentalTotalPrice'] >= 3500) $gravar_resultado = 1;
                            break;
                        case 'SALE':
                            if ($imovel['usableAreas'] > 0 && ($imovel['pricingInfos']['price'] / $imovel['usableAreas']) > 3500) {
                                if ($this->validarRegiao($latitude, $longitude)) {
                                    if ($imovel['pricingInfos']['price'] >= (600000 * 0.9)) $gravar_resultado = 1;
                                } else {
                                    if ($imovel['pricingInfos']['price'] >= 600000) $gravar_resultado = 1;
                                }
                            }
                            break;
                    }
                }
                if ($gravar_resultado == 1 && $this->filtroCustomizado($imovel) > 0) $result = $imovel;
                return $result;
            });
            return $this->handleResponse($response);
        }

        private function filterViva()
        {
            $response = array_filter($this->curlResponse, function($imovel) {
                $result = [];
                $gravar_resultado = 0;

                $longitude     = $imovel['address']['geoLocation']['location']['lon'];
                $latitude      = $imovel['address']['geoLocation']['location']['lat'];
                $tipo          = $imovel['pricingInfos']['businessType'];

                if ($longitude <> 0 && $latitude <> 0) {
                    switch ($tipo) {
                        case 'RENTAL':
                            $valorCondominio = (isSet($imovel['pricingInfos']['monthlyCondoFee']) && !preg_match("/[a-z]/i", $imovel['pricingInfos']['monthlyCondoFee'])) ? (float) $imovel['pricingInfos']['monthlyCondoFee'] : -1;
                            if ($valorCondominio >= 0 && $valorCondominio < ($imovel['pricingInfos']['rentalTotalPrice'] * 0.3)) {
                                if ($this->validarRegiao($latitude, $longitude)) {
                                    if ($imovel['pricingInfos']['rentalTotalPrice'] <= (4000 * 1.5)) $gravar_resultado = 1;
                                } else {
                                    if ($imovel['pricingInfos']['rentalTotalPrice'] <= 4000) $gravar_resultado = 1;
                                }
                            }
                            break;
                        case 'SALE':
                            if ($imovel['pricingInfos']['price'] <= 700000) $gravar_resultado = 1;
                            break;
                    }
                }
                if ($gravar_resultado == 1 && $this->filtroCustomizado($imovel) > 0) $result = $imovel;
                return $result;
            });

            return $this->handleResponse($response);
        }

        private function handleResponse($response)
        {
            $total_count = count($response);
            $page_number = $this->httpParams['pageNumber'];
            $page_size   = $total_count > 500 ? 500 : $total_count;

            $current_index = ($page_number > 1) ? ($page_number * $page_size) - 1 : 0;
            
            $result = array_slice($response, $current_index, $page_size);

            return [
                'pageNumber' => (int) $page_number,
                'pageSize'   => (int) $page_size,
                'totalCount' => (int) $total_count,
                'listings'   => $result
            ];
        }

        private function validarRegiao($latitude, $longitude)
        {
            if (($longitude >= $this->config['boundingBox']['minlon'] && $longitude <= $this->config['boundingBox']['maxlon']) &&
                ($latitude  >= $this->config['boundingBox']['minlat'] && $latitude  <= $this->config['boundingBox']['maxlat'])) {
                    return true;
            } else {
                return false;
            }
        }

        private function filtroCustomizado($imovel)
        {
            $gravar_filtro = 1;
            $dePara = $this->setDePara($imovel);

            foreach ($this->customParams as $chave => $parametro) {
                if ($this->customParams[$chave] <> '') {
                    if ($this->customParams[$chave] == $dePara[$chave]) {
                        $gravar_filtro++;
                    } else {
                        $gravar_filtro = 0;
                        break;
                    }
                }
            }
            return $gravar_filtro;
        }

        private function setDePara($para)
        {
            return [
                'businessType'  => $para['pricingInfos']['businessType'],
                'bathrooms'     => isset($para['bathrooms']) ? $para['bathrooms'] : 0,
                'bedrooms'      => isset($para['bedrooms']) ? $para['bedrooms'] : 0,
                'usableAreas'   => isset($para['usableAreas']) ? $para['usableAreas'] : 0,
                'parkingSpaces' => isset($para['parkingSpaces']) ? $para['parkingSpaces'] : 0,
                'listingType'   => isset($para['listingType']) ? $para['listingType'] : '',
            ];
        }
    }
?>