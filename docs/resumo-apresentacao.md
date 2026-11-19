# Presenza — Resumo para Apresentação e Estudo

> **Gestão de Treinamentos Internos (RH) · Hackathon FATEC & ETEC · Usina Lins · CetaCeo** — Outubro/2026  
> *presenças reais, certificados à prova.*  
> Processo 100% registrado por IA (skill `registro-hackathon`); código 100% escrito por IA sob gate de aprovação do time (AT01).

---

## 1. Problema e Motivação

A Usina Lins (CetaCeo) precisava modernizar a **gestão de treinamentos internos de RH** (NR-10, NR-35, segurança da informação, etc.) com três dores principais:

1. **Presença falsificável** — lista de presença em papel ou login/senha na chamada; fraude fácil.
2. **Certificados sem validação pública** — papel impresso, sem rastreabilidade; não se sabia se estava vigente ou vencido.
3. **Processo manual e desconectado** — vagas, filas, justificativas, emissão de certificados e relatórios soltos em planilhas.

O projeto escolhido (P1) foca no **ciclo completo de treinamento**: vagas nominais → aula/chamada (facial) → homologação → certificado verificável publicamente → relatórios.

---

## 2. Atores e Permissões

Contas acumulam papéis. O Líder tem **todas as permissões no escopo do seu setor**.

| Ator | Escopo |
|---|---|
| **Colaborador** | Valida a própria presença (facial, na aula ativa), abre tickets, submete justificativas, vê seus certificados. |
| **Líder** | Reservas do time, justificativas diretas (RF13), cria/edita treinamentos do setor, resolve tickets dos liderados. |
| **Instrutor** | Inicia/encerra/ajusta horários das **suas** aulas; executa facial ou homologa manual. |
| **RH** | CRUD de usuários, treinamentos, certificados, configurações, relatórios completos. |
| **Admin** | Poder total + configuração de SMTP/infra. |
| **Público** | Verifica certificado por código/QR/link, **sem login**. |

---

## 3. Arquitetura

### 3.1 Topologia (Docker Compose)

```
┌─────────────────────────────────────────────────────────────┐
│ docker compose (topologia autônoma — AT02)                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐  │
│  │ postgres │  │ mailpit  │  │   app    │  │ scheduler  │  │
│  │ (pgvec.) │  │ (SMTP)   │  │ Laravel  │  │ schedule   │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘  │
│  ┌────────────┐                                             │
│  │ assistente │ (Node + opencode serve; só leitura de docs/) │
│  └────────────┘                                             │
└─────────────────────────────────────────────────────────────┘
```

- **`postgres`** — `pgvector/pgvector:pg16` (PG16 + pgvector): persistência formal (RNF02).
- **`mailpit`** — captura todos os e-mails no dev/teste (web UI para conferir RF01/RF23–25).
- **`app`** — Laravel servindo em `:8000` (homologação).
- **`scheduler`** — `schedule:work` para lembretes 24h/aviso de fila (RF25).
- **`assistente`** — runtime do chat IA público (Node + `@opencode/cli`), exposto só na rede interna (`:4096`).

### 3.2 Stack de tecnologia

| Camada | Tecnologia | Motivo |
|---|---|---|
| Backend | **Laravel 13 / PHP 8.4** | Baterias cobrem a maioria dos RFs (auth, reset por e-mail, SMTP, scheduler, seeders); corpus de IA grande. |
| Frontend | **Blade + Alpine.js vendored** | Sem pipeline de build (menos peça para falhar na demo); interatividade leve. |
| Banco | **PostgreSQL 16 + pgvector** | Fixado pelo time; matching facial por cosseno no SQL. |
| PDF/QR | **dompdf + chillerlan/php-qrcode** | Geração em PHP puro; imagem Docker leve. |
| IA pública | **OpenCode em contêiner** (Node) | Agente só-leitura de `docs/`; modelo free trocável via `.env`. |
| Infra | **Docker Compose** (AT02) | Topologia autônoma, sem `include:` (Coolify ignora). |

