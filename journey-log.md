# 🗂️ Journey Log (Diário de Bordo) — Hackathon FATEC & ETEC · Usina Lins

> Histórico cronológico de tudo o que fizemos com IA. Cada sessão é numerada
> (Nº) e mantida em ordem de data/hora. Editar entradas antigas deve ser
> evitado, mas é permitido quando tornar a história do projeto mais clara
> para a apresentação à banca. Último registro no rodapé.

| Projeto escolhido                                                    | Integrantes                                                                                                                                                                                                                                                      | Início     | Status       |
| -------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- | ------------ |
| **P1 — Gestão de Treinamentos Internos (RH)** (decidido 20/10/15:33) | Dimas Matheus Gomes Picinato, Sara Silva Marsola, João Marcel dos Santos Silva (principais); Bruno Miranda Barcelos Tremeschin Pereira Melo, João Lucas Martins Pazin, Matheus Roberto Baraúna da Silva, Matheus Pereira Jesus, Lucas Yuuji Murayama (coautores) | 20/10/2026 | em andamento |

---

## 1 · DOCUMENTAÇÃO · Leitura e interpretação dos 3 problemas do desafio

- **Quando:** 20/10/2026 14:50
- **O que foi feito:**
  - Extração do conteúdo do `problemas.docx` (Hackathon FATEC & ETEC, parceiros Usina Lins e CetaCeo);
  - Mapeamento dos problemas: **P1** Gestão de Treinamentos Internos (RH), **P2** Controle de Refeitório (~600 refeições/dia, 3 turnos), **P3** Controle de Visitas Externas (segurança patrimonial);
  - Registro dos critérios de avaliação: criatividade, clareza/completude do prompt, arquitetura + design de UI, uso de mocks.
- **Artefatos:** `problemas.docx`
  - (fonte lida; conteúdo iconografado na conversa).
- **Decisões / por quê:** entender primeiro o escopo de cada problema _antes_ de qualquer escolha de tecnologia, conforme a estratégia do time.
- **Prompts-chave:** IA instruída a extrair texto do .docx e estruturar os 3 problemas com atores e dores.
- **Status:** Concluído.

## 2 · ARQUITETURA · Estruturação da arquitetura conceitual dos 3 problemas

- **Quando:** 20/10/2026 14:55
- **O que foi feito:**
  - Definição de uma espinha dorsal comum: portais por ator → serviços de domínio → dados → fachada de integração de hardware, com camada transversal de identidade/LGPD/biometria/auditoria;
  - P1: arquitetura de ciclo completo (catálogo → sessões → reserva pelo líder → chamada facial → certificado com código de verificação → **portal público de verificação**), com/wiki de principais entidades;
  - P2: arquitetura em torno do **evento de consumo** (catraca facial, 1 entrada por janela de refeição, eventos → rateio empresa×funcionário, cardápio online, totem de feedback anônimo);
  - P3: arquitetura de convites: link de uso único com expiração → auto-cadastro do visitante → aprovação da segurança → provisionamento com janela de validade que **expira sozinha** nos leitores;
  - Comparativo dos 3 (complexidade, risco LGPD, hardware, "wow", viabilidade em 24h) para decidir o projeto.
- **Artefatos:** — (estrutura na conversa; será consolidada em doc ao decidir o projeto).
- **Decisões / por quê:** biometria e hardware (câmera, catraca, cancela) sempre atrás de fachada única com **modo simulador**, para a demo não depender de reconhecimento real funcionando na hora da banca.
- **Prompts-chave:** IA orientada a levantar arquitetura **sem tecnologias**, com escopo, atores, entidades e MVP.
- **Status:** Concluído (aguardando escolha do problema pelo time).

## 3 · CONFIGURAÇÃO · Criação da skill `registro-hackathon` e deste arquivo

- **Quando:** 20/10/2026 14:56
- **O que foi feito:**
  - Criação da skill em `.opencode/skills/registro-hackathon/SKILL.md`: define quando registrar, formato das entradas, categorias, regras de estilo e verificações;
  - Criação deste `journey-log.md` como histórico oficial do processo.
- **Artefatos:** `.opencode/skills/registro-hackathon/SKILL.md`, `journey-log.md`
- **Decisões / por quê:** a apresentação precisa de evidência cronológica do uso de IA; o registro é escrito por IA ao fim de cada sessão, sem retrabalho para o time.
- **Prompts-chave:** pedido do time: "faça uma skill que a cada operação/documentação/discussão com IA você adicione um resumo em ordem e quando".
- **Status:** Concluído.

## 4 · DECISÃO · Identidade unificada: reconhecimento facial como primário + login/senha como fallback

- **Quando:** 20/10/2026 15:05
- **O que foi feito:**
  - Definição global para os 3 projetos: **reconhecimento facial é o meio principal** de identificação do usuário (time já tem experiência com o método);
  - **Fallback: login/senha convencional**, vinculado ao mesmo cadastro do usuário que carrega a biometria;
  - Banco escolhido: **PostgreSQL** (versatilidade para todos os cenários);
  - Solicitado o mapeamento da estrutura de dados de reconhecimento facial (credenciais biométricas, foto de referência, assinatura negative dependendo do provedor, consentimento e log de eventos).
