# Roteiro de demonstraÃ§Ã£o â€” 5 minutos

**Regra de ouro (RNF06): a biometria Ã© FACHADA simulÃ¡vel. Nada depende de cÃ¢mera real ao vivo.**

## Antes de comeÃ§ar (checklist)

- [ ] `docker compose up -d` + `migrate:fresh --seed` executados
- [ ] App dev em `http://localhost:8000` respondendo (`GET /verificar` 200)
- [ ] Mailpit aberto em aba separada: `http://localhost:8025`
- [ ] Telas prontas em abas: `/verificar/PZ-2024-000087` (EXPIRADO) e `/verificar/PZ-2025-000101` (VÃLIDO)
- [ ] SuÃ­te verde: `docker run --rm -v .:/app -w /app presenza/php vendor/bin/phpunit` â†’ 93 testes

## Roteiro (cronÃ³grafo)

**0:00â€“0:30 â€” A dor** (slide/contexto)
"Na Usina Lins, quem atesta presenÃ§a em treinamento Ã© a assinatura numa lista de chamada. O certificado que sai disso nÃ£o tem como conferir." â†’ abre `/verificar` com QR do PZ-2025.

**0:30â€“1:15 â€” A soluÃ§Ã£o em 30 segundos**
- Colaborador (`otavio.martins.gomes`) faz **login**.
- **RF04**: portal bloqueia atÃ© o aceite do termo LGPD (mostrar a tela do termo!). Explica: consentimento explÃ­cito de biometria.
- Portal: aula **NR35 em andamento** â†’ botÃ£o **" Validar presenÃ§a agora (facial)"** â†’ presenÃ§a registrada + auditoria.

**1:15â€“2:15 â€” A exceÃ§Ã£o com anti-fraude (RF15)**
- Logout â†’ login `klaus.werner.steiner` (instrutor) â†’ aula NR35 Aula 3 â†’ **Iniciar aula** (notifica RH!).
- Tela "Fazer chamada": homologaÃ§Ã£o manual â€” escolhe colaborador, **senha do COLABORADOR** + motivo do catÃ¡logo (ou motivo novo no ato â€” RF27).
- Mostrar o flash de erro com **senha errada** (recusado, e a tentativa fica na auditoria).

**2:15â€“3:00 â€” Ticket que faz efeito real (RF10â€“RF13)**
- Login RH `isabela.cristina.fonseca` â†’ **Tickets**: resolver "Justificar falta" da Edson (aprovado); resolver "Cancelar vaga" â€” **fila promovida na hora (RF09)** â€” mostrar notificaÃ§Ã£o do otavio "promovido".
- **Justificativas**: lista padronizada + botÃµes Aprovar/Recusar a qualquer momento (RF12).

**3:00â€“4:00 â€” Certificados Ã  prova (RF18â€“RF22)**
- RH â†’ **Certificados**: lista de elegÃ­veis calculada por presenÃ§a em TODAS as aulas (RF18) â†’ **Emitir**.
- **Reemprimir (RF20)**: nova versÃ£o; voltar abas pÃºblicas â†’ `refresh`: cÃ³digo antigo = **NÃƒO ENCONTRADO**, novo = **VÃLIDO**. Esse Ã© o momento de consumaÃ§Ã£o-wow.
- **RelatÃ³rios** (RF28): gravaÃ§Ã£o/resumo por sessÃ£o + CSV por pessoa aberto no Excel.

**4:00â€“4:45 â€” GovernanÃ§a + correiÃ§Ã£o**
- **ConfiguraÃ§Ãµes (RF26)**: publicar **nova versÃ£o do termo** â†’ login qualquer jÃ¡ re-pede aceite (RF04 adversarial-proof).
- Mailpit: e-mails reais chegando (lembrete/reset).
- Lembretes RF25: scheduler no compose homologa rodando sozinho (`schedule:list`).

**4:45â€“5:00 â€” Fechamento**
"PresenÃ§a nÃ£o falsificÃ¡vel, certificado verificÃ¡vel por qualquer leitor de QR â€” e toda a IA interior EA logada no `journey-log.md`."

## Plano B (se algo falhar)

- Facial "nÃ£o funcionar" â†’ homologaÃ§Ã£o manual (que Ã© ASA real feature, mostre-a com orgulho).
- Banco "nÃ£o construir" â†’ suÃ­te PHPUnit no Postgres (93/305 green) prova o domínio rodando.
- Qualquer 403 de escopo â†’ ISSO Ã© feature (RF05/RF12): "lÃ­der nÃ£o sai do seu setor".
