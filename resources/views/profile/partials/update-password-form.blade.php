<section>
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <label for="update_password_current_password">Senha atual</label>
        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password">
        @if ($errors->updatePassword->has('current_password'))
            <p class="erro">{{ $errors->updatePassword->first('current_password') }}</p>
        @endif

        <label for="update_password_password">Nova senha</label>
        <input id="update_password_password" name="password" type="password" autocomplete="new-password">
        @if ($errors->updatePassword->has('password'))
            <p class="erro">{{ $errors->updatePassword->first('password') }}</p>
        @endif

        <label for="update_password_password_confirmation">Confirmar nova senha</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
        @if ($errors->updatePassword->has('password_confirmation'))
            <p class="erro">{{ $errors->updatePassword->first('password_confirmation') }}</p>
        @endif

        <div class="acoes-login" style="justify-content:flex-start; gap:var(--space-3); margin-top:var(--space-4);">
            <button type="submit" class="btn btn-primary">Alterar senha</button>
            @if (session('status') === 'password-updated')
                <span class="muted">Senha alterada.</span>
            @endif
        </div>
    </form>
</section>