<section>
    <p class="muted">Exclui a sua conta e todos os seus dados de forma permanente. Antes de excluir, baixe as informações que quiser manter.</p>

    <details style="margin-top:var(--space-3);">
        <summary class="btn btn-outline" style="cursor:pointer; display:inline-flex;">Excluir minha conta</summary>
        <form method="post" action="{{ route('profile.destroy') }}" style="margin-top:var(--space-3);">
            @csrf
            @method('delete')

            <label for="password">Confirme com a sua senha</label>
            <input id="password" name="password" type="password" placeholder="senha" required>
            @if ($errors->userDeletion->has('password'))
                <p class="erro">{{ $errors->userDeletion->first('password') }}</p>
            @endif

            <div class="acoes-login" style="justify-content:flex-start; gap:var(--space-3); margin-top:var(--space-3);">
                <button type="submit" class="btn btn-primary" style="background:#b91c1c;">Confirmar exclusão</button>
            </div>
        </form>
    </details>
</section>