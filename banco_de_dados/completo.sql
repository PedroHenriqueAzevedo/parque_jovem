CREATE DATABASE parque_joven;

USE parque_joven;

-- Tabela para banners
CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    imagem VARCHAR(255) NOT NULL
);

-- Tabela para administradores
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);