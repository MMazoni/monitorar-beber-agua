# API Beber Água

Um API criada para um aplicativo pessoal para monitorar quantas vezes o usuário bebeu água.

## Tecnologias Utilizadas

- PHP 7.4
- MySQL 5.7
- Apache 2
- Docker*
- Composer*

## Bibliotecas

- firebase/php-jwt
- coffeecode/router

## Instalação

Entre na pasta `api` do projeto e dê o seguinte comando.

```
composer install
composer dumpautoload
```

Volte para a raiz do projeto para subir os containers docker:

```
docker-composer up --build
```

Agora para acessar o servidor apache, digite a url:

```
localhost:8080
```