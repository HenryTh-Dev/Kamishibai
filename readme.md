**Kamishibai – Sistema de Registro de Atividades UTI**

**Índice**

- [Sobre](#sobre)
- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Instalação e Setup](#instalação-e-setup)
- [Uso](#uso)
  - [Admin](#admin)
  - [Enfermeira](#enfermeira)
- [Configurações](#configurações)
- [Banco de Dados](#banco-de-dados)
- [Estrutura de Rotas/API](#estrutura-de-rotasapi)
- [Contribuição](#contribuição)
- [Licença](#licença)

---

## Sobre

O **Kamishibai** é um sistema web desenvolvido para o registro diário de atividades em Unidades de Terapia Intensiva (UTI). Ele oferece perfis diferenciados:

- **Administrador**: cadastra categorias, itens e usuários. Acompanha estatísticas e dashboards de progresso mensal.
- **Enfermeira**: registra o status das tarefas (Concluído, Não Concluído, N/A) por data e as visualiza de forma intuitiva.

O objetivo é otimizar a gestão de checklists e auditorias diárias, garantindo rastreabilidade e insights de desempenho.

---

## Funcionalidades

- CRUD de **usuários**, **categorias** e **itens** (Admin).
- Registro de atividades por enfermeira:
  - Status: `C` (Concluído), `NC` (Não Concluído), `NA` (N/A).
  - Armazenamento de data e auditoria (e-mail da enfermeira em `notes`).
- Dashboard com gráficos (Chart.js):
  - Gráfico de pizza geral (C, NC, NA).
  - Gráfico de barras por categoria.
  - Estatísticas de taxas de conclusão mensais.
- API REST (`api.php`) retornando JSON:
  - `dashboard_data`, `category_progress`, `nurse_activities`, `get_activities_by_date`, `record_activity`.
- Interface responsiva em Bootstrap 5.

---

## Tecnologias Utilizadas

- **PHP 8.x** (back‑end, sem framework).
- **SQLite** (banco de dados leve em arquivo).
- **PDO** (acesso seguro ao DB).
- **Bootstrap 5** (layout e componentes).
- **Chart.js** (gráficos interativos).
- **JavaScript (Fetch API)** para consumo da API.

---

## Estrutura do Projeto

```
kamishibai/
├── api.php               # API endpoints REST
├── config.php            # Conexão e criação de tabelas (users, categories, items, activity_records)
├── database.sqlite       # Banco de dados (versão em arquivo)
├── dashboard.php         # Painel de admin (dashboard)
├── categories.php        # CRUD categorias
├── items.php             # CRUD itens
├── users.php             # CRUD usuários
├── login.php             # Tela de login (admin e nurse)
├── logout.php            # Logout
├── nurse/
│   └── index.php         # Interface da enfermeira
└── assets/
    ├── css/              # Estilos customizados
    └── js/               # Scripts front‑end
```

---

## Instalação e Setup

1. Clone este repositório:
   ```bash
   git clone https://github.com/seu-usuario/kamishibai.git
   cd kamishibai
   ```
2. Configure o PHP e o servidor local (XAMPP, MAMP ou similar).
3. Garanta permissão de escrita no arquivo `database.sqlite`:
   ```bash
   touch database.sqlite
   chmod 666 database.sqlite
   ```
4. Acesse via navegador: `http://localhost/kamishibai/login.php`.
5. O `config.php` criará as tabelas automaticamente na primeira execução.

---

## Uso

### Admin

- Faça login com o usuário padrão:
  - **E-mail**: `admin@kamishibai.com`
  - **Senha**: `admin123`
- Cadastre novos usuários (papel = `nurse` para enfermeiras).
- Defina categorias e itens de checklist.
- Acesse o **Dashboard** para visualizar relatórios e gráficos.

### Enfermeira

- Faça login com um usuário com `role = 'nurse'`.
- Selecione a data desejada e registre o status de cada item.
- O sistema salvará também o e‑mail da enfermeira em `notes`.
- Veja o resumo do dia com contadores e taxa de conclusão.

---

## Configurações

- **Sessão**: controlada em `config.php` com `session_start()` protegido.
- **CORS e JSON**: em `api.php` definimos headers para JSON e métodos permitidos.
- **Erros**: configurados para logar em `php-error.log` e não vazar HTML.

---

## Banco de Dados

Tabelas principais:

```sql
-- users
describe users;
-- categories
describe categories;
-- items
describe items;
-- activity_records
describe activity_records;
```

A tabela `activity_records` inclui:

- `item_id` (PK composta)
- `record_date`
- `status` CHECK(`C`,`NC`,`NA`)
- `notes` (e‑mail da enfermeira)
- `created_at`

---

## Estrutura de Rotas/API

| Endpoint                                 | Método | Descrição                             |
| ---------------------------------------- | ------ | ------------------------------------- |
| `?action=nurse_activities`               | GET    | Lista categorias e itens              |
| `?action=get_activities_by_date&date=`   | GET    | Registros de uma data                 |
| `?action=record_activity`                | POST   | Insere ou atualiza um registro (JSON) |
| `?action=dashboard_data&month=`          | GET    | Dados para dashboard mensal           |
| `?action=category_progress&category_id=` | GET    | Progresso detalhado de uma categoria  |

---

## Contribuição

1. Faça um fork deste repositório.
2. Crie uma branch com a sua feature: `git checkout -b feature/nova-funcionalidade`.
3. Commit suas alterações: `git commit -m 'Adiciona nova funcionalidade'`.
4. Envie para o repositório remoto: `git push origin feature/nova-funcionalidade`.
5. Abra um Pull Request para revisão.

---

## Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

