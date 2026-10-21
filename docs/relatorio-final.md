# Presenza — Relatório Final do Hackathon FATEC & ETEC · Usina Lins

> **Presenças reais, certificados à prova.**
> Plataforma de gestão de treinamentos internos (RH) com presença validada por reconhecimento facial e certificado digital verificável publicamente por QR/código.

| | |
|---|---|
| **Hackathon** | 3º Hackathon FATEC & ETEC — parceiros Usina Lins e CetaCeo |
| **Datas** | 20/10/2026 (13h30) a 21/10/2026 (14h00) · Apresentações 21/10, 14h30–17h30 (5 min/grupo) |
| **Problema escolhido** | **P1 — Gestão de Treinamentos Internos (RH)** (decidido 20/10, 15h33) |
| **Produto** | **Presenza** |
| **Equipe** | Dimas Matheus Gomes Picinato · Sara Silva Marsola · João Marcelo dos Santos Silva (principais) · Bruno Miranda Barcelos Tremeschin Pereira Melo · João Lucas Martins Pazin · Matheus Roberto Baraúna da Silva · Matheus Pereira Jesus · Lucas Yuuji Murayama (coautores) |
| **Entrega** | Este documento em PDF — contém **todos os prompts criados pelo grupo** para o desenvolvimento, conforme exigido no enunciado |
| **Estado final** | 28/28 RFs implementados · 93 testes / 305 asserções verdes · aplicação no ar em `https://hackathon.dpicinato.com` · módulo **assistente de IA** (RF29–RF34) com requisitos e mockup aprovados, implementação na janela da demo |

**Como ler este relatório:** o **ATO 1** é a síntese (tecnologias, arquitetura e metodologia). O **ATO 2** é o relato completo e detalhado: o desafio, os acordos, os requisitos, o design, **todos os prompts relevantes em ordem cronológica com seus resultados**, a transcrição do questionário de requisitos, os passos do desenvolvimento, o diário de bordo (42 sessões registradas por IA) e as evidências finais.

---

# ATO 1 — SÍNTESE

## 1.1 Tecnologias usadas

| Camada | Tecnologia | Papel no projeto |
|---|---|---|
| Backend | **Laravel 13 / PHP 8.4** (decisão original: Laravel 11 / PHP 8.3 — ajustada na execução, ver ATO 2 §6) | Framework único da aplicação (rotas, Eloquent, migrations, seeders, scheduler, mail) |
| Frontend | **Blade + Alpine.js vendored, CSS com tokens — sem build de Node** | Portais server-rendered, menos peças para falhar na demo |
| Banco de dados | **PostgreSQL 16 + extensão pgvector** | Persistência formal + `vector(128)` para os embeddings biométricos (similaridade por distância cosseno em SQL) |
| Autenticação (scaffold) | **Laravel Breeze (Blade)** | Login/senha + reset por e-mail (RF01/RF28) |
| PDF | **dompdf** (`barryvdh/laravel-dompdf`) | Certificados e relatórios em PDF (RF21/RF28) + o próprio PDF de prompts |
| QR Code | **chillerlan/php-qrcode** | QR do certificado → página pública de verificação (RF21) |
| E-mail (dev) | **Mailpit** | Captura de todos os e-mails da demo com UI web |
| Infra | **Docker + Docker Compose** (dev e homologação) | `postgres` + `mailpit` (dev); `app` + `scheduler` buildados (homolog) |
| Deploy | **Coolify em VPS** | Domínio `https://hackathon.dpicinato.com` |
| Testes | **PHPUnit** (feature) rodando no PostgreSQL real | 93 testes / 305 asserções |
| Versionamento | **Git / GitHub** | Processo e código versionados |
| IA | **IA generativa de preferência do time, operada via agente OpenCode (CLI)** com *skills* (`grilling`, `ui-ux-pro-max`, `frontend-design`, `ui-styling`, `design-system`, `web-design-guidelines`, `laravel-best-practices`, `testing-best-practices`, `registro-hackathon`) | 100% do código escrito por IA; decisões sempre com o time (AT01) |

## 1.2 Arquitetura usada no projeto

**Monolito Laravel server-rendered, orientado a portais por papel**, com regras de negócio concentradas em services de domínio e biometria atrás de uma fachada simulável.

```
Navegador — Blade + Alpine.js (sem build)
├── Página pública: /verificar/{código}  (QR/código/link, sem login)
└── Portais autenticados: Colaborador · Líder · Instrutor · RH/Admin
        │  middleware: auth → ExigirAceiteTermo (RF04) → escopo por papel (RF02/RF05)
        ▼
Laravel 13 (PHP 8.4) — aplicação única
├── Controllers por portal (Portal · Termo · Aula · Chamada · RH · Verificação pública)
├── Services de domínio (regras de negócio):
│     PresencaService (facial/homologação, anti-fraude RF16)
│     VagaService (fila de espera e promoção automática RF09)
│     CertificadoService (emissão/reemissão imutável RF19–RF22)
│     EscopoGestao (trait: RH/Admin = tudo; Líder = só o próprio setor)
├── 18 Models Eloquent + 8 Enums de domínio
└── 5 blocos de migrations: identidade · treinamento · tickets · certificados · sistema
        │
        ▼
PostgreSQL 16 + pgvector  (embeddings biométricos vector(128), unique anti-fraude 1 presença/pessoa/aula)

Transversal: Auditoria de eventos sensíveis (RNF01) · Termo LGPD versionado + aceites (RF04)
             Notificações in-app + SMTP (RF23–24) · Scheduler de lembretes (RF25)

Infra (AT02): docker-compose.dev.yaml = postgres (:5433) + mailpit (:8025)
              docker-compose.yaml   = idem + app (:18000→8000) + scheduler, env fixo,
              código e vendor EMBUTIDOS na imagem (local = VPS)
Deploy: Coolify (VPS) → https://hackathon.dpicinato.com
```

Pontos-chave: a biometria nunca toca hardware real — é uma **fachada simulável** (RNF06) com embeddings no pgvector; a integridade da chamada é garantida por **constraints no banco** (unique), não só por código; o certificado é **imutável por versão** (reemissão mata o código antigo na verificação pública).

## 1.3 Metodologia de desenvolvimento

- **Desenvolvimento dirigido por IA com gate de aprovação humana (AT01):** todo o código foi escrito por IA; toda dúvida ou alternativa virava um questionário ao time (opções + recomendação) antes de prosseguir — 59 decisões registradas (Q1–Q59).
- **Requisitos antes da arquitetura:** questionário guiado interativo (skill `grilling`) com o time — 30 perguntas em 4 rodadas + ajustes — consolidado em RF01–RF28, RNF01–06 e SEED01–03; só depois design e stack.
- **Design system antes das telas:** paleta "verde agro + laranja de segurança", tipografia Lexend/Source Sans 3 e tokens de status mapeados 1:1 nos RFs.
- **Docker-first (AT02):** serviços em contêiner no dev; topologia de homologação/demonstração estende os mesmos serviços com a app buildada.
- **Instalações instruídas (AT03):** a IA nunca instalou nada direto na máquina do time — emitia passos com justificativa.
- **Testes como regressão da demo:** suíte PHPUnit feature criada junto com cada bloco de RFs, sempre verde no PostgreSQL real.
- **Mocks por seed:** 57 contas fictícias com embeddings biométricos fake, treinamentos/aulas/presenças/tickets/certificados nos 3 cenários de demonstração (SEED01–04) — a demo não depende de câmera, hardware ou rede.
- **Processo registrado (AT04):** skill `registro-hackathon` alimentou o `journey-log.md` (diário de bordo) ao fim de cada sessão — 42 registros cronológicos com prompts, decisões e resultados.
- **Sequência executada:** problemas → escolha do P1 → requisitos → design → stack → scaffold → domínio (migrations/models/seed) → telas/fluxos → testes → blocos RH/scheduler → deploy (Coolify) → polimento de UI → documentação/entregáveis.

## 1.4 Resultados-chave

- **28/28 requisitos funcionais** (RF01–RF28) implementados e cobertos por testes: **93 testes / 305 asserções verdes** no PostgreSQL real.
- Demo de 5 min com "momento uau": reemissão de certificado → código antigo vira **NÃO ENCONTRADO** na verificação pública ao vivo.
- Aplicação publicada: `https://hackathon.dpicinato.com` · códigos de demonstração: `PZ-2025-000101` (VÁLIDO) e `PZ-2024-000087` (EXPIRADO).

## 1.5 Quesitos da banca → como o projeto atende

| Quesito (enunciado) | Como atendemos |
|---|---|
| Criatividade | Facial como anti-fraude de presença + certificado verificável publicamente por qualquer leitor de QR; reemissão imutável |
| Clareza do prompt | Prompts numerados, polidos (contexto → objetivo → restrições → formato) e cada um com **resultado documentado** (ATO 2 §6) |
| Completude do prompt | Transcrição integral do questionário interativo Q1–Q35 + Q36–Q59 (assistente de IA) + 43 prompts de desenvolvimento polidos + **53 prompts originais das conversas** (ATO 2 §6–§7 + anexo `docs/prompts-conversas.md`) |
| Arquitetura + design de UI | Monolito em camadas com services de domínio + design system verde/laranja com tokens de status ligados aos RFs (ATO 1 §2, ATO 2 §4) |
| Uso adequado de mocks | Seed com 57 contas e embeddings fake, fachada biométrica simulável (RNF06), Mailpit como mock de e-mail (ATO 2 §3/§9) |

---

# ATO 2 — RELATO DETALHADO

## §1. O desafio (`problemas.docx`)

Nos dias 20 e 21 de outubro, 3º Hackathon FATEC & ETEC em parceria com a Usina Lins. Cada time (até 3 estudantes) escolhe **um** de três problemas e **necessariamente utiliza IA** para desenvolver a solução.

