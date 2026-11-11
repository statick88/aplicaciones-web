// =====================================================================
// Datos de prueba compartidos por todos los archivos de la suite.
// =====================================================================

using ClientesWeb.Models;

namespace ClientesWeb.Tests;

internal static class ClientesFalsos
{
    /// <summary>
    /// Devuelve un formulario con los 12 campos en un estado válido,
    /// para que cada test solo tenga que variar lo que está probando.
    /// </summary>
    public static ClienteInput Validos(
        string cedula = "0102030405",
        string correo = "ana.perez@example.com") => new()
    {
        Cedula = cedula,
        Nombre = "Ana",
        Apellido = "Pérez",
        Correo = correo,
        Telefono = "0987654321",
        Direccion = "Av. Universitaria 123",
        Ciudad = "Ambato",
        FechaNacimiento = new DateTime(1995, 4, 12),
        TipoCliente = TipoClientes.Natural,
        LimiteCredito = 1500.00m,
        Activo = true,
        Observaciones = "Alta generada por la suite de pruebas.",
    };

    /// <summary>Extrae los mensajes de una lista de resultados.</summary>
    public static IReadOnlyList<string> Mensajes(IReadOnlyList<System.ComponentModel.DataAnnotations.ValidationResult> resultados)
        => resultados
            .Select(resultado => resultado.ErrorMessage ?? string.Empty)
            .Where(texto => texto.Length > 0)
            .ToList();
}
