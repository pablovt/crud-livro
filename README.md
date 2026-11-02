# Cadastro de Livros

CRUD de livros, autores e assuntos, com relatório (tela e PDF) agrupado por autor.

## Stack

- PHP 8.4 / Symfony 7.4
- Twig + Bootstrap 5.3 (local)
- MariaDB 11.8 (Docker)
- Doctrine ORM (CRUD) e DBAL (view do relatório)
- Dompdf
- PHPUnit

## Como rodar

Docker com Compose e `make`:

```bash
make up
make test
```

App em http://localhost:8080.

| Comando | Ação |
|---|---|
| `make up` / `make down` | Sobe ou para o ambiente |
| `make logs` / `make shell` | Logs ou shell no container |
| `make test` | Roda a suíte |

No primeiro `make up`, o MariaDB aplica os scripts de `database/` (schema, view e seed).

## Banco

| Script | Conteúdo |
|---|---|
| `database/01_schema.sql` | Tabelas e índices |
| `database/02_view.sql` | View do relatório |
| `database/03_seed.sql` | Dados de demo |

## Testes

`make test` recria o banco de teste com os mesmos scripts de `database/` e executa o PHPUnit (`tests/Unit`, `tests/Integration`, `tests/Functional`).