**Cronograma:** 20/10 13h30 apresentação dos problemas · 20/10 14h30 → 21/10 14h00 desenvolvimento · 21/10 14h30–17h30 apresentações (5 min por grupo, solução em execução).

**Entrega:** *"Na apresentação, cada grupo deverá entregar, em arquivo .PDF, todos os prompts criados pelo grupo para o desenvolvimento da solução."* — é exatamente o que este documento contém.

**Avaliação pela banca (técnicos da Usina e da CetaCeo):** criatividade · clareza do prompt · completude do prompt · desenvolvimento da arquitetura e do design da interface de usuário · uso adequado de mocks.

### Os três problemas

1. **P1 — Gestão de Treinamentos Internos (RH):** controle manual de vagas e presença (planilhas/e-mail), certificados em sistema externo, sem visibilidade de convocados/presentes/faltas e **sem como validar um certificado antigo**. Melhoria proposta: plataforma com cadastro de treinamentos, reserva pelo líder, chamada com reconhecimento facial e **certificado digital com código de verificação pública**.
2. **P2 — Controle de Refeitório (~600 refeições/dia, 3 turnos):** catraca facial, 1 entrada por janela de refeição, rateio empresa×funcionário, cardápio online, totem de feedback.
3. **P3 — Controle de Visitas Externas:** link de uso único → auto-cadastro facial → aprovação da segurança → acesso que expira sozinho nos leitores.

### Comparativo e decisão (sessões 2 e 7)

| Critério | P1 Treinamentos | P2 Refeitório | P3 Visitas |
|---|---|---|---|
| Hardware real necessário | só câmera na chamada | catraca física | leitores Hikvision |
| Viabilidade MVP em 24h | ✅ alta | ⚠️ rateio complexo | ✅ média |
| "Wow" para a banca | validação de certificado ao vivo no celular | catraca | expiração do acesso |
| Risco LGPD | médio (tratado com termo versionado) | alto | alto |

**Decisão (20/10, 15h33): P1**, com P3 como plano B. P2 descartado por depender de hardware físico e rateio complexo demais para a janela.

## §2. Acordos de trabalho (governança do processo)

Definidos com o time em 20/10 e seguidos durante todo o hackathon:

- **AT01 — Gate de aprovação:** *"toda vez que houver qualquer dúvida, ou mais de uma forma de fazer algo, a IA deve questionar o time — apresentando opções com recomendação justificada — e aguardar a aprovação antes de prosseguir."* Mecanismo prático: questionário interativo do OpenCode (opções numeradas, recomendado em primeiro lugar).
- **AT02 — Orquestração com Docker:** dev/testes = compose só com serviços (banco, mail-catcher); homologação/demo = segundo compose que **estende** os serviços e adiciona a aplicação buildada com variáveis fixas (hosts = nomes de serviço).
- **AT03 — Instalação instruída:** ferramentas nunca instaladas diretamente pela IA na máquina do time; sempre passo a passo instruído, e o que for pesado roda em contêiner.
- **AT04 — Processo registrado:** toda sessão registrada no `journey-log.md` (skill `registro-hackathon`); prompts relevantes são polidos e salvos para o PDF de entrega.

## §3. Requisitos do produto (consolidados)

Fonte: questionário interativo de requisitos (ATO 2 §7) → `docs/requisitos-p1.md`, aprovado pelo time.

**Premissas fixadas:** PostgreSQL · reconhecimento facial **só** para validar presença (homologação manual como exceção) · login/senha nos portais com reset por e-mail · e-mail real via SMTP configurável.

### Atores e permissões (RF02 — papéis acumuláveis; Líder tem todas as permissões no seu setor)

| Domínio | Colaborador | Líder (seu setor) | Instrutor (suas aulas) | RH | Admin | Público |
|---|---|---|---|---|---|---|
| Usuários | — | CRUD dos liderados | — | CRUD total | CRUD total | — |
| Treinamentos/Aulas | ver os seus | criar/editar do setor | iniciar/encerrar/ajustar | CRUD total | CRUD total | — |
| Vagas/Reservas | ver as suas | reservar/devolver nominais | — | CRUD total | CRUD total | — |
| Chamada/Presença | validar a própria (facial, aula ativa) | ver do time | facial / homologar manual | ver tudo | ver tudo | — |
| Tickets | abrir (próprio) | resolver dos liderados | — | resolver todos | resolver todos | — |
| Justificativas | submeter via ticket | registrar/alterar status | — | registrar/alterar | idem RH | — |
| Certificados | ver os seus | ver do time | — | emitir manualmente | emitir | — |
| Catálogos | — | — | motivo novo no ato | manter | manter | — |
| Config (SMTP/infra) | — | — | — | — | **só Admin** | — |
| Relatórios | — | do time + export | das suas aulas | completo + export | completo + export | — |
| Verificação de certificado | — | — | — | — | — | por código/QR, sem login |

### Requisitos funcionais

- **RF01** — Login/senha em todos os portais; recuperação por e-mail (link temporário, SMTP).
- **RF02** — Papéis: Colaborador, Líder, Instrutor, RH, Admin (poder total) — acumuláveis na mesma conta.
- **RF03** — Reconhecimento facial **apenas para validar presença**, só com **aula em andamento**; fora da janela, só homologação manual.
- **RF04** — Aceite do **termo LGPD** de biometria no primeiro login; nova versão re-pede aceite; sem aceite → presença só manual.
- **RF05** — CRUD de usuários **sem self-service**: só RH/Admin (e Líder para seus liderados); rota pública `/register` desativada.
- **RF06** — **Treinamento 1:N Aula** (cursos multi-dia); janela da aula controlada pelo instrutor (iniciar/encerrar/ajustar) com notificação ao RH.
- **RF07** — RH **e Líder** criam/editam treinamentos e aulas (líder: só o próprio setor); aula `em_andamento`/`encerrada` não é re-agendada pela gestão.
- **RF08** — Vaga sempre **nominal** (líder reserva para o time; sem vagas anônimas).
- **RF09** — **Fila de espera automática** por ordem de pedido; promoção automática quando vaga é liberada.
- **RF10** — Cancelamento/ausência **não é ação direta**: colaborador **abre um ticket** (só submete, nunca edita o resultado).
- **RF11** — Tipos de ticket de **catálogo** mantido pelo Admin (seed: *cancelar vaga*, *justificar falta*, *informação*) + texto livre.
- **RF12** — RH/líder resolvem tickets: cancelar vaga **ou** justificar falta; justificativas com status **APROVADO/RECUSADO** alterável a qualquer momento, só por eles.
- **RF13** — Líder justifica falta **diretamente** (sem ticket do liderado).
- **RF14** — Presença facial em duas formas, sempre na aula ativa: **autônoma** (colaborador) ou **via instrutor**.
- **RF15** — **Homologação manual** (sem facial) pelo instrutor exige **senha do próprio colaborador** + **motivo do catálogo** ou **motivo novo cadastrado no ato**.
- **RF16** — Anti-fraude: **1 presença por pessoa por aula**; duplicidade recusada e registrada em auditoria.
- **RF17** — RH/relatórios distinguem **facial vs. homologada** (com motivo), sempre com autor e horário.
- **RF18** — Elegibilidade a certificado: presença em **todas as aulas** (falta, mesmo justificada/aprovada = sem certificado).
- **RF19** — Emissão **manual pelo RH** após revisão.
- **RF20** — Certificado **imutável**: correção/reemissão gera **nova versão**; o código original para de verificar.
- **RF21** — QR/código/link abre **página pública de verificação** com mínimo de dados (LGPD): status **VÁLIDO/EXPIRADO/NÃO ENCONTRADO** + PDF para download; sem foto/documentos/setor.
- **RF22** — Validade por treinamento (NRs ≈ 2 anos) alimenta expiração na verificação e nos relatórios.
- **RF23** — Canais: in-app + **e-mail real via SMTP** (config no painel do Admin).
- **RF24** — Eventos notificados: vaga reservada/devolvida; aula iniciada/encerrada; presença homologada; justificativa alterada; certificado emitido; ticket novo/resolvido.
- **RF25** — **Lembretes**: 24h antes da aula; avanço de posição na fila.
- **RF26** — Painéis de configuração (Admin/RH): motivos, tipos de ticket, versões do termo, SMTP.
- **RF27** — "Motivo novo no ato" do instrutor alimenta o catálogo persistente.
- **RF28** — Relatórios completos **+ exportação CSV/PDF**: por sessão, por colaborador, por setor, **matriz de conformidade**, por instrutor/aula.

### Requisitos não funcionais

- **RNF01 — LGPD:** consentimento versionado; mínimo de dados na verificação pública; trilha de auditoria de eventos sensíveis.
- **RNF02 — PostgreSQL** como persistência (testes rodam no banco formal, não em SQLite).
- **RNF03 — Segurança:** senhas nunca em texto puro; homologação exige autenticação do colaborador; audit-trail completo.
- **RNF04 — E-mail:** SMTP por `.env` + painel Admin; falha de e-mail nunca impede o fluxo in-app.
- **RNF05 — Usabilidade:** PT-BR, uma tela por tarefa por ator; presença validável em < 10 s.
- **RNF06 — Demo resiliente:** todos os fluxos críticos operam sem hardware real (fachada biométrica simulável).

### Dados de demonstração (seed)

- **SEED01** — 30–40 colaboradores fictícios **com metadados faciais** nos registros (o mock facial os reconhece).
- **SEED02** — Admin, RH, instrutores (incl. externo), líderes, tipos/motivos de ticket e catálogos populados.
- **SEED03** — Cenários: NR10 concluído (3 certificados VÁLIDOS), NR35 **em andamento** (aula ativa + fila de espera + ticket/justificativa pendente), Segurança da Informação concluído (**certificado EXPIRADO** para a verificação pública).
- **SEED04** (pedido posterior do time) — volume extra: +2 setores, +13 pessoas, +6 motivos, +5 treinamentos, +15 aulas, filas, tickets/justificativas em todos os estados e uma **reemissão real** (v1 `PZ-2026-000105` morta → v2 `PZ-2026-000111` vigente). Total: **57 contas, 7 setores, 8 treinamentos, 83 inscrições, 15 certificados**.

