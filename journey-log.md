# 🗂️ Journey Log (Diário de Bordo) — Hackathon FATEC & ETEC · Usina Lins

> Histórico cronológico de tudo o que fizemos com IA. Cada sessão é numerada
> (Nº) e mantida em ordem de data/hora. Editar entradas antigas deve ser
> evitado, mas é permitido quando tornar a história do projeto mais clara
> para a apresentação à banca. Último registro no rodapé.

| Projeto escolhido                                                    | Integrantes                                                                                                                                                                                                                                                      | Início     | Status       |
| -------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- | ------------ |
| **P1 — Gestão de Treinamentos Internos (RH)** (decidido 20/10/15:33) | Dimas Matheus Gomes Picinato, Sara Silva Marsola, João Marcelo dos Santos Silva (principais); Bruno Miranda Barcelos Tremeschin Pereira Melo, João Lucas Martins Pazin, Matheus Roberto Baraúna da Silva, Matheus Pereira Jesus, Lucas Yuuji Murayama (coautores) | 20/10/2026 | em andamento |

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

## 28 · CÓDIGO · Matriz de conformidade (RF28+) e encerramento 28/28 — README atualizado

- **Quando:** 21/10/2026 02:06 → 02:24
- **O que foi feito:**
  - **Matriz de conformidade** (`portal/rh/conformidade`): para cada pessoa × treinamento aplicável, badge de situação — **VIGENTE** (com data), **EXPIRADO**, **EM_CURSO (x/y aulas)**, **PENDENTE**; treinamento restrito a outro setor vira célula n/a (aplicabilidade por setor na visão do júri); Líder vê apenas liderados;
  - README atualizado para **"28/28 RFs implementados"** (93 testes/305 asserções), tabela de papéis da demo ampliada (RF10, RF07, RF05); roteiro de demo atualizado (93/305 no plano B);
  - Smoke homolog: as 3 telas novas (conformidade, treinamentos, usuários) **200 com o DB semeado**.
- **Testes:** `ConformidadeTest` (4): vigente/expirado/em_curso na mesma tela, n/a para setor não aplicável, líder restrito, 403 colaborador.
- **Resultados:** **suíte final OK — 93 testes, 305 asserções, verde.** **REQUISITOS FECHADOS: 28/28 RFs** (alcance alemão: RF07 e RF05 entraram nesta noite após aprovação da equipe).

---


## 29 · CÓDIGO · Seed enriquecido (SEED04) — lista de dados de exemplo cheias para a demo

- **Quando:** 21/10/2026 01:30 → 01:49
- **O que foi feito:**
  - Pedido do time ("adicione no seed vários dados de exemplo, pode encher") virou o `DadosExtrasSeeder` (SEED04), registrado no `DatabaseSeeder` após os cenários de demo — os alvos da apresentação (NR35 A2 em andamento, códigos PZ-2025-000101/000087) ficaram intactos;
  - Volume novo: **2 setores** (Expedição, Laboratório), **13 pessoas** (2 líderes, 1 instrutor interno, 10 colaboradores) com biometria de fachada **128 dimensões** para todos (RNF06), **6 motivos** de homologação, **5 treinamentos** (NR-12, NR-33, Brigada de Emergência, Saúde Mental, NR-11), **15 aulas**, fila de espera em 4 aulas;
  - Cenários de gestão: **3 tickets abertos** (justificar falta — Jéssica; cancelar vaga — Rafael, para promoção de fila ao vivo; informação — Natália) + 4 resolvidos; justificativas **aprovada** (Fernando NR-12) e **recusadas** (Wesley NR-12, Éverton NR-33); **reemissão real de certificado** (RF20): v1 `PZ-2026-000105` morre (`nao_encontrado`), v2 `PZ-2026-000111` vigente;
  - Validação: DB re-semeado no contêiner de homologação (57 contas, 7 setores, 8 treinamentos, 83 inscrições, 5 na fila, 15 certificados, 1 substituído) e 3 rotas públicas em `200 OK`;
- **Artefatos:** `database/seeders/DadosExtrasSeeder.php`, `database/seeders/DatabaseSeeder.php`
- **Decisões / por quê:** encher o seed sem mexer nos alvos da demo — volume a mais para a matriz de conformidade e as telas de RH parecerem "vivos" nos 5 minutos de apresentação;
- **Prompts-chave:** "adicione no seed vários dados de exemplo, pode encher" — direto e curto; IA releu seedadores/migrações antes de escrever para não quebrar chaves únicas;
- **Resultados:** seed completo verde em homologação; suíte de testes OK — **93 testes, 305 asserções** (nada mudou no comportamento testado).
- **Status:** Concluído.

---

## 30 · CONFIGURAÇÃO · Renome dos composes (.yml → .yaml): homologação vira o padrão do Docker Compose

- **Quando:** 21/10/2026 01:49 → 01:59
- **O que foi feito:**
  - Mudança do time revisada e aceita: `docker-compose.yml` (dev) → **`docker-compose.dev.yaml`** e `docker-compose.homolog.yml` → **`docker-compose.yaml`** — efeito: o comando sem `-f` (`docker compose up -d`) agora sobe a **topologia completa de homologação** (app + scheduler + serviços), e o dev usa `-f docker-compose.dev.yaml`;
  - Correção necessária pelo renome: o `include` dentro de `docker-compose.yaml` apontava para o arquivo antigo (`docker-compose.yml`, inexistente) → atualizado para `docker-compose.dev.yaml` + comentário no cabeçalho marcando o padrão;
  - Documentação sincronizada: README (blocos de desenvolvimento e homologação com comandos reais validados) e `docs/stack.md` (tabela/árvore de arquivos); `docs/roteiro-demo.md` segue válido (o passo `docker compose up -d` agora é literalmente o comando de subida da demo);
  - Validação ao vivo: `docker compose config --quiet` OK; `docker compose up -d` reconciliou pilha já em execução **sem recriar contêineres** (nenhum dado perdido) e `/verificar` em `200 OK`.
