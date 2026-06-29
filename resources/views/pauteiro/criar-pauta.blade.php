@extends('layouts.app', ['title' => 'Cadastrar Pauta'])
@section('content')
<section class="form card">
    <h1>Cadastrar pauta</h1>
    <form method="post" action="{{ route('pauteiro.pautas.salvar') }}">
        @csrf
        <label>Título</label><input name="titulo" required>
        
        <label>Nicho</label>
        <div class="chips">
            @foreach($nichos as $nicho)
                @if($nicho !== 'Todos')
                <label class="chip">
                    <input type="radio" name="nicho" value="{{ $nicho }}" required>
                    <span>{{ $nicho }}</span>
                </label>
                @endif
            @endforeach
        </div>
        
        <label>Descrição</label><textarea name="descricao" required></textarea>
        <label>Cidade</label><input name="cidade" required>
        <label>Estado</label><input name="estado" maxlength="2" required>
        
        <label>Valor que irá cobrar</label>
        <input name="valor" placeholder="150" required oninput="calcularTaxaPreview(this.value)">
        
        <div id="taxa-preview" class="taxa-container" style="display:none">
            <p class="taxa-texto"></p>
        </div>
        
        <label><input type="checkbox" name="negociavel" checked style="width:auto"> Aceito negociar</label>
        
        <label>Arquivo</label>
        <input type="file" name="arquivo">
        
        <h3>Dados do pauteiro</h3>
        <label>Nome</label><input name="nome" required>
        <label>Telefone para contato</label><input name="telefone" required>
        <label>E-mail</label><input name="email" type="email" required>
        
        <div class="actions"><button class="btn primary" type="submit">Cadastrar pauta</button><a class="btn" href="{{ route('pauteiro.pautas') }}">Cancelar</a></div>
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
