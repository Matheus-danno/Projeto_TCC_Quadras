<div class="grupo-avatares">
    @foreach ($sala->participantes->take(3) as $participante)
        <img src="{{ $participante->avatarUrl() }}" alt="{{ $participante->name }}">
    @endforeach
    @if ($sala->participantes->count() > 3)
        <div class="avatar-extra">+{{ $sala->participantes->count() - 3 }}</div>
    @endif
</div>