**Sem Node/npm nos assets do app** — CSS com tokens de design + Alpine vendored em `public/` não exigem build. Exceção aprovada: o runtime do Assistente roda em contêiner próprio com Node.

### 3.3 Padrão arquitetural

- **MVC Laravel** com services (`BiometriaService`, `PresencaService`, `RuntimeAssistente`) encapsulando regras de negócio.
- **Middleware `ExigirAceiteTermo`** (RF04): bloqueia o portal até o aceite do termo LGPD vigente.
- **Escopo de gestão** (`EscopoGestao` trait): líder só vê seu setor; RH/Admin veem tudo.
- **Auditoria** (`Auditoria` model): eventos sensíveis registrados com ator, IP e payload.
- **Biometria funcional**: captura no **navegador** (face-api.js vendored); só o embedding de 128-d chega ao servidor; matching por cosseno no pgvector.

### 3.4 Segurança (RNF01/RNF03)

- `trustProxies` restrito a RFC1918 (evita forja de IP via `X-Forwarded-For`).
- Senhas nunca em texto puro (hash).
- Homologação manual exige a senha do próprio colaborador.
- Verificação pública expõe **mínimo de dados** (LGPD): sem foto, documentos ou setor.
- Conversas do assistente: retenção de **30 dias** (purge agendado).
- CSRF dispensado apenas na rota pública `/assistente`.

---

## 4. Funcionalidades (por módulo)

### 4.1 Identidade e acesso (RF01–RF05)
- Login/senha em todos os portais; recuperação de senha por e-mail (link temporário, SMTP).
- 5 papéis acumuláveis: Colaborador, Líder, Instrutor, RH, Admin.
- Aceite do **termo LGPD** de biometria no primeiro login; nova versão re-pede aceite.
- CRUD de usuários sem self-service (só RH/Admin; Líder para seus liderados).
- Cadastro facial (autocadastro) em `/portal/biometria`, exige termo aceito.

### 4.2 Treinamentos, aulas e vagas (RF06–RF09)
- Modelo: **Treinamento 1:N Aula** (cursos multi-dia, ex.: NR35 = 3 aulas).
- Cada aula: horário e janela controlada pelo instrutor (início/encerramento/ajuste), com notificação ao RH.
- RH e Líder criam/editam treinamentos (líder só no setor).
- Vaga sempre **nominal**; fila de espera automática com promoção.

### 4.3 Tickets, cancelamentos e justificativas (RF10–RF13)
- Colaborador abre ticket (tipo + texto) — nunca edita o resultado.
- Tipos de ticket de catálogo (seed: cancelar vaga, justificar falta, informação).
- RH/líder resolvem: cancelar vaga **ou** registrar justificativa (APROVADO/RECUSADO, alterável a qualquer momento).
- Líder pode justificar falta diretamente (RF13).

### 4.4 Presença / chamada (RF14–RF17)
- **Facial autônoma**: colaborador valida a própria presença pela câmera (durante aula ativa).
- **Facial via instrutor**: instrutor executa o reconhecimento para o colaborador.
- **Matching real**: face-api.js extrai embedding de 128-d no navegador; comparado por cosseno no pgvector (limiar **0,95**). Seed mock (`mock-facenet-v1`) fica fora do matching.
- **Homologação manual**: exige senha do colaborador + motivo do catálogo (ou motivo novo).
- Anti-fraude: 1 presença por pessoa por aula; divergência reivindicado×reconhecido auditada.
- Distinção facial vs. homologada em relatórios (com autor e horário).

### 4.5 Certificados (RF18–RF22)
- Elegibilidade: presença em **todas as aulas** (falta justificada = sem certificado).
- Emissão **manual pelo RH** após revisão.
- Certificado **imutável**: código único; reemissão gera nova versão.
- Verificação pública por QR/código/link: **VÁLIDO/EXPIRADO/NÃO ENCONTRADO** + download do PDF.
- Validade por treinamento (ex.: NRs ~2 anos).

