<?php

	class agendarController extends controller{

		public function index(){
			$agenda = new Agenda();
			$servicos = new Servicos();
			$profissionais = new Profissionais();

            $dados = array();

		    $dados['meses'] = array("Janeiro", "Fevereiro", "Março", "Abril",  "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

		    $dados['semanas'] = array("D", "S", "T", "Q", "Q", "S", "S");

		    if( isset($_POST['data']) && !empty($_POST['data']) ){
		        $d = explode('-', $_POST['data']);
		        $_a = array_shift($d);
		        $_m = array_shift($d);
		        $_d = date('d');

		        $dados['data'] = date('Y-m-d', strtotime( $_d.'-'.$_m.'-'.$_a ));
		        
		    }else{
		        $dados['data'] = date('Y-m-d');
		    }

		    $dados['dia'] = intval( date('d', strtotime($dados['data'])) );
		    $dados['mes'] = intval( date('m', strtotime($dados['data'])) );
		    $dados['ano'] = intval( date('Y', strtotime($dados['data'])) );
		    $dados['qtdDias'] = cal_days_in_month(CAL_GREGORIAN, $dados['mes'], $dados['ano']);
		    $dados['diaSemana'] = date( 'w', strtotime($dados['data']) );
		    $dados['mesNome'] = $dados['meses'][$dados['mes'] - 1];
		    $dados['agenda'] = $agenda->getAgenda($dados['data']);
		    $dados['servicos'] = $servicos->getServicos();
			$dados['prof_disponivel'] = $profissionais->getDisponibilidade($dados['diaSemana'], 0);
			$dados['servicos_agenda'] = array();
			$dados['diaHorario'] = 1;

		    //se for ano atual e mês for passado
		    if( $dados['ano'] == intval( date('Y') ) && $dados['mes'] < intval( date('m') ) ){
		    	$dados['diaHorario'] = $dados['qtdDias']; //recebe qtd de dias do mês pra não entrar no for
		    	
		    //se for ano atual e mês for o atual
		    }else if( $dados['ano'] == intval( date('Y') ) && $dados['mes'] == intval( date('m') )  ){
		    	$dados['diaHorario'] = intval(date('d')); //recebe dia atual para iniciar a contagem
		    	
		    }

		    if($dados['servicos']){

			    //busca horários disponíveis
			    for($i = $dados['diaHorario']; $i <= $dados['qtdDias']; $i++){
			    	//define data como Y-m-d
			    	$_diaHorario = $dados['ano'].'-'.$dados['mes'].'-'.str_pad($i, 2, '0', STR_PAD_LEFT);

			    	$dia_da_semana = date( 'w', strtotime($_diaHorario) ); //dia da semana
			    	$disponibilidade = $profissionais->getDisponibilidade($dia_da_semana, 0); //profissionais do dia

			    	//se haver profissional disponível
					if( $disponibilidade ){
						//cria dia no array
						$dados['servicos_agenda'][$i] = array();

						//percorre os profissionais disponíveis
						foreach($disponibilidade as $p_d){
							//busca os serviços do profissional
							$prof_servico = $profissionais->getServicos( $p_d['id_profissional'] );					
							//se haver serviço
							if( $prof_servico ){
								//percorre os serviços
								foreach($prof_servico as $p_s){
									//busca os horários disponíveis
									$prof_horarios = $agenda->getHorarios($p_d['id_profissional'], $p_s['id_servico'], $dados['data']);

									//se haver horário
									if($prof_horarios){
										//verifica se serviço já está disponibilizado na agenda
										if( !in_array( $p_s['id_servico'], $dados['servicos_agenda'][$i] ) ){
											//disponibiliza serviço
											array_push($dados['servicos_agenda'][$i], $p_s['id_servico']);
										}
									}
								}
							}
						}
					} 
			    }
			}

		    //echo '<pre>';
		    //print_r($dados['servicos_agenda']);
		    //echo '</pre>';

			$this->loadTemplate('agendar', $dados);

		}
	}