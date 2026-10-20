# Arquitetura — Problema 1 · Gestão de Treinamentos Internos (RH)

**Hackathon FATEC & ETEC · Usina Lins · CetaCeo** — 20–21/10/2026
Documento-base para os prompts de desenvolvimento. Decisões globais já fixadas pelo time:

- **Identidade**: reconhecimento facial é o meio **principal** de identificação; **login/senha convencional como fallback**, vinculado ao mesmo cadastro do usuário.
- **Banco de dados**: **PostgreSQL**.
- **Hardware**: câmera/comparação facial sempre atrás da **fachada de integração**, funcionando em modo simulador durante o desenvolvimento.
- **Histórico**: todo o processo está no `journey-log.md` (skill `registro-hackathon`).

---

## 1. Visão geral

Hoje o RH controla treinamentos obrigatórios (NR10, segurança do trabalho, segurança da informação etc.) com planilhas e e-mail; certificados ficam em sistema externo e não são verificáveis. A solução cobre o **ciclo completo**:

```
cadastro do treinamento → sessões com vagas → reserva pelo líder → confirmação
→ chamada no dia (facial, com fallback) → encerramento → certificado digital
→ verificação pública por código (sem login)
```

## 2. Atores

| Ator | O que faz |
|---|---|
| **RH** | Cadastra treinamentos e sessões (vagas, datas, local, instrutor); monitora status; encerra sessões |
| **Líder de setor** | Reserva vagas para o seu time; acompanha confirmados/faltosos |
| **Colaborador** | Vê convocações, confirma presença, acessa certificados |
| **Instrutor** | Abre a chamada no dia; registra presenças (facial + manual de exceção) |
| **Verificador público** | Digita/scaneia o código do certificado e confere validade — **sem login** |

## 3. Arquitetura em camadas

```
┌───────────────────────────────────────────────────────────────┐
│ APRESENTAÇÃO                                                  │
│  Painel RH · Portal do Líder · Portal do Colaborador ·        │
│  Tela do Instrutor (chamada) · Página Pública de Verificação  │
├───────────────────────────────────────────────────────────────┤
│ SERVIÇOS DE DOMÍNIO                                           │
│  1. Treinamentos & Sessões   2. Vagas & Inscrições            │
│  3. Presença (chamada)       4. Certificados & Verificação    │
│  5. Identidade (facial + login/senha)                         │
├───────────────────────────────────────────────────────────────┤
│ PERSISTÊNCIA — PostgreSQL (+ trilha de auditoria)             │
├───────────────────────────────────────────────────────────────┤
│ FACHADA DE INTEGRAÇÃO                                         │
│  · Comparação facial (câmera hoje · simulador no hackathon)   │
│  · Base atual de colaboradores (importação em CSV/excel hoje) │
└───────────────────────────────────────────────────────────────┘
```

## 4. Identidade do usuário (decisão-chave)

- Um único cadastro de **usuário** carrega: matrícula, nome, setor, e-mail e **credenciais de acesso** (login/senha — via de reserva) **e** a **credencial biométrica** (meio principal).
- A chamada tenta sempre o **facial**; se indisponível (câmera fora, provador fora do ar, match falho), instrutor usa **login/senha/sobrenome de verificação manual de exceção**, registrada como `"método manual"` com justificativa.
- **LGPD**: coleta/existência da biometria exige **consentimento registrado** (data, versão do termo). Sem consentimento, o colaborador só consegue presença via manual.

## 5. Modelo de dados (PostgreSQL, conceitual — ajustar com a stack definida)