- **Artefatos:** `docker-compose.yaml`, `docker-compose.dev.yaml`, `README.md`, `docs/stack.md`
- **Decisões / por quê:** renome do time mantido e documentado — default de compose = stack de demonstração conta o 5 minutos; histórico (`journey-log` Nº 10–28) preservado com os nomes da época, sem reescrita.
- **Prompts-chave:** "ve o que alterei, documente onde precisar e commit" revisão de mudança humana: IA identifica cross-impact (include quebrado + docs fora de sync) antes de documentar;
- **Resultados:** topologia validada com os novos nomes; nada de código funcional alterado (99 linhas movidas entre arquivos).
- **Status:** Concluído.

---

## 31 · CONFIGURAÇÃO · Seed automático no `up` (reset total aprovado pela equipe)

- **Quando:** 21/10/2026 02:00 → 02:10
- **O que foi feito:**
  - Pergunta AT01 ao time (reset total vs. só-que-banco-vazio) — resposta: **reset total**; comando do `app` no `docker-compose.yaml` agora executa `migrate:fresh --seed --force` antes do `serve` (banco repovado a cada (re)início do contêiner com SEED01–04);
  - Aviso documentado no README: `restart`/recriação **apaga dados ao vivo** — não reiniciar durante a apresentação; primeiros acessos após `up` aguardam ~20 s pelo seed;
  - Validação ao vivo: `up --force-recreate app` → contêiner refeito, seed rodou sozinho (57 contas, 3 tickets abertos, 15 certificados conferidos no banco) e `/verificar` em `200 OK` após ~30 s.
- **Artefatos:** `docker-compose.yaml`, `README.md`
- **Decisões / por quê:** demo "à prova de esquecimento": qualquer máquina do time sobe a apresentação já semeada, sem lembrar do comando manual; custo: dados criados ao vivo não sobrevivem a restart (aceito pelo time na pergunta);
- **Prompts-chave:** "quero que rode o seed quando eu subir o docker-compose.yaml" — AR01 aplicado: IA apresentou as duas políticas com recomendação e o time escolheu reset total;
- **Resultados:** comprovado em execução: subida com seed 100% automático, servidor após o seed, dados da demo conferidos.
- **Status:** Concluído.

---

## Nº 32 · CONFIGURAÇÃO · Compose de homologação autossuficiente (deploy no Coolify)