### 4.6 Notificações (RF23–RF25)
- Canais: in-app (sino/toast) + e-mail real via SMTP (config sensível, só Admin).
- Eventos: vaga reservada/devolvida, aula iniciada/encerrada, presença homologada, justificativa alterada, certificado emitido, ticket novo/resolvido.
- Lembretes 24h antes da aula; avanço de posição na fila.

### 4.7 Catálogos e configuração (RF26–RF27)
- Painéis Admin/RH: motivos de homologação, tipos de ticket, versões do termo, SMTP.
- "Motivo novo no ato" alimenta o catálogo persistente.

### 4.8 Relatórios (RF28)
- Completo + exportação CSV/PDF: por sessão, por colaborador, por setor, **matriz de conformidade** (quem precisa fazer o quê), por instrutor/aula (taxa de presença).

### 4.9 Assistente de IA público (RF29–RF34)
- Widget público (sem login) em **todas as telas** (home, login, verificação, portal logado).
- Respostas baseadas **exclusivamente na documentação do projeto** (`docs/` + mapa de páginas). Sem acesso ao banco ou dados do sistema.
- Escopo restrito ao Presenza; recusa educada de perguntas fora do escopo.
- **Zero PII**: não recebe/envia dado pessoal.
- Toda conversa gravada (pergunta, resposta, IP, data/hora) — visível só ao Admin, com aviso fixo no widget.
- Histórico vive **só na aba aberta** (fechar/recarregar zera).
- Falha do runtime → mensagem de cortesia (nunca impede os fluxos principais).
- Sem limite de taxa (risco aceito conscientemente).
- UX: resposta em **bloco único** com **indicador de carregamento animado** (pontinhos).

---

## 5. Decisões-chave (e por quê)

| Decisão | Escolha | Motivo |
|---|---|---|
| Problema do hackathon | **P1 — Gestão de Treinamentos Internos (RH)** | Menos hardware, MVP factível, "wow" acessível (sessão 7–8). |
| Biometria | **Facial como validação de presença** (não login) | Anti-fraude na chamada sem trocar o login/senha (Q10/Q11). |
| Banco | **PostgreSQL + pgvector** | Fixado pelo time; matching facial por cosseno no SQL, sem serviço externo. |
| Stack | **Laravel 11/PHP 8.3 → 8.4/Blade+Alpine** | Baterias cobrem a maioria dos RFs; corpus de IA grande; sem Node nos assets. |
| Vaga | **Nominal** (não blocos anônimos) | Rastreabilidade e fila automática (Q3/Q4). |
| Cancelamento | **Via ticket** (colaborador não cancela direto) | Governança: RH/líder decidem (Q5). |
| Certificado | **Emissão manual pelo RH** | Revisão humana antes de emitir (Q26). |
| Termo LGPD | **Aceite no primeiro login**, re-pede em nova versão | Conformidade (Q29). |
| Assistente IA | **OpenCode em contêiner** (exceção AT01 — único uso de Node) | Agente só-leitura de `docs/`; sem banco/PII; modelo free trocável. |
| Topologia | **Compose autônomo** (sem `include:`) | Coolify ignora `include:` no deploy. |
| `trustProxies` | **RFC1918** (não `*`) | Evita forja de IP em auditorias. |
| CSS `hidden` | **Regra global `[hidden] { display: none !important; }`** | Causa raiz do overlay "morto": classes com `display` venciam o atributo. |
| Cache-busting | **`?v=filemtime`** no `app.css` | Fix chega a navegadores com CSS antigo. |
| Testes flaky | **Comparar com `e()` (Blade)** | Nomes com apóstrofo eram escapados no HTML. |

---

## 6. Fluxos principais (resumo)

