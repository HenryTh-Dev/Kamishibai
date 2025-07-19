@extends('layouts.app')

@section('title', $category->name . ' - Kamishibai')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h1 class="h3 mb-1">
            <i class="bi bi-folder-fill text-primary me-2"></i>
            {{ $category->name }}
        </h1>
        <p class="text-muted mb-0">{{ $category->description ?: 'Sem descrição' }}</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-2"></i>
            Editar
        </a>
        <a href="{{ route('items.create', $category) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>
            Novo Item
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>
                    Itens de Verificação
                </h5>
                <span class="badge bg-primary">{{ $category->items->count() }} itens</span>
            </div>
            
            @if($category->items->count() > 0)
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="60">Ordem</th>
                                    <th>Descrição do Item</th>
                                    <th class="text-center" width="120">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->items->sortBy('order') as $item)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-dark">{{ $item->order ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-square text-success me-2"></i>
                                            {{ $item->description }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card-body text-center py-5">
                    <i class="bi bi-list-ul display-4 text-muted mb-3"></i>
                    <h5 class="text-muted mb-3">Nenhum item cadastrado</h5>
                    <p class="text-muted mb-4">
                        Adicione itens de verificação para esta categoria.
                    </p>
                    <a href="{{ route('items.create', $category) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Adicionar Primeiro Item
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informações da Categoria
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Nome:</small>
                    <div class="fw-bold">{{ $category->name }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Descrição:</small>
                    <div>{{ $category->description ?: 'Sem descrição' }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Total de Itens:</small>
                    <div class="fw-bold">{{ $category->items->count() }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Criado em:</small>
                    <div>{{ $category->created_at->format('d/m/Y H:i') }}</div>
                </div>
                
                @if($category->updated_at != $category->created_at)
                <div class="mb-3">
                    <small class="text-muted">Última atualização:</small>
                    <div>{{ $category->updated_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>
        </div>
        
        @if($category->items->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Ações Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('records.create', $category) }}" class="btn btn-success">
                        <i class="bi bi-plus-square me-2"></i>
                        Registrar Conformidade
                    </a>
                    <button class="btn btn-outline-primary" disabled>
                        <i class="bi bi-bar-chart me-2"></i>
                        Ver Relatórios
                        <small class="ms-2 text-muted">(Em breve)</small>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
