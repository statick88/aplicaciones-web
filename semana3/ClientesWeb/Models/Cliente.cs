// =====================================================================
// Modelo de dominio: la entidad `Cliente`.
//
// Equivale a semana2/models/Cliente.php, pero con los 12 campos de la
// consigna ("mínimo 10 campos"). Es una clase POCO: sin dependencias de
// ASP.NET ni de MySQL, para que el controlador, los adaptadores de
// repositorio y las pruebas puedan usarla por igual.
// =====================================================================

namespace ClientesWeb.Models;

/// <summary>
/// Registro de la tabla <c>clientes</c>. Cada propiedad corresponde
/// uno a uno con una columna del esquema de <c>docker/initdb/01-esquema.sql</c>.
/// </summary>
public sealed class Cliente
{
    /// <summary>Clave primaria. 0 significa "todavía no está en la base".</summary>
    public int Id { get; set; }

    // --- 12 campos de datos (la consigna pide mínimo 10) ---
    public string Cedula { get; set; } = string.Empty;
    public string Nombre { get; set; } = string.Empty;
    public string Apellido { get; set; } = string.Empty;
    public string Correo { get; set; } = string.Empty;
    public string? Telefono { get; set; }
    public string? Direccion { get; set; }
    public string? Ciudad { get; set; }
    public DateTime? FechaNacimiento { get; set; }
    public string TipoCliente { get; set; } = TipoClientes.Natural;
    public decimal LimiteCredito { get; set; }
    public bool Activo { get; set; } = true;
    public string? Observaciones { get; set; }

    /// <summary>Nombre completo, usado en los mensajes de la interfaz.</summary>
    public string NombreCompleto => $"{Nombre} {Apellido}".Trim();
}

/// <summary>
/// Catálogo de tipos de cliente. Se mantiene en un solo lugar para que
/// la vista, la validación y el formulario usen exactamente los mismos
/// valores que se guardan en la columna <c>tipo_cliente</c>.
/// </summary>
public static class TipoClientes
{
    public const string Natural = "Natural";
    public const string Juridica = "Jurídica";

    /// <summary>Valores admitidos, en el orden en que se ofrecen en el formulario.</summary>
    public static readonly IReadOnlyList<string> Valores = new[] { Natural, Juridica };
}