1. **Ciclo de vagas**: RH/líder cria sessão → vagas nominais → fila de espera quando esgotar → promoção automática.
2. **Aula/chamada**: instrutor inicia aula → facial autônomo ou via instrutor (reconhecimento real) → homologação manual (senha + motivo) → encerra aula → RH notificado.
3. **Ticket**: colaborador submete (tipo + texto) → RH/líder resolvem (cancela vaga | justificativa APROVADO/RECUSADO, sempre alterável).
4. **Certificação**: fim da última aula → RH revisa presenças (todas as aulas) → emite manualmente → certificado com código → QR/link para verificação pública (válido/expirado) → PDF para download.

---

## 7. Requisitos não funcionais (RNF)

- **RNF01 — LGPD**: consentimento versionado; mínimo de dados na verificação; trilha de auditoria; imagem processada só no dispositivo (nada armazenado nem enviado); conversas do assistente com retenção de 30 dias.
- **RNF02 — PostgreSQL** como persistência.
- **RNF03 — Segurança**: senhas em hash; homologação exige autenticação; audit-trail completo.
- **RNF04 — E-mail**: SMTP por `.env` + painel Admin; falha de e-mail não impede o fluxo.
- **RNF05 — Usabilidade**: portais em PT-BR, uma tela por tarefa, presença validável em < 10s.
- **RNF06 — Demo resiliente**: todos os fluxos críticos operam sem hardware; falha de câmera/permissão/rosto não reconhecido cai na homologação manual; embeddings de seed ficam fora do matching.

---

## 8. Fora do escopo (explicitado)

- Portal com login facial (biometria só na presença — decisão Q10/Q11).
- Módulos de refeitório e visitas (problemas 2 e 3 do desafio).
- Integração real com o RH da Usina (hoje: importação/seed).

---

## 9. Qualidade e evidências

- **Suíte**: 111 testes, 367 asserções, **verde** (PostgreSQL real com pgvector).
- **Cobertura**: aceite do termo, biometria funcional (enrollment, match 1:N, recusa abaixo do limiar, divergência, seed mock fora do matching), janela facial, anti-fraude (duplicidade), homologação (senha certa/errada + motivo novo), ciclo da aula com notificação, verificação pública (VÁLIDO/EXPIRADO/NÃO ENCONTRADO + log + PDF), tickets, justificativas, emissão/reemissão, relatórios CSV, lembretes, configurações, reservas, conformidade, assistente (resposta do turno atual, erro de gateway → cortesia, validação, purge de 30 dias, auditoria só do Admin).
- **Demo ao vivo**: `https://hackathon.dpicinato.com` (domínio público, HTTPS).
- **Credenciais da demo**: todas as senhas `Password@123`, e-mails `login@exemplo.com` (ex.: `isabela.cristina.fonseca` = RH, `heloisa.fernandes.peixoto` = Líder, `otavio.martins.gomes` = Colaborador, `klaus.werner.steiner` = Instrutor externo).
- **Verificação pública**: códigos `PZ-2025-000101` (VÁLIDO) e `PZ-2024-000087` (EXPIRADO).
- **Diário de bordo**: `journey-log.md` — 55 sessões com prompts, decisões e resultados.
- **Relatório final**: `docs/relatorio-final.md` — base do PDF de entrega (ATO 1 síntese + ATO 2 detalhado).

---

## 10. Números-chave para a apresentação

- **34 RFs** (RF01–RF34) + **9 RNFs** (RNF01–RNF09) + **3 SEEDs**.
- **5 papéis** acumuláveis.
- **111 testes / 367 asserções** verdes.
- **55 sessões** de diário de bordo, **55 prompts** polidos no relatório.
- **57 contas** no seed (44 + 13 extras), **5 setores**, **5 treinamentos** de exemplo.
- **Limiar facial: 0,95** (cosseno), matching 1:1 (conferir), seed mock fora do matching.
- **Retenção do assistente: 30 dias** (LGPD).
- **Build**: PHP 8.4, Laravel 13, PG16 + pgvector, sem Node nos assets (exceto runtime do assistente).

---

*Documento gerado para estudo e apresentação — Presenza · Hackathon FATEC & ETEC · Usina Lins · Outubro/2026.*