### Fluxos principais

1. **Ciclo de vagas:** criação da aula → vagas nominais → fila de espera quando esgotar.
2. **Aula/chamada:** instrutor inicia → facial autônoma ou via instrutor → homologação manual (senha + motivo) → encerra → RH notificado.
3. **Ticket:** colaborador submete (tipo + texto) → RH/líder resolvem (cancela vaga | justificativa APROVADO/RECUSADO).
4. **Certificação:** fim da última aula → RH revisa (todas as aulas) → emite manualmente → código/QR → verificação pública → PDF.

**Fora do escopo:** login facial (biometria só na presença), refeitório e visitas (P2/P3), integração real com o RH da Usina.

## §4. Design system (Q31–Q33)

Conceito: **"a cor da usina e a cor da segurança"** — verde agro como identidade institucional + laranja de segurança industrial (EPI/NR) como cor de ação.

| Token | Hex | Uso |
|---|---|---|
| `primary` | `#15803D` | Header, botões primários, links, foco |
| `accent` | `#EA580C` | CTAs de ação (reservar/emitir/iniciar aula), alertas NR |
| `background` / `foreground` | `#F0FDF4` / `#14532D` | Fundo dos portais / texto |
| `destructive` | `#DC2626` | Cancelamentos, RECUSADO |
| `border` | `#BBF7D0` | Bordas em tom esverdeado |

**Tokens de status** (ligados diretamente aos RFs): facial `#16A34A` (RF17) · manual âmbar `#D97706` (RF15/RF17) · APROVADO `#16A34A` / RECUSADO `#DC2626` (RF12) · fila `#0891B2` (RF09) · expirado `#DC2626` (RF21/RF22).

**Tipografia:** **Lexend** (títulos — fonte desenhada para legibilidade/a11y, decisão visível para o público industrial) + **Source Sans 3** (corpo); números tabulares em tabelas e códigos de certificado. Dark mode fora do MVP (uso diurno).

**UI implementada:** sidebar fixa por papel com estado ativo, botão "Voltar" padronizado, família única de ícones SVG, ritmo de espaçamento 4/8, busca client-side nas listas grandes, tabelas densas para o RH.

## §5. Stack decidida (Q35) e ajustes de execução

**Decisão (Q35):** Laravel 11 / PHP 8.3 · Blade + Alpine · PostgreSQL (pgvector) · Mailpit · dompdf — **sem Node/npm no MVP** (assets prontos, sem build), comparada a Django 5, Rails 8, AdonisJS 7 e NestJS+React pelos critérios dos RFs (auth/reset, scheduler, SMTP, PDF, QR, CSV, biometria, corpus de IA, encaixe AT02).

**Ajustes constatados ao rodar o scaffold** (arquitetura e topologia mantidas):

| Item | Decidido (Q35) | Executado | Por quê |
|---|---|---|---|
| PHP | 8.3 | **8.4** | Esqueleto Laravel atual exige ≥ 8.4.1 |
| Laravel | 11 | **13** (`^13.17`) | API usada (Blade/Eloquent/migrations) é idêntica; downgrade somaria risco na janela curta |
| Porta do PG no host | 5432 | **5433** | Evita conflito com PG local |
| `.env` dev (hosts) | localhost | `host.docker.internal` | App dev roda em contêiner |
| E-mail dev | SMTP | **Mailpit** | Captura de e-mails p/ conferir RF23–25 |

Dependências: `laravel/breeze` (auth/reset), `barryvdh/laravel-dompdf` (PDF), `chillerlan/php-qrcode` (QR), `barryvdh/laravel-debugbar` (só dev), Alpine.js vendored. CSV via PHP nativo (`fputcsv`).

## §6. Prompts do desenvolvimento (cronológicos, com resultado)

> Todos os prompts abaixo são as **versões polidas** (contexto → objetivo → restrições → formato) dos prompts originais do time, preservado o sentido — prática acordada (AT04) porque clareza e completude de prompt são quesitos da banca. Cada prompt traz o **resultado** obtido. As 35 perguntas interativas do questionário (Q1–Q35) estão transcritas integralmente no §7.

### Fase 0 — Leitura do desafio e arquitetura conceitual (20/10, 14h50–14h55)

> **P-01 · Extrair e estruturar o desafio**
> "Leia o arquivo `problemas.docx` do Hackathon FATEC & ETEC (parceiros Usina Lins e CetaCeo). Extraia todo o conteúdo e estruture os 3 problemas propostos com: setor afetado, situação atual, dificuldades, quem é afetado e o que melhorar. Registre também o cronograma, a regra de entrega (PDF com todos os prompts) e os critérios de avaliação da banca. Não escolha tecnologia ainda."
>
> **Resultado:** os 3 problemas mapeados (P1 Treinamentos, P2 Refeitório, P3 Visitas) + critérios da banca registrados; ponto de partida para qualquer decisão.

> **P-02 · Arquitetura conceitual dos 3 problemas (sem tecnologias)**
> "Para cada um dos 3 problemas, proponha uma arquitetura conceitual SEM definir tecnologias: escopo, atores, entidades principais, fluxo MVP e riscos. Regra para os 3: biometria e hardware (câmera, catraca, cancela) sempre atrás de uma fachada única com modo simulador, para a demo não depender de reconhecimento real na hora da banca. Depois compare os 3 em complexidade, risco LGPD, hardware, efeito 'wow' e viabilidade em 24h."
>
> **Resultado:** espinha dorsal comum (portais por ator → serviços de domínio → dados → fachada de hardware, com camada transversal de identidade/LGPD/auditoria) e comparativo que fundamentou a escolha do P1.

### Fase 1 — Governança do processo (20/10, 14h56–15h13)

> **P-03 · Criar o registro de processo**
> "Faça uma skill que, a cada operação, documentação ou discussão com IA, adicione um resumo em ordem cronológica com data/hora — e crie o arquivo do diário."
>
> **Resultado:** skill `registro-hackathon` + `journey-log.md` (diário de bordo oficial; 42 sessões até o fim do hackathon).

> **P-04 · Versionar o processo e polir prompts**
> "Inicialize o Git no repositório com `.gitignore` apropriado e faça o commit inicial. A partir de agora, todo prompt que o time enviar deve ser polido por você (contexto → objetivo → restrições → formato de saída, preservando o sentido) e os resultados de cada uso documentados — porque a clareza dos prompts é critério avaliado pela banca."
>
> **Resultado:** repositório Git na branch `main`, commit inicial `chore: init repo...`; skill de registro ampliada para polir prompts e registrar resultados.

> **P-05 · Renomear o diário**
> "Renomeie o arquivo de registro para um termo em inglês referente a 'diário de bordo' e remova a regra de que nenhum retrabalho/edição é permitido — permitir edição quando ajudar a apresentar melhor a história do projeto à banca, preservando a ordem cronológica."
>
> **Resultado:** arquivo renomeado para `journey-log.md` (com `git mv`, preservando histórico) e política de edição flexibilizada.

### Fase 2 — Decisão do problema (20/10, 15h05–15h33)

> **P-06 · Identidade unificada (diretiva do time)**
> "Decisão global para os 3 problemas: o reconhecimento facial é o meio principal de identificação do usuário, com fallback de login/senha vinculado ao mesmo cadastro. Banco de dados: PostgreSQL. Mapeie a estrutura de dados de reconhecimento facial (credenciais biométricas, foto de referência, consentimento e log de eventos)."
>
> **Resultado:** diretriz de biometria como padrão + PostgreSQL fixado; modelo de armazenamento biométrico planejado (mais tarde refinado: facial apenas na presença — Q11).

> **P-07 · Recomendação de escolha**
> "Em poucas palavras, qual dos 3 problemas você recomenda e por quê?"
>
> **Resultado:** **P1** recomendado — menor dependência de hardware real (só câmera na chamada), MVP factível no prazo, "wow" acessível (validar certificado ao vivo na página pública). P3 plano B; P2 o mais arriscado.

> **P-08 · Travar o P1 e reverter a ordem do processo**
> "Time trava o Problema 1 (Gestão de Treinamentos Internos — RH). Mas: sem definir arquitetura ou nada ainda — remova o documento de arquitetura do pacote, procure uma skill de levantamento de requisitos e depois faça o questionário de requisitos comigo."
>
> **Resultado:** P1 confirmado; arquitetura prévia removida (ficou no histórico do Git, commit `c081de1`); skill `grilling` (mattpocock/skills, ~1,4 mi instalações) instalada e questionário iniciado — **requisitos antes do design**.

### Fase 3 — Levantamento de requisitos (20/10, 16h02–21h20)

Questionário interativo em 4 rodadas, **Q1–Q30**, sempre com opção recomendada e resposta livre do time (transcrição integral no §7). Destaques que **mudaram o modelo de domínio**:

- **Q11 (pivô do modelo):** o time criou a entidade **Aula** (Treinamento 1:N Aula) com perfil próprio do instrutor (iniciar/encerrar/controlar chamada, notificando RH) — e o facial ficou **apenas para validar presença**, com o fallback trocado pela **homologação do instrutor**.
- **Q2 + ajuste final presencial:** homologação manual com **senha do próprio colaborador + motivo** (catálogo editável, com motivo novo cadastrado no ato); facial em duas formas — **autônoma** e **via instrutor**.
- **Q5/Q9/Q22:** cancelamentos e justificativas via **tickets** (colaborador só submete; RH/líder resolvem; APROVADO/RECUSADO alterável a qualquer momento por eles).
- **Q18/Q19:** **Admin** com poder total (don único do SMTP); líder com permissões totais no seu setor.
- **Q8:** mock facial via **seed** com metadados biométricos; toda gestão de contas por RH/líder (sem self-service).

