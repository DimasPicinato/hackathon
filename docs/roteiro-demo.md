# Roteiro de demonstração — 5 minutos

**Regra de ouro (RNF06): a biometria é FACHADA simulável. Nada depende de câmera real ao vivo.**

## Antes de começar (checklist)

- [ ] Stack de demo no ar: `docker compose up -d --build` (seed automático; aguardar ~30 s)
- [ ] App respondendo: `http://localhost:18000/verificar` → 200
- [ ] Mailpit aberto em aba separada: `http://localhost:8025`
- [ ] Telas prontas em abas: `/verificar/PZ-2024-000087` (EXPIRADO) e `/verificar/PZ-2025-000101` (VÁLIDO)
- [ ] Suíte verde: `docker run --rm -v .:/app -w /app presenza/php vendor/bin/phpunit` → 100 testes / 331 asserções
- [ ] Assistente no ar: `docker compose ps assistente` → **Up** (runtime interno `:4096`; a API interna responde só na rede do compose)

**Não arecare: `docker compose restart` re-semeia o banco (reset total) e apaga dados criados ao vivo durante a demo.**

## Roteiro (cronógrafo)

**0:00–0:30 — A dor** (slide/contexto)
"Na Usina Lins, quem atesta presença em treinamento é a assinatura numa lista de chamada. O certificado que sai disso não tem como conferir." → abre `/verificar` com QR do PZ-2025.

**0:30–1:15 — A solução em 30 segundos**
- Colaborador (`otavio.martins.gomes@exemplo.com`) faz **login** — todas as contas do seed já têm o **termo LGPD aceito** (o aceite volta a ser exigido quando o RH publicar nova versão do termo, mostrado em 4:00).
- Portal: aula **NR35 em andamento** → botão **"Validar presença agora (facial)"** → **abre a câmera do navegador**: contagem 3‑2‑1, captura do rosto → **"Rosto não reconhecido — melhor similaridade ~36% (limiar 85)"**.
  Fala pronta: *"Na demo as contas ainda não têm rosto cadastrado — o sistema NUNCA inventa presença. Ele recusa e manda para a via auditável."* (Informe à banca que a imagem da câmera não sai do navegador.)

**1:15–2:15 — A exceção com anti-fraude (RF15) — é aqui que o facial recusado se resolve**
- Logout → login `klaus.werner.steiner` (instrutor) → aula NR35 ativa → chamada.
- Tela da chamada: registrar presença do colaborador que o facial recusou — **homologação manual com a senha do COLABORADOR** + motivo do catálogo (tente "Falha no reconhecimento facial" — agora a história fecha!) ou motivo novo no ato (RF27).
- Mostrar o flash de erro com **senha errada** (recusado, e a tentativa fica na auditoria).

**2:15–3:00 — Ticket que faz efeito real (RF10–RF13)**
- Login RH `isabela.cristina.fonseca` → **Tickets** (na sidebar): resolver "Justificar falta" da Edson (aprovado); resolver "Cancelar vaga" — **fila promovida na hora (RF09)** — mostrar notificação do Otávio "promovido".
- **Justificativas**: lista padronizada + botões Aprovar/Recusar a qualquer momento (RF12).

**3:00–4:00 — Certificados à prova (RF18–RF22)**
- RH → **Certificados**: lista de elegíveis calculada por presença em TODAS as aulas (RF18) → **Emitir**.
- **Reimprimir (RF20)**: nova versão; voltar às abas públicas → refresh: código antigo = **NÃO ENCONTRADO**, novo = **VÁLIDO**. Esse é o momento uau.
- **Relatórios** (RF28): resumo por sessão + CSV por pessoa aberto no Excel.

**4:00–4:30 — Governança + correição**
- **Configurações**: publicar **nova versão do termo** → login qualquer já re-pede aceite (consentimento LGPD à prova de adversário).
- Mailpit: e-mails reais chegando (lembrete/reset).
- Lembretes RF25: scheduler rodando sozinho no compose de homologação (`schedule:list`).

**4:30–4:45 — Assistente público (RF29–RF34)**
- Na página inicial (sem login), abrir o botão **"P"** no canto → perguntar: *"Onde o visitante verifica um certificado?"* → resposta em bloco único, citando `/verificar` e sem pedir dados.
- Perguntar algo fora do escopo (*"qual a previsão do tempo?"*) → recusa educada e volta ao Presenza.
- Mostrar que o **mesmo botão "P" aparece dentro do portal logado** (dashboard/RH) — o assistente é público e não lê dados do usuário.
- Login Admin → **Conversas do assistente**: a pergunta/resposta e o IP registrados (auditoria); explicar que RH/colaborador **não** acessam e que o histórico do visitante vive só na aba.
- Se a primeira resposta demorar (cold start do modelo free), aguardar: o timeout é de 100 s e a segunda em diante responde em ~20 s.

**4:45–5:00 — Fechamento**
"Presença não falsificável, certificado verificável por qualquer leitor de QR — e toda a jornada com IA documentada no `journey-log.md`."

## Plano B (se algo falhar)

- Facial "não funcionar" → homologação manual (que é feature real — mostre-a com orgulho).
- Banco "não subir" → suíte PHPUnit no Postgres (100/331 verde) prova o domínio rodando.
- Assistente "não responder" → a tela mostra a **mensagem de cortesia** (RNF08), sem erro 500; a demo segue. Para religar: `docker compose up -d --build assistente` (a imagem embute `docs/`).
- Qualquer 403 de escopo → ISSO é feature (RF05/RF12): "líder não sai do seu setor".
