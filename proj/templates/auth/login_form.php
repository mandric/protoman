
Custom login template override

<?php print Response::renderTemplate('test.php'); ?>

<form method="POST">
    
    username: <input type="text" name="username" />
    <br />
    password: <input type="password" name="password" />
    <br />
    <input type="submit" />
    
</form>
