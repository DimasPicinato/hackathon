<section>
    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <label for="name">Nome</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        @error('name') <p class="erro">{{ $message }}</p> @enderror

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
        @error('email') <p class="erro">{{ $message }}</p> @enderror

        <div class="acoes-login" style="justify-content:flex-start; gap:var(--space-3); margin-top:var(--space-4);">
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
            @if (session('status') === 'profile-updated')
                <span class="muted">Salvo.</span>
            @endif
        </div>
    </form>
</section>