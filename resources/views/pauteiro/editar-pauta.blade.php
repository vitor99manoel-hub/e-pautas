@extends('layouts.app', ['title' => 'Editar Pauta'])
@section('content')
<section class="form card">
    <h1>Editar pauta</h1>
    <form method="post" action="{{ route('pauteiro.pautas.atualizar', $pauta->id) }}">
        @csrf
        <label>Título</label><input name="titulo" value="{{ $pauta->titulo }}" required>
        
        <label>Nicho</label>
        <div class="chips">
            @foreach($nichos as $nicho)
                @if($nicho !== 'Todos')
                <label class="chip {{ $pauta->nicho === $nicho ? 'active' : '' }}">
                    <input type="radio" name="nicho" value="{{ $nicho }}" {{ $pauta->nicho === $nicho ? 'checked' : '' }} required>
                    <span>{{ $nicho }}</span>
                </label>
                @endif
            @endforeach
        </div>
        
        <label>Descrição</label><textarea name="descricao" required>{{ $pauta->descricao }}</textarea>
        <label>Cidade</label><input name="cidade" value="{{ $pauta->cidade }}" required>
        <label>Estado</label><input name="estado" value="{{ $pauta->estado }}" maxlength="2" required>
        
        <label>Valor que irá cobrar</label>
        <input name="valor" value="{{ $pauta->valor }}" required oninput="calcularTaxaPreview(this.value)">
        
        <div id="taxa-preview" class="taxa-container">
            <p class="taxa-texto"></p>
        </div>
        
        <label><input type="checkbox" name="negociavel" style="width:auto" {{ $pauta->negociavel ? 'checked' : '' }}> Aceito negociar</label>
        
        <label>Arquivo</label>
        <input type="file" name="arquivo">
        @if($pauta->arquivo)
        <p class="muted">Arquivo atual: {{ $pauta->arquivo }}</p>
        @endif
        
        <h3>Dados do pauteiro</h3>
        <label>Nome</label><input name="nome" value="{{ $pauta->nome }}" required>
        <label>Telefone para contato</label><input name="telefone" value="{{ $pauta->telefone }}" required>
        <label>E-mail</label><input name="email" type="email" value="{{ $pauta->email }}" required>
        
        <div class="actions"><button class="btn primary" type="submit">Atualizar pauta</button><a class="btn" href="{{ route('pauteiro.pautas') }}">Voltar</a></div>
    </form>
</section>

<script>
function calcularTaxaPreview(valor) {
    const preview = document.getElementById('taxa-preview');
    const texto = preview.querySelector('.taxa-texto');
    
    if (!valor) {
        preview.style.display = 'none';
        return;
    }
    
    const valorNumerico = parseFloat(valor.replace(',', '.'));
    if (isNaN(valorNumerico)) {
        preview.style.display = 'none';
        return;
    }
    
    let porcentagem = 0;
    if (valorNumerico <= 100) {
        porcentagem = 20;
    } else if (valorNumerico <= 300) {
        porcentagem = 15;
    } else if (valorNumerico <= 700) {
        porcentagem = 12;
    } else {
        porcentagem = 10;
    }
    
    const valorTaxa = valorNumerico * (porcentagem / 100);
    const valorFinal = valorNumerico + valorTaxa;
    
    texto.textContent = `Taxa E-Pautas: ${porcentagem}%. Valor final estimado para o comprador: R$ ${valorFinal.toFixed(2).replace('.', ',')}. Essa taxa garante segurança e intermediação da negociação.`;
    preview.style.display = 'block';
}

// Inicializar com valor atual
document.addEventListener('DOMContentLoaded', function() {
    const valorInput = document.querySelector('input[name="valor"]');
    if (valorInput && valorInput.value) {
        calcularTaxaPreview(valorInput.value);
    }
});

document.querySelectorAll('.chip input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.chip').forEach(chip => {
            chip.classList.remove('active');
        });
        if (this.checked) {
            this.closest('.chip').classList.add('active');
        }
    });
});
</script>

<style>
.chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.chip {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    background: #fee2e2;
    border-radius: 20px;
    cursor: pointer;
    border: 1px solid transparent;
}

.chip input {
    display: none;
}

.chip span {
    color: #b91c1c;
    font-weight: 600;
    font-size: 12px;
}

.chip.active {
    background: #d32f2f;
}

.chip.active span {
    color: #fff;
}

.taxa-container {
    background: #fef3c7;
    border: 1px solid #fca5a5;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 12px;
}

.taxa-texto {
    color: #92400e;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.5;
    margin: 0;
}
</style>
@endsection
