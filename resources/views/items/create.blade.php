@extends('layouts.app')

@section('title', 'Novo Item - ' . $category->name . ' - Kamishibai')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('categories.show', $category) }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-plus-circle me-2"></i>
                    Novo Item
                </h1>
                <p class="text-muted mb-0">
                    Adicionar item à categoria: <strong>{{ $category->name }}</strong>
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('items.store', $category) }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">
                            <i class="bi bi-check-square me-1"></i>
                            Descrição do Item *
                        </label>
                        <input type="text" 
                               id="description"
                               name="description" 
                               class="form-control @error('description') is-invalid @enderror" 
                               value="{{ old('description') }}"
                               placeholder="Ex: Verificar higienização das mãos antes do procedimento"
                               required>
                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            Descreva claramente o item de verificação que será avaliado
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="order" class="form-label">
                            <i class="bi bi-sort-numeric-up me-1"></i>
                            Ordem de Exibição
                        </label>
                        <input type="number" 
                               id="order"
                               name="order" 
                               class="form-control @error('order') is-invalid @enderror" 
                               value="{{ old('order', $category->items->count() + 1) }}"
                               min="1"
                               placeholder="1">
                        @error('order')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            Define a ordem em que este item aparecerá na lista (opcional)
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('categories.show', $category) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Salvar Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb me-2"></i>
                    Dicas para Criar Itens Eficazes
                </h6>
                <ul class="text-muted small mb-0">
                    <li>Use linguagem clara e objetiva</li>
                    <li>Seja específico sobre o que deve ser verificado</li>
                    <li>Evite itens muito genéricos ou ambíguos</li>
                    <li>Considere a ordem lógica dos procedimentos</li>
                    <li>Mantenha a descrição concisa mas completa</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