> **P-09 · Consolidar requisitos**
> "Com as 30 respostas confirmadas, gere o documento consolidado de requisitos: RFs numerados e rastreáveis, RNFs, matriz de permissões por ator, seed de demonstração, fluxos principais e fora de escopo. Sem arquitetura neste documento."
>
> **Resultado:** `docs/requisitos-p1.md` (28 RFs, 6 RNFs, SEED01–03, fluxos, fora de escopo) + transcrição em `docs/questionario-requisitos-p1.md`, aprovado pelo time.

> **P-10 · Checar maturidade**
> "Você está confiante em seguir a partir desses requisitos? O que falta?"
>
> **Resultado:** requisitos considerados **maduros**; pendências não-bloqueantes mapeadas (SMTP da demo no `.env`, fotos do seed, ensaio do simulador); sequência de execução acordada: arquitetura enxuta → seed/esqueleto → portais → chamada+certificado → verificação pública.

> **P-11 · Skills globais**
> "Torne as skills `grill-me` e `grilling` globais no meu usuário — pode apenas mover os arquivos — e depois verifique se estão disponíveis."
>
> **Resultado:** skills movidas para o escopo global (`~/.agents/skills/`) e verificadas; `skills-lock.json` do repositório limpo.

### Fase 4 — Design (20/10, 21h45–22h20)

> **P-12 · Paleta, tipografia e kit (Q31–Q33)**
> "Vamos decidir uma paleta de cores e um conjunto de skills para design e UI/UX. Apresente opções do seu dataset com recomendação."
>
> **Resultado (Q31–Q33):** paleta **verde agro `#15803D` + laranja de segurança `#EA580C`** ("a cor da usina e a cor da segurança"), neutros slate, fundo `#F0FDF4`; tokens de status por RF (facial/manual/aprovado/recusado/fila/expirado); tipografia **Lexend + Source Sans 3** (a11y + números tabulares); kit de skills `ui-ux-pro-max` + `frontend-design` + `ui-styling` + `design-system` + `web-design-guidelines`. Consolidado em `docs/design-p1.md`.

> **P-13 · Nome do produto (Q34)**
> "Qual nome damos ao projeto? Proponha 5 opções com racional, pensando no hero do produto (certificado verificável)."
>
> **Resultado:** 5 candidatas (Certia, TurmaCert, NRFlow, Assidu, **Presenza**) → time escolheu **Presenza**, tagline **"presenças reais, certificados à prova"**.

### Fase 5 — Stack (20/10, 22h40–22h55)

> **P-14 · Escolha da stack (Q35) + regra fixa de trabalho**
> "Vamos decidir a stack. Compare Ruby on Rails, Laravel, Django e Node (com e sem React) pelos critérios dos nossos RFs: auth + reset por e-mail, scheduler de lembretes, SMTP, geração de PDF, QR code, CSV, biometria vetorial no Postgres, e encaixe na topologia que vou impor: docker-compose de serviços para dev/testes **+ um compose de homologação que estende o de serviços** e adiciona a aplicação buildada com env fixo. Duas regras a partir de agora: **(1)** toda vez que houver dúvida ou outra forma de fazer, questione-me com opções + recomendação e aguarde aprovação; **(2)** quando precisar instalar algo, **instrua-me** a instalar — nunca instale direto."
>
> **Resultado (Q35):** **Laravel 11 / PHP 8.3 / Blade + Alpine / PostgreSQL(pgvector) / Mailpit / dompdf** aprovado — maior cobertura de RFs pelas baterias do framework na janela restante, imagem Docker leve, corpus de IA grande em PHP. Regras viraram **AT01** e **AT03** em `docs/acordos-de-trabalho.md` (AT02 = topologia Docker). Instruções de instalação do Docker Desktop emitidas ao time.

### Fase 6 — Scaffold e domínio (20/10, 22h56 → 23h38)

> **P-15 · "Dá play e manda bala"**
> "Docker instalado — dá play e manda bala: gere o projeto Laravel, configure o compose conforme a AT02 (dev = serviços; homolog = app + scheduler buildados, env fixo), aplique as migrations base, instale dompdf/qrcode/breeze, desligue o build de Node (assets estáticos + Alpine vendored) e deixe a home e o /login respondendo."
>
> **Resultado:** Docker Desktop v29 ativo; projeto Laravel gerado por contêiner Composer e movido para a raiz; imagem `presenza/php` (PHP **8.4** + pdo_pgsql/gd/bcmath/zip + Composer); `docker-compose.yml` (postgres `pgvector/pg16` :5433 + mailpit) e compose de homologação com `app`/`scheduler`; Breeze (Blade) para login/reset (RF01/RF28); `public/css/app.css` com os tokens do design system; Laravel Boost instalado; smoke 200 na home e no `/login`.

> **P-16 · Domínio completo (continuação do P-15)**
> "Continue: migrations, models, enums e seed cobrindo RF01–RF28. Garanta no BANCO o anti-fraude (1 presença por pessoa por aula), papéis acumuláveis em jsonb, termo LGPD versionado com aceites, biometria com vetor pgvector 128d, e o seed SEED01–03 com os 3 cenários da demo (NR10 concluído com certificados válidos, NR35 em andamento com aula ativa + fila + ticket pendente, Seg. da Informação com certificado EXPIRADO)."
>
> **Resultado:** 5 migrations, 8 enums, 18 models com regras (`Aula::estaAtiva()`, `Certificado::resultadoVerificacao()`, `User::temPapel()`, `Auditoria::registrar()`), seeders SEED01–03 (44 contas com embedding fake); `migrate:fresh --seed` verde: 44 users, 39 inscrições, 22 presenças, 4 certificados, 3 verificações, 44 biometrias. Correções no caminho: pluralizer do Eloquent não pluraliza português (`$table` explícito nos models).

### Fase 7 — Telas, fluxos e testes (20/10 23h39 → 21/10 00h33)

> **P-17 · Telas da demo**
> "Implemente as telas e fluxos críticos: middleware de aceite do termo (RF04), login PT-BR com redirect por papel (RF01), presença facial autônoma do colaborador (RF14.a), tela 'Fazer chamada' do instrutor com facial via instrutor OU homologação manual com senha do colaborador + motivo do catálogo ou novo (RF14.b/RF15/RF27), início/encerramento de aula notificando RH (RF06/RF24) e página pública `/verificar/{código}` com VÁLIDO/EXPIRADO/NÃO ENCONTRADO, dados mínimos, QR, log e PDF (RF21/RF22). Desabilite o `/register` (RF05)."
>
> **Resultado:** middleware `ExigirAceiteTermo`, 9 views Blade, `PresencaService`, fachada biométrica documentada (RNF06); smoke manual end-to-end: login → termo → portal → facial grava presença+auditoria → duplicidade recusada (RF16) → homologação com senha correta ok / errada recusada → `/verificar` VÁLIDO e EXPIRADO → PDF 200.

> **P-18 · Suíte automatizada (decisão AT01)**
> "Em vez de curls ad hoc, crie uma suíte PHPUnit automatizada dos fluxos críticos, rodando no próprio PostgreSQL (pgvector não existe no SQLite)."
>
> **Resultado:** banco `presenza_test`, `phpunit.xml` apontando para ele, trait `CriaFixtures`; 5 suítes (AceiteTermo, PresencaFacial, HomologacaoManual, CicloAula, VerificacaoPublica) + testes legados do Breeze alinhados — **49 testes / 144 asserções verdes**. Comando: `docker run --rm -v .:/app -w /app presenza/php vendor/bin/phpunit`.

### Fase 8 — Bloco RH, scheduler e gestão (21/10, 00h34–02h05)

Prompts de continuação sob AT01 (nenhuma dúvida material nova), cobrindo:

- **P-19 · Tickets/justificativas/certificados/relatórios** → telas do RH com efeito real (cancelamento devolve vagas em todas as aulas e **promove a fila**, justificativas APROVADO/RECUSADO a qualquer momento, emissão manual pelo RH, reemissão imutável, CSVs com BOM UTF-8, escopo `EscopoGestao`: líder só no próprio setor). **66 testes / 209 asserções.**
- **P-20 · Lembretes (RF25)** → comando `presenza:lembretes` agendado de hora em hora no serviço `scheduler` do compose homolog (aula em ~24h → notifica vaga nominal e fila; auditoria `lembretes_enviados`). **68/213.**
- **P-21 · Configurações (RF26)** → motivos de homologação, tipos de ticket, **publicação de nova versão do termo** (todos re-pedem aceite) e SMTP. **72/228.**
- **P-22 · Reservas nominais (RF05/RF08/RF09)** → líder/RH reservam vagas nominais; lotada → fila com posição; cancelamento promove a fila na hora; notificações em tudo. **76/239.**
- **P-23 · RF10 real** → colaborador abre o próprio ticket (tipo do catálogo ativo + texto), notificando todos os RHs. **3 testes novos** (integração com RF26: tipo inativo recusado).
- **P-24 · RF07/RF05** → CRUD de treinamentos/aulas (líder só no próprio setor; aula ativa/encerrada não re-agendada — 422) e CRUD de usuários (criar/editar/desativar/reativar, reset de senha; conta desativada não loga). Bugs pegos pelos testes antes do verde (comparação enum vs string, `Enum::is()` inexistente). **89 testes / 294 asserções.**

### Fase 9 — Matriz de conformidade e encerramento dos RFs (21/10, 02h06–02h24)

> **P-25 · Matriz de conformidade (RF28+)**
> "Crie a matriz de conformidade: para cada pessoa × treinamento aplicável, badge VIGENTE (com data) / EXPIRADO / EM_CURSO (x/y aulas) / PENDENTE; treinamento restrito a outro setor = n/a; líder vê apenas liderados."
>
> **Resultado:** tela `portal/rh/conformidade` + **93 testes / 305 asserções verdes** — **28/28 RFs fechados**. README e roteiro de demo atualizados.

