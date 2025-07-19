@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>Categorias</h1>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">Nova Categoria</a>
</div>
<table class="table table-bordered">
    <tr><th>Nome</th><th>Ações</th></tr>
    @foreach($categories as $category)
    <tr>
        <td>{{ $category->name }}</td>
        <td><a href="{{ route('categories.show', $category) }}" class="btn btn-sm btn-secondary">Ver</a></td>
    </tr>
    @endforeach
</table>
@endsection
