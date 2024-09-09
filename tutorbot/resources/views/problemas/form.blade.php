<p class="text-uppercase text-sm">Información del Problema</p>
<div class="row">
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="example-text-input"
                class="form-control-label @error('nombre') is-invalid @enderror">Nombre</label>
            <input class="form-control" type="text" name="nombre" placeholder="Ej. Sumar A y B"
                value="{{ isset($problema) ? old('nombre', $problema->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="example-text-input"
                class="form-control-label @error('codigo') is-invalid @enderror">Código</label>
            <input class="form-control" type="text" name="codigo" placeholder="Ej. suma-a-b"
                value="{{ isset($problema) ? old('codigo', $problema->nombre) : old('codigo') }}">
            @error('codigo')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="fecha_inicio" class="form-control-label @error('fecha_inicio') is-invalid @enderror">Fecha de
                Inicio</label>
            <p class="opacity-25"><small>Indica la disponibilidad del problema, en caso de no ingresar estara disponible
                    inmediatamente.</small></p>
            <input class="form-control" type="date" name="fecha_inicio" id="fecha_inicio"
                value="{{ isset($problema) ? old('fecha_inicio', $problema->fecha_inicio) : old('fecha_inicio') }}">
            @error('fecha_inicio')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="fecha_termino" class="form-control-label">Fecha de Termino</label>
            <p class="opacity-25"><small>Indica hasta que fecha estara disponible el problema, en caso de no ingresar
                    estara disponible hasta que el usuario desee eliminarlo o ocultarlo.</small></p>
            <input class="form-control @error('fecha_termino') is-invalid @enderror" type="date" name="fecha_termino"
                id="fecha_termino"
                value="{{ isset($problema) ? old('fecha_termino', $problema->fecha_termino) : old('fecha_termino') }}">
            @error('fecha_termino')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="memoria_limite" class="form-control-label">Memoria Limite</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control  @error('memoria_limite') is-invalid @enderror"
                    name="memoria_limite" id="memoria_limite" placeholder="Ej. 5000 (5MB)">
                <span class="input-group-text" id="basic-addon1">KB</span>
            </div>
            @error('memoria_limite')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="tiempo_limite" class="form-control-label">Tiempo Limite</label>
            <div class="input-group mb-3">

                <input type="text" class="form-control @error('tiempo_limite') is-invalid @enderror"
                    name="tiempo_limite" id="tiempo_limite" placeholder="Ej. 5">
                <span class="input-group-text" id="basic-addon1">Segundos</span>
            </div>
            @error('tiempo_limite')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<p class="text-uppercase text-sm">Enunciado</p>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label for="body_problema">Enunciado del Problema</label>
            <textarea class="form-control @error('body_problema_resumido') is-invalid @enderror" id="body_problema"
                name="body_problema" rows="8"
                placeholder="Ej. Dado dos números A y B, desarrolle un código que entregue como resultado la suma de A y B.">{{ isset($problema) ? old('body_problema', $problema->body_problema) : old('body_problema') }}</textarea>
        </div>
        @error('body_problema')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label for="body_problema_resumido">Enunciado del Problema Resumido</label>
            <p class="opacity-25"><small>Esté es un resumen del problema, que se utilizara para la Large Languge Model
                    para la retroalimentación.</small></p>
            <textarea class="form-control @error('body_problema_resumido') is-invalid @enderror" id="body_problema_resumido"
                name="body_problema_resumido" rows="8" placeholder="Ej. El código debe entregar una suma de dos numeros">{{ isset($problema) ? old('body_problema_resumido', $problema->body_problema_resumido) : old('body_problema_resumido') }}</textarea>
        </div>
        @error('body_problema_resumido')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group">
        <label for="cursos">Cursos</label>
        <label for="cursos">(Mantenga pulsado CTRL o CMD para seleccionar más de un curso)</label>
        <select multiple class="form-control" id="cursos" name="cursos[]">
            @foreach ($cursos as $curso)
                <option value="{{ $curso->id }}" @if (isset($problema) && $problema->cursos()->get()->contains($curso)) selected @endif>
                    {{ $curso->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="form-group">
        <label for="categorias">Categorías</label>
        <label for="categorias">(Mantenga pulsado CTRL o CMD para seleccionar más de un curso)</label>
        <select multiple class="form-control" id="categorias" name="categorias[]">
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @if (isset($problema) && $problema->categorias()->get()->contains($categoria)) selected @endif>
                    {{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="form-group">
        <label for="lenguajes">Lenguajes de Programación</label>
        <label for="lenguajes">(Mantenga pulsado CTRL o CMD para seleccionar más de un curso)</label>
        <select multiple class="form-control" id="lenguajes" name="lenguajes[]">
            @foreach ($lenguajes as $lenguaje)
                <option value="{{ $lenguaje->id }}" @if (isset($problema) && $problema->lenguajes()->get()->contains($lenguaje)) selected @endif>
                    {{ $lenguaje->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>
<p class="text-uppercase text-sm">Configuración de la Large Language Model</p>
<div class="row d-flex justify-content-start align-items-center">
    <div class="col">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="habilitar_llm" name="habilitar_llm" value="true">
            <label class="form-check-label" for="habilitar_llm">Habilitar Large Language Model</label>
        </div>
    </div>
    <div class="col">
        <div class="form-group has-danger">
            <label for="limite_llm" class="form-control-label">Límite de uso de LLM</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control @error('limite_llm') is-invalid @enderror"
                    name="limite_llm" id="limite_llm" placeholder="Ej. 3">
                <span class="input-group-text" id="basic-addon1">Usos</span>
            </div>
            @error('limite_llm')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <p class="text-uppercase text-sm">Base de Datos (Opcional)</p>
    <p class="text-sm">En caso de utilizar SQL como lenguaje de programación, suba el archivo de la base de datos con la que se utilizara para realizar las consultas.</p>
    <div class="mb-3">
        <input class="form-control form-control-sm" id="archivos_adicionales" type="file">
        @error('archivos_adicionales')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
         @enderror
    </div>
</div>
