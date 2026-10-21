# Transcrição do Questionário de Requisitos — P1 · Gestão de Treinamentos

**Hackathon FATEC & ETEC · Usina Lins** — 20/10/2026, via questionário interativo do OpenCode
Estrutura: skill `grilling` (mattpocock/skills) — perguntas em rodadas, sempre com resposta recomendada; o time respondeu e ajustou livremente.
Documento consolidado: `docs/requisitos-p1.md` · Histórico: `journey-log.md` (sessões 9–10)

---

## Rodada 1 — Produto, operação e dados de demo (Q1–Q8)

### Q1 — Verificação pública com validade
**Pergunta:** A página pública de verificação deve indicar também se o certificado **expirou** (ex.: NR10/35 ≈ 2 anos), e não só se existe? Exemplo: certificado da Ana de NR10 emitido em 2024 vence em 2026 — o que a consulta por código responde?
**Resposta:** `Sim, mostra expirado (recomendado)`.
**Impacto:** RF21/RF22 (status VÁLIDO/EXPIRADO/NÃO ENCONTRADO).

### Q2 — Presença manual de exceção
**Pergunta:** A presença manual (sem facial) deve ficar sempre disponível ao instrutor, com justificativa + marcação distinta no painel RH?
**Resposta:** "sim, sempre com motivo opcional, porém recomendado **e uma lista de opções pré-prontas para ele clicar e já preencher o motivo** … e ele pode **adicionar um motivo novo** também; essa lista de motivos pode iniciar zerada ou por meio de um seed."
**Impacto:** RF15/RF26/RF27 (catálogo de motivos editável, motivo novo no ato).

### Q3 — Semântica da vaga
**Pergunta:** Vagas nominais por colaborador ( Marcos escolhe João, Maria, Pedro) ou blocos sem titular ("3 vagas para o time")?
**Resposta:** `Vaga nominal (recomendado)`.
**Impacto:** RF08.

### Q4 — Lista de espera
**Pergunta:** Sessão com 20 vagas esgotadas e Paulo como 21º: a vaga devolvida vai automaticamente a ele (fila por ordem de pedido) ou só aparece "esgotado"?
**Resposta:** `Sim, fila automática (recomendado)`.
**Impacto:** RF09.

### Q5 — Cancelamentos e justificativas
**Pergunta:** Quem pode cancelar/justificar, e até quando?
**Resposta:** "a qualquer momento, os usuários que farão o treinamento podem lançar um **ticket** no sistema relacionado ao treinamento, que pode ser visto pelo RH ou pelo Líder; ambos podem **cancelar a vaga dele ou justificar falta**; as faltas emitidas pelo RH e Líder podem ter o status **APROVADO ou RECUSADO alterados a qualquer momento por eles**; já o usuário do treinamento **só pode submeter a justificativa e não pode alterar**."
**Impacto:** RF10–RF13 (fluxo de tickets).

### Q6 — Canais de aviso
**Pergunta:** Convocações/lembretes/certificados: aviso só no app, ou também e-mail real?
**Resposta:** `App + e-mail real`.
**Impacto:** RF23.

### Q7 — Consentimento LGPD
**Pergunta:** Incluir tela de aceite do termo de biometria ("Autorizo o uso da minha imagem/facial para registro de presença") como etapa funcional; sem aceite, só presença manual?
**Resposta:** `Sim, tela de aceite (recomendado)`.
**Impacto:** RF04.

### Q8 — Dados de demonstração
**Pergunta:** Demo com colaboradores fictícios + fotos dos integrantes do time no simulador facial?
**Resposta:** "esse mock será feito através de um **seed no banco de dados, incluindo os metadados de reconhecimento facial** inclusos nos registros desses usuários; de resto, toda a manipulação de usuários deve ser feita pelo **RH ou Líder — todas as operações de CRUD e manutenção de usuários**."
**Impacto:** SEED01/SEED02, RF05.

---

## Rodada 2 — Modelo de chamada, aulas e infraestrutura (Q9–Q16)

