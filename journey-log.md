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

---

_Última atualização: 20/10/2026 22:55 — gerado pela skill `registro-hackathon`._