### Fase 10 — Entregáveis e topologia final (21/10, 01h30–02h10)

- **P-26 · Entregáveis:** *"Valide o e-mail de verdade no Mailpit, escreva o README do produto, o roteiro de demo de 5 min com plano B, e gere o PDF de prompts com os docs do processo."* → e-mail real comprovado no Mailpit; `README.md` completo; `docs/roteiro-demo.md` (cronometrado, check-list, plano B); comando `presenza:pdf-prompts` gerando `docs/prompts-ia.pdf` (dompdf + commonmark).
- **P-27 · Topologia homolog (AT02):** *"Suba a topologia de homologação e valide de ponta a ponta."* → app + scheduler de pé, migrate+seed dentro da rede do compose (`DB_HOST=postgres`), smokes públicos e do fluxo crítico na topologia real.
- **P-28 · Renome dos composes:** *"Ve o que alterei, documente onde precisar e commit."* → `docker-compose.yml` → `docker-compose.dev.yaml` e homolog → `docker-compose.yaml` (o comando sem `-f` sobe a demo); include corrigido; docs sincronizadas; validado sem recriar contêineres.
- **P-29 · Seed no `up` (AT01):** *"Quero que rode o seed quando eu subir o docker-compose.yaml."* → pergunta ao time (reset total vs. só banco vazio) → **reset total aprovado**: comando do `app` executa `migrate:fresh --seed --force` antes do `serve`; aviso documentado (restart apaga dados ao vivo); validado ao vivo (57 contas, `/verificar` 200 após ~30 s).

### Fase 11 — Seed enriquecido (SEED04)

> **P-30 · Encher o seed**
> "Adicione no seed vários dados de exemplo — pode encher."
>
> **Resultado:** `DadosExtrasSeeder` (SEED04) registrado **após** os cenários de demo (alvos intactos): +2 setores, +13 pessoas com biometria de fachada, +6 motivos, +5 treinamentos, +15 aulas, filas, 3 tickets abertos (um para promoção de fila ao vivo), justificativas aprovada/recusadas e **reemissão real de certificado** (v1 morre, v2 vigente). Validado em homologação; suíte segue 93/305.

### Fase 12 — Deploy no Coolify (21/10, 02h18–03h09) — prompts P1–P5 do runbook

Contexto: VPS gerenciada pelo Coolify, domínio `https://hackathon.dpicinato.com`. Quatro problemas reais de plataforma, cada um com prompt, causa raiz e correção (runbook completo em `docs/deploy-coolify.md`):

