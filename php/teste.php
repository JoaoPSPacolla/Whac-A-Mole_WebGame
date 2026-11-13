<?php

require 'conexao.php';

$stmt = $conn->query("SELECT 1");
echo "Conexão bem-sucedida!";