- **Artefatos:** (planejamento consolidado na conversa).
- **Decisões / por quê:** simplicidade operacional e confiabilidade: biometria sempre como padrãoização, login/senha como via alternativa caso indisponível.
- **Prompts-chave:** diretiva do time consolidando: biometria facial primária, login/senha fallback, PostgreSQL.
- **Resultados:** plano atualizado com modelo de armazenamento de dados biométricos.
- **Status:** Concluído.

## 5 · CONFIGURAÇÃO · Versionamento Git + reforço na skill de registro

- **Quando:** 20/10/2026 15:10
- **O que foi feito:**
  - Inicialização/normalização do repositório Git (branch `main`) e `.gitignore` (locks Office, `.env`, temporários do OpenCode);
  - Commit inicial: `chore: init repo — problemas, registro de sessões do hackathon e skill registro-hackathon`;
  - Atualização da skill `registro-hackathon`: agora **puxa e refina os prompts do time antes de registrar** (contexto → objetivo → restrições → formato saída, preservando o sentido) e documenta os **resultados** de cada uso;
  - Preparado campo para sincronização com GitHub (aguardando URL do repositório remoto).
- **Artefatos:** `.opencode/skills/registro-hackathon/SKILL.md`, `.gitignore`, `journey-log.md`
- **Decisões / por quê:** versionamento do processo e dos prompts para a entrega em PDF; clareza dos prompts é critério avaliado pela banca.
- **Prompts-chave:** solicitação para todo prompt enviado ser polido e ter resultados documentados; alteração registrada na skill.
- **Resultados:** skill ampliada; repositório versionado.
- **Status:** Concluído (push ao GitHub pendente de URL do remote).

## 6 · CONFIGURAÇÃO · Renomeio para `journey-log.md` e flexibilização da regra de edição

- **Quando:** 20/10/2026 15:13
- **O que foi feito:**
  - Renomeação do arquivo de registro para **`journey-log.md`** (termo em inglês para "diário de bordo", a pedido do time), com `git mv` para preservar o rastreio de histórico;
  - Ajuste na skill `registro-hackathon` e no próprio cabeçalho deste log: edição e correção de entradas antigas deixou de ser proibida absoluta — **evita-se, porém é permitido** quando ajudar a apresentar melhor a história do projeto à banca (preservados sempre os Nº e a ordem cronológica).
- **Artefatos:** `journey-log.md`, `.opencode/skills/registro-hackathon/SKILL.md`
- **Decisões / por quê:** nomenclatura mais forte/daora para o artefato de apresentação; histórico útil vale mais que histórico "congelado", desde que mantida a integridade cronológica.
- **Prompts-chave:** pedido para renomear o arquivo com termo em inglês referente a diário de bordo e remover a regra de nenhum retrabalho permitido.
- **Resultados:** nome e política de edição atualizados; sem perda de histórico anterior.
- **Status:** Concluído.

## 7 · DISCUSSÃO · Recomendação: escolher o Problema 1 (Treinamentos)

- **Quando:** 20/10/2026 15:29
- **O que foi feito:** análise rápida dos 3 cenários para escolha do desafio.
- **Artefatos:** —
- **Decisões / por quê:** **P1** recomendado: menor dependência de hardware real (só câmera na chamada), MVP factível no prazo, e o "wow" da demo é acessível de celular — o avaliador valida um certificado ao vivo na página pública de verificação. **P3** é o plano B (fluxo de aprovação + expiração visível). **P2** é o mais rico, mas o mais arriscado (catraca física + rateio complexo).
- **Prompts-chave:** pedido de recomendação em poucas palavras para a escolha.
- **Resultados:** decisão pendente de confirmação do time.
- **Status:** Parcial — aguardando decisão final do time.

## 8 · DECISÃO · Problema 1 escolhido — arquitetura adiada até o levantamento de requisitos

- **Quando:** 20/10/2026 15:33 (revisado às 16:00)
- **O que foi feito:**
  - Time travou o **Problema 1 (Gestão de Treinamentos Internos — RH)** como desafio do hackathon;
  - ~~Documento-base de arquitetura criado e registrado nesta sessão~~ → **removido e revertido**: antes de definir arquitetura, o time decidiu rodar um **levantamento de requisitos** com IA (questionário guiado). A arquitetura/infradesign será reconstruída a partir dos requisitos levantados.
- **Artefatos:** `journey-log.md` (demo: `docs/arquitetura-p1-treinamentos.md` foi removido do pacote — permanece no histórico do Git via commits `c081de1`).
- **Decisões / por quê:** engenho dos requisitos antes do design para que arquitetura/código sejam derivados takich das regras de negócio corretas documento léxico portão.
- **Prompts-chave:** decisão de rever: "sem definir arquitetura ou nada ainda, remova isso do journey, procure skill de levantamento de requisitos e depois faça o questionário comigo".
- **Resultados:** escolha de P1 permanece válida; implementação de design aguarda requisitos.
- **Status:** Concluído (revisão aplicada).

## 9 · DOCUMENTAÇÃO · Levantamento de requisitos do P1 com IA

- **Quando:** 20/10/2026 16:02
- **O que foi feito:**
  - Remoção da arquitetura prévia do pacote de trabalho (só história e história do Git preservadas);
  - Busca por skills de levantamento de requisitos no ecossistema (skills.sh) — seleção do candidato mais confiável;
  - Questionário guiado de requisitos a ser rodado com o time (interatividade, um bloco por vez: produto/usuários, funcionalidades por ator, regras de negócio, restrições, riscos e critérios de aceite).
