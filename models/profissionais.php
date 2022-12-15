<?php

	class Profissionais extends Model{

		public function getProfissionais(){
			$dados = array();

			$sql = $this->db->query("SELECT * from profissionais");

			if($sql->rowCount() > 0){
				$dados = $sql->fetchAll();
			}

			return $dados;
		}

		public function getProfissional($id){
			$dados = array();

			$id = addslashes($id);

			$sql = "SELECT * from profissionais where id = ?";
			$sql = $this->db->prepare($sql);
			$sql->execute(array($id));

			if($sql->rowCount() > 0){
				$dados = $sql->fetchAll();
			}

			return $dados;
		}

		public function getDisponibilidade($semana, $prof){
			$dados = array();

			$semana = addslashes($semana);

			if($prof){
				$and = 'and profissionais_horarios.id_profissional = '.$prof;
			}else{
				$and = '';
			}

			$sql = "select 
					profissionais_horarios.id, 
					profissionais_horarios.id_profissional, 
					profissionais_horarios.semana, 
					profissionais_horarios.hora, 
					profissionais_horarios.hora_final,
					profissionais.nome,
					(SELECT COUNT(*) FROM profissionais_servicos
						WHERE profissionais_servicos.id_profissional = profissionais_horarios.id_profissional) as qtdServico
				from profissionais_horarios 
				inner join profissionais on profissionais.id = profissionais_horarios.id_profissional
				where profissionais_horarios.semana = ? $and
				order by 7 desc";
			$sql = $this->db->prepare($sql);
			$sql->execute(array($semana));

			if($sql->rowCount() > 0){
				$dados = $sql->fetchAll();
			}

			return $dados;
		}

		public function getServicos($prof){
			$dados = array();

			$id = addslashes($prof);			

			$sql = "SELECT profissionais_servicos.id, profissionais_servicos.id_profissional, profissionais_servicos.id_servico, servicos.descricao, profissionais.nome
				from profissionais_servicos 
				INNER JOIN servicos ON servicos.id = profissionais_servicos.id_servico
				INNER JOIN profissionais ON profissionais.id = profissionais_servicos.id_profissional			
				where profissionais_servicos.id_profissional = ?";
			$sql = $this->db->prepare($sql);
			$sql->execute(array($id));

			//se tiver serviço pro profissional
			if($sql->rowCount() > 0){
				$dados = $sql->fetchAll();
			}

			return $dados;
		}
	}