### Q9 — Confirmação do fluxo de tickets
**Resposta:** `Sim, é isso (recomendado)`. (Participante só **abre** ticket; RH/líder executam cancelamento ou justificativa; apenas RH/líder alteram APROVADO/RECUSADO.)
**Impacto:** RF10–RF12.

### Q10 — Onde o facial atua
**Pergunta:** Facial só na chamada, ou também no login dos portais?
**Resposta:** "a gente decide isso melhor mais tarde… quero entender melhor como vai funcionar o sistema para saber onde deve ser aplicado a anti-fraude (reconhecimento facial)."
**Resolução posterior:** na Q11 — facial apenas validação de presença; portais com login/senha.
**Impacto:** RF03.

### Q11 — Instrutor e entidade de aula ⭐ (mudou o modelo)
**Pergunta:** Instrutor tem perfil próprio que abre a chamada, ou RH/líder fazem a chamada?
**Resposta:** "o treinador tem um perfil próprio, e com isso **adicionaremos uma entidade de controle no sistema que servirá como módulo e aula do treinamento** — o professor pode **iniciar aula, encerrar aula, atualizar esses horários, controlar a chamada da aula, e isso é notificado ao RH**. Isso soluciona a questão anterior: o reconhecimento facial **servirá apenas para validar presença**, tirando o login e senha como fallback e **trocando o fallback pela presença do Instrutor** (homologação), para flexibilidade."
**Impacto:** RF06, RF03, RF14–RF17.

### Q12 — Treinamento multi-dia
**Resposta:** "Trataremos isso **por meio de aulas**: no período em que a aula estiver ocorrendo, pode-se validar a presença com reconhecimento facial (modelo definido na Q11)."
**Impacto:** RF06 (Treinamento 1:N Aula).

### Q13 — Etapa "confirmar presença"
**Resposta:** "a questão dos tickets já resolve, porque a confirmação não é feita pelo usuário aluno do treinamento — ele apenas **submete tickets**, e o RH vê o ticket e atualiza a questão da vaga dele; pode-se **remover a questão do módulo e deixar apenas as aulas de treinamento**, ficando **(Treinamento 1:N Aula)**."
**Impacto:** RF10 (sem botão "confiro" — status vem de ticket/fila/presença).

### Q14 — Formato do certificado
**Resposta:** "tem **os dois**: o QR code, ou código, ou link manda para a **tela web**, e ela dá a **opção de download do PDF**."
**Impacto:** RF21.

### Q15 — Manutenção dos catálogos
**Resposta:** `Sim, RH edita (recomendado)` (painel de configuração para motivos e versão do termo).
**Impacto:** RF26.

### Q16 — Canal do e-mail real
**Pergunta:** Conta do time via `.env` ou serviço de teste (Mailtrap)?
**Resposta:** "isso é **requisito não funcional**; a arquitetura que vou implementar **já tem um módulo de envio de e-mail via SMTP com configuração por `.ENV`**; sugerido painel de configuração de SMTP para o Líder — **ajustado depois (Q19) para o Admin**."
**Impacto:** RNF04, RF23.

---

## Rodada 3 — Papéis e permissões (Q17–Q23)

### Q17 — Tipos de usuário e acumulação
**Resposta:** `Sim, 4 papéis + público (recomendado)` (Colaborador, Líder, Instrutor, RH + Público; papéis acumuláveis na mesma conta — ex.: Marcos é colaborador E líder).
**Impacto:** RF02.

### Q18 — Matriz de permissões
**Resposta:** "sim, está correta, mas **adicione o Admin que tem poder total em tudo**; também faça com que **o líder tenha todas as permissões no setor dele**."
**Impacto:** RF02 + matriz §1 (Admin; escopo de setor do Líder).

### Q19 — Dono da config SMTP
**Resposta:** "deixa essa questão do SMTP **apenas para o Admin**."
**Impacto:** RF26/RNF04.

### Q20 — Qualificação do certificado
**Resposta:** `Todas as aulas (recomendado)` (falta justificada e aprovada = sem certificado; aula precisa ser reposta).
**Impacto:** RF18.

### Q21 — Janela de validação do facial
**Resposta:** `Sim, só na aula ativa (recomendado)` (fora da janela → homologação manual do instrutor com motivo).
**Impacto:** RF03/RF15.