> **P-31 (P1 do runbook) · include ignorado**
> "Estou deployando uma aplicação Laravel (Docker Compose) na VPS com Coolify. O deploy falhou no build com: `service "app" depends on undefined service "mailpit": invalid compose project`. O arquivo topo usa `include:` para puxar um compose de dev que declara `postgres` e `mailpit`. Diagnostique a causa raiz e corrija com a menor mudança possível, sem quebrar o fluxo local." — o time orientou: *"pode copiar o trecho do dev para dentro do padrão, preciso apenas que funcione."*
>
> **Resultado:** IA comprovou via issues do Coolify (#5921/#2859) que o `include:` é ignorado; `postgres`/`mailpit` embutidos no arquivo topo (commit `4514fbe`); `docker compose config` validado.

> **P-32 (P2) · porta host ocupada**
> "Novo erro na fase de `up`: `Bind for 0.0.0.0:8000 failed: port is already allocated`. A aplicação vai rodar em contêiner atrás de um subdomínio servido pelo proxy do Coolify. Corrija **sem exigir variáveis externas** e preservando o acesso local do time."
>
> **Resultado:** o painel do Coolify ocupa a 8000 do host; host port default movida para **18000** (contêiner segue 8000 — é ela que o proxy acessa) (commit `3ecf784`).

> **P-33 (P3) · porta do domínio**
> "Ao configurar o subdomínio no Coolify aparece: `Unrecognized internal port 18000 is not listed in Ports Exposes...`. O que significa e como resolver?"
>
> **Resultado:** o campo espera a porta **interna do contêiner** (8000); ajustado na UI, sem mudança de código.

> **P-34 (P4) · contêiner sem código (503 eterno)**
> "O deploy com o domínio `app:8000` abre os contêineres, mas o `app` entra em loop: `Composer could not find a composer.json file in /app` + `Could not open input file: artisan`. O compose usa o bind `.:/app`. Faça local e VPS **usarem a mesma imagem**, eliminando qualquer dependência do código fora do build."
>
> **Resultado:** causa raiz — o `up` do Coolify roda num diretório do helper que não existe no daemon, tornando o bind uma pasta vazia. Solução definitiva: código + vendor **embutidos na imagem** (Dockerfile `COPY` + `composer install --no-dev`, WORKDIR `/presenza`, `.dockerignore` novo, binds removidos) — commit `2e123d2`; pilha verde local; **redeploy confirmado: `https://hackathon.dpicinato.com` respondendo**.

> **P-35 (P5) · documentar o deploy**
> "Finalmente funcionou. Documente esses problemas que tivemos em questão do Docker e as ações para resolver, inclua alguns prompts também na documentação disso."
>
> **Resultado:** runbook `docs/deploy-coolify.md` (sintoma → causa → ação, com commits e issues), prompts P1–P5 embutidos, checklist de redeploy e acessos.

### Fase 13 — Polimento de UI (21/10, 03h10–05h00)

> **P-36 · Revisão de layout**
> "Faça uma revisão no layout: revise espaçamentos e intuitividade; botões de voltar para páginas anteriores; e menus laterais claros e intuitivos."
>
> **Resultado:** **app shell** novo — sidebar fixa **por papel** (RH/Admin veem o grupo GESTÃO completo; Líder só o que tem âmbito) com estado ativo por rota; botão "Voltar" padronizado em 10 telas; componente `<x-icon>` (SVG uniforme); skip-link e flash centralizados; CSS em escala 4/8; sidebar vira faixa horizontal `<960px` sem JS. Bug pego de passada (`@if` vazio engolia badge de fila). Validado em 3 níveis: suíte 93/305, smoke autenticado nas 10 páginas, **screenshots reais** com automação de navegador.

> **P-37 · Polimento 2 (mojibake + login + listas)**
> "Os botões e inputs estão muito colados no `/verificar`; os textos muito colados na `/login`; deixe a lista de e-mails e a senha numa listinha mais bem feita; há caracteres errados como 'UsuÃ¡rios'; e para as listas, algo mais próximo de tabelas com pesquisa."
>
> **Resultado:** causa-raiz do mojibake isolada por varredura byte-a-byte (dupla codificação UTF-8 em exatamente 2 arquivos — o resto da árvore limpa; o primeiro scan deu falso positivo pois "Ã" existe em PT em "NÃO"); arquivos reescritos; login com listinha de contas da demo (e-mail clicável para copiar, badge de papel); **busca client-side** (5 linhas de JS, sem build) em Usuários (tabela de verdade, 57 linhas), Matriz, Tickets e Justificativas; espaçamentos corrigidos. 93/305 verde + screenshots conferidos.

> **P-38 · Copy amigável**
> "Remova mensagens que são pros desenvolvedores (códigos RF/Q, notas de implementação) — a interface tem que ser amigável pra quem usa. Troque o e-mail dessas contas por `@exemplo.com`. Os códigos estranhos incluem os Q*."
>
> **Resultado:** 39 ocorrências RF + Q + "fachada" limpas em 12 views (títulos e botões em linguagem de uso); domínio de e-mail `@exemplo.com` em seeders/README/testes; login com e-mail completo copiável; `.btn` com fonte herdada. 93/305 verde.

### Fase 14 — Documentação final

> **P-39 · Relatório final (este documento)**
> "Faça um .md que contenha: ATO 1, em poucas palavras: tecnologias usadas, arquitetura usada no projeto, metodologia de desenvolvimento. ATO 2, detalhado: todos os prompts relevantes para o desenvolvimento, os passos e diário de bordo, requisitos, tudo. O documento deve possuir tudo necessário para o PDF final exigido em problemas.docx."
>
> **Resultado:** este documento (`docs/relatorio-final.md`), pronto para conversão em PDF.

> **P-40 · Manutenção contínua do relatório (governança)**
> "Inclua também, documentado em algum lugar relevante — talvez a skill `registro-hackathon` ou o `AGENTS.md` — para que a IA vá completando o relatório conforme eu for pedindo atualizações."
>
> **Resultado:** regra de manutenção criada em dois lugares — seção "Manutenção do relatório final de entrega" na skill `registro-hackathon` (prompt novo → §6, sessão → §9, cronologia → §8, números → ATO 1/§10; item no checklist final) e bloco `<processo-presenza>` no `AGENTS.md` (visível a toda sessão de IA). Efeito prático: **journey-log e relatório andam sempre juntos, sem pedido explícito** — esta própria sessão já foi registrada conforme a nova regra (Nº 39).

### Fase 15 — Hotfix e ajustes de demo

> **P-41 · Seed com termo LGPD aceito por todos + hotfix da chamada**
> "Faça com que todos os usuários no seed já tenham aceitado os termos da LGPD." (+ ParseError em `/portal/aulas/3` reportado com stack trace)
>
> **Resultado:** ParseError corrigido (linha `@if` engolida por um edit anterior — `php -l` + 93/305 verde) e Docker Desktop reerguido após queda; aceites passaram a ser criados no `CatalogoSeeder` logo após o termo ativo (`INSERT … SELECT … ON CONFLICT DO NOTHING` — a 1ª tentativa no `UserSeeder` rodava antes do termo existir: 0 aceites); **44/44 contas com aceite** — login vai direto ao portal; tela do termo segue viva na demo ao publicar nova versão (força os aceites de novo); SMTP/catálogo e README no domínio `@exemplo.com`.

### Fase 16 — Extensão: assistente de IA público (21/10)

> **P-42 · Assistente de IA no produto (runtime de agente + modelo gratuito)**
> - **Contexto:** Presenza implementado e no ar (28/28 RFs, 93 testes verdes); apresentação à banca no mesmo dia às 14h30.
> - **Objetivo:** avaliar viabilidade e planejar a inclusão de um **assistente de IA embutido ao sistema**, usando o runtime de agente OpenCode e um modelo gratuito; levantar requisitos com o time via skill `grilling` até não restarem dúvidas.
> - **Restrições:** seguir a AT01 (toda dúvida vira questionário com recomendação); levantar fatos do ambiente **antes** de perguntar (catálogo de modelos free, documentação oficial do agente, credenciais do gateway); não codificar antes da aprovação; manter o processo registrado.
> - **Formato:** plano curto de alto nível + rodadas de perguntas numeradas (Qn) com resposta recomendada; resumo consolidado em tabela + plano de entrega; confirmação final do time antes de documentar/agir.
>
> **Resultado:** 24 perguntas (**Q36–Q59**) em 4 rodadas + confirmação final ("Aprovado, seguir"). Consenso: widget **público "Assistente Presenza"** (sem login) nas páginas abertas, **só leitura**, baseado em `docs/` + novo `docs/mapa-paginas.md`; **runtime do agente em contêiner próprio** no compose (exceção ao "sem Node no MVP" aceite, AT01); modelo **`opencode/big-pickle`** ($0, trocável via `.env`, **API key** necessária mesmo para free); **zero PII** (verificação de certificado = só indicar página RF21); respostas em **bloco único com carregamento**; conversas **gravadas (IP + data)** visíveis só ao Admin com **aviso no widget**; **sem limite de taxa (risco aceito)**; mensagem de cortesia em falha (RNF06); requisitos em **seção 7 de `requisitos-p1.md`** (RF29–RF34, RNF07–09); transcrição em `docs/questionario-requisitos-assistente-ia.md`; mockup em `docs/design-assistente-ia.md` **pendente de aprovação**; entrada no fim do roteiro de demo.

### Fase 17 — Anexo de evidência: prompts originais das conversas (21/10, 11h27–11h55)

> **P-43 · Nome da equipe no PDF + anexo dos prompts originais**
> - **Contexto:** no `docs/prompts-ia.pdf` (volume de evidência entregue à banca) o nome de um dos integrantes aparece truncado — "João Marcel dos Santos Silva"; além disso, o PDF trazia só os documentos polidos, sem o texto original dos prompts enviados nas conversas.
> - **Objetivo:** (1) corrigir o nome em todos os artefatos que alimentam o PDF (capa do PDF, README, relatório final, diário de bordo); (2) **extrair do histórico real de conversas** os prompts enviados pelo time e incluí-los no PDF.
> - **Restrições:** preservar o texto dos prompts **verbatim** (sem reescrita); manter a numeração já existente (P-01…P-42 no relatório, Nº 1…Nº 41 no diário); registrar a sessão no diário e no relatório (AT04); não alterar os demais conteúdos dos docs.
> - **Formato:** anexo `docs/prompts-conversas.md` com os prompts numerados (C-01…), agrupados por sessão, cada um com horário, texto original e resultado; extração por script versionado e reproduzível (`scripts/extrair-prompts-conversas.php`).
>
> **Resultado:** nome corrigido nos 4 artefatos (capa do PDF, `README.md`, relatório final, cabeçalho do diário); **53 prompts originais** recuperados do banco de sessões do agente (6 sessões, 19–21/10) e anexados ao PDF — incluindo o stack trace colado pelo time e os prompts de confirmação curtos, para a completude do processo; comando `presenza:pdf-prompts` ampliado (entram `docs/deploy-coolify.md`, `docs/questionario-requisitos-assistente-ia.md` e `docs/prompts-conversas.md`) e **PDF regerado** (1,8 MB/31 pág. → **2,4 MB/39 pág.**); script de extração versionado no repositório (`scripts/extrair-prompts-conversas.php`).

## §7. Transcrição do questionário interativo (Q1–Q35)

Método: skill `grilling` (perguntas em rodadas, sempre com resposta recomendada; o time respondia e ajustava livremente). **Q1–Q30 = requisitos · Q31–Q33 = design · Q34 = nome · Q35 = stack.**

> **Extensão Q36–Q59 (21/10):** questionário do módulo **assistente de IA** — transcrição completa (4 rodadas + fatos + confirmação final) em `docs/questionario-requisitos-assistente-ia.md`; requisitos em `docs/requisitos-p1.md` §7 (RF29–RF34, RNF07–09) e registro em P-42/§6.

### Rodada 1 — Produto, operação e dados de demo (Q1–Q8)

- **Q1 — Verificação pública com validade:** a página pública deve indicar também se o certificado **expirou** (NRs ≈ 2 anos), e não só se existe? → **Sim, mostra EXPIRADO.** *→ RF21/RF22.*
- **Q2 — Presença manual:** sempre disponível ao instrutor? → "Sim, sempre **com motivo** — e uma **lista de opções pré-prontas** para clicar e preencher, podendo **adicionar motivo novo**; a lista pode iniciar zerada ou por seed." *→ RF15/RF26/RF27.*
- **Q3 — Semântica da vaga:** nominal ou blocos sem titular? → **Vaga nominal.** *→ RF08.*
- **Q4 — Lista de espera:** vaga devolvida vai automaticamente ao primeiro da fila? → **Sim, fila automática.** *→ RF09.*
- **Q5 — Cancelamentos/justificativas:** quem e até quando? → "A **qualquer momento**; o participante lança um **ticket**; RH ou Líder podem **cancelar a vaga ou justificar falta**; o status **APROVADO/RECUSADO pode ser alterado a qualquer momento por eles**; o usuário só submete e não altera." *→ RF10–RF13.*
- **Q6 — Canais de aviso:** só app ou e-mail real também? → **App + e-mail real.** *→ RF23.*
- **Q7 — Consentimento LGPD:** tela de aceite do termo de biometria como etapa funcional? → **Sim.** *→ RF04.*
- **Q8 — Dados de demonstração:** "o mock será feito via **seed no banco, incluindo os metadados de reconhecimento facial**; de resto, toda manipulação de usuários é feita pelo **RH ou Líder** (CRUD completo)." *→ SEED01, RF05.*

### Rodada 2 — Chamada, aulas e infraestrutura (Q9–Q16)

- **Q9 — Fluxo de tickets:** participante só abre; RH/líder executam e alteram status? → **Sim, é isso.** *→ RF10–RF12.*
- **Q10 — Onde o facial atua:** só na chamada ou também no login? → "Vamos decidir melhor mais tarde; quero entender onde deve ser aplicada a anti-fraude." **Resolvido na Q11:** facial apenas na presença.
- **Q11 — Instrutor e entidade de aula (pivô):** "o treinador tem **perfil próprio**, e adicionaremos uma **entidade de controle — a aula do treinamento**: o professor pode **iniciar, encerrar, atualizar horários e controlar a chamada**, notificando o RH. O facial **servirá apenas para validar presença**, tirando o login/senha do meio e **trocando o fallback pela presença do Instrutor** (homologação), para flexibilidade." *→ RF06, RF03, RF14–RF17.*
- **Q12 — Treinamento multi-dia:** "por meio de **aulas**: durante a aula ocorrendo, valida-se presença com facial." *→ RF06 (1:N).*
- **Q13 — Etapa 'confirmar presença':** "os tickets já resolvem; remova o módulo de confirmação e deixe apenas as aulas (Treinamento 1:N Aula)." *→ RF10.*
- **Q14 — Formato do certificado:** "os **dois**: QR/código/link manda para a **tela web**, que dá a **opção de download do PDF**." *→ RF21.*
- **Q15 — Manutenção de catálogos:** **Sim, RH edita.** *→ RF26.*
- **Q16 — Canal do e-mail real:** "é **requisito não funcional**; minha arquitetura **já tem envio SMTP por `.ENV`**" (painel ajustado depois na Q19 para o Admin). *→ RNF04, RF23.*

### Rodada 3 — Papéis e permissões (Q17–Q23)

- **Q17 — Tipos de usuário:** 4 papéis + público, **acumuláveis**? → **Sim.** *→ RF02.*
- **Q18 — Matriz de permissões:** "correta, mas **adicione o Admin com poder total em tudo** e o **líder com todas as permissões no setor dele**." *→ RF02 + matriz.*
- **Q19 — Dono do SMTP:** "deixa essa questão **apenas para o Admin**." *→ RF26/RNF04.*
- **Q20 — Qualificação do certificado:** presença em **todas as aulas** (falta justificada/aprovada = sem certificado). *→ RF18.*
- **Q21 — Janela do facial:** **só na aula ativa** (fora → homologação manual com motivo). *→ RF03/RF15.*
- **Q22 — Quem abre tickets:** "**apenas os colaboradores**, em seus nomes; o líder não precisa abrir — **ele dá a justificativa de falta diretamente**." *→ RF10–RF13.*
- **Q23 — Gatilhos de notificação:** pacote padrão + **lembretes 24h** e avanço na fila. *→ RF24/RF25.*

### Rodada 4 — Escopo do líder, tickets, certificado e relatórios (Q24–Q30)

- **Q24 — Até onde vai o líder:** **líder também cadastra** (treinamentos do seu setor). *→ RF07.*
- **Q25 — Formato do ticket:** "**tipo livre**, tipos **cadastrados pelo administrador**; o seed traz as 3 sugestões (cancelar vaga / justificar falta / informação); colaborador seleciona **tipo + texto livre**." *→ RF11.*
- **Q26 — Emissão de certificados:** **RH emite manualmente.** *→ RF19.*
- **Q27 — Relatórios:** **completo + exportação** (por sessão, colaborador, setor, matriz, instrutor/aula + CSV/PDF). *→ RF28.*
- **Q28 — Recuperação de senha:** **reset por e-mail** (link temporário via SMTP). *→ RF01.*
- **Q29 — Aceite do termo:** **no primeiro login**; nova versão re-pede aceite. *→ RF04.*
- **Q30 — Dados na verificação pública:** **mínimo necessário** (número, nome, treinamento, carga, datas, instrutor, status, PDF — sem foto/documentos/setor). *→ RF21.*

### Ajuste final presencial (após consolidação) ⭐

> "O que precisamos ajustar: **para confirmar presença, tanto o colaborador quanto o instrutor podem fazer o reconhecimento facial** — o colaborador de forma **autônoma** durante a aula, ou **o instrutor** durante a aula. O instrutor também pode **cadastrar motivo na hora** ao validar presença **manualmente**; e será **exigida a senha do colaborador** para o cadastro manual."
>
> *→ RF14 (facial autônoma e via instrutor) + RF15 (senha do colaborador + motivo do catálogo ou novo no ato).*

**Confirmação final:** time validou o resumo consolidado — `Correto, gera a doc` → `docs/requisitos-p1.md` aprovado.

### Rodada de design e produto (Q31–Q35)

- **Q31 — Paleta:** **verde agro + laranja segurança** (`#15803D` + `#EA580C`). *→ `docs/design-p1.md`.*
- **Q32 — Tipografia:** **Lexend + Source Sans 3** (a11y, números tabulares).
- **Q33 — Kit de skills de design:** confirmado (`ui-ux-pro-max`, `frontend-design`, `ui-styling`, `design-system`, `web-design-guidelines`; opcionais banner/slides/shadcn/firecrawl-clone).
- **Q34 — Nome:** 5 candidatas com racional → **Presenza** ("presenças reais, certificados à prova").
- **Q35 — Stack:** **Laravel (Blade + Alpine) / PostgreSQL(pgvector) / Mailpit / dompdf**, Docker conforme AT02, sem Node no MVP. *→ `docs/stack.md` + AT01/AT03.*

## §8. Passos do desenvolvimento (cronologia executiva)

1. **Ler o desafio e estruturar os 3 problemas** (P-01) — 20/10 14h50.
2. **Arquitetura conceitual dos 3 + comparativo** (P-02) — 14h55.
3. **Governança:** skill de registro, Git, política de prompts (P-03–P-05) — 14h56–15h13.
4. **Diretriz de identidade (biometria primária + PostgreSQL)** (P-06) — 15h05.
5. **Comparativo e decisão: P1** (P-07, P-08) — 15h29–15h33.
6. **Questionário de requisitos Q1–Q30 + ajuste final** → `requisitos-p1.md` (P-09) — 16h02–21h20.
7. **Design: paleta/tipografia/kit + nome Presenza** (P-12, P-13) — 21h45–22h20.
8. **Stack Laravel + AT01/AT02/AT03** (P-14) — 22h40–22h55.
9. **Scaffold Docker + Laravel + Breeze + assets sem build** (P-15) — 22h56–23h28.
10. **Domínio: migrations, models, enums, seed SEED01–03** (P-16) — 23h29–23h38.
11. **Telas/fluxos da demo (termo, portais, chamada, verificação pública)** (P-17) — 23h39–00h24.
12. **Suíte PHPUnit (49→66→68→72→76→89→93 testes)** (P-18–P-25) — 00h24–02h24.
13. **Bloco RH completo + lembretes + configurações + reservas + RF10 + RF07/RF05 + matriz de conformidade → 28/28 RFs** — 00h34–02h24.
14. **Entregáveis: README, roteiro de demo, PDF de prompts** (P-26) — 01h30–01h41.
15. **Topologia homolog validada de ponta a ponta** (P-27) — 01h42–01h49.
16. **Renome dos composes + seed automático no `up`** (P-28, P-29) — 01h49–02h10.
17. **SEED04 (dados extras)** (P-30) — 01h30–01h49.
18. **Deploy no Coolify: 4 problemas resolvidos + runbook** (P-31–P-35) — 02h18–03h09 → **no ar em `https://hackathon.dpicinato.com`**.
19. **Polimento de UI (sidebar/voltar/4-8 → mojibake/login/busca → copy amigável)** (P-36–P-38) — 03h10–05h00.
20. **Documentação final para a banca** (P-39) — 21/10.
21. **Hotfix da chamada + seed com termo aceito (44/44) + dev reerguido** (P-41) — 21/10 09h30–11h17.
22. **Assistente de IA público: requisitos Q36–Q59 fechados + docs do módulo (transcrição, seção 7 dos RFs, mapa de páginas, mockup)** (P-42) — 21/10, até 11h47.
23. **Nome da equipe corrigido no PDF + anexo com os 53 prompts originais das conversas** (P-43) — 21/10 11h27–11h55.

## §9. Diário de bordo (`journey-log.md`, 42 sessões)

Histórico cronológico escrito pela skill `registro-hackathon` ao fim de cada sessão — resumo fiel de cada registro.

### 20/10/2026 — Descoberta e fundação

- **Nº 1 · 14h50 — Documentação:** leitura/interpretação dos 3 problemas do `problemas.docx`; critérios da banca registrados. ✔
- **Nº 2 · 14h55 — Arquitetura:** espinha dorsal comum (portais → serviços → dados → fachada de hardware) para os 3 problemas; comparativo para a escolha. ✔
- **Nº 3 · 14h56 — Configuração:** criação da skill `registro-hackathon` e do diário. ✔
- **Nº 4 · 15h05 — Decisão:** biometria facial primária + login/senha fallback + PostgreSQL; mapeamento de dados biométricos. ✔
- **Nº 5 · 15h10 — Configuração:** Git inicializado (branch `main`, `.gitignore`); skill de registro passa a **polir prompts e registrar resultados** (para o PDF). ✔
- **Nº 6 · 15h13 — Configuração:** renomeio para `journey-log.md`; edição de entradas antigas permitida quando melhorar a história (sem quebrar cronologia). ✔
- **Nº 7 · 15h29 — Discussão:** recomendação do P1 (menos hardware, MVP factível, "wow" acessível). Parcial.
- **Nº 8 · 15h33 — Decisão:** **P1 travado**; arquitetura prévia removida — requisitos antes do design. ✔
- **Nº 9 · 16h02 — Documentação:** skills de levantamento de requisitos pesquisadas; questionário guiado preparado. ✔
- **Nº 10 · 16h10–21h20 — Documentação:** questionário Q1–Q30 em 4 rodadas (instalação de `grill-me`/`grilling`; ajuste de PowerShell para `npx`); evoluções-chave: entidade **Aula**, facial só na presença, homologação com **senha + motivo**, tickets, Admin total, líder por setor, certificado manual pelo RH, relatórios com export; consolidado em `docs/requisitos-p1.md`. ✔

### 20/10/2026 (noite) — Design e stack

- **Nº 11 · 21h25 — Discussão:** requisitos **maduros**; pendências não-bloqueantes; sequência de execução proposta. ✔
- **Nº 12 · 21h27 — Configuração:** `grill-me`/`grilling` movidas para escopo global do usuário. ✔
- **Nº 13 · 21h45–22h11 — Decisão:** design system (Q31–Q33): paleta agro verde + laranja segurança, Lexend/Source Sans 3, kit de skills de design → `docs/design-p1.md`. ✔
- **Nº 14 · 22h20 — Decisão:** nome **Presenza** (Q34, entre 5 candidatas); tagline aplicada nos artefatos. ✔
- **Nº 15 · 22h40–22h55 — Decisão:** **AT01** documentada (gate de aprovação) + AT02/AT03; comparativo de stacks; **Q35 = Laravel/Blade+Alpine/PostgreSQL(pgvector)/Mailpit/dompdf**, sem Node → `docs/stack.md`. ✔
- **Nº 16 · 22h56–23h28 — Código:** scaffold rodando — Docker Desktop, Laravel gerado, imagem `presenza/php` (PHP 8.4), composes (AT02), `.env` dev, migrations base, dompdf/qrcode/breeze, assets sem build, Boost, smoke 200. ✔
- **Nº 17 · 23h29–23h38 — Código:** domínio completo — 5 migrations (identidade/treinamento/tickets/certificados/sistema), 8 enums, 18 models, SEED01–03 (44 contas com embeddings fake; NR10 válido, NR35 em andamento com aula ativa + fila, certificado expirado `PZ-2024-000087`); correção do pluralizer PT. ✔

### 20/10 23h39 → 21/10 — Construção da demo

- **Nº 18 · 23h39–00h24 — Código:** telas e fluxos da demo — aceite do termo (RF04), login por papel, facial autônoma, chamada do instrutor (facial/homologação com senha+motivo), `/verificar` pública com QR/PDF/log; smokes manuais end-to-end; RF05: `/register` desativado. ✔
- **Nº 19 · 00h24–00h33 — Testes:** infra de teste no Postgres real (`presenza_test`), trait `CriaFixtures`, 5 suítes — **49 testes/144 asserções**. ✔
- **Nº 20 · 00h34–01h02 — Código:** bloco RH — tickets com efeito real (fila promovida), justificativas, certificados (emissão manual + reemissão imutável), relatórios CSV, `EscopoGestao` — **66/209**. ✔
- **Nº 21 · 01h03–01h10 — Código:** lembretes RF25 no scheduler do compose homolog — **68/213**. ✔
- **Nº 22 · 01h11–01h19 — Código:** configurações RF26 (motivos, tipos, termo versionado, SMTP) — **72/228**. ✔
- **Nº 23 · 01h20–01h29 — Código:** reservas nominais RF05/RF08 com fila automática RF09 — **76/239**. ✔
- **Nº 24 · 01h30–01h41 — Entregáveis:** e-mail real comprovado no Mailpit; README; roteiro de demo 5 min com plano B; `presenza:pdf-prompts` → `docs/prompts-ia.pdf`. ✔
- **Nº 25 · 01h42–01h49 — Testes:** topologia AT02 (homolog) construída e validada de ponta a ponta (smokes públicos e do fluxo crítico; scheduler confirmado). ✔
- **Nº 26 · 01h15–01h27 — Código:** RF10 real — colaborador abre o próprio ticket com notificação a cada RH; interrupção registrada (Docker Desktop caiu, religado por instrução AT03). ✔
- **Nº 27 · 01h28–02h05 — Código:** RF07 (treinamentos/aulas com escopo do líder e trava de aula ativa) + RF05 (CRUD de usuários, desativada não loga) — **89/294**. ✔
- **Nº 28 · 02h06–02h24 — Código:** matriz de conformidade (VIGENTE/EXPIRADO/EM_CURSO/PENDENTE) — **93/305 · 28/28 RFs fechados**; README atualizado. ✔
- **Nº 29 · 01h30–01h49 — Código:** SEED04 (dados extras: +2 setores, +13 pessoas, +5 treinamentos, +15 aulas, tickets/justificativas em todos os estados, reemissão real) — 57 contas no total. ✔
- **Nº 30 · 01h49–01h59 — Configuração:** composes renomeados (`docker-compose.dev.yaml` / `docker-compose.yaml` = padrão da demo); include corrigido; docs sincronizadas. ✔
- **Nº 31 · 02h00–02h10 — Configuração:** seed automático no `up` (reset total aprovado via AT01); aviso de restart documentado; validado ao vivo. ✔

### 21/10 (madrugada) — Deploy e polimento

- **Nº 32 · 02h18 — Configuração:** Coolify ignora `include:` → compose autossuficiente; zero variáveis no deploy; `APP_URL` fixa. Parcial (redeploy pendente). ✔ após Nº 34.
- **Nº 33 · 02h31 — Configuração:** colisão da porta 8000 com o painel do Coolify → host port 18000. ✔
- **Nº 34 · 03h01 — Configuração:** código embutido na imagem (Dockerfile `COPY` + `composer install`, WORKDIR `/presenza`, `.dockerignore`); pilha verde local; **redeploy confirmado — `https://hackathon.dpicinato.com` no ar**. ✔
- **Nº 35 · 03h09 — Documentação:** runbook do deploy (`docs/deploy-coolify.md`) com sintoma→causa→ação, commits, issues e prompts polidos. ✔
- **Nº 36 · 03h10–03h41 — Código:** revisão de layout — sidebar por papel, botão Voltar, ícones SVG, ritmo 4/8; validado com testes + smoke + screenshots. ✔
- **Nº 37 · 03h45–04h10 — Código:** mojibake eliminado (2 arquivos, varredura byte-a-byte), login repaginado, busca client-side nas listas. ✔
- **Nº 38 · 04h30–05:00 — Código:** copy amigável (fim dos RF/Q na UI), e-mails `@exemplo.com`, botões com fonte herdada — **93/305 verde**. ✔
- **Nº 39 · 11h12–11h13 — Documentação:** relatório final da entrega (ATO 1 + ATO 2, este documento) compilado a partir do journey-log e dos docs; **governança de manutenção** criada (seção na skill `registro-hackathon` + bloco no `AGENTS.md`) para que o relatório evolua junto com o projeto em toda sessão de IA. ✔
- **Nº 40 · 09h30–11h17 — Código:** hotfix de ParseError na chamada (edit engoliu o `@if`) + Docker Desktop reerguido; **seed agora pré-assina o termo LGPD (44/44)** — login direto ao portal, aceite re-vivotado ao publicar nova versão; SMTP/README no domínio `@exemplo.com`. ✔
- **Nº 41 · 11h47 — Decisão:** assistente de IA público — requisitos Q36–Q59 fechados em 4 rodadas de grilling com o time; widget público "Assistente Presenza" (só leitura, zero PII), runtime do agente em contêiner no compose (exceção "com Node" aceita), modelo gratuito trocável, conversas gravadas com aviso, risco "sem limite de taxa" assumido; RF29–RF34 + RNF07–09 na seção 7 dos requisitos, transcrição, mapa de páginas e mockup criados; implementação pendente de aprovação do mockup. ✔
- **Nº 42 · 11h27–11h55 — Documentação:** nome do integrante corrigido nos 4 artefatos (capa do PDF, README, relatório, diário) e **anexo com os 53 prompts originais das conversas** extraídos do banco de sessões do agente (`docs/prompts-conversas.md`, script `scripts/extrair-prompts-conversas.php`); PDF de prompts regerado (1,8 MB → 2,4 MB) com o anexo e o runbook do deploy. ✔

*Última atualização do diário: 21/10/2026, 11h55.*

## §10. Estado final e evidências

### Como rodar

```sh
# Desenvolvimento (serviços + app one-off)
docker compose -f docker-compose.dev.yaml up -d
docker run --rm -v .:/app -w /app presenza/php php artisan migrate:fresh --seed
docker run --rm -v .:/app -w /app -p 8000:8000 presenza/php php artisan serve --host=0.0.0.0

# Demonstração/homologação (topologia AT02 — seed automático)
docker compose up -d --build        # app em http://localhost:18000
```

### Credenciais da demo (seed) — todas as senhas `Password@123`, e-mails `login@exemplo.com`

O seed também **pré-assina o termo LGPD (44/44)** — login vai direto ao portal; a tela do aceite reaparece ao publicar nova versão do termo em Configurações.

| Papel | Login | Uso na demo |
|---|---|---|
| RH | `isabela.cristina.fonseca` | tickets, certificados, configurações, reservas, treinamentos, usuários, matriz |
| Instrutor externo | `klaus.werner.steiner` | chamada facial / homologação manual |
| Líder | `heloisa.fernandes.peixoto` | reservas do time, justificativas diretas (RF13) |
| Colaborador | `otavio.martins.gomes` | fila promovida (RF09), abrir ticket (RF10) |

### Verificação pública (sem login)

`https://hackathon.dpicinato.com/verificar` · códigos: **`PZ-2025-000101` (VÁLIDO)** · **`PZ-2024-000087` (EXPIRADO)**.

### Qualidade

- Suíte: `docker run --rm -v .:/app -w /app presenza/php vendor/bin/phpunit` → **93 testes, 305 asserções, verde** (PostgreSQL real com pgvector).
- Cobertura dos fluxos críticos: aceite do termo, janela facial, anti-fraude (duplicidade), homologação (senha certa/errada + motivo novo), ciclo da aula com notificação, verificação pública (VÁLIDO/EXPIRADO/NÃO ENCONTRADO + log + PDF), tickets, justificativas, emissão/reemissão, relatórios CSV, lembretes, configurações, reservas, conformidade.

### Roteiro da demo (5 min)

0:00 a dor (lista de presença falsificável) → 0:30 login + **presença facial** (termo LGPD já aceito no seed) → 1:15 instrutor: iniciar aula + **homologação com senha do colaborador + motivo** (senha errada recusada) → 2:15 **tickets** (justificar falta aprovada; cancelar vaga promove a **fila** na hora) → 3:00 **certificados**: emissão + **reemissão** (código antigo vira NÃO ENCONTRADO ao vivo — momento uau) + CSV → 4:00 governança: **nova versão do termo** re-pede aceite, Mailpit com e-mails reais, scheduler → 4:45 fechamento. Plano B: homologação manual é feature; suíte verde prova o domínio; 403 de escopo é feature (líder não sai do setor).

### Mapa de artefatos

| Artefato | Conteúdo |
|---|---|
| `journey-log.md` | Diário de bordo — 42 sessões com prompts, decisões e resultados |
| `docs/requisitos-p1.md` | RF01–RF28, RNF01–06, SEED01–03, fluxos, fora de escopo + **§7 assistente de IA (RF29–RF34, RNF07–09)** |
| `docs/questionario-requisitos-p1.md` | Transcrição Q1–Q33 (perguntas, respostas, impacto) |
| `docs/questionario-requisitos-assistente-ia.md` | Transcrição Q36–Q59 (módulo assistente de IA — 21/10) |
| `docs/design-p1.md` | Design system (paleta, tipografia, tokens de status, kit de skills) |
| `docs/design-assistente-ia.md` | Mockup do widget público do assistente (aprovação AT01 pendente) |
| `docs/mapa-paginas.md` | Mapa de rotas/telas para contexto do agente (fonte de conhecimento, RF30) |
| `docs/stack.md` | Decisão de stack (Q35), serviços, dependências, ajustes de execução |
| `docs/acordos-de-trabalho.md` | AT01–AT04 |
| `docs/deploy-coolify.md` | Runbook do deploy (sintoma→causa→ação + prompts P1–P5) |
| `docs/roteiro-demo.md` | Roteiro cronometrado da demo + plano B |
| `docs/prompts-ia.pdf` | **Volume de evidência dos prompts** (gerado pelo comando `presenza:pdf-prompts`, 39 páginas): questionário Q1–Q35, questionário Q36–Q59 (assistente de IA), requisitos, stack, acordos, design, runbook do deploy e **anexo com os 53 prompts originais das conversas** |
| `docs/prompts-conversas.md` | Anexo — **53 prompts originais (verbatim)** extraídos do histórico de conversas do agente, por sessão, com horário e resultado |
| `scripts/extrair-prompts-conversas.php` | Script reproduzível da extração dos prompts (lê o banco de sessões do agente e gera o anexo) |
| `docs/relatorio-final.md` | **Este documento** — base do PDF final de entrega |

---

*Presenza · Hackathon FATEC & ETEC · Usina Lins · Outubro de 2026. Processo 100% registrado por IA (skill `registro-hackathon`); código 100% escrito por IA sob gate de aprovação do time (AT01).*