- **Artefatos:** `journey-log.md` (docs de requisitos serão criados a partir das respostas).
- **Decisões / por quê:** requisitos primeiro, design depois; perguntas anotadas para o PDF de prompts.
- **Prompts-chave:** "remova isso do journey, acha skill de levantamento de requisitos e faça o questionário comigo".
- **Resultados:** questionário concluído; requisitos consolidados em `docs/requisitos-p1.md` (sessão 10).
- **Status:** Concluído.

## 10 · DOCUMENTAÇÃO · Questionário de requisitos (30 perguntas, 4 rodadas) — doc consolidado

- **Quando:** 20/10/2026 16:10 → 21:20 (ajustes)
- **O que foi feito:**
  - Instalação das skills `grill-me` + `grilling` (mattpocock/skills, ~1,4 mi instalações) no repositório (`.agents/skills/`) e desconto de PowerShell para `npx` (ExecutionPolicy CurrentUser RemoteSigned);
  - Questionário interativo (via OpenCode) em 4 rodadas — Q1–Q30 — cobrindo: verificação pública com validade, presença manual, vagas nominais, fila de espera, fluxo de tickets, certificado, relatórios, papéis e permissões;
  - **Evoluições importantes decididas pelo time**: entidade **Aula** (Treinamento 1:N Aula) com instrutor iniciando/encerrando; facial **só para validar presença na aula ativa** (com colaborador autônomo OU via instrutor); homologação manual exige **senha do colaborador + motivo** (catálogo editável, incl. motivo novo no ato); confirmação prévia removida — **tickets** resolvem cancelamentos/justificativas (APROVADO/RECUSADO); líder com permissões totais no seu setor e **Admin** com poder total (SMTP); RH emite certificados **manualmente**; relatórios completos com exportação;
  - Consolidação em `docs/requisitos-p1.md` (28 RFs, 6 RNFs, seed de demo, fluxos e fora-de-escopo) após confirmação de entendimento compartilhado.
- **Artefatos:** `docs/requisitos-p1.md`, `docs/questionario-requisitos-p1.md` (transcrição Q&A), `.agents/skills/grill-me/`, `.agents/skills/grilling/`, `journey-log.md`
- **Decisões / por quê:** requisitos antes de arquitetura (decisão da sessão 8/9); pergunta a pergunta para não deixar nada assumido; certificado manual dá o RH como "dono da verdade" dos NRs.
- **Prompts-chave:** 30 perguntas respondidas no questionário interativo do OpenCode (respostas registradas na conversa; a consolidar no PDF de prompts).
- **Resultados:** `docs/requisitos-p1.md` aprovado pelo time; base pronta para mockups e esqueleto.
- **Status:** Concluído.

## 11 · DISCUSSÃO · Avaliação de maturidade dos requisitos e plano de sequência

- **Quando:** 20/10/2026 21:25
- **O que foi feito:** avaliação de confiança do conjunto levantado (`docs/requisitos-p1.md`) vs. riscos de execução.
- **Artefatos:** `docs/requisitos-p1.md`
- **Decisões / por quê:** requisitos considerados **maduros** (ciclo completo, RF/RNF rastreáveis, ambiguidades resolvidas); pendências não-bloqueantes mapeadas: SMTP do time (`.env` na demo), fotos dos integrantes para o seed biométrico, direção visual para mockups e ensaio do simulador no notebook da banca. Fator-limite restante: prazo de desenvolvimento (encerra 21/10 14h). Sequência proposta: arquitetura enxuta → seed/esqueleto → portais → chamada+certificado → verificação pública (momento "wow").
- **Prompts-chave:** pergunta do time sobre confiança em seguir a partir dos requisitos.
- **Resultados:** sequência de execução acordada a confirmar; nenhum requisito crítico pendente.
- **Status:** Concluído.

## 12 · CONFIGURAÇÃO · Skills grill-me e grilling movidas para escopo global do usuário

- **Quando:** 20/10/2026 21:27
- **O que foi feito:**
  - Movidas `grill-me` e `grilling` de `.agents/skills/` (escopo do repositório) para `~/.agents/skills/` (escopo global do usuário), tornando-as usáveis em qualquer projeto;
  - `skills-lock.json` do repositório limpo (as skills não são mais instalações locais).
- **Artefatos:** `journey-log.md`, `skills-lock.json`, `%USERPROFILE%\.agents\skills\grill-me\`, `%USERPROFILE%\.agents\skills\grilling\`
- **Decisões / por quê:** as skills são utilitários gerais de levantamento de requisitos, não específicos deste projeto; escopo global evita instalação duplicada em outros repositórios.
- **Prompts-chave:** "torne as skills grill-me e grilling globais ... pode apenas mover os arquivos ... depois verifique se estão disponíveis".
- **Resultados:** verificação positiva — ambas carregam corretamente pelo tool `skill` a partir do diretório global.
- **Status:** Concluído.

## 13 · DECISÃO · Design system: paleta "Agro verde + laranja segurança", tipografia Lexend/Source Sans 3 e kit de skills

- **Quando:** 20/10/2026 21:45 → 22:11
- **O que foi feito:**
  - Estudo das paletas candidatas no dataset da skill `ui-ux-pro-max` (SaaS geral, e-learning, slate industrial, agro/farm tech);
  - **Decisões (Q31–Q33 do questionário de design, via OpenCode):**
    - **Paleta**: verde agro `#15803D` (identidade) + laranja de segurança industrial `#EA580C` (ações/alertas NR) + neutros slate + fundo `#F0FDF4` — "a cor da usina e a cor da segurança";
    - **Tokens de status**: facial `#16A34A`, manual âmbar `#D97706`, APROVADO/RECUSADO, fila, expirado — ligação direta com RF12/RF15/RF17/RF21;
    - **Tipografia**: **Lexend** (títulos) + **Source Sans 3** (corpo) — Lexend desenhada para legibilidade/a11y (público industrial); números tabulares em tabelas e no código do certificado;
    - **Kit de skills**: `ui-ux-pro-max` + `frontend-design` + `ui-styling` + `design-system` + `web-design-guidelines` (auditoria pré-banca); opcionais: banner/slides/shadcn/firecrawl-clone (site da usina);
  - Obs.: Python não instalado nesta máquina — script `--persist` da skill indisponível; tokens transpostos manualmente (fonte: CSV do dataset, leitura direta).
