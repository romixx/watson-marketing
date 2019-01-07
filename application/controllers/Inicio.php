<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Inicio extends CI_Controller {
	public function index()
	{		
		// $this->load->view('inicio/principal');
		echo "Bienvenido"; 
	}
	
	public function conecta(){
	//	prueba de codigo referencia-> https://developer.ibm.com/customer-engagement/docs/watson-marketing/ibm-engage-2/watson-campaign-automation-platform/xml-api/codesamplesresponses/#CodeSample%E2%80%93PHP
		$this->load->library('watsonmarketing');
		$pod = 8;
		$username = 'username';
		$password = 'password';

		$endpoint = "https://api{$pod}.silverpop.com/XMLAPI";
		$jsessionid = null;

		$baseXml = '%s';
		$loginXml = '<Envelope><Body>
						<Login>
						<USERNAME>%s</USERNAME>
						<PASSWORD>%s</PASSWORD>
						</Login>
						</Body>
					</Envelope>';
		$getListsXml = '<Envelope><Body>
							<GetLists>
							<VISIBILITY>%d</VISIBILITY>
							<LIST_TYPE>%d</LIST_TYPE>
							</GetLists>
							</Body>
						</Envelope>';
		$logoutXml = '<Envelope><Body>
						<Logout/>
						</Body>
					</Envelope>';

		try {

			$xml = sprintf($baseXml, sprintf($loginXml, $username, $password));

			$result = $this->watsonmarketing->xmlToArray($this->watsonmarketing->makeRequest($endpoint, $jsessionid, $xml));			
			
			if (!isset($result['SESSIONID']) || empty($result['SESSIONID'])) {
				die("\n\n <br> Error: No se ha podido establecer session con los datos especificados\n\n");
			}
			$jsessionid = $result['SESSIONID'];
		
			$xml = sprintf($baseXml, sprintf($getListsXml, 1, 2)); // VISIBILITY 1 = Shared, LIST_TYPE 2 = Regular and Query
			
			$result = $this->watsonmarketing->xmlToArray($this->watsonmarketing->makeRequest($endpoint, $jsessionid, $xml));
			print_r($result); echo "<br><br>";
		
			$xml = $logoutXml;
			$result = $this->watsonmarketing->xmlToArray($this->watsonmarketing->makeRequest($endpoint, $jsessionid, $xml, true));
			print_r($result);
		
			$jsessionid = null;
		
			print "\nDone\n\n";
		} catch (Exception $e) {

			die("\nException caught: {$e->getMessage()}\n\n");
		}

	}
	
}
