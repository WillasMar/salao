<?php

	class ProfissionalServico extends Model{

		public function getProfissionalServico(){
			$dados = array();

			$sql = $this->db->query("SELECT * from profissional_servico");

			if($sql->rowCount() > 0){
				$dados = $sql->fetchAll();
			}

			return $dados;
		}
	}