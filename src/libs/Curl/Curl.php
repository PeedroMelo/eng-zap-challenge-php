<?php
    class Curl
    {
        public function get($options = [])
        {
            $url = isSet($options['url']) ? $options['url'] : '';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL            => "$url",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "GET",
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        }

        public function getTest($options = [])
        {
            $url = isSet($options['url']) ? $options['url'] : '';

            $response = file_get_contents($url);

            return $response;
        }
    }