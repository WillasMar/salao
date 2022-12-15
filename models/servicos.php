<?php
	
	class Servicos extends Model{

		public function getServicos(){
			$dados = array();

			$sql = $this->db->query("SELECT * from servicos");

			if($sql->rowCount() > 0){
				$dados = $sql->fetchAll();
			}

			return $dados;
		}

	}