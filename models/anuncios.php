<?php
    class Anuncios extends Model{

        public function getQuantidade(){
            $sql = "select count(*) as c from anuncios";
            $sql = $this->db->query($sql);

            if($sql->rowCount() > 0){
                $qtd = $sql->fetch();
                
                return $qtd['c'];
            
            }else{
                return 0;
            }

        }
    }