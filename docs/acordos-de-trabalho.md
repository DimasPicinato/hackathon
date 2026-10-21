# Acordos de Trabalho — Presenza (Hackathon P1)

Convenções acordadas entre o time e a IA para todo o desenvolvimento. Referenciadas no `README.md` e no `journey-log.md`.

## AT01 — Gate de aprovação (regra fixa, acordada em 20/10/2026)

> **Toda vez que houver qualquer dúvida, ou mais de uma forma de fazer algo, a IA deve questionar o time — apresentando opções com recomendação justificada — e aguardar a aprovação antes de prosseguir.**

Aplica-se a: decisões técnicas, mudanças de rota no plano, divergências com requisitos, escolhas de bibliotecas, formatos de arquivos, estrutura das telas, nomenclatura — qualquer ponto onde exista "outra forma de fazer".
Mecanismo prático: questionário interativo do OpenCode (opções numeradas + recomendado em primeiro lugar).

## AT02 — Orquestração com Docker (acordada em 20/10/2026)

- **Desenvolvimento/testes**: `docker-compose` somente com os **serviços** (banco de dados, mail-catcher, etc.) — a aplicação roda fora ou em contêiner de desenvolvimento, conectando via `localhost` pelos valores do `.env`.
- **Homologação/demonstração**: um **segundo** compose que **estende** o compose de serviços, adicionando a aplicação já **buildada** e as **variáveis de ambiente dela fixas no compose** (nomes de serviço como host, ex.: `DB_HOST=postgres`) para compatibilidade de rede.
- Os **mesmos serviços** nos dois ambientes — o que muda é só a camada de aplicação.

## AT03 — Instalação de ferramentas

O ambiente da máquina do time só recebe ferramentas **instruídas pela IA** (passo a passo, com justificativa) — nunca instaladas diretamente pelo agente. Ferramentas pesadas rodam preferencialmente dentro de contêineres (AT02), minimizando o que precisa existir no Windows.

## AT04 — Processo registrado

Toda sessão de trabalho é registrada no `journey-log.md` (skill `registro-hackathon`) — prompts relevantes são polidos e salvos em `prompts/` quando solicitado, para o PDF de entrega.
