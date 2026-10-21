# Questionário de requisitos — Assistente de IA (Q36–Q59)

> **Produto: Presenza** — *presenças reais, certificados à prova.*
> **Hackathon FATEC & ETEC · Usina Lins · CetaCeo** — 21/10/2026 (manhã)
> Fonte: questionário interativo via skill `grilling` (OpenCode), 4 rodadas + resposta de fato do ambiente + confirmação final do time. Continua a numeração de `docs/questionario-requisitos-p1.md` (que parou em Q35).
> Requisitos consolidados: seção **7 de `docs/requisitos-p1.md`** (RF29–RF34, RNF07–RNF09).

## 0. Fatos levantados pela IA antes de perguntar (não foram decisão do time)

- **Modelos gratuitos no provider OpenCode** (todos $0 em input/output/cache, trocáveis por `.env`): `big-pickle`, `glm-5.3-flash-free`, `deepseek-v4.1-flash-free`, `minimax-m3.1-flash-free`, `nemotron-3.5-super-vl-free`, `beam-free`, `longcat-2.5-preview-free`, `mimo-v2.6-flash-free`, entre outros.
- **OpenCode em modo servidor** (`opencode serve`) expõe API HTTP (OpenAPI em `/openapi.json`) — dá para embutir como agente headless no produto; exige runtime Node/Bun.
- **Credenciais:** o acesso ao gateway de modelos é autenticado por **API key** (console OpenCode) mesmo para os modelos free → chave via `.env`, sem commit.
- A stack vigente (Q35/AT02) previa **"sem Node no MVP"** — usar o runtime OpenCode no produto é uma **exceção**, decidida e aprovada pelo time nesta sessão (Q38/Q44).

## Rodada 1 — Consumidor, escopo, arquitetura e prazo (Q36–Q41)

- **Q36 — Quem conversa com o assistente?** → **Página pública (sem login)**, não os portais logados. *(→ parte do widget nas públicas)*
- **Q37 — Só responde ou executa ações?** → **Só leitura** (recomendação aceita; nenhuma alteração no banco).
- **Q38 — O que "usar o OpenCode" significa no produto?** → **OpenCode como runtime** (`opencode serve` embutido), não apenas os modelos via HTTP direto. Exceção ao "sem Node" aprovada.
- **Q39 — Qual modelo gratuito?** → **`opencode/big-pickle`** ($0, trocável via `.env`).
- **Q40 — Quais dados pessoais podem ir ao gateway (LGPD)?** → Resposta inicial do time: "PII do usuário logado". **Conflito resolvido na Q42** — a página pública não tem usuário logado.
- **Q41 — Prazo/cenário?** → **MVP da demo** (diferencial estratégico na apresentação; sem atrasar RFs de P1 já fechados).

## Rodada 2 — Dados, conhecimento, infra e UX (Q42–Q47)

- **Q42 — Conflito Q36×Q40:** → **Zero PII**: o chat não recebe nem envia dado pessoal; dados de RF21 (nº, nome, treinamento, status) ficam restritos à página de verificação em si.
- **Q43 — De onde vêm as respostas?** → **Documentação do projeto** (`docs/`), e o time completou: *"permite ele ter acesso às documentações do projeto e escreva uma também para as páginas com suas rotas e conteúdos que possuem para ficar bem completo o contexto pra IA"* → criação de **`docs/mapa-paginas.md`** como artefato de contexto.
- **Q44 — Onde o runtime roda?** → **Contêiner próprio no `docker-compose.yaml`** (padrão/homolog), agente configurado em `opencode.json`; Laravel chama a API HTTP do runtime.
- **Q45 — Proteção do endpoint público contra abuso de quota?** → **Sem limite** (decisão do time; registrada como **risco aceito** — a quota gratuita fica exposta atrás de endpoint anônimo).
- **Q46 — O que fazer quando o modelo/gateway falhar?** → **Mensagem de cortesia** (orienta RH/ticket); a demo nunca para (alinha com RNF06).
- **Q47 — Streaming ou bloco único?** → **Resposta completa** exibida de uma vez, com **indicador de carregamento** ("deixa na opção de resposta completa e exibe um carregamento enquanto aguarda").

## Rodada 3 — Acesso aos docs, registro e identidade (Q48–Q53)

- **Q48 — Como o agente dentro do contêiner acessa os docs?** → **Só a pasta `docs/`** montada read-only (sem expor código-fonte); `docs/` embutida por volume, não por build.
- **Q49 — Onde o widget aparece?** → Resposta: *"tem como fazer um mockup para isso?"* → virou **Q54** (mockup próprio antes da implementação; local: botão flutuante nas páginas públicas).
- **Q50 — As conversas ficam registradas?** → **Salvar tudo** (pergunta/resposta + IP + data; visível só ao Admin).
- **Q51 — O assistente limita os temas?** → **Só Presenza**; recusa educada qualquer outro assunto (poupa quota).
- **Q52 — O contêiner autentica como no gateway?** → Resposta: *"precisa de chave autenticada para usar os modelos free? de toda forma, se precisar, deixa na .env"* → **Fato confirmado: sim, precisa de API key**; chave entra no `.env` do compose, sem commit.
- **Q53 — Nome/identidade?** → **"Assistente Presenza"** (neutro, pronto de imediato).

## Rodada 4 — Mockup, persistência, conciliação e formalização (Q54–Q59)

- **Q54 — Mockup (decorrência de Q49):** → **Mockup próprio** em `docs/design-assistente-ia.md` (com os tokens de `design-p1.md`), aprovado pelo time **antes** de implementar.
- **Q55 — Histórico persiste para o visitante?** → *"persista da forma mais fácil que tiver pra desenvolver"* → **vive só na aba aberta** (fechar/recarregar zera; o registro completo fica no banco, via RF33).
- **Q56 — Conciliação docs (sem banco) × consulta de RF21 no chat:** → **Só indicar a página**: o chat orienta quem tem um código a usar a página de verificação; **nenhum dado do sistema passa pelo chat**.
- **Q57 — Onde registrar os requisitos?** → **Seção nova no fim de `requisitos-p1.md`** (continuidade RF29…; P1 original fica intacto).
- **Q58 — Transparência da gravação (LGPD):** → **Aviso fixo no widget** de que a conversa é gravada para auditoria (IP e data).
- **Q59 — Entra na apresentação à banca?** → **No roteiro, no fim** — fechar a demo de 5 min mostrando o assistente (diferencial de IA).

## Confirmação final do time ⭐

Resumo consolidado (tabela de decisão + plano de entrega em 8 passos) apresentado à equipe → **"Aprovado, seguir"**.

## Pendências assumidas (não são dúvidas)

1. **Implementar** após aprovação do mockup: contêiner `opencode` + `opencode.json` (agente PT-BR, big-pickle, permissões de só-leitura, escopo Presenza) · endpoint público do Laravel · widget Blade/Alpine vendored · tabela de conversas + visão Admin · mensagem de cortesia · entrada no `roteiro-demo.md` · exceção "com Node" registrada nos acordos de trabalho.
2. **Risco aceito (Q45):** endpoint público sem limite de taxa — a quota da conta do time pode ser consumida por qualquer pessoa.
3. **Prazo:** janela de desenvolvimento fecha às **14:00** e a apresentação é às **14:30** (21/10) — escopo do mockup e da implementação mantido intencionalmente mínimo.
4. LGPD: **IP é dado pessoal** — o aviso do widget cobre a gravação (Q58); conteúdo do chat segue zero PII (Q42).
