# 🚀 Deploy no Coolify — runbook dos problemas de Docker (Presenza)

História dos problemas que apareceram ao subir a pilha de homologação (AT02: `app`, `scheduler`, `postgres`, `mailpit`) na VPS gerida pelo Coolify, com o domínio `http://hackathon.dpicinato.com` na frente. Cada item: **sintoma → causa raiz → ação** (com o commit que fechou). Serve como runbook: **uma nova VPS ou um deploy do zero reaproveita este guia**, porque os primeiros obstáculos são padrões de plataforma (Coolify), e não erros do projeto.

## Como ficou o deploy (estado atual)

- `docker-compose.yaml` **autossuficiente** (sem `include:`), com `APP_KEY` / `APP_URL` fixos como fallback e **zero variáveis a definir** no Coolify;
- Código + `vendor` **embutidos na imagem** (`docker/php/Dockerfile` + `.dockerignore`); binds de fonte eliminados;
- Porta host default `18000` para o `app` (o contêiner segue `8000`, alvo do proxy); `APP_PORT` é a única válvula de resgate;
- Acessos: `http://hackathon.dpicinato.com` via proxy → `app:8000` · debug `IP:18000` (liberar no firewall, se precisar) · Mailpit UI `:8025` · Postgres `:5433` (recomendação: fechar para fora no firewall/security group);
- Tempo de 1º start: build ~2–3 min (instalação do vendor) + seed ~20 s depois de `app Started`.

## 1) `service "app" depends on undefined service "mailpit": invalid compose project`

