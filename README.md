# Kamishibai

Aplicação Laravel para registro e análise de adesão a pacotes de cuidados (Kamishibai). Gestores podem cadastrar categorias (por exemplo, *"ADESÃO MENSAL AO PACOTE DE CUIDADOS DE CATETER VESICAL DE DEMORA (CVD)"*) e os itens de verificação que compõem cada categoria. Usuários marcam diariamente se cada item foi cumprido (**C**) ou não (**NC**), possibilitando geração de relatórios mensais e anuais.

O projeto foi inicializado com Laravel 12 e contém migrations e models para:

* **categories** – grupos de verificação cadastrados pelo gestor;
* **items** – itens pertencentes às categorias;
* **records** – registros diários de conformidade;
* **users** – com coluna `role` para diferenciar `nurse` e `manager`.

## Configuração Rápida

1. Instale as dependências:
   ```bash
   composer install --no-interaction
   ```
2. Copie o arquivo de exemplo `.env` e gere uma chave de aplicação:
   ```bash
   cp src/.env.example src/.env
   php artisan key:generate
   ```
3. Crie o banco SQLite e execute as migrações com seeders:
   ```bash
   touch src/database/database.sqlite
   php artisan migrate:fresh --seed --force
   ```
4. Rode a suíte de testes:
   ```bash
   php artisan test --testsuite=Feature
   php artisan test --testsuite=Unit
   ```
