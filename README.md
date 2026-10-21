# Presenza

> **Presenças reais, certificados à prova.** Gestão interna de treinamentos da Usina Lins com **validação de presença por reconhecimento facial** — e **verificação pública de certificados** que qualquer pessoa confere no site, sem login, pelo QR.

Hackathon FATEC & ETEC — Usina Lins · Problema 1 (gestão de treinamentos + certificação com presença).

---

## O que resolve

| Problema do caso real | Solução Presenza |
|---|---|
| Listas de presença fáceis de falsificar | Presença **só pelo reconhecimento facial na janela da aula** (RF03/RF14); homologação manual é exceção rastreada: senha do **próprio colaborador** + motivo do catálogo (RF15/RF27) |
| Certificados "de geladeira" sem como conferir | **Verificação pública** por código/QR/link com status VÁLIDO/EXPIRADO/NÃO ENCONTRADO (RF21/RF22) + PDF público |
| Faltas e cancelamentos por conversa de corredor | Fluxo por **tickets** resolvidos por RH/Líder com efeito real (devolver vaga, promover fila, justificar falta) (RF10–RF13) |
| Fila de espera virada por Excel | **Fila automática com promoção em cascata** quando alguém cancela (RF09) |
| Elegibilidade a certificado no "achismo" | Emissão só para quem compareceu a **todas** as aulas (RF18) — cálculo automático |
| Certificado errado não pode ser "editado" | **Imutável**: reemissão cria nova versão; o código antigo para de verificar (RF20) |

## Stack

- **Laravel 13 (PHP 8.4)** + Blade + Alpine.js — sem build de Node (`css` de tokens + Alpine vendido)
- **PostgreSQL 16 + pgvector** — embeddings de biometria facial (`vector(128)`), fachada simulável para demo (RNF06)
- **dompdf** (certificados/relatórios) · **php-qrcode** (QR público) · **Mailpit** (e-mails dev)
- **Testes**: PHPUnit feature no próprio Postgres — `OK: 93 testes, 305 asserções`
- **28/28 RFs implementados** (RF01–RF28), com auditoria em cada ação sensível (RNF01)

## Como rodar

### Desenvolvimento (serviços + app em contêiner one-off)

```sh
docker compose -f docker-compose.dev.yaml up -d          # postgres:5433 (host) + mailpit:8025
docker run --rm -v .:/app -w /app presenza/php php artisan migrate:fresh --seed
docker run --rm -v .:/app -w /app -p 8000:8000 presenza/php php artisan serve --host=0.0.0.0   # http://localhost:8000
```

### Homologação/demonstração (topologia AT02)

```sh
docker compose up -d --build              # sem -f: o padrão É o compose de homologação (app em http://localhost:18000)
```

**Seed automático (reset total — aprovado pela equipe):** a cada (re)criação do contêiner `app` o banco é repovado com `migrate:fresh --seed` (SEED01–04, ~15 s) antes do `serve`. Logo após `up`, aguarde ~20 s para o primeiro `200`. `docker compose restart` **apaga e resemeia** — não reinicie o contêiner durante a apresentação se quiser conservar dados criados ao vivo.

`docker-compose.yaml` (padrão do compose) = topologia completa de homologação — todos os serviços declarados no próprio arquivo, autossuficiente p/ deploy no Coolify (que ignora `include:` e falharia em ver `mailpit`/`postgres`); `docker-compose.dev.yaml` = apenas `postgres` + `mailpit`.

## Credenciais da demo (seed)

Todas as senhas `Password@123` · e-mails `nome.completo@usinalins.local`:

| Papel | Login | Cenário |
|---|---|---|
| RH | `isabela.cristina.fonseca` | tickets, certificados, configurações, reservas, treinamentos, usuários, matriz de conformidade |
| Instrutor externo | `klaus.werner.steiner` | chamada facial/homologação |
| Líder | `heloisa.fernandes.peixoto` | reservas do time, justificativas (RF13), senha dos liderados |
| Colaborador na fila | `otavio.martins.gomes` | promoção de fila (RF09), abrir ticket (RF10) |

Verificação pública (sem login): `http://localhost:18000/verificar` — códigos de demonstração: `PZ-2025-000101` (VÁLIDO), `PZ-2024-000087` (EXPIRADO).

## Mapa de requisitos

Aprovação RF01–RF28/RNF01–06 em [`docs/requisitos-p1.md`](docs/requisitos-p1.md);
decision log técnico em [`docs/stack.md`](docs/stack.md); identidade visual em [`docs/design-p1.md`](docs/design-p1.md);
transcrição das perguntas de requisitos em [`docs/questionario-requisitos-p1.md`](docs/questionario-requisitos-p1.md);
evidência cronológica IA (24+ sessões) em [`journey-log.md`](journey-log.md).

## Testes

```sh
docker run --rm -v .:/app -w /app presenza/php vendor/bin/phpunit   # Postgres real (banco presenza_test, pgvector)
```

## Equipe

Dimas Matheus Gomes Picinato · Sara Silva Marsola · João Marcel dos Santos Silva

> **AT03/AT01**: todo o código foi escrito por IA (proveito pelo log); decisões técnicas sempre com a equipe por votação registrada.
