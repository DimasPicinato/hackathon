# Requisitos — P1 · Gestão de Treinamentos Internos (RH)

> **Produto: Presenza** — *presenças reais, certificados à prova.***

**Hackathon FATEC & ETEC · Usina Lins · CetaCeo** — 20/10/2026
Fonte: questionário interativo de levantamento de requisitos (skill `grilling`, via OpenCode) — 30 perguntas em 4 rodadas + ajustes, com o time.
Transcrição completa (perguntas, respostas e impacto): **`docs/questionario-requisitos-p1.md`** · Histórico: `journey-log.md` (sessões 9–10). Sem arquitetura definida neste documento — apenas requisitos.

## 0. Premissas já fixadas pelo time

- **PostgreSQL** como banco de dados.
- **Reconhecimento facial** como meio de **validação de presença** (único uso), com **homologação manual** como alternativa de exceção.
- **Login/senha** como método de acesso aos portais (com recuperação por e-mail).
- Envio de **e-mail real** via SMTP (módulo de própria arquitetura do time), com configuração sensível por `.env` + painel.

## 1. Atores e permissões

Contas internas **acumulam papéis** (ex.: colaborador que também é líder). Detalhe: `Líder` tem **todas as permissões no escopo do seu setor**.

| Domínio | Colaborador | Líder (seu setor) | Instrutor (suas aulas) | RH | Admin | Público |
|---|---|---|---|---|---|---|
| Usuários | — | CRUD dos liderados do seu setor | — | CRUD total | CRUD total | — |
| Treinamentos/Aulas | ver os seus | criar/editar do setor | iniciar/encerrar/ajustar horários das suas | CRUD total | CRUD total | — |
| Vagas/Reservas | ver as suas | reservar/devolver nominais do time | — | CRUD total | CRUD total | — |
| Chamada/Presença | validar a própria (facial, na aula ativa) | ver presenças do time | executar facial / homologar manual | ver tudo | ver tudo | — |
| Tickets | abrir (próprio) | resolver dos liderados | — | resolver todos | resolver todos | — |
| Justificativas de falta | submeter via ticket (sem editar resultado) | registrar/alterar status (APROVADO/RECUSADO) | — | registrar/alterar | idem RH | — |
| Certificados | ver os seus | ver do time | — | emitir manualmente | emitir | — |
| Catálogos (motivos, tipos de ticket, termo) | — | — | cadastrar motivo novo no ato | manter | manter | — |
| Config sistema (SMTP/infra) | — | — | — | — | **só Admin** | — |
| Relatórios | — | do time (completo + export) | das suas aulas | completo + export | completo + export | — |
| Verificação de certificado | — | — | — | — | — | validar por código/QR, sem login |

## 2. Requisitos funcionais

### 2.1 Identidade e acesso
- **RF01** — Autenticação por login/senha em todos os portais; recuperação de senha por **e-mail** (link temporário; SMTP).
- **RF02** — Papéis: Colaborador, Líder, Instrutor, RH, Admin (poder total) — acumuláveis na mesma conta.
- **RF03** — Reconhecimento facial **apenas para validar presença**, funcionando **somente com aula em andamento** (instrutor iniciou e ainda não encerrou). Fora da janela, presença só por homologação manual.
- **RF04** — Aceite do **termo LGPD** de biometria no **primeiro login**; nova versão do termo re-pede aceite na entrada seguinte. Sem aceite ativo: presença apenas manual.
- **RF05** — CRUD de usuários **sem self-service**: só RH/Admin (e Líder, para seus liderados).

### 2.2 Treinamentos, aulas e vagas
- **RF06** — Modelo domínio-anotado (conceitual): **Treinamento 1:N Aula** (cursos multi-dia, ex.: NR35 = 3 aulas). Cada aula tem horário e janela controlada pelo instrutor (início/encerramento/ajuste), com **notificação ao RH** nessas ações.
- **RF07** — RH **e Líder** podem criar/editar treinamentos e sessões (líder: apenas no escopo do seu setor).
- **RF08** — Vaga sempre **nominal** — reserva por colaborador nomeado (líder reserva para o time; sem vagas anônimas).
- **RF09** — **Fila de espera automática** por ordem de pedidos quando vagas esgotarem; promoção automática quando uma vaga é liberada (devolução/cancelamento).

### 2.3 Tickets, cancelamentos e justificativas
- **RF10** — Cancelamento/ausência **não é ação direta** do colaborador: ele **abre um ticket** vinculado ao treinamento (só submete, nunca edita o resultado).
- **RF11** — Tipos de ticket de **catálogo** mantido pelo Admin (seed: *cancelar vaga*, *justificar falta*, *informação*) + texto livre.
- **RF12** — RH/líder resolvem tickets: **cancelar vaga** **ou** **registrar justificativa de falta**. Justificativas têm status **APROVADO/RECUSADO**, alterável **a qualquer momento** e apenas por eles.
- **RF13** — Líder pode **justificar falta diretamente** (sem depender de ticket do liderado), ex.: liderado de férias.

