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

		public function getServico($id){
			$id = addslashes($id);

			$sql = "SELECT * from servicos where id = ?";
			$sql = $this->db->prepare($sql);
			$sql->execute(array($id));

			if($sql->rowCount() > 0){
				return $sql->fetch();
			}else{
				return false;
			}
			
		}

	}