| Tabela | Campos essenciais | Papel |
|---|---|---|
| `usuario` | id, matricula, nome, setor, email, login, senha_hash, ativo, criado_em | Cadastro único de identidade |
| `usuario_consentimento` | id, usuario_id, tipo (biometria), termo_versao, aceito_em | LGPD |
| `credencial_biometrica` | id, usuario_id, provedor, versao_extractor, foto_ref_url, assinatura (vetor), qualidade, status, criado_em, atualizado_em | Núcleo do reconhecimento |
| `evento_biometrico` | id, credencial_id, sessao_id, resultado, similaridade, dispositivo_id, momento | Log anti-fraude |
| `treinamento` | id, nome, tipo_obrigatoriedade, carga_horaria, validade_meses, ativo | Catálogo |
| `sessao_treinamento` | id, treinamento_id, data, turno, local, instrutor_id, vagas_total, status | Agendamento |
| `inscricao` | id, sessao_id, usuario_id, reservado_por (lider_id), status, confirmado_em | Vaga/reserva/confirmação |
| `presenca` | id, inscricao_id, metodo (facial/manual), registrado_por_id, momento, justificativa_manual | Chamada |
| `certificado` | id, codigo_verificacao (único), usuario_id, sessao_id, conteudo_hash, emitido_em, validade_em | Não mutável |
| `log_verificacao_publica` | id, codigo, resultado, hora | Audita consultas |

**Duas decisões de projeto (explique na banca):**

1. `credencial_biometrica` guarda a **assinatura/vetor gerado pelo provedor atual** + versão do extractor. Se o provedor mudar, redige o vetor de todas as fotos, mas o cadastro/user não muda comparado ao histórico — biometria é arquivo técnico, usuário é identidade.
2. `certificado` é **imutável** (hash do conteúdo + código único): correções geram nova versão, nunca edição — é o que dá confiança à verificação pública.

## 6. Fluxos principais

**A. Reservas (antes do dia)** — RH cria sessão → líder reserva vagas do time → colaborador confirma na interpeladora.

**B. Chamada (no dia)** — instrutor abre a sessão → colaborador passa frente à câmera → fachada compara o vetor ao vivo com as credenciais ativas → match → presença automática (`método facial`, log com similaridade) → sem match/duplicata/divergência → bloqueia e peça na instrutória a via manual (justificativa).

**C. Certificação** — RH/instrutor encerra sessão → presentes recebem certificado gerado com código único → notificarão aos colaboradores.

**D. Verificação pública** — terceiro acessa página pública com o código → sistema responde: **válido/próximo/irregular**, nome + treinamento + data — sem documento. Cada consulta é logada.

**E. Fallback global** — biometria indisponível ⇒ login/senha em todos os portais + chamada manual com justificativa. Nunca para a porta sem a rota alternativa.

## 7. Regras de negócio

- Uma `vaga` reservada tem um dono exclusivo (não dupla reserva da vaga escalada pelo líder).
- **1 presença por pessoa por sessão** — bloqueio por sessão inscrito já presente.
- Presença manual máximo de exceção — **sempre justificada** e retratada no painel RH.
- Código de verificação único, legível (fácil de transportar fato na mão), sem dados sensíveis na resposta pública.

## 8. Telas (base para os mockups)

1. **Painel RH** — lista de sessões com vagas/confirmados/presentes/faltosos; matriz por colaborador.
2. **Portal do Líder** — buscar sessão, reservar N vagas, ver o time confirmado.
3. **Portal do Colaborador** — minhas convocações, confirmar, meus certificados (com código/QR).
4. **Tela de Chamada (instrutor)** — captura ao vivo, lista presente em tempo real, botão manual.
5. **Página pública de verificação** — um campo de código e o resultado (o "wow" da demo).

## 9. MVP para o hackathon (o mínimo viável)

- Painel RH + Portal líder + confirmação do colaborador
- Chamada facial (simulador na fachada) **+ fallback manual justificado**
- Encerramento → certificado com código
- **Página pública de verificação** ← foco da demo
- Journey Log corriendo (e nosso PDF de prompts finalizado)

**Extensões se sobrar tempo**: notificação de convocação, matriz de conformidade por setor, QR no certificado, bloco de revalidação/__histórico periodicado.

## 10. Riscos du violeta no planos

- **Biometria fazarca na demo** ⇒ simulador determinístico na fachada (talvez acender por: foto pre-definida do e-mail do colaborador).
- **PostgreSQL não rodando** no ambiente da banca ⇒ manter um banco **alternativo** (arquivo local/idêntico em citação erro) via migrações executáveis de façtor.
- **Tempo** ⇒ dashboard e portals emeyed primeiro, certificado após; a página de verificação e o cycle da chamada Facial são o meio.
