# Roteiro de demonstração — 5 minutos

**Regra de ouro (RNF06): a biometria é FACHADA simulável. Nada depende de câmera real ao vivo.**

## Antes de começar (checklist)

- [ ] `docker compose up -d` + `migrate:fresh --seed` executados
- [ ] App dev em `http://localhost:8000` respondendo (`GET /verificar` 200)
- [ ] Mailpit aberto em aba separada: `http://localhost:8025`
- [ ] Telas prontas em abas: `/verificar/PZ-2024-000087` (EXPIRADO) e `/verificar/PZ-2025-000101` (VÁLIDO)
- [ ] Suíte verde: `docker run --rm -v .:/app -w /app presenza/php vendor/bin/phpunit` → 76 testes

## Roteiro (cronógrafo)

**0:00–0:30 — A dor** (slide/contexto)
"Na Usina Lins, quem atesta presença em treinamento é a assinatura numa lista de chamada. O certificado que sai disso não tem como conferir." → abre `/verificar` com QR do PZ-2025.

**0:30–1:15 — A solução em 30 segundos**
- Colaborador (`otavio.martins.gomes`) faz **login**.
- **RF04**: portal bloqueia até o aceite do termo LGPD (mostrar a tela do termo!). Explica: consentimento explícito de biometria.
- Portal: aula **NR35 em andamento** → botão **" Validar presença agora (facial)"** → presença registrada + auditoria.

**1:15–2:15 — A exceção com anti-fraude (RF15)**
- Logout → login `klaus.werner.steiner` (instrutor) → aula NR35 Aula 3 → **Iniciar aula** (notifica RH!).
- Tela "Fazer chamada": homologação manual — escolhe colaborador, **senha do COLABORADOR** + motivo do catálogo (ou motivo novo no ato — RF27).
- Mostrar o flash de erro com **senha errada** (recusado, e a tentativa fica na auditoria).

**2:15–3:00 — Ticket que faz efeito real (RF10–RF13)**
- Login RH `isabela.cristina.fonseca` → **Tickets**: resolver "Justificar falta" da Edson (aprovado); resolver "Cancelar vaga" — **fila promovida na hora (RF09)** — mostrar notificação do otavio "promovido".
- **Justificativas**: lista padronizada + botões Aprovar/Recusar a qualquer momento (RF12).

**3:00–4:00 — Certificados à prova (RF18–RF22)**
- RH → **Certificados**: lista de elegíveis calculada por presença em TODAS as aulas (RF18) → **Emitir**.
- **Reemprimir (RF20)**: nova versão; voltar abas públicas → `refresh`: código antigo = **NÃO ENCONTRADO**, novo = **VÁLIDO**. Esse é o momento de consumação-wow.
- **Relatórios** (RF28): gravação/resumo por sessão + CSV por pessoa aberto no Excel.

**4:00–4:45 — Governança + correição**
- **Configurações (RF26)**: publicar **nova versão do termo** → login qualquer já re-pede aceite (RF04 adversarial-proof).
- Mailpit: e-mails reais chegando (lembrete/reset).
- Lembretes RF25: scheduler no compose homologa rodando sozinho (`schedule:list`).

**4:45–5:00 — Fechamento**
"Presença não falsificável, certificado verificável por qualquer leitor de QR — e toda a IA interior EA logada no `journey-log.md`."

## Plano B (se algo falhar)

- Facial "não funcionar" → homologação manual (que é ASA real feature, mostre-a com orgulho).
- Banco "não construir" → suíte PHPUnit no Postgres (76/239 green) prova o domain rolada.
- Qualquer 403 de escopo → ISSO é feature (RF05/RF12): "líder não sai do seu setor".
