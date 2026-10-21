{{-- RF29–RF34 — widget público do Assistente Presenza (sem login; aviso LGPD; histórico só na aba) --}}
<link rel="stylesheet" href="/css/assistente.css">
<div class="assistente" x-data="assistenteWidget()" x-cloak>
    <button type="button" class="assistente-botao" @click="abrir = !abrir"
            :aria-expanded="abrir ? 'true' : 'false'" aria-controls="assistente-painel"
            :aria-label="abrir ? 'Fechar assistente' : 'Abrir assistente'">P</button>

    <section id="assistente-painel" class="assistente-painel" x-show="abrir" x-cloak
             role="dialog" aria-label="Assistente Presenza" @keydown.escape.window="abrir = false">
        <header class="assistente-cabecalho">
            <strong>Assistente Presenza</strong>
            <button type="button" class="assistente-fechar" @click="abrir = false" aria-label="Fechar assistente">&times;</button>
        </header>

        <p class="assistente-aviso">
            &#9888; Esta conversa é registrada para auditoria (IP e data). Não envie dados pessoais.
        </p>

        <div class="assistente-mensagens" x-ref="lista" aria-live="polite">
            <template x-for="msg in mensagens" :key="msg.id">
                <div class="assistente-msg" :class="msg.autor === 'voce' ? 'assistente-msg-voce' : 'assistente-msg-assistente'" x-text="msg.texto"></div>
            </template>
            <div class="assistente-digitando" x-show="carregando">Assistente escrevendo&hellip;</div>
        </div>

        <form class="assistente-form" @submit.prevent="enviar()">
            <textarea x-model="pergunta" maxlength="500" rows="1" placeholder="Digite sua pergunta..." required></textarea>
            <button type="submit" class="assistente-enviar" :disabled="carregando || pergunta.trim().length < 3" aria-label="Enviar pergunta">&rarr;</button>
        </form>

        <p class="assistente-rodape">IA · responde só sobre o Presenza · sem dados pessoais</p>
    </section>
</div>
@once
    <script src="/js/alpine.min.js" defer></script>
    <script>
        function assistenteWidget() {
            return {
                abrir: false,
                carregando: false,
                pergunta: '',
                sessao: (window.crypto && crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2)),
                mensagens: [{
                    id: 1,
                    autor: 'assistente',
                    texto: 'Olá! Sou o Assistente Presenza. Explico como funciona a gestão de treinamentos: vagas, presença, certificados e verificação. Nenhuma informação sua é necessária para conversar.'
                }],

                async enviar() {
                    if (this.carregando) return;
                    const texto = this.pergunta.trim();
                    if (texto.length < 3) return;

                    this.mensagens.push({ id: Date.now(), autor: 'voce', texto: texto });
                    this.pergunta = '';
                    this.carregando = true;
                    this.rolar();

                    try {
                        const resposta = await fetch('/assistente', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ sessao: this.sessao, pergunta: texto })
                        });
                        const dados = await resposta.json();
                        this.mensagens.push({
                            id: Date.now() + 1,
                            autor: 'assistente',
                            texto: dados.resposta || 'Não consegui responder agora. Fale com o RH da Usina.'
                        });
                    } catch (erro) {
                        this.mensagens.push({
                            id: Date.now() + 1,
                            autor: 'assistente',
                            texto: 'O assistente está indisponível neste momento. Fale com o RH da Usina ou abra um ticket pelo portal.'
                        });
                    } finally {
                        this.carregando = false;
                        this.rolar();
                    }
                },

                rolar() {
                    this.$nextTick(() => {
                        const lista = this.$refs.lista;
                        if (lista) lista.scrollTop = lista.scrollHeight;
                    });
                }
            };
        }
    </script>
@endonce
