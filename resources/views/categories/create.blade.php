@extends('layouts.app')

@section('title', 'Nova Categoria - Kamishibai')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nova Categoria
                </h1>
                <p class="text-muted mb-0">Crie uma nova categoria de pacote de cuidados</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="name" class="form-label">
                            <i class="bi bi-tag me-1"></i>
                            Nome da Categoria *
                        </label>
                        <input type="text" 
                               id="name"
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}"
                               placeholder="Ex: Adesão Mensal ao Pacote de Cuidados de CVD"
                               required>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            Digite um nome descritivo para a categoria de cuidados
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">
                            <i class="bi bi-text-paragraph me-1"></i>
                            Descrição
                        </label>
                        <textarea id="description"
                                  name="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="4"
                                  placeholder="Descreva os objetivos e características desta categoria de cuidados...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            Forneça uma descrição detalhada para ajudar na identificação da categoria
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Salvar Categoria
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Próximos Passos
                </h6>
                <p class="card-text text-muted mb-2">
                    Após criar a categoria, você poderá:
                </p>
                <ul class="text-muted small mb-0">
                    <li>Adicionar itens de verificação específicos</li>
                    <li>Configurar a ordem dos itens</li>
                    <li>Registrar conformidades diárias</li>
                    <li>Gerar relatórios de adesão</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
