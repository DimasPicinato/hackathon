# Design — Assistente de IA (widget público)

> **Produto: Presenza** — *presenças reais, certificados à prova.*
> 21/10/2026 · Mockup para **aprovação do time (AT01) antes de implementar** — decisão Q49/Q54.
> Tokens, paleta e tipografia: exatamente `docs/design-p1.md` (verde agro `#15803D`, laranja de segurança `#EA580C`, Lexend + Source Sans 3, cards `.card`, botões `.btn`). **Sem build**: Blade + Alpine vendored, CSS herdando o `app.css` existente.

## 1. Comportamento (Q37/Q47/Q55 — travado nos requisitos RF29–RF34)

| Aspecto | Decisão |
|---|---|
| Onde aparece | Botão flutuante **apenas nas páginas públicas**: home `/`, login e `/verificar` (+ tela de resultado). **Nunca** nos portais logados. |
| Sem login | Widget aberto a qualquer visitante; sem formulário de identificação. |
| Respostas | Bloco único após carregar, com **indicador "assistente escrevendo…"** (sem streaming). |
| Histórico | Vive **só na aba**; recarregar/fechar zera (RF34). O registro completo fica no banco → visão do Admin (RF33). |
| Falha | Mensagem de cortesia: "Não consegui consultar o assistente agora. Fale com o RH ou abra um ticket pelo portal." (RNF08) |
| Temas | Só Presenza; fora do escopo → recusa educada (RF31). |
| Verificação de certificado | O chat **não verifica código** — orienta para `/verificar` (Q56). |

## 2. Botão flutuante (fechado)

- **Posição**: canto inferior direito (todas as resoluções), `z-index` acima do restante, `16px` das bordas.
- **Forma**: círculo `56px`, fundo **laranja de segurança `#EA580C`**, letra "**P**" branca (mesma gramática do `brand-mark`), sombra suave; `hover`/`focus`: escala 1.05 + anel de foco verde.
- **Contraste**: branco sobre laranja ≥ 3:1 (ícone decorativo; a cor é reforço, não o único sinal).
- **A11y**: `<button aria-label="Abrir assistente" aria-expanded="false">`; teclado abre/fecha (`Enter`/`Esc`).

## 3. Painel de chat (aberto)

Desktop: caixa **360 × 480px** ancorada no botão (canto inf. dir.), raio `12px`, borda `1px` verde-média, sombra idêntica aos `.card`. Mobile (`< 480px`): painel ocupa a tela menos barra superior, cantos retos — feeling de página, não de janela.

```
┌──────────────────────────────────────┐
│ ● Assistente Presenza           [×]  │ ← header verde `#15803D` (texto branco)
├──────────────────────────────────────┤
│ ⚠ Aviso: esta conversa é gravada     │ ← aviso LGPD (Q58): fundo neutro verde-claro,
│   para auditoria (IP e data/situação)│   texto escuro; não pedimos dados pessoais
├──────────────────────────────────────┤
│                                      │
│  ┌ Assistente ────────────────────┐  │ ← bolhas assistente: fundo verde-suave,
│  │ Olá! Sou o Assistente Presenza;│  │   alinhado à esquerda, raio 12px
│  │ posso explicar como o sistema  │  │
│  │ funciona (vagas, presença,     │  │
│  │ certificado...). Como ajudo?   │  │
│  └────────────────────────────────┘  │
│                                      │
│   ┌──────────────────── Você ─────┐  │ ← bolhas do visitante: fundo laranja `#EA580C`,
│   │ Como funciona a fila de       │  │   alinhado à direita, texto branco
│   │ espera?                       │  │
│   └───────────────────────────────┘  │
│                                      │
│   ┌ Assistente: escrevendo… ●●●      │ ← estado carregando (Q47): 3 pontos animados;
│   └────────────────────────────────┘  │   substituído pela resposta em bloco único
├──────────────────────────────────────┤
│ [Digite sua pergunta...        ][➤]  │ ← input + botão enviar (laranja); Enter envia;
│ powered by IA · sem dados pessoais   │   textarea 1 linha → 3 (auto-expande); máx. 500 chars
└──────────────────────────────────────┘
```

### Estados

1. **Fechado** — só o botão flutuante.
2. **Aberto** — painel + primeira mensagem de apresentação fixa (texto curto, PT-BR).
3. **Carregando** — input desabilitado + bolha "escrevendo… ●●●".
4. **Resposta** — bloco único renderizado (texto plano; **sem** markdown/heurísticas HTML — segurança XSS).
5. **Erro/cortesia** — bolha de cortesia (RNF08) com link real para `/verificar` quando a pergunta for sobre certificado.
6. **Fora do escopo** — mesma bolha com recusa educada (RF31).

### Côpia (pt-BR, tom do produto)

- **Apresentação:** "Olá! Eu sou o Assistente Presenza. Explico como funciona a gestão de treinamentos: vagas, presença, certificados e verificação. Nenhuma informação sua é necessária para conversar."
- **Aviso LGPD (fixo no topo):** "⚠ Esta conversa é registrada para auditoria (IP e data). Não pedimos e não devemos receber dados pessoais aqui."
- **Cortesia (erro):** "O assistente está indisponível neste momento. Fale com o RH da Usina ou abra um ticket pelo portal."
- **Fora de escopo:** "Converso apenas sobre o sistema Presenza. Sobre treinamentos, presença ou certificados, pode perguntar!"

## 4. Integração (para o time aprovar a lógica junto com a forma)

| Camada | O que acontece |
|---|---|
| Widget (Alpine) | Clique no botão → painel; envia `POST /assistente` (rota pública) com a pergunta; recebe bloco; badge "sem dados pessoais" no rodapé. |
| Endpoint Laravel | Rota pública **sem limite de taxa** (decisão Q45 — risco aceito, RNF09); guarda pergunta/resposta + IP + data (RF33); chama o runtime; se cair → cortesia com HTTP 200 (500 quebraria a UX). |
| Runtime OpenCode | Contêiner próprio (`opencode serve`) no `docker-compose.yaml`; `opencode.json` fixa agente PT-BR, modelo `opencode/big-pickle`, permissões só-leitura, fontes = pasta `docs/` + `docs/mapa-paginas.md`. |
| Visão Admin | Página em `/portal/rh/assistente` (Admin): lista de conversas (pergunta/resposta/IP/data), sem editar nada. |

## 5. Aprovação

- [ ] Time aprova o mockup (forma + cópia + integração) → implementação abre nesta janela (demo às **14h30** de hoje).
- Aprovar pelos três pontos: botão flutuante nas públicas · painel conforme figurino acima · cópia/aviso como definido.
