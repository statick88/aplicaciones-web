// =====================================================================
// Modelo de datos de la página de error (heredado de la plantilla).
// =====================================================================

namespace ClientesWeb.Models;

public sealed class ErrorViewModel
{
    /// <summary>Identificador de la solicitud, para correlacionar con los logs.</summary>
    public string? RequestId { get; set; }

    /// <summary>Solo se muestra si hay un id real que reportar.</summary>
    public bool MostrarRequestId => !string.IsNullOrEmpty(RequestId);

    /// <summary>Mensaje amigable; la traza técnica nunca llega a la vista.</summary>
    public string Mensaje { get; set; } = "Ocurrió un error inesperado al procesar la solicitud.";
}
