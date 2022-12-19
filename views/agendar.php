<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/agendar.css">
<script defer type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/agendar.js"></script>

<!-- calendario -->
<table class="table table-bordered calendario">
  <thead>
    <!-- menu -->
    <tr>
      <th colspan="7">
        <div class="logo" title="Ir pra Home">
          <a href="<?php echo BASE_URL; ?>"><img src="<?php echo BASE_URL; ?>assets/img/logo.jpg"></a>
        </div>
      </th>
    </tr>
    <!-- mês -->
    <tr>
      <th colspan="7">    
        <!-- mês anterior -->   
        <form title="Mês Anterior" method="POST" action="<?php echo BASE_URL; ?>agendar">
          <input type="hidden" name="data" value="<?php echo date('Y-m-d', strtotime('-1 months', strtotime(date($data)))); ?>" />
          <input type="submit" value="<" class="btn" style="color: #fff;" />
        </form> 
        <!-- esolher mês -->
        <form method="POST" action="<?php echo BASE_URL; ?>agendar" id="formData">
          <input type="month" name="data" id="inputData" value="<?php echo $ano.'-'.str_pad($mes , 2 , '0' , STR_PAD_LEFT); ?>">
        </form>
        <!-- mês seguinte -->
        <form title="Mês Seguinte" method="POST" action="<?php echo BASE_URL; ?>agendar">
          <input type="hidden" name="data" value="<?php echo date('Y-m-d', strtotime('+1 months', strtotime(date($data)))); ?>" />
          <input type="submit" value=">" class="btn" style="color: #fff;" />
        </form>
      </th>
    </tr>
  </thead>
  <tbody>
    <!-- semanas -->
    <tr class="semanas">
      <?php 
        foreach($semanas as $item){
          echo '<th>'.$item.'</th>';
        }
      ?>
    </tr>
    <!-- linhas das semanas e dias -->
    <?php
      $diaCal = 0; //dia do calendário
      $diaMes = 1; //dias do mês
      $semanaInicioCal =  date('w', strtotime( $ano.'/'.$mes.'/01' )); //dia inicial da semana
      
      for($l = 1; $l <= 6; $l++){

        //linha semana
        echo '<tr class="dias">'; 

        //dias
        for($c = 1; $c <= 7; $c++){
          if( $semanaInicioCal <= $diaCal && $diaMes <= $qtdDias ){
            $diaAtual = '';
            $diaPassado = '';
            $ocupado = '';
            $detalhe = '';
            $dataDia = $ano.'-'.str_pad($mes, 2, '0', STR_PAD_LEFT).'-'.str_pad($diaMes, 2, '0', STR_PAD_LEFT);

            //define dia atual
            if( $diaMes == $dia && $mes == intval(date('m')) ){
              $diaAtual = ' diaAtual';
            }

            //define dia já passado
            if( ($diaMes < $dia && $mes <= intval(date('m')) && $ano <= intval(date('Y'))) || 
              ($mes < intval(date('m')) && $ano <= intval(date('Y'))) ){
              
              $diaPassado = ' diaPassado';
            }

            //define dias disponíveis
            if( ($diaMes >= $dia && $mes >= intval(date('m')) && $ano >= intval(date('Y'))) || 
              ($ano > intval(date('Y'))) ){
              //se tiver horário no dia
              if( isset($servicos_agenda[$diaMes]) ){
                $modal = 'data-toggle="modal" data-target="#modalAgendar"';
                $detalhe = '<span>Escolha um Serviço</span>';

                foreach($servicos_agenda[$diaMes] as $s_a){
                  foreach($servicos as $s){
                    if( $s_a == $s['id'] ){
                      $detalhe = $detalhe.'<span '.$modal.' class="spanServico" data-servico="'.$s['id'].'">'.$s['descricao'].'</span>';
                    }
                  }
                }

              }else{
                $ocupado = ' ocupado';
                $detalhe = '<span>Sem Horário!</span>';
              }              
            }

            echo '<td data-dataDia="'.$dataDia.'" class="dia'.$diaAtual.$diaPassado.$ocupado.'">'.
              '<div>'.$diaMes.'</div>'.
              '<div>'.$detalhe.'</div>'.
            '</td>';
            
            $diaMes++;
          }else{
            echo '<td></td>';
          }
          
          $diaCal++;

        } //fim for

        echo '</tr>';
      
      }
    ?>
  </tbody>
</table>

<!-- modal -->
<div class="modal fade" id="modalAgendar" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="Agendar" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
      	<!-- formulário de agendamento -->
      	<form method="POST" id="formAgendar">
          <div class="modalGrupo">
            <div class="form-group">  
              <label for="selectProfissional"><b>Profissional:</b></label>
              <div class="grupoItem">
                <select name="agendar[profissional]" class="form-control" id="selectProfissional">
                  <!-- processado pelo JS -->
                </select>
                <div class="grupoItemInt profHorario" id="profHorario">00:00:00</div>
              </div>
            </div>
          </div>
          <div class="modalGrupo">
            <div class="form-group">  
              <label for="selectServico"><b>Serviço:</b></label>
              <select name="agendar[servico]" class="form-control" id="selectServico">
                <!-- processado pelo JS -->
              </select>
            </div>
            <div class="form-group">  
              <label for="inputData"><b>Data:</b></label>
              <input type="date" name="agendar[data]" class="form-control" id="inputData">
            </div>
            <div class="form-group">  
              <label for="selectHora"><b>Hora:</b></label>
              <select name="agendar[hora]" class="form-control" id="selectHora">
                <!-- processado pelo JS -->
              </select>
      		  </div>
          </div>
      		<div class="form-group">
      			<label for="inputNome"><b>Nome:</b></label>
      			<input type="text" name="agendar[nome]" class="form-control" id="inputNome" />
      		</div>
      		<div class="form-group">
      			<label for="inputEmail"><b>E-mail:</b></label>
      			<input type="email" name="agendar[email]" class="form-control" id="inputEmail" />
      		</div>
      		<div class="modalGrupo">
            <div class="form-group">
        			<label for="inputCpf"><b>CPF:</b></label>
        			<input type="text" name="agendar[cpf]" class="form-control somenteNumeros" id="inputCpf" data-mask="cpf" data-length='11' placeholder="___.___.___-__" />
            </div>
            <div class="form-group"> 
              <label for="inputCelular"><b>Celular:</b></label>
              <input type="text" name="agendar[celular]" class="form-control somenteNumeros" id="inputCelular" data-mask="celular" data-length='11' placeholder="(  ) _ ____-____" />
      		  </div>
          </div>
      	</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success btnSalvar" data-form="#formAgendar">Salvar</button>
        <button type="button" class="btn btn-danger btnCancelarModal" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

