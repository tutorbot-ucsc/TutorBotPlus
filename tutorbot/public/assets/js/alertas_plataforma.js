function submitCodigo() {
    Swal.fire({
        title: "¿Estás seguro de que quieres subir el código? Asegúrate de que todo esté correcto",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector('#codigo').value = editor.getValue();
            document.getElementById('evaluacion_form').submit();
        }
    });
}

function solicitarRetroalimentacion() {
    Swal.fire({
        title: "¿Estás seguro de que solicitar retroalimentación?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector('#codigo').value = editor.getValue();
            document.getElementById('evaluacion_form').submit();
        }
    });
}