- **Artefatos:** `docs/design-p1.md`
- **Decisões / por quê:** identidade agro + cultura de segurança dos NRs geram narrativa forte na banca; acessibilidade como decisão de design visível (Lexend).
- **Prompts-chave:** "vamos decidir uma paleta de cores e um conjunto de skills para design e UI/UX" — respondido pelo dataset da skill + confirmação em questionário (Q31–Q33).
- **Resultados:** tokens prontos para os mockups; pendências leves: instalar Python (geração de `design-system/MASTER.md` via script) e verificar cores do site oficial da Usina Lins (alinhamento de marca).
- **Status:** Concluído.

## 14 · DECISÃO · Nome do produto: **Presenza**

- **Quando:** 20/10/2026 22:20
- **O que foi feito:**
  - Escolha do nome via questionário (Q34) entre 5 candidatas geradas com racional (Certia, TurmaCert, NRFlow, Assidu, Presenza);
  - Aplicação do nome e tagline **"presenças reais, certificados à prova"** no cabeçalho/título/intro dos artefatos de apresentação.
- **Artefatos:** `README.md`, `docs/requisitos-p1.md`, `docs/design-p1.md`, `journey-log.md`
- **Decisões / por quê:** nome curto (cabe em capa/PDF/certificado/QR/banner/pitch), liga biometria + assiduidade, humano e memorável.
- **Prompts-chave:** "qual nome damos ao projeto?" — 5 opções com base no hero do produto (certificado verificável).
- **Resultados:** identidade verbal do produto fixada para mockups/banner/apresentação.
- **Status:** Concluído.

## 15 · DECISÃO · Gate de aprovação como regra fixa (AT01) + Stack decidida: **Laravel 11**

- **Quando:** 20/10/2026 22:40 → 22:55
- **O que foi feito:**
  - **Nova regra fixa do time, documentada em `docs/acordos-de-trabalho.md`** (AT01): sempre que houver dúvida ou forma alternativa de fazer, questionar o time com opções + recomendação e aguardar aprovação. Também registrados AT02 (Docker: compose de serviços p/ dev/testes + compose homolog que estende e adiciona app buildada com env fixo) e AT03 (instalações sempre instruídas, nunca diretas);
  - Comparativo de stacks via critérios dos RFs (auth/reset, scheduler, SMTP, PDF, QR, CSV, biometria, admin, corpus de IA, encaixe Docker);
  - **Q35 aprovado: Laravel 11 / PHP 8.3 / Blade + Alpine / PostgreSQL(pgvector) / Mailpit / dompdf** — sem Node/npm no MVP (assets prontos, sem build);
  - Doc técnico criado com servicios do compose (pgvector/pg16, mailpit) e dependências propostas (breeze, dompdf, qrcode).
- **Artefatos:** `docs/acordos-de-trabalho.md`, `docs/stack.md`, `README.md`
- **Decisões / por quê:** maior cobertura dos RFs pelas baterias do framework na janela de horas restante; DomPDF puro evita imagem pesada; o time mantém proficiência (Node) como evolução pós-hackathon.
- **Prompts-chave:** "vamos decidir a stack... Ruby-on-Rails, Laravel, Django... docker-compose de serviços + compose homolog que estende... toda vez que houver dúvida questione-me... instrua-me a instalar" (136 QRS: ver `prompts/` quando o time pedir os prompt finalizados para o PDF).
- **Resultados:** stack travada; instruções de instalação emitidas ao time (Docker Desktop requisito único).
- **Status:** Concluído — scaffolding aguarda Docker instalado no ambiente.

## 16 · CÓDIGO · Scaffold do Presenza rodando: Laravel + Docker (AT02) + auth Breeze + assets sem-build

