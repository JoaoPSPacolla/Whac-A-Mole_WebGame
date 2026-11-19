<?php
    $host = '127.0.0.1'; //ip loopback
    $username = 'root';
    $password = '';

    $dsn = "mysql:host=$host;charset=utf8mb4";

    try{
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sqlDatabase = "CREATE DATABASE IF NOT EXISTS `whack_a_mole` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sqlDatabase);

        $pdo->exec("USE `whack_a_mole`");
        
        $sqlUsuarios = "CREATE TABLE IF NOT EXISTS usuarios(
            id int unsigned auto_increment primary key,
            nome_completo varchar(150) not null,
            data_nascimento date not null,
            cpf char(14) not null unique,
            telefone char(15) not null,
            email varchar(150) not null unique,
            username varchar(50) not null unique,
            senha varchar(250) not null,
            criado_em datetime not null default current_timestamp
        )";

        $pdo->exec($sqlUsuarios);

        $sqlPartidas = "CREATE TABLE IF NOT EXISTS partidas(
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT UNSIGNED NOT NULL,
            linhas TINYINT UNSIGNED NOT NULL,
            colunas TINYINT UNSIGNED NOT NULL,
            modalidade ENUM('classica', 'explosiva') NOT NULL,
            pontuacao INT NOT NULL,
            toupeiras INT UNSIGNED NOT NULL,
            nivel SMALLINT UNSIGNED NOT NULL,
            status ENUM('finalizada', 'desistencia') NOT NULL,
            data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )";

        $pdo->exec($sqlPartidas);

        echo "Tabelas criadas com sucesso!";
    }
    catch(PDOException $erro){
        echo "Erro ao criar: " . $erro->getMessage();
    }
?>
