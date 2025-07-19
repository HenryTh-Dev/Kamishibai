@extends('layouts.app')

@section('content')
<h1>Editar Categoria</h1>
<form method="POST" action="{{ route('categories.update', $category) }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Descrição</label>
        <textarea name="description" class="form-control">{{ old('description', $category->description) }}</textarea>
    </div>
    <button class="btn btn-primary">Salvar</button>
</form>
<form method="POST" action="{{ route('categories.destroy', $category) }}" class="mt-3">
    @csrf
    @method('DELETE')
    <button class="btn btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
</form>
@endsection