- **Quando:** 20/10/2026 22:56 → 23:28
- **O que foi feito:**
  - Docker Desktop iniciado (daemon v29.9.1/Compose v5.6.0);
  - Projeto Laravel gerado por contêiner Composer e movido para a raiz do repositório (mesclando `.gitignore` do time com os padrões Laravel);
  - **Imagem `presenza/php` (PHP 8.4 + extensões: pdo_pgsql, gd, bcmath, zip + Composer integrado)** — ajuste: esqueleto Laravel atual exige PHP ≥ 8.4.1 (original pensado era 8.3; alinhado);
  - `docker-compose.yml` (dev/testes): `postgres` (pgvector/pg16 — suporte a vetor biométrico) + `mailpit` (captura de e-mails) com IK emitories persistente e healthcheck — conforme AT02;
  - `docker-compose.homolog.yml`: `include` do compose de serviços + `app`/`scheduler` buildados com env fixo (hosts = nomes de serviço) — conforme AT02;
  - `.env` dev configurado (pg em `host.docker.internal:5433`, Mailpit `:1025`, APP_NAME=Presenza) + `.env.example` espelhado;
  - Migrações base aplicadas no PostgreSQL;
  - Dependências: dompdf, php-qrcode +.dev debugbar/boost/breeze; **Breeze (Blade) scaffolding** de login/reset (RF01/RF28);
  - **Assets sem build**: removido `@vite` das telas; `public/css/app.css` com tokens do design system + Alpine.js vendored (`public/js/alpine.min.js`);
  - Laravel Boost instalado (skills de Laravel entraram no ambiente);
  - Smoke test: container dev rodando em `localhost:8000` — home e /login respondendo 200.
- **Artefatos:** `docker/php/Dockerfile`, `docker-compose.yml`, `docker-compose.homolog.yml`, `docker/.env`(implícito), `public/css/app.css`, `public/js/alpine.min.js`, app Laravel completo na raiz
- **Decisões / por quê:** decisão da sessão 14; serviços rodam em contêiner (AT03); Build UI para o MVP (menos peças);ação real da usina vem depois da fachada aprovada.
- **Prompts-chave:** "docker instalado, dá play e manda bala" — execução direta sob decisões aprovadas (Q35), sem novas aprovações necessárias.
- **Resultados:** MVP básico em pé: banco+serviços nocivos e auth com reset-lzy; baseima code-level pronta para o entity injection.
- **Status:** Concluído — próximo passo: migrations/seed do domínio (RF06–RF27) e telas conforme requisitos.

---

## 17 · CÓDIGO · Domínio completo: migrations, models, enums e seed (SEED01–03)

- **Quando:** 20/10/2026 23:29 → 23:38
- **O que foi feito:**
  - **5 migrations do domínio** cobrindo RF01–RF28: identidade (setores, extensão de `users` com matrícula/papéis acumuláveis em jsonb, termos LGPD versionados + aceites, biometrias com **vetor pgvector 128d**), treinamento (treinamentos, aulas com janela do instrutor, inscrições nominais com fila, motivos de homologação, presenças com **unique anti-fraude 1/pessoa/aula**), tickets (tipos, tickets, justificativas alteráveis), certificados (imutável por versão + log de verificações públicas) e sistema (notificações in-app, configurações Admin, auditorias);
  - **8 enums** de domínio (Papel, Turno, MetodoPresenca, StatusAula, StatusInscricao, StatusJustificativa, StatusTicket, ResultadoVerificacao);
  - **18 models** com relações + helpers de regra de negócio (ex.: `Aula::estaAtiva()` = janela facial do RF03; `Certificado::resultadoVerificacao()` = VÁLIDO/EXPIRADO do RF21/RF22; `User::temPapel()` = RF02; `Auditoria::registrar()` = RNF01);
  - **Seeders SEED01–03**: 44 contas (34 colaboradores fictícios + admin/RH×2/instrutores×2 incl. externo/líderes de 5 setores) **todas com embedding biométrico fake** para a fachada da demo; catálogos populados (termo v1.0, 3 motivos, 3 tipos de ticket, configurações SMTP);
  - **Cenários de demo**: NR10 concluído (presenças facial+manual, 3 certificados VÁLIDOS), NR35 em andamento (aula 1 encerrada, **aula 2 ATIVA hoje** com fila de espera nominal, falta com ticket+justificativa pendente, homologação auditada), Segurança da Informação concluído (**certificado PZ-2024-000087 EXPIRADO** para a verificação pública) + log de 3 verificações.
- **Artefatos:** `database/migrations/2026_10_20_1000*-{1..5}_*.php`, `app/Enums/*` (8), `app/Models/*` (17 novos + `User` estendido), `database/seeders/*` (5)
- **Decisões / por quê:** inscrições na **aula** (não no treinamento) — RF28 conta "vagas/convocados/presentes/faltas por sessão" e elegibilidade do RF18 é presença em todas as aulas; papéis como jsonb na conta (RF02 acumulável, 1 query a menos que pivô); anti-fraude do RF16 garantida por unique no banco, não só no código.
- **Correções no caminho:** pluralizer do Eloquent não pluraliza português (`setor`→`setors`) → `$table` explícito nos models com terminação -ão/-es; colunas faltantes/parse error corrigidos no ato.
- **Prompts-chave:** continuação direta do "dá play e manda bala" (sessão 16) — nenhuma dúvida material de aprovação AT01; divergências menores (Laravel 13/PHP 8.4) flaggadas no `stack.md`.
- **Resultados:** `migrate:fresh --seed` verde no PostgreSQL: 44 users, 39 inscrições, 22 presenças, 4 certificados (2 estados demo validados via tinker), 3 verificações, 44 biometrias, 3 auditorias; aula ativa localizada ("Aula 2 — práticas em altura").
- **Status:** Concluído — próximo passo: telas/portais (login+aceite do termo → portais por papel → chamada com fachada biométrica → verificação pública com QR, demo "wow").

---

