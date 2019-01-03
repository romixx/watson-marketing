<?php

class Watsonmarketing {

    function makeRequest($endpoint, $jsessionid, $xml, $ignoreResult = false)
    {
        $url = $this->getApiUrl($endpoint, $jsessionid);
        
        $xmlObj = new SimpleXmlElement($xml);
        $request = $xmlObj->asXml();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        $headers = array(
            'Content-Type: text/xml; charset=UTF-8',
            'Content-Length: ' . strlen($request),
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        $response = @curl_exec($curl);

        if (false === $response) {
            throw new Exception('CURL error: ' . curl_error($curl));
        }

        curl_close($curl);

        if (true === $response || !trim($response)) {
            throw new Exception('Empty response from WCA');
        }

        $xmlResponse = simplexml_load_string($response);

        if (false === $ignoreResult) {
            if (false === isset($xmlResponse->Body->RESULT)) {
                var_dump($xmlResponse);
                throw new Exception('Unexpected response from WCA');
            }

            return $xmlResponse->Body->RESULT;
        }

        return $xmlResponse->Body;
    }

    function getApiUrl($endpoint, $jsessionid)
    {
        return $endpoint . ((null === $jsessionid)
            ? ''
            : ';jsessionid=' . urlencode($jsessionid));
    }

    function xmlToJson($xml)
    {
        return json_encode($xml);
    }

    function xmlToArray($xml)
    {
        $json = $this->xmlToJson($xml);
        return json_decode($json, true);
    }

}

?>