### Q22 — Quem abre tickets
**Resposta:** "**apenas os colaboradores podem abrir tickets em seus nomes**; o líder não precisa abrir ticket — como a finalidade é apenas informar, e nesse caso o líder já sabe, então ele pode **dar a justificativa de falta diretamente**."
**Impacto:** RF10–RF13.

### Q23 — Gatilhos de notificação
**Resposta:** `Padrão + lembretes` (pacote padrão de eventos + lembrete 24h antes da aula e avanço na fila).
**Impacto:** RF24/RF25.

---

## Rodada 4 — Escopo do líder, tickets, certificado e relatórios (Q24–Q30)

### Q24 — Até onde vai o líder
**Resposta:** `Sim, líder também cadastra` (treinamentos/sessões do seu setor).
**Impacto:** RF07.

### Q25 — Formato do ticket
**Resposta:** "pode deixar a questão do **tipo livre**, onde **os tipos podem ser cadastrados pelo administrador**; por padrão o **seed traz essas 3 sugestões** (cancelar vaga / justificar falta / informação); o colaborador **seleciona um tipo + texto livre**."
**Impacto:** RF11.

### Q26 — Emissão de certificados
**Resposta:** `RH emite manualmente` (revisão antes de emitir).
**Impacto:** RF19.

### Q27 — Relatórios
**Resposta:** `Completo + exportação` (por sessão, colaborador, setor, matriz de conformidade, instrutor/aula + CSV/PDF).
**Impacto:** RF28.

### Q28 — Recuperação de senha
**Resposta:** `Reset por e-mail` (link temporário via SMTP — dispensa reset presencial pelo RH).
**Impacto:** RF01.

### Q29 — Aceite do termo LGPD
**Resposta:** `No primeiro login (recomendado)`; nova versão re-pede aceite na entrada seguinte.
**Impacto:** RF04.

### Q30 — Dados na verificação pública
**Resposta:** `Sim, mínimo necessário (recomendado)` (número, nome, treinamento, carga horária, datas, instrutor, status, download do PDF — sem foto/documentos/setor).
**Impacto:** RF21.

---

## Ajuste final presencial (após consolidação) ⭐

> "o que precisamos ajustar é: **para confirmar presença, tanto o colaborador quanto o instrutor podem fazer o reconhecimento facial para marcar presença** — seja o colaborador de forma **autônoma** durante a aula, seja **o instrutor também durante a aula**. Inclusive, o instrutor também pode **cadastrar motivo na hora** de preencher o motivo para o caso de validar presença **manualmente**; e será **exigida a senha de acesso do colaborador** caso precise cadastrar **manualmente** o motivo da presença."

**Impacto:** RF14 (facial em duas formas: autônoma e via instrutor) + RF15 (homologação = senha do colaborador + motivo do catálogo **ou novo cadastrado no ato**).

## Confirmação final

Time validou o resumo consolidado (após o ajuste acima): `Correto, gera a doc` → requisitos consolidados em `docs/requisitos-p1.md` e aprovados.

---

## Rodada de design (Q31–Q33) — após o documento de requisitos

### Q31 — Paleta de cores
**Pergunta:** Paleta base para o produto (mesma linguagem em todos os portais e no certificado), candidatas extraídas do dataset da skill `ui-ux-pro-max`.
**Resposta:** `Agro verde + laranja segurança (recomendado)` — verde `#15803D` + laranja `#EA580C`.
**Impacto:** `docs/design-p1.md` §2 (paleta) e tokens de status.

### Q32 — Tipografia
**Resposta:** `Lexend + Source Sans 3 (recomendado)` (legibilidade/a11y; números tabulares).
**Impacto:** `docs/design-p1.md` §3.

### Q33 — Kit de skills de design/UI-UX
**Resposta:** `Confirma o kit (recomendado)` — ui-ux-pro-max + frontend-design + ui-styling + design-system + web-design-guidelines; opcionais banner/slides/shadcn/firecrawl-clone.
**Impacto:** `docs/design-p1.md` §5.
