<?php

class Watsonmarketing {

    private $endpoint = "https://api8.silverpop.com/XMLAPI";   
    private $baseXml = '%s';

    function makeRequest($jsessionid, $xml, $ignoreResult = false)
    {
        $url = $this->getApiUrl($this->endpoint, $jsessionid);
        
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

    // functions for the XML API watson Marketing
    function login($username, $password){
        $loginXml = '<Envelope><Body>
						<Login>
						<USERNAME>%s</USERNAME>
						<PASSWORD>%s</PASSWORD>
						</Login>
						</Body>
                    </Envelope>';
        
        $xml = sprintf($this->baseXml, sprintf($loginXml, $username, $password));
        $result = $this->xmlToArray($this->makeRequest(null, $xml));			
        
        if (!isset($result['SESSIONID']) || empty($result['SESSIONID'])) {
            return array("tipo"=>"error", 
                        "referencia"=>"Por favor verifique el usuario y la contraseña");
        }else{            
            $_SESSION['SESSIONID'] = $result['SESSIONID'];
            return array("tipo"=>"success",
                        "token"=>$_SESSION['SESSIONID']);
        }
        
    }

    function logout(){
        $logoutXml = '<Envelope><Body>
						<Logout/>
						</Body>
                    </Envelope>';
        $_SESSION['SESSIONID'] = (isset($_SESSION['SESSIONID'])?$_SESSION['SESSIONID']:null);
        $result = $this->xmlToArray($this->makeRequest($_SESSION['SESSIONID'], $logoutXml, true));
        unset($_SESSION['SESSIONID']);
        if ($result['RESULT']['SUCCESS']==TRUE) {            
            return array("tipo"=>"success",
                        "mensaje"=>"La session ha sido cerrada exitosamente.");
        }else{
            return array("tipo"=>"error",
                        "mensaje"=>"La session no se ha podido cerrar.");
        }
    }

    function getLists(){
        if (!isset($_SESSION['SESSIONID'])) {
            return json_encode(array("tipo"=>"error", "mensaje"=>"error en la session de usuario"));
        }
        $getListsXml = '<Envelope><Body>
							<GetLists>
							<VISIBILITY>%d</VISIBILITY>
							<LIST_TYPE>%d</LIST_TYPE>
							</GetLists>
							</Body>
                        </Envelope>';
        $xml = sprintf($this->baseXml, sprintf($getListsXml, 1, 2)); // VISIBILITY 1 = Shared, LIST_TYPE 2 = Regular and Query

        $result = $this->xmlToArray($this->makeRequest($_SESSION['SESSIONID'], $xml));
        return json_encode($result);
    }

}

?>