@extends('layouts.app')

@section('title', 'Categorias - Kamishibai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-folder me-2"></i>
            Categorias de Cuidados
        </h1>
        <p class="text-muted mb-0">Gerencie as categorias de pacotes de cuidados</p>
    </div>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Categoria
    </a>
</div>

@if($categories->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Nome da Categoria</th>
                            <th>Descrição</th>
                            <th>Itens</th>
                            <th class="text-center" width="200">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-folder-fill text-primary me-2"></i>
                                    <strong>{{ $category->name }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">
                                    {{ Str::limit($category->description, 80) ?: 'Sem descrição' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $category->items->count() }} itens
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('categories.show', $category) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('categories.edit', $category) }}" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            title="Excluir"
                                            onclick="confirmDelete('{{ $category->name }}', '{{ route('categories.destroy', $category) }}')">
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
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-folder-x display-1 text-muted mb-3"></i>
            <h4 class="text-muted mb-3">Nenhuma categoria encontrada</h4>
            <p class="text-muted mb-4">
                Comece criando sua primeira categoria de pacote de cuidados.
            </p>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Criar Primeira Categoria
            </a>
        </div>
    </div>
@endif

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a categoria <strong id="categoryName"></strong>?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(categoryName, deleteUrl) {
    document.getElementById('categoryName').textContent = categoryName;
    document.getElementById('deleteForm').action = deleteUrl;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
