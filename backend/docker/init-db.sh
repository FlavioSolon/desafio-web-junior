#!/bin/bash
# Aguarda o SQL Server estar pronto e cria o banco de dados
echo "Aguardando SQL Server ficar pronto..."

/opt/mssql-tools18/bin/sqlcmd -S db -U sa -P "SenhaFametro123!" -No -Q "
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'laravel_db')
BEGIN
    CREATE DATABASE laravel_db;
    PRINT 'Banco laravel_db criado com sucesso!';
END
ELSE
BEGIN
    PRINT 'Banco laravel_db já existe.';
END
"
