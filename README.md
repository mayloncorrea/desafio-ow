# Desafio OW Backend

Para rodar o projeto basta ter o Docker Desktop e o composer instalado na maquina e rodar o codigo:
- ```composer install --ignore-platform-reqs``` para instalar as dependências do framework;
- ```docker-compose up -d``` para rodar os containers usados na aplicação;
- ```docker-compose exec laravel php artisan migrate --seed``` para gerar as tabelas e dados de teste;

Então API estará disponibilizada na porta em localhost:80.

Os exemplos dos endpoits da API está disponibilizado no formato collection do POSTMAN.
