<html>
    <head>
        <title>Salão</title>
        <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css">
    </head>
    <body>

        <!-- alert-success | alert-danger | alert-warning -->    
        <div class="alert" role="alert">
            <div class="btnFecharAlerta">x</div>            
            <p><img src=""></p>
            <div class="msg"></div>  
        </div>

        <script defer type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery-3.3.1.min.js"></script>
        <script defer type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
        <script defer type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
        
        <?php $this->loadViewInTemplate($viewName, $viewData); ?>
        
    </body>
</html>