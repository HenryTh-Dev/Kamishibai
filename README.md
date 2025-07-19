# Kamishibai: Sistema de Gestão de Adesão a Pacotes de Cuidados

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white)

## 📝 Descrição do Projeto

O Kamishibai é uma aplicação web desenvolvida em Laravel que visa simplificar o processo de registro e monitoramento da adesão a pacotes de cuidados específicos. A plataforma permite que gestores criem e organizem categorias de cuidados, adicionem itens de verificação detalhados a cada categoria e, posteriormente, registrem diariamente a conformidade ou não-conformidade de cada item. O objetivo é fornecer uma ferramenta intuitiva para acompanhar a execução de protocolos e identificar áreas que necessitam de atenção.

Este projeto foi aprimorado para oferecer uma experiência de usuário mais fluida e visualmente agradável, com foco na usabilidade e na clareza das informações.

## ✨ Funcionalidades

### Funcionalidades Atuais (Implementadas e Aprimoradas)

- **Gestão de Categorias**: Crie, visualize, edite e exclua categorias de pacotes de cuidados. Cada categoria pode ter um nome e uma descrição detalhada.
- **Gestão de Itens**: Adicione itens de verificação específicos a cada categoria, com descrições claras e uma ordem de exibição personalizável.
- **Interface Intuitiva**: Navegação aprimorada com um menu lateral, layout responsivo e uso de ícones para facilitar a identificação das ações.
- **Feedback Visual**: Mensagens de sucesso e erro claras para todas as operações, além de modais de confirmação para ações críticas como exclusão.
- **Validação de Formulários**: Validação robusta no backend e feedback visual no frontend para garantir a integridade dos dados.

### Funcionalidades Futuras (Em Desenvolvimento ou Planejamento)

- **Registro Diário de Conformidade**: Sistema para marcar diariamente se cada item de verificação foi cumprido (C) ou não (NC).
- **Dashboard de Estatísticas**: Visão geral com gráficos e métricas de adesão aos pacotes de cuidados.
- **Relatórios Detalhados**: Geração de relatórios mensais e anuais para análise de tendências e desempenho.
- **Sistema de Autenticação e Autorização**: Gerenciamento de usuários com diferentes níveis de acesso (enfermeiros, gestores, etc.).
- **Exportação de Dados**: Opções para exportar relatórios em formatos como PDF ou Excel.

## 🚀 Tecnologias Utilizadas

O projeto Kamishibai é construído com as seguintes tecnologias:

- **Backend**: [Laravel 12](https://laravel.com/) (PHP Framework)
- **Frontend**: [Bootstrap 5.3](https://getbootstrap.com/) (Framework CSS)
- **Iconografia**: [Bootstrap Icons](https://icons.getbootstrap.com/)
- **Banco de Dados**: [SQLite](https://www.sqlite.org/)
- **Gerenciamento de Dependências**: [Composer](https://getcomposer.org/)

## ⚙️ Pré-requisitos

Para rodar a aplicação Kamishibai em seu ambiente local, você precisará ter instalado:

- **PHP** (versão 8.1 ou superior)
- **Composer**
- **SQLite** (com a extensão `php-sqlite3`)
- **Git**

## 📦 Instalação e Execução

Siga os passos abaixo para configurar e iniciar a aplicação:

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/HenryTh-Dev/Kamishibai
    cd Kamishibai/src
    ```

2.  **Instale as dependências do Composer:**
    ```bash
    composer install --no-interaction
    ```
    *Se houver erros de extensões PHP, instale-as (ex: `sudo apt install php8.2-intl php8.2-dom` para PHP 8.2 no Ubuntu) e tente novamente.*

3.  **Configure o arquivo de ambiente:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Crie o banco de dados SQLite e execute as migrações:**
    ```bash
    touch database/database.sqlite
    php artisan migrate
    ```

5.  **Inicie o servidor de desenvolvimento:**
    ```bash
    php artisan serve --host 0.0.0.0 --port 8000
    ```

6.  **Acesse a aplicação no navegador:**
    Abra seu navegador e vá para `http://localhost:8000`.

## 🤝 Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues para bugs ou sugestões, e enviar pull requests com melhorias.

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE.md).

---

Desenvolvido com 💙 por Manus AI.

