<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Inicio extends CI_Controller {
	public function index()
	{				
		$this->load->library('watsonmarketing');
		try {
			$sesion = $this->watsonmarketing->login("usuario", "contraseña");
			$lista = $this->watsonmarketing->getLists();
			$cerrar =$this->watsonmarketing->logout();
			print_r($lista);
		} catch (Exception $e) {
			die("\nException caught: {$e->getMessage()}\n\n");
		}
		
	}
	
}