- **Sintoma:** deploy falha imediatamente na validação do `docker compose build` (nenhum contêiner criado); o log também mostrava que o Coolify só enxergava `app` e `scheduler` no compose.
- **Causa:** o Coolify **ignora a diretiva `include:`** de compose e processa apenas os serviços do arquivo topo (issues [coollabsio/coolify#5921](https://github.com/coollabsio/coolify/issues/5921) e referencial [#2859](https://github.com/coollabsio/coolify/issues/2859); o PR #8850, que daria suporte a `include`, está aberto sem merge). Nosso compose declarava `postgres` e `mailpit` no arquivo de dev e os puxava via `include`.
- **Ação (commit `4514fbe`):** `postgres` + `mailpit` (+ volume) declarados no próprio `docker-compose.yaml` (cópia literal do arquivo de dev); `include:` removido. `docker-compose.dev.yaml` segue intacto para o fluxo de dev "serviços + app one-off".

## 2) `Bind for 0.0.0.0:8000 failed: port is already allocated`

- **Sintoma:** build e pull OK, contêineres criam, `postgres` fica healthy — e o `up -d` aborta ao iniciar o `app`.
- **Causa:** **a 8000 do host é o painel próprio do Coolify** na VPS. O `ports: "8000:8000"` do compose obrigava a publicar a porta — e este conflito do host é independente do subdomínio (o tráfego real passa pelo proxy, não pela porta do host).
- **Ação (commit `3ecf784`):** host port default movida para **18000** (`${APP_PORT:-18000}:8000`); porta do **contêiner** segue **8000** (é ela que o proxy do Coolify acessa). README alinhado (`localhost:18000` no fluxo de homologação) e `.env` local (fora do git) atualizado.

## 3) `Unrecognized internal port 18000 is not listed in Ports Exposes ...` (ao salvar o domínio)

- **Sintoma:** warning ao apontar o subdomínio para o serviço no painel do Coolify.
- **Causa:** a **porta do domínio é a porta interna do contêiner**, não a do host — no mapeamento `18000:8000`, o `18000` só se refere ao lado do host; e dentro do contêiner nada escuta na 18000 (`artisan serve` sobe na 8000).
- **Ação (UI, sem código):** domínio → serviço `app`, porta `8000`; warning desaparece e o proxy roteia para `app:8000` na rede interna do compose.

## 4) `Composer could not find a composer.json file in /app` + `Could not open input file: artisan` (503 no domínio)

- **Sintoma:** contêineres criam e iniciam; o `app` entra em crash loop e o domínio responde **503** eterno (proxy vivo, backend morto).
- **Causa:** o `up` do Coolify roda dentro do contêiner helper usando `--project-directory /artifacts/<id>` — caminho que **não existe no lado do daemon**; o bind relativo `.:/app` resolvia para ele e o Docker criava um **diretório vazio** como fonte do bind — o `/app` subia sem código: `composer.json`, vendor ou `artisan`.
- **Ação (commit `2e123d2`):** abandonou-se a dependência de fonte; o app passa a viver dentro da imagem:
  - Dockerfile: `WORKDIR /presenza` (afasta qualquer montagem esparsa em `/app`) → `COPY composer.json composer.lock` → `composer install --no-dev` → `COPY . .` → `dump-autoload` + `package:discover`;
  - `.dockerignore` novo (`.git`, `.env*`, `vendor/`, diretórios de docs/dev fora do contexto de build);
  - compose: binds `.:/app` removidos (`working_dir: /presenza`), guarda de `vendor` sai do comando do `app`, `scheduler` volta ao comando simples;
  - ponto de atenção: cada serviço ganha a própria imagem (`hackaton-app`/`hackaton-scheduler`) — veja o *gotcha* abaixo.

## 5) *Gotcha* local: scheduler rodava a imagem antiga ("Could not open input file: artisan")

- **Sintoma:** após trocar o Dockerfile, o `app` funcionou e o `scheduler` não (mesma imagem teórica).
- **Causa:** cada serviço gera uma imagem de nome próprio; o build só do `app` deixou o `scheduler` com a **imagem antiga** (pré-COPY, sem código).
- **Ação local:** `docker compose build` (ambos) + `docker compose up -d --force-recreate`. No Coolify não se aplica: cada deploy reconstroi os dois com o contexto do commit corrente.

## Checklist de redeploy

1. push (o log do deploy deve mostrar `Importing ... commit sha <novo sha>`);
2. acompanhar: `postgres Healthy` → `app Started` → `scheduler Started` (sem erro de bind);
3. esperar ~1–2 min (build do vendor + seed ~20 s);
4. `http://hackathon.dpicinato.com/verificar` em `200` (códigos demo `PZ-2025-000101` VÁLIDO / `PZ-2024-000087` EXPIRADO);
5. login com seed (`isabela.cristina.fonseca` / `Password@123`) confirma app+DB;
6. conferir e-mails capturados na UI do Mailpit (`http://IP:8025`).

## Prompts-chave (versão polida — ordem cronológica)

Os prompts originais do time, reorganizados para legibilidade (contexto → objetivo → restrições → formato da resposta).

> **P1 — build falha no include**
> "Estou deployando uma aplicação Laravel (Docker Compose) na VPS com Coolify. O deploy falhou no build com: `service "app" depends on undefined service "mailpit": invalid compose project`. O arquivo topo usa `include:` para puxar um compose de dev que declara `postgres` e `mailpit`. Diagnostique a causa raiz e corrija com a menor mudança possível, sem quebrar o fluxo local (`docker compose up`)."
>
> *Resultado:* IA comprovou via issues do Coolify (#5921/#2859) que o `include:` é ignorado e resolveu embutir `postgres`/`mailpit` no arquivo topo (commit `4514fbe`); `docker compose config` validado.

> **P2 — porta host ocupada**
> "Novo erro na fase de `up`: `Bind for 0.0.0.0:8000 failed: port is already allocated`. A aplicação vai rodar em contêiner atrás de um subdomínio servido pelo proxy do Coolify. Corrija **sem exigir variáveis externas** e preservando o acesso local (`http://localhost:...`) do time."
>
> *Resultado:* diagnóstico mostrou o painel do Coolify ocupando a 8000 do host; host port default trocada para 18000 (contêiner segue 8000), docs + `.env` local alinhados (commit `3ecf784`).

> **P3 — domínio com porta errada**
> "Ao configurar o subdomínio no Coolify aparece: `Unrecognized internal port Port 18000 is not listed in Ports Exposes and is not used by any application domain. The proxy will still route to it, but the container may not be listening there.` O que isso significa e como resolver?"
>
> *Resultado:* o campo de porta do domínio espera a porta **interna do contêiner** (8000); ajustado na UI — sem mudança de código.

> **P4 — contêiner sem código (503 eterno)**
> "O deploy com o domínio `app:8000` abre os contêineres, mas o `app` entra em loop: `Composer could not find a composer.json file in /app` + `Could not open input file: artisan`. O compose usa o bind `.:/app`. Faça local e VPS **usarem a mesma imagem**, eliminando qualquer dependência do código fora do build."
>
> *Resultado:* IA ligou o erro ao path do helper (invisível ao daemon), que torna o bind uma pasta vazia, e propôs a solução definitiva: código+vendor embutidos na imagem (Dockerfile `COPY` + `composer install`, WORKDIR `/presenza`, `.dockerignore`, binds removidos) — commit `2e123d2`; validado localmente (4 contêineres Up, `/verificar` = 200).

> **P5 — documentação**
> "O domínio finalmente funciona. Documente em `docs/` os problemas de Docker/Coolify do deploy (sintoma, causa raiz e ações de correção) e inclua alguns prompts usados."
>
> *Resultado:* este runbook + os prompts acima + links no README e no journey-log (Nº 32–35).

## Referências

- [Coolify issue #5921 — compose deployment ignores includes](https://github.com/coollabsio/coolify/issues/5921) · [#2859 — mesma causa, 2024](https://github.com/coollabsio/coolify/issues/2859) · PR #8850 (suporte a `include`, em aberto)
- Commits: `4514fbe` (include → topologia autônoma) · `3ecf784` (porta host 18000) · `2e123d2` (código embutido na imagem)
- Histórico completo das sessões: [`journey-log.md`](../journey-log.md) Nº 32–35 · decisão de reset total: Nº 31
