<?php
	
	require 'config.php';

	spl_autoload_register(function($class){
		if(file_exists('controllers/'.$class.'.php')){
			require 'controllers/'.$class.'.php';
		}else if(file_exists('models/'.$class.'.php')){
			require 'models/'.$class.'.php';
		}else if(file_exists('core/'.$class.'.php')){
			require 'core/'.$class.'.php';
		}
	});

	$agenda = new Agenda();
	$profissionais = new Profissionais();
	$servicos = new Servicos();

	$dados = array();
	
	//busca horários
	if( isset($_POST['horarios']) && !empty($_POST['horarios']) ){

		$prof = addslashes($_POST['prof']);
		$serv = addslashes($_POST['serv']);
		$data = $_POST['data'];
		$diaSemana = date( 'w', strtotime($data) );

		//se data estiver vazia ou menor que a data atual
		if( empty($data) || (!empty($data) && strtotime($data) < strtotime(date('Y-m-d'))) ){
			$data = date('Y-m-d'); //recebe data atual
			$diaSemana = date( 'w', strtotime($data) );
		}

		//prepara dados de retorno
		$dados['keyProf'] = 0; //posição na lista 
		$dados['keyServ'] = 0; //posição na lista
		$dados['prof'] = ( $prof ) ? $prof : 1; //seleção pro input
		$dados['serv'] = ( $serv ) ? $serv : 1; //seleção pro input
		$dados['data'] = $data;				
		$dados['servicos'] = array(); //serviços do profissional		
		$dados['horarios'] = array(); //horários disponíveis
		$dados['profissionais'] = $profissionais->getDisponibilidade($diaSemana, 0); //profissionais disponíveis

		//se haver profissional disponível
		if( $dados['profissionais'] ){
			//se informou profissional
			if($prof){
				$profDisponivel = 0; //saber se prof informado está na lista
				$servDisponivel = 0; //saber se serv informado está na lista

				//busca profissional na lista
				foreach($dados['profissionais'] as $key => $p_d){
					if( $p_d['id_profissional'] == $prof ){
						$dados['keyProf'] = $key;
						$profDisponivel = $prof;
						$dados['servicos'] = $profissionais->getServicos($prof);

						//busca serviço na lista
						foreach($dados['servicos'] as $key => $s_d){
							if( $s_d['id_servico'] == $serv ){
								$dados['keyServ'] = $key;
								$servDisponivel = $serv;
								$dados['horarios'] = $agenda->getHorarios($prof, $serv, $data);
								break;
							}
						}

						if(!$servDisponivel){
							$_serv = $dados['servicos'][0]['id_servico'];
							$dados['horarios'] = $agenda->getHorarios($prof, $_serv, $data);
							$dados['serv'] = 1;
						}

						break;
					}
				}

				//se profissional não existir na lista, pega o primeiro da lista
				if(!$profDisponivel){
					$_prof = $dados['profissionais'][0]['id_profissional'];
					$dados['servicos'] = $profissionais->getServicos($_prof);
					
					$_serv = $dados['servicos'][0]['id_servico'];
					$dados['horarios'] = $agenda->getHorarios($_prof, $_serv, $data);
					$dados['prof'] = 1;
				}

			}else{//se não informar profissional, pega o primeiro da lista
				$_prof = $dados['profissionais'][0]['id_profissional'];
				$dados['servicos'] = $profissionais->getServicos($_prof); //busca serviços do profissional

				$_serv = $dados['servicos'][0]['id_servico'];
				$dados['horarios'] = $agenda->getHorarios($_prof, $_serv, $data); //busca horários
			}
		}
		
		echo json_encode($dados, JSON_FORCE_OBJECT);
	}

	//agendar
	if( isset($_POST['agendarServico']) && !empty($_POST['agendarServico']) ){

		$dados = $agenda->agendar($_POST);

		echo json_encode($dados, JSON_FORCE_OBJECT);
	}

	/*$h = $agenda->getHorarios(1, 1, '2022-12-14');

	echo '<pre>';
	print_r($h);*/

		

    