## 18 · CÓDIGO · Telas e fluxos da demo: termo, portais, chamada e verificação pública

- **Quando:** 20/10/2026 23:39 → 00:24
- **O que foi feito:**
  - **RF04**: middleware `ExigirAceiteTermo` — sem aceite, portal bloqueado e tudo redireciona à tela do termo; aceite cria `TermoAceite` (validado na prática: `/portal` → `/portal/termo` → aceite → portal);
  - **RF01**: login redesignado (PT-BR, tokens própria) com redirect por papel; `/dashboard` mantido para compat com reset/reset-porde-password do Breeze;
  - **RF14.a**: presença facial **autônoma** do colaborador (botão "Validar presença agora" na aula em andamento);
  - **RF14.b + RF15 + RF27**: tela "Fazer chamada" do instrutor — facial via instrutor OU homologação manual com **senha do próprio colaborador** + motivo do catálogo OU novo (alimentando catálogo); início/encerramento da aula notificam RH (RF06/RF24);
  - **RF21/RF22**: página pública `/verificar/{código}` com VÁLIDO/EXPIRADO/NÃO ENCONTRADO, dados mínimos (nome, treinamento, carga, datas, instrutores — sem setor/foto/doc), **QR renderizado** (chillerlan), log de verificação e **PDF para download** (dompdf);
  - Welcome público com hero + busca de certificado; layout dos portais com topbar/sair;
  - Fachada biométrica documentada no service (`RNF06`): demo segura sem hardware.
- **Artefatos:** `app/Services/PresencaService.php`, controllers `Portal/Termo/Chamada/Aula/VerificacaoPublica`, middleware `ExigirAceiteTermo`, 9 views Blade, routes/web.php refeito, CSS estendido, `.env` com `QUEUE_CONNECTION=sync` (Mailpit sem worker) e `DEBUGBAR_ENABLED=false`
- **Validação (smoke manual com curl + cookies):** login → termo → portal 200; facial autônoma grava presença + auditoria; **RF16 recusa duplicidade** (flash na tela); instrutor homologa manualmente com senha correta (presença manual + notificação RH + auditoria) e **recusa senha errada**; `/verificar` VÁLIDO com QR e EXPIRADO; PDF 200.
- **Decisão AT01:** equipe pediu método mais eficiente que curls ad hoc → **suíte automatizada PHPUnit aprovada** (ver sessão 19). Skills `testing-best-practices` + `laravel-best-practices` (do Boost) governam a suíte.
- **Ajuste de requisito executado com o time:** **RF05** exigiu remover self-service — rota pública `/register` desabilitada (contas só via RH/Admin/líder).
- **Resultados:** fluxo crítico da demo end-to-end funcionando: login → termo LGPD → chamada facial/homologada → certificado verificável com QR + PDF.

## 19 · TESTES · Suíte automatizada dos fluxos críticos (PHPUnit via contêiner, Postgres)

- **Quando:** 21/10/2026 00:24 → 00:33
- **O que foi feito:**
  - Infra de teste: banco `presenza_test` no próprio container Postgres (pgvector não existe no SQLite — **testes rodam no banco formal**, RNF02), `phpunit.xml` apontando para ele, debugbar desligada;
  - `UserFactory` estendido (matrícula/papéis) + trait `Tests\Concerns\CriaFixtures` (colaboradores/instrutores/RH, termo+aceite reutilizável, aula nos 3 status, inscrição, certificado com validade);
  - **5 suítes feature, 21 testes novos**: `AceiteTermoTest` (RF04: bloqueio, aceite+trilha, re-aceite em nova versão), `PresencaFacialTest` (RF03/RF14/RF16: janela ativa, fora da janela, duplicidade com auditoria, facial via instrutor, 403 aula alheia), `HomologacaoManualTest` (RF15/RF27: senha correta+motivo, senha errada com auditoria, motivo novo no catálogo), `CicloAulaTest` (RF06/RF24: inicia/encerra com notificação RH, intruso 403), `VerificacaoPublicaTest` (RF21/RF22: VÁLIDO com dados mínimos e sem matrícula, EXPIRADO, NÃO ENCONTRADO, log, PDF público);
  - Testes legados do Breeze alinhados: login→portal, rota `dashboard` renomeada, registro público desativado (RF05).
- **Resultados:** **OK — 49 testes, 144 asserções, verde** no PostgreSQL. Os smokes manuais viraram regressão re-executável e evidência para o júri.
- **Comando:** `docker run --rm -v .:/app -w /app presenza/php vendor/bin/phpunit`

---

## 20 · CÓDIGO · Bloco RH: tickets, justificativas, certificados e relatórios

