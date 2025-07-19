@extends('layouts.app')

@section('content')
<h1>{{ $category->name }}</h1>
<p>{{ $category->description }}</p>
<a class="btn btn-secondary mb-3" href="{{ route('categories.edit', $category) }}">Editar</a>
<a class="btn btn-primary mb-3" href="{{ route('items.create', $category) }}">Novo Item</a>
<table class="table table-bordered">
    <tr><th>Descrição</th></tr>
    @foreach($category->items as $item)
    <tr><td>{{ $item->description }}</td></tr>
    @endforeach
</table>
<a class="btn btn-success" href="{{ route('records.create', $category) }}">Registrar Conformidade</a>
@endsection
