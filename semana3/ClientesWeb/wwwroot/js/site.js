// =====================================================================
// Mejoras progresivas de la interfaz.
//
// Todo lo esencial funciona sin JavaScript (el CRUD es HTML + POST):
// este archivo solo añade comodidad, y si no carga nada se pierde
// únicamente la confirmación del borrado.
// =====================================================================

document.addEventListener('submit', function (evento) {
    var formulario = evento.target;
    var mensaje = formulario.getAttribute('data-confirm');

    // Solo los formularios que declaren data-confirm preguntan antes
    // de enviarse. Los demás (alta y edición) se envían directo.
    if (!mensaje) {
        return;
    }

    // window.confirm bloquea hasta que la persona responda; si cancela,
    // se impide el envío y no se realiza ninguna petición POST.
    if (!window.confirm(mensaje)) {
        evento.preventDefault();
    }
});
