<?php 

$mot_de_passe_hache = password_hash("admin", PASSWORD_DEFAULT);
echo $mot_de_passe_hache;

?>