### 2.4 Presença (chamada)
- **RF14** — Validar presença com facial de duas formas, sempre **durante a aula ativa**:
  - **autônoma**: o colaborador valida a própria presença pela câmera;
  - **via instrutor**: o instrutor executa o reconhecimento para o colaborador.
- **RF15** — **Homologação manual** (sem facial), pelo instrutor, exige: a **senha de acesso do próprio colaborador** no ato (anti-fraude) + **motivo** do catálogo (ex.: câmera desligada) **ou motivo novo cadastrado na hora** pelo instrutor (passa a integrar a lista; Admin/RH mantêm a lista).
- **RF16** — Anti-fraude da chamada: **1 presença por pessoa por aula**; impedir duplicidade e identificar divergência de captura como evento audível.
- **RF17** — RH/líder/relatórios distinguem **facial vs. homologada** (com motivo), sempre com autor e horário (auditoria).

### 2.5 Certificados
- **RF18** — Elegibilidade: presença **em todas as aulas** do treinamento (falta, mesmo justificada e aprovada, = sem certificado; aula precisa ser reposta).
- **RF19** — Emissão **manual pelo RH** após revisão (não automática).
- **RF20** — Certificado **imutável**: código de verificação único; correção/reemissão gera nova versão, nunca altera o original.
- **RF21** — QR/código/link abre a **página pública de verificação** com **mínimo de dados** (LGPD): número, nome, treinamento, carga horária, datas, instrutor, **status VÁLIDO/EXPIRADO/NÃO ENCONTRADO** e download do PDF. Sem foto, documentos ou setor.
- **RF22** — Validade por treinamento (ex.: NRs ~2 anos) alimenta o status de expiração na verificação e nos relatórios.

### 2.6 Notificações
- **RF23** — Canais: **in-app** (sino/toast) + **e-mail real via SMTP** (config sensível: `.env` + painel **do Admin**).
- **RF24** — Eventos: vaga reservada/devolvida; aula iniciada/encerrada; presença homologada manualmente; justificativa alterada (APROVADA/RECUSADA); certificado emitido; ticket novo/resolvido.
- **RF25** — **Lembretes**: 24h antes da aula; avanço de posição na fila de espera.

### 2.7 Catálogos e configuração
- **RF26** — Painéis de configuração (Admin/RH): motivos de homologação, tipos de ticket, versões do termo LGPD, SMTP.
- **RF27** — Campos "motivo novo no ato" (instrutor) alimenta o catálogo persistente.

### 2.8 Relatórios
- **RF28** — Completo **+ exportação CSV/PDF**: por sessão (vagas/convocados/presentes/faltas), por colaborador (histórico + vencidos/a vencer), por setor, **matriz de conformidade** (quem precisa fazer o quê), por instrutor/aula (taxa de presença).

## 3. Requisitos não funcionais
- **RNF01 — LGPD**: consentimento versionado para biometria; mínimo de dados na verificação pública; trilha de auditoria de eventos sensíveis (captura, homologação, alteração de status).
- **RNF02 — PostgreSQL** como persistência (decisão do time).
- **RNF03 — Segurança**: senhas nunca em texto puro; homologação manual exige autenticação do colaborador; audit-trail completo.
- **RNF04 — E-mail**: SMTP por `.env` + painel Admin; falha de e-mail nunca impede o fluxo (in-app segue funcionando).
- **RNF05 — Usabilidade**: portais em PT-BR, uma tela por tarefa por ator; presença validável em < 10s.
- **RNF06 — Demo resiliente**: todos os fluxos críticos operam sem depender de hardware (fachada de biometria simulável).

## 4. Dados de demonstração (seed)
- **SEED01** — 30–40 colaboradores fictícios (nomes/setores/turnos plausíveis) **com metadados de reconhecimento facial já nos registros** (o mock facial da demo os reconhece).
- **SEED02** — Admin, RH, instrutores (incl. externo), líderes e tipos/motivos de ticket e catálogo de motivos populados.
- **SEED03** — Treinamentos de exemplo: NR10 (1 aula), NR35 (3 aulas), segurança da informação (2 aulas) — com validade para a demo de verificação expirada.

## 5. Fluxos principais (resumo)
1. **Ciclo de vagas**: RH/líder cria sessão do treinamento → vagas nominais → fila de espera quando esgotar.
2. **Aula/chamada**: instrutor inicia aula → facial autônomo ou via instrutor → homologação manual (senha + motivo) → encerra aula → RH notificado.
3. **Ticket**: colaborador submete (tipo + texto) → RH/líder resolvem (cancela vaga | justificativa APROVADO/RECUSADO, sempre alterável).
4. **Certificação**: fim da última aula → RH revisa presenças (todas as aulas) → emite manualmente → certificado com código → QR/link para verificação pública (válido/expirado) → PDF para download.

## 6. Fora do escopo (explicitado)
- Portal com login facial (biometria só na presença — decisão Q10/Q11).
- Módulos de refeitório e de visitas (problemas 2 e 3 do desafio).
- Integração real com o RH da Usina (hoje: importação/seed).