- **Quando:** 21/10/2026 00:34 → 01:02
- **O que foi feito:**
  - **RF10–RF12 + RF09**: telas de tickets — resolver "Cancelar vaga" devolve vagas em **todas** as aulas e promove a fila automática (serviço `VagaService` com reordenação da fila + notificação ao promovido); "Justificar falta" resolve com aula + status e cria/altera a justificativa; "Informação" encerra com resposta. Notificações RF24 ao colaborador em todas as resoluções;
  - **RF12/RF13**: tela de justificativas — troca de status (aprovado/recusado/pendente, vale a qualquer momento) e **registro direto por RH/Líder sem ticket** (RF13), com botões Aprovar/Recusar;
  - **RF18–RF20**: tela de certificados — lista de elegíveis calculada por RF18 (presença em TODAS as aulas, falta justificada = sem certificado), emissão manual pelo RH (serviço `CertificadoService`, código único `PZ-AAAA-NNNNNN`, validade às `validade_meses` após a última aula — RF22) e **reemissão imutável** (nova versão, versão antiga → NÃO ENCONTRADO na verificação);
  - **RF28**: relatórios — resumo por sessão (convocados/presentes/faltas) e CSVs com BOM UTF-8: detalhe da sessão (nome, matrícula, setor, inscrição, presença, método, motivo de homologação) e histórico por pessoa (presenças, certificados vigentes, validade);
  - Trait `EscopoGestao`: RH/Admin agem em tudo; **Líder só no próprio setor** (RF05/RF12), emissão de certificado exclusiva do RH (RF19);
  - 4 novas views (`portal/rh/{tickets,justificativas,certificados,relatorios}`) com trust do design system, links diretos na home do RH/Líder;
- **Testes (4 suítes novas, 17 testes):** `ResolucaoTicketsTest` (cancelamento+fila RF09, justificativa via ticket, falta sem aula → 422, líder só no próprio setor, colaborador 403), `StatusJustificativaTest` (qualquer momento RF12, RF13 direto, escopo), `EmissaoCertificadoTest` (elegível emite, falta bloqueia RF18, só RH, reemissão v2 + antigo não verifica RF20), `RelatorioCsvTest` (CSV sessão com método/motivo, CSV pessoas, 403 colaborador, líder vê só o setor com `streamedContent`).
- **Resultados:** **OK — 66 testes, 209 asserções, verde**; smoke no app dev: 4 telas RH 200 + CSV `text/csv` com login da RH isabela.
- **Aprendizados:** `streamDownload` exige `$response->streamedContent()` nos asserts (corpo não vem em `assertSee`); `whereJsonContains('papeis', …)` para filtrar colaboradores no jsonb.

---

## 21 · CÓDIGO · Lembretes automáticos (RF25) no scheduler de homologação

- **Quando:** 21/10/2026 01:03 → 01:10
- **O que foi feito:**
  - Comando `presenza:lembretes` (`app/Console/Commands/EnviarLembretesAula.php`): aula começando em ~24h → notifica vaga nominal ("sua aula é amanhã") e fila de espera (posição + lembrete de que RF09 promove automaticamente); janela fechada quando a aula já está `em_andamento`; auditoria `lembretes_enviados` quando algo é enviado;
  - Agendamento horário em `routes/console.php` (`0 * * * *`) — roda no serviço `scheduler` do compose homolog (`schedule:work`), zero intervenção manual na apresentação;
- **Testes:** `LembretesTest` (2 testes): dentre aulas em janela e fora dela, suficiente/notifica na fila, ou aula em andamento errada recebe nada.
- **Resultados:** **OK — 68 testes, 213 asserções, verde**; `php artisan schedule:list` mostra o comando agendado no contêiner.

---

## 22 · CÓDIGO · Painel de configurações (RF26): motivos, tipos, termo LGPD, SMTP

- **Quando:** 21/10/2026 01:11 → 01:19
- **O que foi feito:**
  - Tela `portal/rh/configuracoes` (Admin/RH apenas): catálogo de motivos de homologação criar/alterar (alimenta RF27); tipos de ticket alternar (RF10); **publicação de nova versão do termo LGPD** — a anterior deixa de vigir e TODOS são re-impostos ao aceite no próximo portal (RF04, wow-factor de demo); SMTP em leitura;
  - Todas as ações com auditoria (`motivo_criado`, `motivo_alternado`, `termo_publicado`, `tipo_ticket_alternado`); link "Configurações (RF26)" na home do RH/Admin;
- **Testes:** `ConfiguracoesRhTest` (4): motivo criar/alternar com auditoria, publicação de termo re-pede aceite (403 do colaborador, alternar tipo).
- **Resultados:** **OK — 72 testes, 228 asserções, verde**; smoke dev: tela 200 com catálogo e tipos real carregados.

---

## 23 · CÓDIGO · Reservas nominais do time (RF05/RF08) com fila automática (RF09)

- **Quando:** 21/10/2026 01:20 → 01:29
- **O que foi feito:**
  - Tela `portal/rh/reservas`: Líder/RH reserva vaga nominal em aula agendada para liderados (RF05: Líder só nos seus; RH em todos); **aula lotada → entrada automática na fila de espera com posição**; cancelamento de reserva promove a fila na hora (serviço VagaService, RF09);
  - Notificações RF24 em tudo (vaga reservada / entrou na fila / cancelada); auditoria `reserva_feita`; link "Reservas do time (RF08)" na home;
  - Listagem por aula com vagas restantes, reservadas e fila completa + botão Cancelar.
- **Testes:** `ReservasTest` (4): reserva nominal + rastro `reservado_por_id`, lote → fila posição 1, cancelamento promove fila, líder fora do setor 403.
- **Resultados:** **OK — 76 testes, 239 asserções, verde.**

---

## 24 · ENTREGÁVEIS · README, roteiro de demo 5 min e PDF de prompts IA

