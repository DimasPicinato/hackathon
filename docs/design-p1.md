# Design System — P1 · Gestão de Treinamentos (Usina Lins)

**Decisão do time (20/10/2026, Q31–Q33 — ver `docs/questionario-requisitos-p1.md`)** · Fonte das paletas: dataset da skill `ui-ux-pro-max` (row "Agriculture/Farm Tech" com accent ajustado para o laranja de segurança industrial).

## 1. Conceito visual

**"A cor da usina e a cor da segurança."** Verde agro como identidade institucional + laranja de segurança industrial (associado culturalmente a EPI/NR/treinamentos) como cor de ação. Neutros slate para dados densos (dashboard RH), fundo levemente esverdeado para superficies.

## 2. Paleta (tokens semânticos)

| Token | Hex | Uso |
|---|---|---|
| `primary` | `#15803D` | Identidade: header, botões primários, links, ring de foco |
| `on-primary` | `#FFFFFF` | Texto/ícones sobre primary |
| `secondary` | `#22C55E` | Estados de progresso, chips de treino confirmado |
| `on-secondary` | `#0F172A` | Texto sobre secondary |
| `accent` | `#EA580C` | CTAs de ação (reservar/emitir/iniciar aula), alertas NR |
| `on-accent` | `#0F172A` | Texto sobre accent (contraste > 4.5:1) |
| `background` | `#F0FDF4` | Fundo geral dos portais |
| `foreground` | `#14532D` | Texto padrão sobre background |
| `card` | `#FFFFFF` | Cartões/tabelas/telas |
| `card-foreground` | `#14532D` | Texto em card |
| `muted` | `#E8F0F1` | Superficies secundárias (placeholders, badges) |
| `muted-foreground` | `#475569` | Texto secundário |
| `border` | `#BBF7D0` | Bordas/divisores (tom esverdeado) |
| `destructive` | `#DC2626` | Cancelamentos, rejeição de justificativa |
| `on-destructive` | `#FFFFFF` | |
| `ring` | `#15803D` | Foco de teclado |

### Tokens de status (herdam a semântica exigida pelos requisitos)

| Token | Hex | Uso (regra de negócio) |
|---|---|---|
| `status-facial` | `#16A34A` | Presença validada por facial (verde cheio — RF17) |
| `status-manual` | `#D97706` | Presença homologada manualmente (âmbar — RF15/RF17) |
| `status-aprovado` | `#16A34A` | Justificativa/ticket APROVADO (RF12) |
| `status-recusado` | `#DC2626` | RECUSADO (RF12) |
| `status-pendente` | `#F59E0B` | Ticket/justificativa pendente |
| `status-fila` | `#0891B2` | Em fila de espera (RF09) |
| `status-expirado` | `#DC2626` | Certificado expirado na verificação pública (RF21/22) |

Dark mode: **não é escopo do MVP** (portal interno, uso diurno) — tokens acima são light-only por decisão de escopo.

## 3. Tipografia (Google Fonts — visível também no certificado)

| Papel | Fonte | Pesos |
|---|---|---|
| Títulos/headings | **Lexend** | 500, 600, 700 |
| Corpo/labels | **Source Sans 3** | 300, 400, 500, 600, 700 |

Racional (para a banca): Lexend foi **desenhada para legibilidade** — decisão alinhada ao público (do chão de fábrica ao admin) e ao RNF de acessibilidade/a11y. Números tabulares (`tabular-nums`) para tabelas de presença e códigos de certificado.

## 4. Densidade e iconografia

- Dashboards do RH/líder: **media density** (tabelas com 8px de ritmo interno, respiro de 16px entre blocos).
- Telas de chamada/validação: ** espaçadas** — uma ação por tela, toque fácil no chão de fábrica (alvo ≥ 44px).
- Ícones: família única vetorial (sem emoji como ícone), no tom `primary`/`muted-foreground`.

## 5. Kit de skills de design/UI-UX (decisão Q33)

**Kit de trabalho (fluxo):**

1. **`ui-ux-pro-max`** — paleta, tipografia, guidelines UX, icons, searchable (o "cérebro" do design).
2. **`frontend-design`** — direção estética intencional na hora de desenhar cada tela/mockup.
3. **`ui-styling`** — implementação acessível e componentes de UI consistentes.
4. **`design-system`** — token architecture (MASTER.md + overrides por página) para manter consistência.
5. **`web-design-guidelines`** — auditoria final pré-banca (checklist de acessibilidade/UX).

**Opcionais (por etapa de entrega):** `banner-design` (posster/banner da apresentação), `slides` (apresentação final HTML), `shadcn` (se o stack de implementação usar componentes prontos), `firecrawl-website-design-clone` (**única pendência**: se a Usina Lins tiver site oficial com identidade própria, extrair logo/cores para alinhar a marca).

## 6. Referência cruzada

- Requisitos de negócio que exigem cores de status: `docs/requisitos-p1.md` (RF09, RF12, RF15, RF17, RF21–RF22).
- Histórico das decisões: `journey-log.md` (sessão 13).
- Transcrição das perguntas de design: `docs/questionario-requisitos-p1.md` (seção "Rodada de design", Q31–Q33).
