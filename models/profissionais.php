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
				$dados = $sql->fetch();
			}

			return $dados;
		}

		//busca disponibilidade pelo dia da semana
		public function getDisponibilidade($semana, $prof){
			$dados = array();
			$and = '';

			$semana = addslashes($semana);

			if($prof){
				$and = 'and profissionais_horarios.id_profissional = '.$prof;
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

		//busca indisponibilidade pelo dia da semana
		public function getIndisponibilidade($semana, $prof){
			$dados = array();
			$semana = addslashes($semana);
			$prof = addslashes($prof);
			$horaAtual = "'".date('H:i:s')."'";

			//se for o dia da atual da semana
			if( $semana == date('w') ){
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
					where profissionais_horarios.semana = :semana and 
					($horaAtual not between  profissionais_horarios.hora AND profissionais_horarios.hora_final)
					and (profissionais_horarios.id_profissional = :prof OR :prof = 0)
					order by 7 desc";
				$sql = $this->db->prepare($sql);
				$sql->bindValue(':semana', $semana);
				$sql->bindValue(':prof', $prof);
				$sql->execute();

				if($sql->rowCount() > 0){
					$dados = $sql->fetchAll();
				}

			}

			return $dados;
		}

		//busca serviços dos profissionais
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

		//busca serviço de um profissional
		public function getServico($prof, $serv){
			$dados = array();

			$prof = addslashes($prof);
			$serv = addslashes($serv); 			

			$sql = "SELECT profissionais_servicos.id, 
						profissionais_servicos.id_profissional,
						profissionais_servicos.id_servico,
						servicos.tempo
					from profissionais_servicos
					INNER JOIN servicos ON servicos.id = profissionais_servicos.id_servico 
					where profissionais_servicos.id_profissional = ? and profissionais_servicos.id_servico = ?";
			$sql = $this->db->prepare($sql);
			$sql->execute(array($prof, $serv));

			//se tiver serviço pro profissional
			if($sql->rowCount() > 0){
				$dados = $sql->fetch();
			}

			return $dados;
		}
	}