- **Quando:** 21/10/2026 02:18
- **O que foi feito:**
  - Falha ao subir na VPS (pico de: `service "app" depends on undefined service "mailpit"`); causa confirmada em pesquisa: o Coolify ignora a diretiva `include:` do compose e só vê os serviços do arquivo topo (issues #5921/#2859, PR #8850 sem merge) — confirmado pelo time: bastava copiar os serviços de dev para dentro do padrão;
  - `docker-compose.yaml`: `postgres` + `mailpit` (e o volume) declarados no próprio arquivo; `include:` removido; `docker-compose.dev.yaml` segue intacto para o fluxo de dev "serviços + app one-off";
  - Coberturas para rodar direto na VPS: guarda de `vendor/` no comando do `app` (`composer install` no 1º start da VPS, pois `vendor` não viaja no git e o bind aponta para o checkout) e espera do `scheduler` pelo autoload; `APP_KEY`/`APP_URL` interpolados do compose (VPS gera chave efêmera se a var não for definida — sessões reiniciam junto com o banco, coerente com o reset total Nº 31); porta do `app` parametrizada (`APP_PORT`) por causa do conflito da 8000 na VPS;
  - Validação: `docker compose config --quiet` sem avisos (topo e dev) — o 1º teste de validação chegou a interpolar `$n` do script do scheduler; contador trocado por `timeout 120`;
  - Docs sincronizadas (README + `docs/stack.md` deixam de descrever o `include`).
- **Artefatos:** `docker-compose.yaml`, `README.md`, `docs/stack.md`
- **Decisões / por quê:** arquivo autossuficiente resolve o deploy gerenciado sem duplicar a stack; exigência do time de zero variáveis externas ⇒ valores fixos no compose (URL pública do subdomínio + chave de demo); com subdomínio no Coolify, a exposição é via proxy na porta do contêiner (requisitos de sessão/QR dependem do `APP_URL` correto na VPS).
- **Prompts-chave:** "subi na VPS com Coolify e deu este erro (log completo)"; time orientou: "pode copiar o trecho do dev para dentro do padrão, preciso apenas que funcione" — IA confirmou a causa antes de corrigir e avisou os requisitos escondidos (vendor, APP_KEY, porta host na VPS);
- **Resultados:** compose validado e pronto para novo deploy; zero variáveis a definir no Coolify — `APP_URL` fixa (`https://hackathon.dpicinato.com`, pergunta ao time) e `APP_KEY` de demo fixa no arquivo; commit efetuado; pendente: redeploy na VPS.
- **Status:** Parcial (falta o redeploy na VPS).

---

## Nº 33 · CONFIGURAÇÃO · Porta do host desviada da 8000 (colisão com o painel do Coolify)

- **Quando:** 21/10/2026 02:31
- **O que foi feito:**
  - Redeploy (commit 4514fbe) avançou — build/seed/postgres OK, contêineres criados — e BATEU EM PORTA: `Bind for 0.0.0.0:8000 failed: port is already allocated` (painel do Coolify ocupa a 8000 do host na VPS);
  - Default do host port do `app` trocado p/ **18000** (raramente ocupada local/VPS); porta do contêiner segue **8000** — o subdomínio do Coolify aponta via proxy, à porta do contêiner, então o acesso público é só https://hackathon.dpicinato.com;
  - Zero variáveis mantido (default único local+VPS, `APP_PORT` segue como resgate); alinhados README (`up` homolog + `/verificar` p/ 18000) e `.env` local (`APP_URL=http://localhost:18000` — fora do git);
  - Validação: `docker compose config --quiet` OK.
- **Artefatos:** `docker-compose.yaml`, `README.md`, `.env` (local)
- **Decisões / por quê:** porta de host é consumida DENTRO da máquina; trocar o default evita nova colisão sem exigir var na VPS; na VPS o host port publica só como debug, pois o tráfego real passa pelo proxy.
- **Prompts-chave:** "agora esse erro (log da porta 8000 ocupada No Coolify)" — IA já tinha marcado o risco e resolveu via default único, estudado a partir da exigência de zero variáveis;
- **Resultados:** compose validado; commit; pendente: push + redeploy na VPS.
- **Status:** Parcial (redeploy pendente).

---

## Nº 34 · CONFIGURAÇÃO · Código embarcado na imagem (morre a dependência de bind de fonte no Coolify)

- **Quando:** 21/10/2026 03:01
- **O que foi feito:**
  - Redeploy do `3ecf784` subiu contêineres mas o `app` crashou em loop no Coolify: `Composer could not find a composer.json in /app` — causa raiz: o `up` do Coolify roda a partir de diretório do helper que **não existe no lado do daemon**, então o bind `.:/app` chegava vazio no contêiner (sem código/artisan);
  - Fix estrutural: Dockerfile agora **embute código + dependências** (`COPY composer.json/lock → composer install --no-dev → COPY . . → dump-autoload/package:discover`, WORKDIR dedicado `/presenza`) + `.dockerignore` novo (segredos/dev fora do contexto); compose perde os binds `.:/app`, `working_dir: /presenza`, guarda de `vendor` sai do command e o scheduler volta ao comando simples;
  - Validação local completa: `docker compose build` p/ ambos (scheduler tinha ficado em imagem antiga — cada serviço tem sua imagem nomeada, `hackaton-app`/`hackaton-scheduler`) → 4 contêineres Up, `/verificar` em `200` e scheduler com ticks normais.
- **Artefatos:** `docker/php/Dockerfile`, `.dockerignore`, `docker-compose.yaml`, `README.md`
- **Decisões / por quê:** imagem autossuficiente elimina a classe inteira de erros de fonte ausente (local = VPS, mesma família de build); WORKDIR `/presenza` afasta qualquer montagem obliqua em `/app`; custo: mudança de código exige rebuild (aprovado — o fluxo de dev segue pelo `docker run` one-off de dev).
- **Prompts-chave:** log do Coolify com o erro do composer; IA liga o `Could not open input file: artisan` ao bind vazio (não ao código) e troca a estratégia de "runtime install" para "imagem embarcada";
- **Resultados:** pilha verde local (build + up + smoke 200); **redeploy confirmado pelo time: `https://hackathon.dpicinato.com` respondendo**.
- **Status:** Concluído.

---

## Nº 35 · DOCUMENTAÇÃO · Runbook do deploy no Coolify (`docs/deploy-coolify.md`)

- **Quando:** 21/10/2026 03:09
- **O que foi feito:**
  - Runbook com os 4 problemas reais do deploy + *gotcha* local (sintoma → causa raiz → ação, com commits `4514fbe`/`3ecf784`/`2e123d2` e referências às issues do Coolify), estado atual da stack, checklist de redeploy e acessos (domínio `hackathon.dpicinato.com`, debug `:18000`, Mailpit `:8025`);
  - Seção de **prompts-chave polidos** (contexto → objetivo → restrições → formato), cada um com o resultado objetivo (P1 include, P2 porta, P3 domínio, P4 runtime, P5 documentação);
  - Link no README (bloco de documentação); Nº 34 fechado (deploy confirmado pelo time).
- **Artefatos:** `docs/deploy-coolify.md`, `README.md`, `journey-log.md`
- **Decisões / por quê:** runbook e não post de bug: os bloqueios são padrões de plataforma (`include` ignorado, painel na 8000, porta interna no domínio, bind vazio no `up`) — reutilizável em nova VPS/deploy do zero; prompts embutidos pedidos pelo time (alimentação direta do PDF de prompts da banca).
- **Prompts-chave:** "finalmente funcionou, documente esses problemas que tiveram em questão do docker e as ações para resolver, inclua alguns prompts também na documentação disso" — polido em P5 dentro do próprio runbook;
- **Resultados:** guia único de recuperação de deploy + evidência de raciocínio para a banca; caminho completo de depuração documentado.
- **Status:** Concluído.

---

## Nº 36 · CÓDIGO · Revisão de layout: sidebar por papel, botão Voltar em tudo e ritmo de espaçamento 4/8

- **Quando:** 21/10/2026 03:10 → 03:41
- **O que foi feito:**
  - Pedido do time ("revise o layout… espaçamentos e intuitividade… botões de voltar e menus laterais claros") virou um **app shell** novo: sidebar fixa por papel (todos: Início; RH/Admin: grupo **GESTÃO** completo com Tickets e Configurações; Líder só o que tem âmbito) com estado ativo por rota (`routeIs`), rótulo de grupo e pé com Meu perfil + Sair;
  - **Botão "Voltar" padronizado** nas 10 telas que tinham link solto (tickets, justificativas, reservas, treinamentos, certificados, relatórios, conformidade, usuários, configurações, chamada da aula) — a seta quebrada (mojibake) em `usuarios` também morreu aqui;
  - Componente `<x-icon>`: família única de SVG (stroke uniforme, 18px, currentColor, `aria-hidden`), usado pela sidebar e pelo voltar; skip-link "Pular para o conteúdo" + `flash-ok`/`flash-erros` e `alert`s centralizados no layout (desduplicados das views);
  - **CSS em escala 4/8** (`--space-1..7`): cards, tabelas, títulos, formulários e container com ritmo único; hover/focus consistentes; sidebar vira faixa horizontal rolável `<960px` (sem JS);
  - Bug achado de passada na chamada da aula: `@if` vazio engolia o badge de fila — corrigido (fila + cancelada renderizam);
  - Validação: suíte **93/305 verde**; smoke autenticado nas 10 páginas como RH (`ativo` correto em cada, `voltar` presente, zero 500); **screenshots reais** com automação de navegador (portal, tickets, matriz, usuários) — pegou no ato os ícones gigantes (wrapper `.icn` faltando no componente) e corrigiu com rebuild;
  - Compatibilidade com o Nº 32–35 preservada: como o código agora é **embutido na imagem**, o rebuild (`docker compose up -d --build`) aplica a UI tanto local (`:18000`) quanto no redeploy da VPS.
- **Artefatos:** `public/css/app.css`, `resources/views/layouts/portal.blade.php`, `resources/views/components/icon.blade.php`, `resources/views/portal/aula.blade.php`, `resources/views/portal/rh/*.blade.php`
- **Decisões / por quê:** substitui "tudo na home + link textual de volta" por navegação de aplicativo — menos cliques para o júri percorrer os RFs e caráter mais profissional; sidebar servida no servidor (sem Alpine/JS) para não quebrar sem rede;
- **Prompts-chave:** "faça uma revisão no layout… espaçamentos e intuitividade… botões de voltar para páginas anteriores, e menus laterais claros e intuitivos" — IA partiu das guias de UX (hierarquia de navegação, ritmo 4/8, estados de foco) aplicadas ao design system verde/laranja existente;
- **Resultados:** validado em 3 níveis (testes, smoke HTTP autenticado, screenshots); UI aplicada no build local; pendente: redeploy na VPS quando o time quiser.
- **Status:** Concluído.

---

## Nº 37 · CÓDIGO · Polimento de UI 2: mojibake eliminado, login com listinha de credenciais e listas com busca

- **Quando:** 21/10/2026 03:45 → 04:10
- **O que foi feito:**
  - Time apontou: botões/inputs colados em `/verificar`, textos colados na lista de contas da `/login`, **caracteres errados** (ex.: "UsuÃ¡rios" em `/portal/rh/usuarios`) e pedido de **listas como tabelas com pesquisa**;
  - **Causa-raiz das mojibake** (item 1 de 33 do método): dupla codificação UTF-8 em exatamente 2 arquivos — `docs/roteiro-demo.md` (81 pontos) e `portal/rh/usuarios.blade.php` (15) — resto da árvore **limpa**, comprovada por varredura byte-a-byte (padrões `C3 83 C2 xx` etc. via grep com arquivo de padrões no contêiner; o primeiro scan com 'Ã' deu falso positivo em "NÃO" — a letra Ã existe no PT!); ambos reescritos do zero (roteiro também atualizado p/ porta 18000 + seed automático);
  - **Login repaginado**: bloco "Contas da demo" virou listinha com badge de papel por linha + `user-select:all` no e-mail (clique-copia), senha única destacada, dominínio explicado uma vez só;
  - **Busca client-side** (5 linhas de JS inline, sem build): `input[data-busca="id"]` filtra `<tbody tr>` de tabelas **ou** cards com `data-linha`; aplicado em **Usuários** (agora tabela de verdade: Usuário/Setor/Turno/Papéis/Status/Ações, 57 linhas filtráveis), **Matriz de conformidade** (thead formal + busca por nome/setor), **Tickets** e **Justificativas** (cards filtráveis); formulário "Novo usuário" virou grid com labels;
  - Espaçamentos: `form-inline` gap maior, `.linha-aula` com coluna principal flexível (ticket não espreme mais o texto), inputs distanciados no `/verificar` (botão full-width)');
- **Artefatos:** `docs/roteiro-demo.md`, `resources/views/auth/login.blade.php`, `resources/views/portal/rh/{usuarios,conformidade,tickets,justificativas,certificados}.blade.php`, `resources/views/verificar/busca.blade.php`, `public/css/app.css`, `layouts/portal.blade.php` (JS de busca)
- **Decisões / por quê:** busca no cliente (sem servidor) porque as listas são pequenas/passadas inteiras e a demo não pode depender de rede; tabelas substituem cards-exclusivos onde a densidade de informação manda (57 pessoas);
- **Prompts-chave:** "…botões e inputs muito colados… textos muito colados na /login… deixe a lista de e-mails e a senha numa listinha mais bem feita… caracteres errados como UsuÃ¡rios… para as listas, algo mais próximo de tabelas com pesquisa";
- **Resultados:** 93/305 verde; rebuild aplicado localmente; screenshots conferidos (login, usuários, matriz, tickets) — nenhum mojibake restante nas páginas servidas.
- **Status:** Concluído.

---

## Nº 38 · CÓDIGO · Copy amigável: fim dos códigos RF/Q na interface e e-mails @exemplo.com

- **Quando:** 21/10/2026 04:30 → 05:00
- **O que foi feito:**
  - Team pediu: deixar tudo **amigável para quem usa a plataforma** — sem códigos internos (RF** e Q*) e sem notas de desenvolvedor (ex.: "a biometria aqui NÃO é usada… Q10");
  - **Limpeza de copy**: 39 ocorrências RF + Q + "fachada" em 12 views → títulos sem jargão ("Tickets", "Certificados", "Usuários"), botões diretos ("Emitir certificado", "Resolver", "Reemitir (nova versão)", "Registrar presença manual"), avisos em linguagem de uso ("Aula ainda não iniciada — a validação abre quando o instrutor iniciar"); Telefonista "📷 Facial (fachada)" → "📷 Reconhecimento facial"; Telefonista matriz mantida com subtítulo "quem precisa fazer o quê?";
  - **Domínio de e-mail** `@usinalins.local` → `@exemplo.com` (UserSeeder, DadosExtrasSeeder, README, teste); telefonista SMTP/carta deixou de apontar '.local' p/ manter coerência;
  - **Login**: listinha de contas agora mostra **e-mail completo copiável de uma vez** (user-select all, clique copia), senha única destacada, instrução "clique no e-mail para copiar" — acabou a necessidade de montar e-mail em duas partes; Telefonista validada: os 5 e-mails completos são servidos;
  - Só dev no ar: stack de demo desligada (`compose down`; postgres/mailpit refeitos pelo dev compose) e dev `:8000` com **login real testado** com novo domínio;
  - Telefonista de botões: `.btn` unificado com familia de fonte herdada (era padrão do navegador) e semântica já consistente: verde primário (ação principal), laranja accent (atenção: facial, reemitir, cancelar vagas), outline (secundária);
  - **93/305 verde** após tudo (o seeder novo estoque regenerado: contas com @exemplo.com).
- **Artefatos:** 12 views + seeders + README + testes + app.css/login
- **Decisões / por quê:** códigos RF/Q são vocabulário de documentação interna — ficam em README/roteiro/relatórios PDF, desaparecem da UI; e-mail completo na lista elimina passo mental na apresentação;
- **Prompts-chave:** "remova mensagens que são pros desenvolvedores… troque o e-mail dessas contas por @exemplo.com… os códigos estranhos inclui os Q*" — pedido de produto, atendido varredura por padrão;
- **Resultados:** telefone 93/305; dev on-line em :8000 com login validado após >= reseed.
- **Status:** Concluído.

---

## Nº 39 · DOCUMENTAÇÃO · Relatório final da entrega (ATO 1 + ATO 2) compilado

- **Quando:** 21/10/2026 11:12
- **O que foi feito:**
  - Compilação do documento único para o PDF de entrega exigido no enunciado ("todos os prompts criados pelo grupo"): **ATO 1** em síntese (tecnologias, arquitetura, metodologia) e **ATO 2** detalhado (desafio e critérios da banca, acordos AT01–AT04, requisitos RF01–RF28/RNF/SEED, design system, **39 prompts cronológicos polidos com resultado**, transcrição Q1–Q35, passos do desenvolvimento, diário de bordo das 38 sessões e estado final com evidências);
  - Fontes consolidadas: `journey-log.md`, docs (requisitos, questionário, design, stack, acordos, deploy-coolify, roteiro-demo), README e `composer.json` (versões reais: Laravel 13/PHP 8.4);
  - **Governança de manutenção do relatório**: nova seção "Manutenção do relatório final de entrega" na skill `registro-hackathon` (regras por tipo de mudança: prompt → §6, sessão → §9, cronologia → §8, números → ATO 1/§10) + item no checklist final; bloco `<processo-presenza>` no `AGENTS.md` para que **toda sessão de IA** saiba que journey-log e relatório andam juntos, sem esperar pedido do time.
- **Artefatos:** `docs/relatorio-final.md`, `.agents/skills/registro-hackathon/SKILL.md`, `AGENTS.md`
- **Decisões / por quê:** a banca exige um PDF com todos os prompts e avalia clareza/completude; consolidar tudo em um único .md facilita a conversão — e a manutenção automática (via skill + AGENTS.md) garante que o relatório nunca fique defasado conforme o time pedir atualizações.
- **Prompts-chave:** "faça um .md que contenha: ATO 1 (tecnologias, arquitetura, metodologia) e ATO 2 detalhado (prompts, passos, diário de bordo, requisitos) — tudo necessário para o PDF final exigido em problemas.docx"; emenda: "documente também (na skill registro-hackathon ou AGENTS.md) para que a IA vá completando o relatório conforme eu for pedindo atualizações".
- **Resultados:** `docs/relatorio-final.md` criado e pronto para conversão em PDF; regra de sincronização ativa na skill e no `AGENTS.md`.
- **Status:** Concluído.

---

## Nº 40 · CÓDIGO · Hotfix da chamada + seed com termo LGPD aceito por todos

- **Quando:** 21/10/2026 09h30 → 11h17
- **O que foi feito:**
  - **Hotfix P0**: ParseError em `aula.blade.php` (`elseif` órfão — o meu edit anterior tinha engolido a linha `@if ($aula->status === StatusAula::Agendada)`); `php -l` + suíte full verde de novo; **Docker Desktop caiu de novo** nesta janela (processo zero) — reaberto e dev reerguido (`:8000` 200 confirmado);
  - **Seed LGPD**: time pediu todas as contas já com aceite do termo — primeira tentativa no `UserSeeder` rodou **antes** do `CatalogoSeeder` (0 aceites); movido para o `CatalogoSeeder`, logo após criar o termo ativo, com `INSERT ... SELECT` + `ON CONFLICT DO NOTHING` → **44/44 contas com aceite**; login agora vai direto ao portal (smoke: `/portal` 200, sem gate);
  - Tela do termo continua prevista na demo ao **publicar nova versão** em Configurações (roteiro dos 4:00 inalterado);
  - Coerência de domínio: SMTP/catálogo (`smtp.exemplo.com`, `presenza@exemplo.com`) e README (nota do aceite pré-assinado) alinhados; roteiro-demo atualizado (passo do termo sai do 0:30, e-mails completos);
  - **93/305 verde**.
- **Artefatos:** `database/seeders/CatalogoSeeder.php`, `database/seeders/UserSeeder.php`, `resources/views/portal/aula.blade.php`, `docs/roteiro-demo.md`, `README.md`, `docs/relatorio-final.md`
- **Decisões / por quê:** demo não pode começar com portão travando o fluxo — aceite pré-assinado mantém o RF04 real (nova versão re-pede aceite) sem espiar a demo no login;
- **Prompts-chave:** "faça com que todos os usuários no seed já tenham aceitado os termos da LGPD" (+ hotfix reportado com stack trace colado pelo time);
- **Resultados:** aceites 44/44 no banco; login→portal direto comprovado; suíte verde; dev de pé.
- **Status:** Concluído.

---

---

_Última atualização: 21/10/2026 11h17 — gerado pela skill `registro-hackathon`._

## Nº 41 · DECISÃO · Assistente de IA público: requisitos fechados (Q36–Q59) e docs do módulo

- **Quando:** 21/10/2026 11h47
- **O que foi feito:**
  - Requisitos do **assistente de IA público** levantados com o time em 4 rodadas de questionário (Q36–Q59), após verificação de fatos: catálogo de modelos gratuitos ($0), modo servidor do agente (API HTTP) e necessidade de **API key** mesmo para modelos free;
  - Consenso: widget **público "Assistente Presenza"** (sem login) nas páginas abertas, **só leitura**, respostas baseadas na documentação do projeto + **mapa de páginas novo**; **runtime do agente em contêiner próprio no compose** (exceção ao "sem Node no MVP" aceite pelo time); modelo **big-pickle** ($0, trocável via `.env`); zero dados pessoais; resposta em bloco único com carregamento; conversas gravadas (IP + data) visíveis só ao Admin, com aviso no widget; **sem limite de taxa — risco aceito**;
  - Artefatos criados: transcrição completa do questionário, **seção 7 dos requisitos** (RF29–RF34, RNF07–RNF09), **mapa de páginas** (contexto do agente) e **mockup do widget** para aprovação;
  - Próximo passo: aprovação do mockup → implementação (contêiner, endpoint, widget, painel Admin, roteiro) na janela que fecha às **14:00** (apresentação às 14h30).
- **Artefatos:** `docs/questionario-requisitos-assistente-ia.md`, `docs/requisitos-p1.md` (seção 7), `docs/mapa-paginas.md`, `docs/design-assistente-ia.md`, `docs/relatorio-final.md` (P-42)
- **Decisões / por quê:** o time quis o runtime do agente embutido no produto (e não só os modelos), aprovando a exceção de infraestrutura; a fonte de conhecimento é a documentação para dar contexto completo às respostas; o fim do roteiro de demo mostra o diferencial de IA.
- **Prompts-chave:** "acha que dá pra planejar colocar um assistente de IA usando o OpenCode e um modelo gratuito nesse sistema? planeje e me pergunte coisas para elaborar os requisitos até que não restem dúvidas sobre" — polido no relatório §6 (P-42).
- **Resultados:** RF29–RF34 + RNF07–09 escritos e aprovados ("Aprovado, seguir"); mockup pronto para aprovação final; implementação não iniciada.
- **Status:** Concluído (documentação) · Parcial (implementação pendente de aprovação do mockup).

---

_Última atualização: 21/10/2026 11h47 — gerado pela skill `registro-hackathon`._

## Nº 42 · DOCUMENTAÇÃO · Correção do nome da equipe no PDF + anexo dos prompts originais das conversas

- **Quando:** 21/10/2026 11h27 → 11h55
- **O que foi feito:**
  - Corrigido o nome do integrante truncado ("João Marcel" → **João Marcelo dos Santos Silva**) em todos os artefatos que alimentam o PDF: capa do `docs/prompts-ia.pdf` (`resources/views/pdf/documento.blade.php`), `README.md`, `docs/relatorio-final.md` e cabeçalho do `journey-log.md`;
  - **Extração do histórico real de conversas** do agente (banco SQLite de sessões): **53 prompts originais** do time em **6 sessões** (19–21/10), agrupados por sessão, com horário, texto **verbatim** e resultado de cada um;
  - Anexo novo `docs/prompts-conversas.md` (C-01…C-53) gerado por script reproduzível versionado no repo: `scripts/extrair-prompts-conversas.php` (roda no contêiner `presenza/php` com o banco do agente montado em `/data`);
  - Comando `presenza:pdf-prompts` ampliado (padrão agora inclui `docs/deploy-coolify.md`, `docs/questionario-requisitos-assistente-ia.md` e `docs/prompts-conversas.md`) e **PDF regerado** (1,8 MB/31 pág. → **2,4 MB/39 pág.**) com o anexo das conversas e o nome corrigido — conferido por extração do texto do PDF (nome na capa, C-01…C-53, Q36–Q59 e stack trace presentes).
- **Artefatos:** `docs/prompts-conversas.md`, `scripts/extrair-prompts-conversas.php`, `docs/prompts-ia.pdf`, `app/Console/Commands/GerarPdfPrompts.php`, `resources/views/pdf/documento.blade.php`, `README.md`, `docs/relatorio-final.md`
- **Decisões / por quê:** a banca avalia **completude do prompt** — o anexo mostra o texto original, sem edição, permitindo a conferência de que a versão polida (§6 do relatório) preservou o sentido; o relatório continua sendo a base do PDF final, agora com §6/§8/§9/§10 sincronizados.
- **Prompts-chave:** "o nome do João Marcelo está com o Marcelo cortado em 'João Marcel dos Santos Silva' no docs/prompts-ia.pdf. Inclua nele também os prompts relevantes das conversas que conseguir pegar, é importante" — polido no relatório §6 (P-43).
- **Resultados:** nome correto em 4 artefatos; 53 prompts originais anexados ao PDF de prompts (39 páginas); extração reproduzível por script versionado; PDF regerado e verificado.
- **Status:** Concluído.

---

## Nº 43 · CÓDIGO · Assistente de IA público implementado (RF29–RF34) — runtime OpenCode, widget e auditoria

- **Quando:** 21/10/2026 12h10 → 13h45
- **O que foi feito:**
  - **Runtime do agente**: imagem própria `docker/opencode/Dockerfile` (Node 22 + `@opencode/cli@2`) embutindo `docs/`; agente `atende` em `docs/.opencode/opencode.json` com modelo `opencode/big-pickle`, system PT-BR e permissões **somente `read`/`glob`/`grep`** (deny `*`, deny `external_directory`); serviço `assistente` no compose (rede interna `:4096`, Basic auth com `OPENCODE_SERVER_PASSWORD` fixa de demo) e `app` com `ASSISTENTE_URL/MODELO/SENHA` + `depends_on`;
  - **Backend Laravel**: migration/model `assistente_mensagens` (pergunta, resposta, estado, IP, sessão da aba), `App\Services\RuntimeAssistente` (HTTP Basic → `POST /api/session` → `prompt` → `wait` → `message`, sessão cacheada 30 min) e `AssistenteController` (`responder` público + `conversas` só Admin, gate `temPapel('admin')`); rota pública `POST /assistente` com CSRF excluído no `bootstrap/app.php`; bloco `assistente` em `config/services.php`; `.env`/`.env.example` documentados;
  - **Frontend**: widget `resources/views/assistente/widget.blade.php` + `public/css/assistente.css` (tokens do design system, aviso de gravação/LGPD, loader e resposta em bloco único, histórico só na aba) incluído em welcome, login (`layouts/guest`), `/verificar` e resultado; view `portal/rh/assistente.blade.php` paginada e link "Conversas do assistente" na sidebar **só para Admin**;
  - **Testes**: `tests/Feature/AssistenteTest.php` cobre sucesso (registra conversa + IP), runtime fora do ar (cortesia 200 + estado `fallback`), validação (vazio/501 chars) e autorização (Admin vê, RH recebe 403), além do widget nas páginas públicas — **suíte completa 98 testes / 325 asserções verde**;
  - **Correções de contrato pegas no E2E**: o schema real do runtime exige `model: {id, providerID}` (modelID causava 400) e `wait` retorna **204**; `.dockerignore` passou a re-incluir `docs/` para a imagem do assistente; build da imagem validado (`hackaton-assistente`) e **smoke real** em contêiner respondendo sobre fila de espera e `/verificar` — **sem `OPENCODE_API_KEY`** (modelos free do gateway funcionam anônimos; a variável fica opcional e fora do git).
- **Artefatos:** `docker/opencode/Dockerfile`, `docs/.opencode/opencode.json`, `docker-compose.yaml`, `config/services.php`, `bootstrap/app.php`, `routes/web.php`, `.dockerignore`, `database/migrations/2026_10_21_120000_criar_assistente_mensagens.php`, `app/Models/AssistenteMensagem.php`, `app/Services/RuntimeAssistente.php`, `app/Http/Controllers/AssistenteController.php`, `resources/views/assistente/widget.blade.php`, `public/css/assistente.css`, `resources/views/portal/rh/assistente.blade.php`, `layouts/portal|guest`, `welcome`, `verificar/{busca,resultado}`, `tests/Feature/AssistenteTest.php`, `docs/stack.md`, `docs/acordos-de-trabalho.md`, `docs/roteiro-demo.md`, `README.md`
- **Decisões / por quê:** runtime isolado no compose mantém o app sem build (exceção aprovada, AT02); o agente só lê `docs/` e nunca toca banco/usuários (zero PII); registro em banco com IP atende à auditoria RF33 sem expor conversas ao RH; fallback de cortesia garante a demo mesmo sem rede (RNF08); sem rate limit conforme decisão registrada (Q45/RNF09).
- **Prompts-chave:** "mockup aprovado, mete bala" → polido no relatório §6 (**P-44**): implementar o assistente exatamente conforme mockup + seção 7, com runtime em contêiner, zero PII, cortesia em falha e auditoria só do Admin, rodando a suíte e registrando a sessão.
- **Resultados:** assistente público funcional de ponta a ponta (widget → Laravel → runtime → resposta), imagem Docker construída e testada em contêiner, 98/325 verde, tela Admin de auditoria e roteiro de demo atualizado com o bloco de 4:30–4:45.
- **Status:** Concluído.

---

## Nº 44 · CONFIGURAÇÃO · Domínio público de https:// para http://

- **Quando:** 21/10/2026 13h30–13h36
- **O que foi feito:**
  - Time alterou o protocolo do domínio público (`hackathon.dpicinato.com`) de `https://` para `http://`;
  - `docker-compose.yaml`: os 2 defaults `APP_URL` (app + scheduler) passam a `http://` — QR (RF21) e links de e-mail dos e-mails de lembrete/convite seguem o valor configurado;
  - `docs/deploy-coolify.md` (runbook: acessos + smoke pós-redeploy) e `docs/relatorio-final.md` (estado final, stack, evidências, §8, §9 e §10) atualizados para `http://`;
  - Verificação de segurança: nenhum middleware força HTTPS na aplicação (sem `forceScheme`/`forceRootUrl`) — nada quebra em `http://`;
  - `.env` local segue `http://localhost:18000`, sem relação com o domínio público.
- **Artefatos:** `docker-compose.yaml`, `docs/deploy-coolify.md`, `docs/relatorio-final.md`
- **Decisões / por quê:** histórico do journey-log (sessões 32–35) fica como registrado (protocolo da época); documentos vivos apontam sempre para o estado atual.
- **Prompts-chave:** "alteramos o protocolo do domínio de https:// para http://"
- **Resultados:** 13 referências vivas atualizadas (2 no compose, 2 no runbook, 9 no relatório); conferido que não há força de HTTPS no código.
- **Status:** Concluído.

---

## Nº 45 — CÓDIGO — Assistente estável em produção + widget em todas as telas

- **Quando:** 21/10/2026 14h05–14h35
- **O que foi feito:**
  - **Diagnóstico do fallback no domínio público:** reprodução com 4 chamadas mediu a primeira em **60,6 s** (timeout do cliente HTTP) e as seguintes em ~20 s — latência de cold start/variação do modelo free, não erro de configuração; a falha virava a mensagem de cortesia (RNF08);
  - **Correção de resiliência:** `RuntimeAssistente` ganhou retry (2 tentativas) **apenas para falhas rápidas** (<15 s — sessão do runtime morta após restart/deploy), descartando a sessão em cache; `ASSISTENTE_TIMEOUT` padrão passou de 60 s para **100 s** (`config/services.php` + `docker-compose.yaml`);
  - **Teste de regressão:** `falha_rapida_descarta_sessao_morta_e_repete` simula sessão 404, recria e responde;
  - **Widget em todas as telas:** incluído em `layouts/portal.blade.php` (dashboard, RH, Admin, conta) e `layouts/app.blade.php`, além das páginas públicas já cobertas (welcome, login, `/verificar`, resultado); novo teste confirma o widget no portal logado; RF29 e `docs/design-assistente-ia.md` atualizados (o widget segue público e não lê a sessão do usuário);
  - **Deploy e validação:** push `6e06b75`, Coolify reconstruiu (breve 502 durante o restart); após o deploy, login Admin no domínio confirmou o widget no portal e **3/3 respostas `ok`** no endpoint público (15–25 s).
- **Artefatos:** `app/Services/RuntimeAssistente.php`, `config/services.php`, `docker-compose.yaml`, `resources/views/layouts/portal.blade.php`, `resources/views/layouts/app.blade.php`, `tests/Feature/AssistenteTest.php`, `docs/requisitos-p1.md` (RF29), `docs/design-assistente-ia.md`, `docs/mapa-paginas.md`, `docs/roteiro-demo.md`, `README.md`, `docs/relatorio-final.md`
- **Decisões / por quê:** timeout maior em vez de trocar o modelo aprovado; retry só quando a falha é rápida (repetir timeout dobraria a espera e pioraria a demo); widget no portal por pedido do time — como o endpoint é público e não recebe contexto de sessão, não há exposição de dados do usuário logado.
- **Prompts-chave:**
  - "O assistente está indisponível neste momento. Fale com o RH da Usina ou abra um ticket pelo portal. Consegue entender o por quê e corrigir?" (P-46);
  - "Quero que adicione o widget da IA em qualquer tela, não só a home de fora." (P-47).
- **Resultados:** suíte **100 testes / 331 asserções verde** (7/7 do módulo); deploy validado no domínio com widget no portal logado e 3 respostas reais consecutivas; documentação viva sincronizada.
- **Status:** Concluído.

---

## Nº 46 · CÓDIGO · Simulação de câmera no reconhecimento facial (branch `reconhecimento-facial`)

- **Quando:** 21/10/2026 15h35 → 16h08
- **O que foi feito:**
  - Time pediu reconhecimento via **câmera do navegador, simulado**: "veja uma forma rápida… ou simule com câmera… e faça dar erro, porque não cadastramos nossos rostos";
  - **Overlay de câmera real** (`getUserMedia`) no layout do portal, ligado a todo `form[data-facial]` (botão facial do colaborador na home e da chamada do instrutor): "Posicione o rosto no quadro" → contagem **3‑2‑1** → captura com efeito de flash → "Procurando rosto no cadastro biométrico…" → **recusa sempre**: "Rosto não reconhecido — melhor similaridade 34‑52% (limiar 85). Nenhum rosto correspondente no cadastro." + dica por papel (`data-dica`: colaborador → peça homologação ao instrutor; instrutor → use a homologação manual);
  - **O formulário facial nunca é enviado** (preventDefault em todo submit de `data-facial`) — a decisão de registrar passa pela homologação manual (senha + motivo), que é a feature real antifraude;
  - Imagem da câmera **não sai do navegador** (zero armazenamento — coerente com LGPD); degradação graciosa sem câmera/permissão/HTTPS (`rec-status-erro`, fecha sozinho);
  - **Bug pegado no E2E** (agent-browser): a `<div id="rec-overlay">` estava **depois** do `<script>` → `getElementById` null → `return` silencioso → nenhum listener anexado → **submit nativo registrava presenças fantasma** (Alexandre/Fabrício/Leandro); movido para **antes** do script + `#rec-overlay[hidden]{display:none}` (o `display:flex` anulava o `hidden`); presenças/auditorias fantasma apagadas do banco (transação única em `-c` com 2 statements tinha rolado tudo no erro — lição);
  - Flash de duplicidade em `PresencaService` limpo ("(RF16)" removido — regra da sessão 38);
  - Teste completo com `MediaStream` falso: contagem → captura → similaridade 36,4 → **sem navegação, sem registro** ✔; banco intacto (3 presenças na aula 3).
- **Artefatos:** `resources/views/layouts/portal.blade.php`, `resources/views/portal/home.blade.php`, `resources/views/portal/aula.blade.php`, `public/css/app.css`, `app/Services/PresencaService.php`
- **Decisões / por quê:** a câmera dá a experiência visual do RF03 sem depender de enrollment real; a recusa modelada (similaridade < limiar) é a história honesta para a demo e leva naturalmente à homologação manual; simulação inteira no cliente (_facade_ de servidor intacta — 100/331).
- **Prompts-chave:** "na branch reconhecimento-facial, adicione o reconhecimento facial corretamente usando a camera via navegador… ou então simule… faça com que sempre dê erro porque não cadastramos nossos rostos" — polido no relatório §6 (P-48).
- **Resultados:** suíte **100 testes / 331 asserções verde**; E2E no navegador comprovando recusa sem registro; UI nos tokens do design system.
- **Status:** Concluído.

---

_Última atualização: 21/10/2026 16h09 — gerado pela skill `registro-hackathon`._
