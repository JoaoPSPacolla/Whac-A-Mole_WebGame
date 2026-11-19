<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "whack_a_mole";

try{
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    error_log("Erro de conexão: " . $e->getMessage());
    echo "Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.";
}
?>