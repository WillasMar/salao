<?php
	
	date_default_timezone_set('America/Sao_Paulo');

	class Agenda extends Model{

		//busca agendamento por data
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

		//busca horários do profissional
		public function getAgendamento($dt, $prof){
			$sql = "SELECT hora from agenda 
				where data = ? and id_profissional = ? 
				order by 1";
			$sql = $this->db->prepare($sql);
			$sql->execute(array($dt, $prof));

			if($sql->rowCount() > 0){
				return $sql->fetchAll();
			}else{
				return false;
			}
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

			$profissionais = new Profissionais();
			$servicos = new Servicos();

			$profDisp = $profissionais->getDisponibilidade($semana, $prof);
			$profIndisp = $profissionais->getIndisponibilidade($semana, $prof);			

			//se profissional não estiver indisponível
			if(!$profIndisp){
				//busca serviço vinculado ao profissional
				$profServ = $profissionais->getServico($prof, $serv);

				//se serviço estiver vinculado ao profissional
				if($profServ){
					$profDisp = $profDisp[0];
					$hora = date('H:i', strtotime($profDisp['hora'])); //hora inicial
					$horaFinal =  date('H:i', strtotime($profDisp['hora_final'])); //hora final
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

					//busca agendamentos
					$h = $this->getAgendamento($dt, $prof);

					//se tiver horários
					if($h){
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

		//grava agendamento
		public function agendar($array){
			$profissionais = new Profissionais();
			$servicos = new Servicos();

			$dados['result'] = 'aviso';
			$dados['msg'] = "<p>Sem dados, verifique o <u>Profissional</u>, <u>Serviço</u>, <u>Data</u>, <u>Hora</u>, <u>Nome</u>, e <u>Celular</u></p>";

			//verifica campos
			if( (isset($array['agendar']['profissional']) && !empty($array['agendar']['profissional']) )&& 
				(isset($array['agendar']['servico']) && !empty($array['agendar']['servico']) ) &&
				(isset($array['agendar']['data']) && !empty($array['agendar']['data']) ) &&
				(isset($array['agendar']['hora']) && !empty($array['agendar']['hora']) ) &&
				(isset($array['agendar']['nome']) && !empty($array['agendar']['nome']) ) &&
				(isset($array['agendar']['celular']) && !empty($array['agendar']['celular']) ) &&
				(isset($array['agendar']['email']) && isset($array['agendar']['cpf']))
			){
				$prof = addslashes( $array['agendar']['profissional'] );
				$serv = addslashes( $array['agendar']['servico'] );
				$data = $array['agendar']['data'];
				$hora = $array['agendar']['hora'];
				$hora_fim = $array['agendar']['hora'];
				$nome = addslashes( $array['agendar']['nome'] );
				$email = addslashes( $array['agendar']['email'] );
				$cpf = addslashes( $array['agendar']['cpf'] );
				$celular = addslashes( $array['agendar']['celular'] );

				//busca profissional disponível
				$profissionais = new Profissionais();
				$profDisp = $profissionais->getDisponibilidade( date( 'w', strtotime($data) ), $prof );
				$profIndisp = $profissionais->getIndisponibilidade( date( 'w', strtotime($data) ), $prof );

				//se profissional estiver disponível
				if($profDisp && !$profIndisp){
					//busca serviço vinculado ao profissional
					$profServ = $profissionais->getServico($prof, $serv);

					//se haver serviço pro profissional
					if($profServ){
						$tempoServ = date('i', strtotime($profServ['tempo']));
						$hora_fim = date('H:i', strtotime('+'.$tempoServ.' minutes') );

						//verifica horários
						$horarios = $this->getHorarios($prof, $serv, $data);

						//se haver horário disponível
						if( in_array(  $hora, $horarios ) ){
							$dados['horarios'] = $horarios;
							
							$sql = "insert into agenda(id_profissional, id_servico, data, hora, hora_fim, nome, email, cpf, celular) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
							$sql = $this->db->prepare($sql);
							$sql->execute( array($prof, $servico, $data, $hora, $hora_fim, $nome, $email, $cpf, $celular) );
							
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
						$dados['msg'] = '<p>Profissional <b>'.$profDisp['nome'].'</b> não faz o serviço <b>'.$servico['descricao'].'</b>!</p>';
					}

				}else{
					$dados['result'] = false;
					$dados['msg'] = '<p>Profissional <b>'.$profDisp['nome'].'h</b> indisponível!</p>';
				}
			}

			return $dados;
		}

	}