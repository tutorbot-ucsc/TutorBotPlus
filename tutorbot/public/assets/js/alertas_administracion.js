function submitFormEliminar(item) {
    Swal.fire({
        title: "¿Estás seguro que quieres eliminar "+item+"?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('eliminarForm').submit();
        }
    });
}

function submitFormCrear() {
    Swal.fire({
        title: "¿Estás seguro de que los datos están correctos?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('crearForm').submit();
        }
    });
}

function submitFormEditar(item) {
    Swal.fire({
        title: "¿Estás seguro que quieres editar "+item+"?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('editarForm').submit();
        }
    });
}