<div class="row mx-3">
    <div class="col">
        <div class="mb-3">
            <label for="problema" class="form-label">Problema</label>
            <select class="form-select mb-3" id="problema" name="problema" required>
                <option value="">Seleccione un problema</option>
                @foreach($problemas as $problema)
                    <option value="{{$problema->id}}">{{$problema->nombre}}</option>
                @endforeach
              </select>
            @error('problema')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col">
        <div class="mb-3">
            <label for="puntaje" class="form-label">Puntos</label>
            <input type="number" class="form-control @error('puntaje') is-invalid @enderror" id="puntaje" name="puntaje" placeholder="Ej. 5" min="0">
            @error('puntaje')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
<div class="col d-flex justify-content-end">
    <button class="btn btn-primary mt-2" type="submit">Añadir</button>
    <button type="button" class="btn bg-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
        Ayuda
    </button>
    <a href="{{route('certamen.index')}}" class="btn bg-outline-primary mt-2 me-5">Volver</a>
</div>
</div>