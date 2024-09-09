<p class="text-uppercase text-sm">Configuración de la Large Language Model</p>
<div class="row d-flex justify-content-start align-items-center">
    <div class="col">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="habilitar_llm" name="habilitar_llm" value="{{true}}" @if(isset($problema) && $problema->habilitar_llm==true)checked @endif>
            <label class="form-check-label" for="habilitar_llm">Habilitar Large Language Model</label>
        </div>
    </div>
    <div class="col">
        <div class="form-group has-danger">
            <label for="limite_llm" class="form-control-label">Límite de uso de LLM</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control @error('limite_llm') is-invalid @enderror"
                    name="limite_llm" id="limite_llm" placeholder="Ej. 3" value="{{isset($problema)? old('limite_llm', $problema->limite_llm):old('limite_llm')}}">
                <span class="input-group-text" id="basic-addon1">Usos</span>
            </div>
            @error('limite_llm')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
