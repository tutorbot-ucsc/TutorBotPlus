<div class="modal fade" id="ejemplo_modal" tabindex="-1" role="dialog" aria-labelledby="ejemplo_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ejemplo_titulo">Ayuda sobre el Banco de Problemas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Se deben añadir problemas al banco de problemas de la evaluación que has creado, debes ingresar al menos un problema para que la evaluación pueda ser resuelto por un estudiante. <br>
                    <br>Los problemas que se pueden añadir a la evaluación son los que están asociados al curso que asignó para la evaluación, por lo tanto, debe ingresar problemas en la <a href="{{route("problemas.index")}}">Gestión de Problemas</a> los problemas, asociandolo al curso correspondiente de la evaluación. <br>
                    <br> <strong class="text-warning">Ojo: Puedes hacer uso de problemas que están ocultos, se mostrarán en la lista de problemas para su selección.</strong> <br>
                    <br>Los problemas están asociados a un puntaje, si no se ingresa un puntaje, entonces se le asiganara automaticamente 1 puntaje. La selección de problemas durante el desarrollo se basa en la cantidad de problemas asociados a cada puntaje. <br>
                    <br>Por ejemplo: el banco de problemas de la evaluación tiene 3 problemas de 10 puntos, 3 problemas de 20 puntos. Entonces, cuando un estudiante desarrolle la evaluación, el sistema escogera un problema de manera aleatorio en cada puntaje distintos (un problema de 10 puntos y un problema de 20 puntos), dando un total de 2 problemas para desarrollar en la evaluación.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
