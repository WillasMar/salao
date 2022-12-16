$(function(){

	const AJAXURL = 'ajax.php'
	const DATE = new Date()
	const ANO = DATE.getFullYear()
	const MES = ('00' + DATE.getMonth()).slice(-2)
	const DIA = ('00' + DATE.getDate()).slice(-2)	
	const DATA = ANO+'-'+MES+'-'+DIA
	const BASE_URL = 'http://localhost/salao/'
	const IMG_ERRO = BASE_URL + 'assets/img/alerta/erro.png'
	const IMG_AVISO = BASE_URL + 'assets/img/alerta/aviso.png'
	const IMG_SUCESSO = BASE_URL + 'assets/img/alerta/certo.png'
	
	//ajax
	function buscaHorarios(prof, serv, data){
		let resultado = false;

		$.ajax({
			type:'POST',
			url: AJAXURL,
			data: { horarios:true, prof:prof, serv:serv, data:data },
			dataType: "json",
			async: false,
			//contentType: false,
			//cache: false,
			//processData: false,
			success: function(retorno) {
				resultado = retorno								
			}		
		}).fail(function(jqXHR, textStatus ) { //falha
			resultado = 'Falha no ajax: função buscaHorarios()'	
		})

		return resultado
	}

	//ajax
	function agendar(dataForm){
		let resultado = false

		$.ajax({
			type:'POST',
			url: AJAXURL,
			data: dataForm,
			dataType: "json",
			async: false,
			contentType: false,
			cache: false,
			processData: false,
			success: function(retorno) {

				resultado = retorno						
			
			}		
		}).fail(function(jqXHR, textStatus ) { //falha
			resultado = 'Falha no ajax: função agendar()'
		})

		return resultado
	}

	function preencheDados(horarios){
		console.log(horarios)

		let optionH = '' //horários
		let optionP = '' //profissionais
		let optionS = '' //serviços
		let dtAgenda = horarios.data
		
		//descrição do expediente do profissional
		let spanHora = horarios.profissionais[ horarios.keyProf ]['hora'].split(':')[0] + 'h às ' +
			horarios.profissionais[ horarios.keyProf ]['hora_final'].split(':')[0] + 'h'

		if(horarios.profissionais){
			for( let item in horarios.profissionais ){
				let id = horarios.profissionais[item]['id_profissional']
				let nome = horarios.profissionais[item]['nome']
				let idIndisponivel = 0

				if(horarios.profIndisponiveis){
					for(let item in horarios.profIndisponiveis){
						if( id == horarios.profIndisponiveis[item]['id_profissional'] ){
							idIndisponivel = id
							break
						}
					}
				}

				if(idIndisponivel){
					optionP = optionP + '<option value="'+id+'">*'+nome+'</option>'
				}else{
					optionP = optionP + '<option value="'+id+'"><span>'+nome+'</span></option>'
				}
								
			}	
		}

		if(horarios.servicos){
			for( let item in horarios.servicos ){
				let id = horarios.servicos[item]['id_servico'] 
				let descricao = horarios.servicos[item]['descricao']
				optionS = optionS + '<option value="'+id+'">'+descricao+'</option>'				
			}	
		}
		
		if(horarios.horarios){
			for( let item in horarios.horarios ){
				optionH = optionH + '<option value="'+horarios.horarios[item]+'">'+horarios.horarios[item]+'</option>'				
			}	
		}

		if(horarios.data){
			$('#formAgendar').find('#inputData').val(horarios.data)
		}		

		$('#formAgendar').find('#selectHora').html(optionH)
		$('#formAgendar').find('#selectProfissional').html(optionP)
		$('#formAgendar').find('#selectProfissional').val(horarios.prof)
		$('#formAgendar').find('#selectServico').html(optionS)
		$('#profHorario').html(spanHora)

		$('#formAgendar input').css('border-color', '#ced4da')
		$('#formAgendar select').css('border-color', '#ced4da')		

		//se o option selecionado possuir um * indicando indisponibilidade
		if( $('#formAgendar').find('#selectProfissional').find(':selected').text().indexOf('*') >= 0 ){
			$('#profHorario').addClass('profHorarioOff')
		}else{
			$('#profHorario').removeClass('profHorarioOff')
		}

	}

	//ao abrir modal agendar
	$('#modalAgendar').on('shown.bs.modal', function(){
		$(this).find('#nomeHora').focus()
	})

	//ao fechar modal agendar
	$('#modalAgendar').on('hide.bs.modal', function(){
		location.reload()
	})

	//ao clicar no serviço do calendário
	$('.calendario .dia .spanServico').click(function(){
		let prof = 0
		let servico = $(this).attr('data-servico')
		let data = $(this).closest('.dia').attr('data-datadia')
		
		let horarios = buscaHorarios(prof, servico, data)
		
		$('#modalAgendar').on('shown.bs.modal', function(){
			$(this).find('#inputData').val(data)
			$(this).find('#selectServico').val(servico)

			preencheDados(horarios)
			
			$(this).find('#inputNome').focus()
			$(this).find('#selectServico').val(servico)
		})
	})

	//ao mudar o profissional do modal agendar
	$('#modalAgendar #selectProfissional').change(function(e){
		let prof = $(this).val()
		let data = $(this).closest('#modalAgendar').find('#inputData').val()
		let servico = $(this).closest('#modalAgendar').find('#selectServico').val()

		//se data não estiver informada
		if(!data){
			data = DATA
			$(this).closest('#modalAgendar').find('#inputData').val( DATA )
		}

		let horarios = buscaHorarios(prof, servico, data)
		
		preencheDados(horarios)

		//pega serviços preenchidos
		let servicoObj = $(this).closest('#modalAgendar').find('#selectServico option')
		
		$(this).val(prof)
		$(this).closest('#modalAgendar').find('#selectHora').focus()

		//percorre serviços a procura do que estava selecionado antes
		for(let item of servicoObj){
			if( servico == $(item).attr('value') ){
				$(this).closest('#modalAgendar').find('#selectServico').val(servico)
			}
		}
	})	

	//ao mudar a data do modal agendar
	$('#modalAgendar #inputData').keyup(function(e){
		let key = e.key

		//se teclou enter
		if(key == 'Enter'){
			let data = $(this).val()
			let prof = $(this).closest('#modalAgendar').find('#selectProfissional').val()
			let servico = $(this).closest('#modalAgendar').find('#selectServico').val()

			//se data não estiver informada
			if(!data){
				data = DATA
				$(this).val( DATA )
			}

			let horarios = buscaHorarios(prof, servico, data)

			preencheDados(horarios)

			$(this).closest('#modalAgendar').find('#selectHora').focus()
			$(this).closest('#modalAgendar').find('#selectServico').val(servico)
		}
	})	

	//ao clicar no botão salvar
	$('.btnSalvar').click(function(){
		let form = $(this).attr('data-form')

		$(form).submit()
	})

	//submit do form
	$('#formAgendar').submit(function(e){
		e.preventDefault()

		let form = $(this)
		let prof = $(this).find('#selectProfissional')
		let servico = $(this).find('#selectServico')
		let data = $(this).find('#inputData')
		let hora = $(this).find('#selectHora')
		let nome = $(this).find('#inputNome')
		let email = $(this).find('#inputEmail')
		let cpf = $(this).find('#inputCpf')
		let celular = $(this).find('#inputCelular')

		//verifica campos obrigatórios
		if( !$(prof).val() || !$(servico).val() || !$(data).val() || !$(hora).val() || !$(nome).val() ){
			if( !$(prof).val() ){
				$(prof).css('border-color', 'red')
				$(prof).focus()		
			}

			if( !$(servico).val() ){
				$(servico).css('border-color', 'red')
				$(servico).focus()		
			}

			if( !$(data).val() ){
				$(data).css('border-color', 'red')
				$(data).focus()		
			}

			if( !$(hora).val() ){
				$(hora).css('border-color', 'red')
				$(hora).focus()
			}

			if( !$(nome).val() ){
				$(nome).css('border-color', 'red')
				$(nome).focus()	
			}	

		//caso campos estejam prenchidos
		}else{ 
			let dataForm = new FormData(this)
			dataForm.append('agendarServico', true)

			let retorno = agendar(dataForm)
			
			$('.alert').show('fast')
			$('.alert .msg').html( retorno.msg )
			$('.alert').removeClass('alert-success')
			$('.alert').removeClass('alert-warning')
			$('.alert').removeClass('alert-danger')
			
			if( !retorno.result || retorno.result == 'false' ){
				$('.alert').addClass('alert-danger')
				$('.alert img').attr('src', IMG_ERRO)
				
				let horarios = buscaHorarios( $(prof).val(), $(servico).val(), $(data).val() )
				
				preencheDados(horarios)

			}else if( retorno.result == 'aviso' ){
				$('.alert').addClass('alert-warning')
				$('.alert img').attr('src', IMG_AVISO)

			}else{
				$('.alert').addClass('alert-success')
				$('.alert img').attr('src', IMG_SUCESSO)

				$(nome).val('')
				$(email).val('')
				$(cpf).val('')
				$(celular).val('')			

				let horarios = buscaHorarios( $(prof).val(), $(servico).val(), $(data).val() )
				
				preencheDados(horarios)

				$(nome).focus()
			}
		}
	})

	//ao digitar no campo
	$('input').keyup(function(){
		$(this).css('border-color', '#ced4da')
	})

	//clique no botão de fechar alerta
	$('.alert .btnFecharAlerta').click(function(){
		$(this).closest('.alert').hide('fast')
		$('#formAgendar').find('#inputNome').focus()
	})

	//ao mudar data no calendário
	$('#inputData').change(function(){
		$(this).parent().submit()
	})

})