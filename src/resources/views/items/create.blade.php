@extends('layouts.app')

@section('content')
<h1>Novo Item em {{ $category->name }}</h1>
<form method="POST" action="{{ route('items.store', $category) }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Descrição</label>
        <input type="text" name="description" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Ordem</label>
        <input type="number" name="order" class="form-control">
    </div>
    <button class="btn btn-primary">Salvar</button>
</form>
@endsection
