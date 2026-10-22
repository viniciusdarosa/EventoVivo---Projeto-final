-- ============================================================
-- EventoVivo — 00_criar_banco.sql
-- Cria o banco de dados (se ainda não existir) e o seleciona.
-- Execute este arquivo ANTES do 01_esquema.sql.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `eventovivo`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `eventovivo`;