- **Quando:** 21/10/2026 01:30 → 01:41
- **O que foi feito:**
  - **Validação real de e-mail**: pedido de reset de senha (fluxo de acesso) gerou mensagem no **Mailpit** ("Reset your password" — remetente `presenza@usinalins.local`) — caixa postal dev comprovada para a demo;
  - **`README.md`**: pitch do produto, tabela problema→solução, stack, como rodar dev/homolog (topologia AT02), credenciais da demo, códigos de verificação pública, mapa de RFs e comando de testes;
  - **`docs/roteiro-demo.md`**: roteiro cronometrado de 5 min (dor → solução → anti-fraude → tickets → certificados imutáveis → governança → fechamento), check-list pré-demo e **plano B por cenário de falha** (a homologação manual é feature, não gambiarra);
  - **PDF de prompts (entregável do rubric)**: comando `presenza:pdf-prompts` (dompdf + commonmark) compila os docs (`questionario-requisitos-p1` Q1–Q35, requisitos, stack, acordos, design) em `docs/prompts-ia.pdf` (1,8 MB, com capa para a equipe);
  - Bug consertado a caminho: `collect([]) ?:` nunca cai no default em PHP (objeto sempre truthy) — option array `\?:` com array puro.
- **Resultados:** suíte **OK — 76 testes, 239 asserções**; PDF gerado e versionado em `docs/`.

---

## 25 · TESTES · Topologia AT02 (homolog) construída e validada de ponta a ponta

- **Quando:** 21/10/2026 01:42 → 01:49
- **O que foi feito:**
  - Dev manual (`presenza-dev-app`) parado; `docker compose -f docker-compose.homolog.yml up -d --build` — serviços **app** (porta 8000) e **scheduler** de pé, postgres/mailpit compartilhados e saudáveis;
  - `migrate:fresh --seed` DENTRO do serviço `app` (rede interna compose: `DB_HOST=postgres` — nunca localhost);
  - Smoke público homolog: `/`, `/verificar`, VÁLIDO, EXPIRADO, **PDF application/pdf**, `/login` — todos 200;
  - Smoke do fluxo crítico completo na topologia REAL: login 302 → portal bloqueado 302→/portal/termo (**RF04**) → aceite → portal 200 → telas RH 200 (Certificados);
  - `schedule:list` no serviço app confirma o RF25 rodando no scheduler do compose;
  - Curiosidade trabalhada: Docker Desktop rejeita `docker exec/logs` por nome do contêiner do compose — usar `docker compose exec` (funciona direto).
- **Resultados:** topologia AT02 em pé e validada — é o estado que fica para a apresentação; suíte dev segue **76/239 verde**.

---

## 26 · CÓDIGO · RF10 feito real: colaborador abre o próprio ticket (com notificação ao RH)

- **Quando:** 21/10/2026 01:15 → 01:27
- **O que foi feito:**
  - Form "Abrir ticket" na home (todos os papéis): tipo do colaborador catálogo (ativos apenas — RF26 desativa e o form recusa) + treinamento opcional + texto livre obrigatório (RF11);
  - Auditoria `ticket_aberto` + **IDs também notificam cada RH ativo** (`ticket_novo`, RF24 — duplicado por papel, então 2 RHs = 2 notificações, testado);
  - Interrupção do ambiente registrada com transparência: **Docker Desktop e mecanismo caíram com a reinicialização** — religamos o Docker Desktop pelo próprio molde (AT03: instrução, não instalação) e a topologia homolog re-subiu (`compose up -d`), banco e um reapareceram.
- **Testes:** `AbrirTicketTest` (3): abre + 2 RHs notificados, tipo inativo recusado (integra com RF26), sem texto recusado.
- **Resultados:** parcial verde (3/13) na primeira rodada da sessão.
## 27 · CÓDIGO · RF07 e RF05 fechados: catálogo de treinamentos e CRUD de usuários

- **Quando:** 21/10/2026 01:28 → 02:05
- **O que foi feito:**
  - **RF07** (`TreinamentosController` + view): RH/Admin criam/editam treinamentos (nome, carga, validade, setor) e **adicionam/ajustam aulas** (título, data, vagas, local; ordem calculada); **Líder cria apenas para o próprio setor** (setor imposto) e não toca treinamentos de fora (403); aula `em_andamento`/`encerrada` **não é re-agendada pela gestão** — a janela é do instrutor (RF06), abort 422 explicativo; auditoria em tudo;
  - **RF05** (`UsuariosController` + view): RH/Admin **criam contas** (nome, e-mail, matrícula, setor, turno, **papéis acumuláveis**), **editam**, **desativam/reativam** e RH redefine senha; **Líder para liderados apenas redefinir** (escopo 403 fora); conta desativada **não loga** (guard no login: "Conta desativada — procure o RH");
  - Notificação de boas-vindas e de senha redefinida (RF24); auditorias `usuario_criado/ajustado/desativado/reativado/senha_redefinida`;
  - Bugs pegos pelos testes antes de ir pro verde: comparação de enum com string (`status !== 'agendada'` comparado a instância) e `Enum::is()` inexistente em enums nativos PHP.
- **Testes:** `TreinamentosTest` (5) + `UsuariosTest` (5): criação com hash real de senha (não HLER claro), desativado recusa login (guest), desativar/reativar com trilha, líder fora do escopo 403 em senha e treinamento, colaborador 403.
- **Resultados:** **suíte completa OK — 89 testes, 294 asserções, verde.** Faltam: matriz de conformidade (RF28 extra) para adjetivo 28/28 + README final.

---

_Última atualização: 21/10/2026 02:05 — gerado pela skill `registro-hackathon`._
