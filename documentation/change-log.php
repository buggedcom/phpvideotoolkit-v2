<?php
    
    require_once './includes/header.php';
    
?>

        <div class="span9">
          <h1>Change Log</h1>
          <p>
              
<?php echo nl2br(htmlspecialchars(file_get_contents('https://raw.githubusercontent.com/buggedcom/phpvideotoolkit-v2/master/CHANGELOG.md'), ENT_QUOTES, 'UTF-8')); ?>

          </p>
        </div><!--/span-->
        
<?php
    
    require_once './includes/footer.php';
    
