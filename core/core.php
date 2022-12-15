<?php
    class Core{
        public function run(){
            $url = '/'; //inicia url com uma barra
            
            //verifica se enviou get
            if(isset($_GET['url'])){
                $url .= $_GET['url']; //concatena com url
            }

            $params = array();
            
            //se url não for vazia e não for uma barra
            if(!empty($url) && $url != '/' ){
                $url = explode('/', $url); //separa em array dividindo pela barra
                array_shift($url); //remove o primeiro indice do array, pq tá vazio

                $currentController = $url[0].'Controller'; //armazena primeiro link como controller
                array_shift($url); //remove ele do array
                
                //se enviou um segundo link
                if(isset($url[0]) && !empty($url[0])){
                    $currentAction = $url[0]; //armazena como action
                    array_shift($url); //remove action do array                    
                }else{
                    $currentAction = 'index'; //define action padrão
                }

                if(count($url) > 0){
                    $params = $url;
                }

            }else{
                $currentController = 'homeController'; //controller padrão
                $currentAction = 'index'; //action padrão
            }
            
            $c = new $currentController();
            call_user_func_array(array($c, $currentAction), $params);

        }
    }