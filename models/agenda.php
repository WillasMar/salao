<?php
	
	date_default_timezone_set('America/Sao_Paulo');

	class Agenda extends Model{

		public function getAgenda($dt){
			$dados = array();

			$where = '';

			if(!empty($dt)){
				$_dt = explode('-', $dt);
				$ano = array_shift($_dt);
				$mes = array_shift($_dt);
				$data = $ano.$mes;

				$where = " WHERE EXTRACT(YEAR_MONTH FROM agenda.data) = $data ";
			}

			$sql = $this->db->query("SELECT agenda.id, agenda.id_servico, agenda.data, agenda.hora, agenda.nome, agenda.email, agenda.celular, agenda.cpf, servicos.descricao
				from agenda 
				inner join servicos on servicos.id = agenda.id_servico
				$where
				order by agenda.data, agenda.hora");

			if($sql->rowCount() > 0){
				$dados = $sql->fetchAll();
			}

			return $dados;
		}

		//recebe data, seviço e profissional
		public function getHorarios($prof, $serv, $dt){
			//se data estiver vazia ou menor que a data atual
			if( empty($dt) || (!empty($dt) && strtotime($dt) < strtotime(date('Y-m-d'))) ){
				$dt = date('Y-m-d'); //recebe data atual
			}

			$dados = array(); //dados a serem retornados
			$horarios = array(); //horários agendados
			$semana = date( 'w', strtotime($dt) );

			//busca disponibilidade do profissional
			$sql = "SELECT * from profissionais_horarios 
				where semana = ? and id_profissional = ?";
			$sql = $this->db->prepare($sql);
			$sql->execute(array($semana, $prof));				

			//se profissional estiver disponível
			if($sql->rowCount() > 0){
				$profDisp = $sql->fetch(); //salva profissional

				//busca serviço vinculado ao profissional
				$sql = "SELECT profissionais_servicos.id, 
						profissionais_servicos.id_profissional,
						profissionais_servicos.id_servico,
						servicos.tempo
					from profissionais_servicos
					INNER JOIN servicos ON servicos.id = profissionais_servicos.id_servico 
					where profissionais_servicos.id_profissional = ? and profissionais_servicos.id_servico = ?";
				$sql = $this->db->prepare($sql);
				$sql->execute(array($prof, $serv));

				//se serviço estiver vinculado ao profissional
				if($sql->rowCount() > 0){
					$profServ = $sql->fetch(); //salva serviço vinculado

					$hora = date('H:i', strtotime($profDisp['hora'])); //'08:00'; //hora inicial
					$horaFinal =  date('H:i', strtotime($profDisp['hora_final'])); //'18:00'; //hora final
					$duracao = date('i', strtotime($profServ['tempo'])); //tempo do serviço
					
					//se for data atual, define hora atual
					if( $dt == date('Y-m-d') ){	
						$h = date('H'); //hora atual	
						$m = date('i'); //minuto atual

						$hora = $h.':'.$m;
						
						//se for menor que 30m mantém a hora e 30m
						//se não pega próximo horário
						$hora = ( intval($m) < $duracao  ) ? $h.':'.$duracao : date('H', strtotime('+1 hour')).':00';
					}

					//busca horários agendados
					$sql = "SELECT hora from agenda 
						where data = ? and id_profissional = ? 
						order by 1";
					$sql = $this->db->prepare($sql);
					$sql->execute(array($dt, $prof)); 

					//se tiver horários
					if($sql->rowCount() > 0){
						$h = $sql->fetchAll();

						//recebe horários	
						foreach($h as $item){
							array_push($horarios, date('H:i', strtotime( $item['hora'] ) ) );
						}			
					}

					//verifica horas disponíveis
					while( strtotime($hora) <= strtotime($horaFinal) ){
						//se horário não estiver agendado
						if( !in_array($hora, $horarios) ) {
							array_push($dados, $hora); //disponibiliza horário	
						}	

						$hora = date('H:i', strtotime('+'.$duracao.' minutes '.$hora));
					}
				}
			}

			return $dados;
		}

		public function agendar($array){
			$dados['result'] = 'aviso';
			$dados['msg'] = "<p>Sem dados, verifique o <u>Serviço</u>, <u>Data</u>, <u>Hora</u> ou <u>Nome</u></p>";

			//verifica campos
			if( (isset($array['agendar']['profissional']) && !empty($array['agendar']['profissional']) )&& 
				(isset($array['agendar']['servico']) && !empty($array['agendar']['servico']) ) &&
				(isset($array['agendar']['data']) && !empty($array['agendar']['data']) ) &&
				(isset($array['agendar']['hora']) && !empty($array['agendar']['hora']) ) &&
				(isset($array['agendar']['nome']) && !empty($array['agendar']['nome']) ) &&
				(isset($array['agendar']['email']) && isset($array['agendar']['cpf']) &&
				isset($array['agendar']['celular']) )
			){
				$prof = addslashes( $array['agendar']['profissional'] );
				$servico = addslashes( $array['agendar']['servico'] );
				$data = $array['agendar']['data'];
				$hora = $array['agendar']['hora'];
				$nome = addslashes( $array['agendar']['nome'] );
				$email = addslashes( $array['agendar']['email'] );
				$cpf = addslashes( $array['agendar']['cpf'] );
				$celular = addslashes( $array['agendar']['celular'] );

				//busca profissional disponível
				$profissionais = new Profissionais();
				$profDisp = $profissionais->getDisponibilidade( date( 'w', strtotime($data) ), $prof );

				//verifica horários
				$horarios = $this->getHorarios($prof, $data);

				//se profissional estiver disponível
				if($profDisp){
					//se haver horário disponível
					if( in_array(  $hora, $horarios ) ){
						$dados['horarios'] = $horarios;
						
						$sql = "insert into agenda(id_profissional, id_servico, data, hora, nome, email, cpf, celular) values (?, ?, ?, ?, ?, ?, ?, ?)";
						$sql = $this->db->prepare($sql);
						$sql->execute( array($prof, $servico, $data, $hora, $nome, $email, $cpf, $celular) );
						
						$semanas = array('Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado');

						$dados['result'] = $this->db->lastInsertId();
						$dados['msg'] = "
							<p>Horário agendado para <u class='nomePessoa'>".$nome."</u></p>
							<p>com <b>".$profDisp[0]['nome']."</b></p>
							<p><b>".date('d/m/Y', strtotime($data))."</b></p>
							<p><b>".$semanas[ date('w', strtotime($data)) ]."</b></p>
							<p><b>".date('H:i', strtotime($hora))."h</b></p>
						";
					
					}else{
						$dados['result'] = false;
						$dados['msg'] = '<p>Horário de <b>'.date('H:i', strtotime($hora)).'h</b> indisponível para <b>'.date('d/m/Y', strtotime($data)).'</b></p>';
					}

				}else{
					$dados['result'] = false;
					$dados['msg'] = '<p>Profissional <b>'.$profDisp['nome'].'h</b> indisponível!</p>';
				}
			}

			return $dados;
		}

	}