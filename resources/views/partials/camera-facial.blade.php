{{-- RF03/RF14 — captura facial client-side (face-api.js vendored, sem build).
     A imagem NUNCA sai do navegador: o modelo extrai no cliente um embedding de
     128 dimensões, que é injetado em input hidden `embedding` do form acionado
     (presença RF14.a/RF14.b ou autocadastro /portal/biometria) e enviado com o
     POST nativo (CSRF já embutido nos forms). Referência: overlay da branch
     reconhecimento-facial (8af15f6), aqui com matching REAL no pgvector. --}}
<div id="rec-overlay" class="rec-overlay" hidden>
    <div class="rec-caixa" role="dialog" aria-modal="true" aria-labelledby="rec-titulo">
        <h3 id="rec-titulo">📷 Reconhecimento facial</h3>
        <p class="mini" id="rec-nome"></p>
        <div class="rec-cena">
            <video id="rec-video" autoplay playsinline muted></video>
            <span id="rec-numero" class="rec-numero" hidden></span>
            <span id="rec-flash" class="rec-flash"></span>
        </div>
        <p id="rec-status" class="rec-status"></p>
        <div class="linha-acoes">
            <button type="button" class="btn btn-accent" id="rec-capturar" disabled>Capturar</button>
            <button type="button" class="btn btn-outline" id="rec-cancelar">Cancelar</button>
        </div>
        <p class="mini muted">A imagem da câmera é processada apenas neste dispositivo e não é
            armazenada nem enviada — só o vetor biométrico (128 números, sem rosto) chega ao
            servidor, contra o cadastro com termo LGPD aceito (RF04).</p>
    </div>
</div>

<script>
(function () {
    'use strict';

    var overlay = document.getElementById('rec-overlay');
    if (!overlay) return;

    var video = document.getElementById('rec-video');
    var status = document.getElementById('rec-status');
    var numero = document.getElementById('rec-numero');
    var flash = document.getElementById('rec-flash');
    var botaoCapturar = document.getElementById('rec-capturar');
    var botaoCancelar = document.getElementById('rec-cancelar');
    var rotulo = document.getElementById('rec-nome');

    var stream = null;
    var formAlvo = null;
    var rostoVisivel = false;
    var loop = null;
    var modelosOk = false;

    function definirStatus(texto, tipo) {
        status.textContent = texto;
        status.className = 'rec-status' + (tipo ? ' rec-status-' + tipo : '');
    }

    function pararCamera() {
        if (loop) { clearInterval(loop); loop = null; }
        if (stream) {
            stream.getTracks().forEach(function (t) { t.stop(); });
            stream = null;
        }
        rostoVisivel = false;
        botaoCapturar.disabled = true;
    }

    function fechar() {
        pararCamera();
        overlay.hidden = true;
        formAlvo = null;
    }

    function carregarModelos() {
        if (modelosOk) return Promise.resolve(true);
        definirStatus('Carregando modelo facial…');
        var opcoes = { inputSize: 320, scoreThreshold: 0.5 };
        return Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models/faceapi'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models/faceapi'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models/faceapi')
        ]).then(function () {
            modelosOk = true;
            return opcoes;
        });
    }

    function iniciarDeteccao() {
        var detector = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

        loop = setInterval(function () {
            faceapi.detectSingleFace(video, detector).withFaceLandmarks()
                .then(function (resultado) {
                    if (resultado) {
                        if (!rostoVisivel) {
                            rostoVisivel = true;
                            botaoCapturar.disabled = false;
                            definirStatus('Rosto detectado — posicionado bem iluminado, clique em Capturar.', 'ok');
                        }
                    } else if (rostoVisivel) {
                        rostoVisivel = false;
                        botaoCapturar.disabled = true;
                        definirStatus('Nenhum rosto visível — olhe para a câmera.');
                    } else if (status.textContent.indexOf('Nenhum rosto') === -1) {
                        definirStatus('Nenhum rosto visível — olhe para a câmera.');
                    }
                })
                .catch(function () { /* frames corrompidos no meio do boot da câmera */ });
        }, 350);
    }

    function contagem(fn) {
        var restante = 3;
        numero.hidden = false;
        numero.textContent = restante;
        var t = setInterval(function () {
            restante--;
            if (restante > 0) {
                numero.textContent = restante;
                return;
            }
            clearInterval(t);
            numero.hidden = true;
            flash.classList.add('rec-flash-ativo');
            setTimeout(function () { flash.classList.remove('rec-flash-ativo'); }, 220);
            fn();
        }, 700);
    }

    function capturar() {
        botaoCapturar.disabled = true;
        definirStatus('Extraindo vetor biométrico…');
        clearInterval(loop);
        loop = null;

        var detector = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

        faceapi.detectSingleFace(video, detector).withFaceLandmarks().withFaceDescriptor()
            .then(function (resultado) {
                if (!resultado || !resultado.descriptor) {
                    pararCamera();
                    definirStatus('Não consegui ler o rosto no instante da captura — tente de novo.', 'erro');
                    return;
                }
                var embedding = Array.from(resultado.descriptor);
                formAlvo.querySelector('input[name="embedding"]').value = JSON.stringify(embedding);
                overlay.hidden = true;
                pararCamera();
                formAlvo.submit(); // POST nativo: embedding + CSRF do próprio form
            })
            .catch(function () {
                pararCamera();
                definirStatus('Falha ao processar a captura — tente novamente.', 'erro');
            });
    }

    function abrir(form) {
        formAlvo = form;
        rotulo.textContent = form.dataset.nome ? 'Colaborador: ' + form.dataset.nome : 'Modo: ' + (form.dataset.modo === 'cadastro' ? 'cadastro do rosto' : 'validação de presença');
        definirStatus('Iniciando câmera…');
        overlay.hidden = false;

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            definirStatus('Este navegador não expõe câmera (getUserMedia) — use a homologação manual (RF15).', 'erro');
            return;
        }

        var iniciarCamera = function () {
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } })
                .then(function (s) {
                    stream = s;
                    video.srcObject = s;
                    return video.play();
                })
                .then(function () {
                    definirStatus('Olhe para a câmera — aguardando detectar um rosto.');
                    iniciarDeteccao();
                })
                .catch(function () {
                    definirStatus('Não consegui acessar a câmera (permissão negada ou em uso) — use a homologação manual (RF15).', 'erro');
                });
        };

        carregarModelos()
            .then(function () { iniciarCamera(); })
            .catch(function () {
                definirStatus('Não consegui carregar o modelo facial (rede/CDP bloqueada) — use a homologação manual (RF15).', 'erro');
            });
    }

    // face-api.min.js entra com `defer`: só estará pronto antes de DOMContentLoaded.
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.faceapi === 'undefined') return; // ambiente sem o vendor: forms seguem nativos

        document.querySelectorAll('form[data-facial]').forEach(function (form) {
            // input hidden que recebe o vetor antes do submit nativo
            if (!form.querySelector('input[name="embedding"]')) {
                var campo = document.createElement('input');
                campo.type = 'hidden';
                campo.name = 'embedding';
                form.appendChild(campo);
            }

            form.addEventListener('submit', function (evento) {
                if (form.querySelector('input[name="embedding"]').value) return; // segundo submit (após captura)
                evento.preventDefault();
                abrir(form);
            });
        });

        botaoCapturar.addEventListener('click', function () { contagem(capturar); });
        botaoCancelar.addEventListener('click', fechar);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !overlay.hidden) fechar();
        });
    });
})();
</script>
