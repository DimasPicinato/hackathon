# Stack do Presenza — decisão e configuração técnica

**Decidido em 20/10/2026, Q35 (questionário interativo, conforme AT01)** — comparação completa registrada no `journey-log.md` (sessão 15).

## 1. Decisão

**Laravel 11 / PHP 8.3 / Blade + Alpine.js** · **PostgreSQL** (fixado pelo time) com **extensão pgvector** · **Docker Compose** conforme AT02.

**Por que Laravel 11 neste contexto** (resumo do comparativo vs. Django 5, Rails 8, AdonisJS 7, NestJS+React):
- **Baterias cobrem a maioria dos RFs**: autenticação + reset por e-mail (RF01/RF28), e-mail SMTP via `.env` (RF23), agendador nativo para lembretes (RF25), seeders (SEED01–03);
- **dompdf** gera certificado/relatórios PDF em PHP puro → imagem Docker leve, sem bins de sistema;
- Corpus de IA de PHP/Laravel é grande e atual → maior throughput na janela curta do MVP;
- Topologia casa 1:1 com a AT02 (app buildada em compose homolog que estende o de serviços).

**Sem Node/npm neste MVP**: assets (CSS com tokens de `docs/design-p1.md` + Alpine vendored em `public/`) **não exigem build** — menos uma peça para falhar na demo. Inertia/React fica como evolução pós-hackathon (não escopo).

## 2. Serviços (Docker)

| Serviço | Imagem base | Papel |
|---|---|---|
| `postgres` | `pgvector/pgvector:pg16` (PG16 + pgvector) | Banco formal do projeto (RF/RNF02) |
| `mailpit` | `axllent/mailpit` | Captura todos os e-mails no dev/teste (web UI p/ conferir RF01/RF23–25) |
| `app` | PHP 8.3 (FPM/artisan serve) | Aplicação Laravel — só no compose de **homologação** |
| `scheduler` | PHP 8.3 CLI | `schedule:work` para lembretes 24h/aviso de fila (RF25) — só na homologação |

- **`docker-compose.yml`** (dev/testes): `postgres` + `mailpit` — o `.env` de desenvolvimento aponta para `localhost`.
- **`docker-compose.homolog.yml`**: estende o de serviços; adiciona `app` + `scheduler` (build) **com variáveis de ambiente fixas no compose** (ex.: `DB_HOST=postgres`, `MAIL_HOST=mailpit`) para compatibilidade de rede, conforme AT02.
- Biometria: similaridade calculada com o operador do pgvector (distância cosseno via SQL) — sem serviço externo de IA na demo (fachada/simulador definidos em implementação, com aprovação AT01).

## 3. Dependências (propostas — aprovação triste no scaffold)

| Pacote | Papel (RF) |
|---|---|
| `laravel/breeze` (Blade) | Login/senha, tela de aceite do termo, reset por e-mail (RF01, RF04, RF28) |
| `barryvdh/laravel-dompdf` | Certificado em PDF e exportações (RF21, RF28) |
| `chillerlan/php-qrcode` | QR do certificado → verificação pública (RF21) |
| `barryvdh/laravel-debugbar` | Só dev — inspeção rápida |
| (Vendored) Alpine.js | Interatividade leve nas telas sem pipeline de build |

Extra: CSV de exportação/importação via PHP nativo (`fputcsv/fgetcsv`) — sem pacote adicional (RF28, SEED01).

## 4. Diretórios previstos

```
app/            # código Laravel (API web com Blade)
docker/         # Dockerfile(s) PHP
public/         # css/js/comprovantes estáticos
resources/views # telas Blade por plano de mockups
database/migrations + seeders (incl. leituras CSV de seed/)
docker-compose.yml            # serviços (dev)
docker-compose.homolog.yml    # estende serviços + app/scheduler buildados
.env                          # dev: localhost | homolog usa env fixo do compose
```
