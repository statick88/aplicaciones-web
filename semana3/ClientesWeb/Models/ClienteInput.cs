// =====================================================================
// Modelo de entrada del formulario (view model).
//
// Se separa de `Cliente` a propósito:
//   * `Cliente` refleja la tabla y no debe arrastrar atributos de UI;
//   * `ClienteInput` refleja lo que el usuario teclea y ahí sí viven
//     las reglas de validación (DataAnnotations), que son las que
//     alimentan los mensajes en español del formulario.
//
// El controlador traduce de uno a otro; ninguna capa mezcla ambos.
//
// Nota: aquí solo van atributos de cultura independiente (requerido,
// longitud, formato de correo, patrón de texto). Los rangos numéricos
// y de fecha se evalúan de forma explícita en `ValidadorClientes`,
// donde el mensaje puede explicar el motivo en lugar de devolver el
// genérico de RangeAttribute.
// =====================================================================

using System.ComponentModel.DataAnnotations;

namespace ClientesWeb.Models;

public sealed class ClienteInput
{
    /// <summary>0 en el alta; identificador real al editar.</summary>
    public int Id { get; set; }

    // ---------- 12 campos de datos ----------

    [Display(Name = "Cédula")]
    [Required(ErrorMessage = "La cédula es obligatoria.")]
    [RegularExpression(@"^\d{6,15}$", ErrorMessage = "La cédula debe contener entre 6 y 15 dígitos.")]
    [StringLength(15, ErrorMessage = "La cédula no puede superar los 15 caracteres.")]
    public string Cedula { get; set; } = string.Empty;

    [Display(Name = "Nombre")]
    [Required(ErrorMessage = "El nombre es obligatorio.")]
    [StringLength(100, MinimumLength = 2, ErrorMessage = "El nombre debe tener entre 2 y 100 caracteres.")]
    public string Nombre { get; set; } = string.Empty;

    [Display(Name = "Apellido")]
    [Required(ErrorMessage = "El apellido es obligatorio.")]
    [StringLength(100, MinimumLength = 2, ErrorMessage = "El apellido debe tener entre 2 y 100 caracteres.")]
    public string Apellido { get; set; } = string.Empty;

    [Display(Name = "Correo electrónico")]
    [Required(ErrorMessage = "El correo electrónico es obligatorio.")]
    [EmailAddress(ErrorMessage = "El correo electrónico no es válido.")]
    [StringLength(150, ErrorMessage = "El correo no puede superar los 150 caracteres.")]
    public string Correo { get; set; } = string.Empty;

    [Display(Name = "Teléfono")]
    [RegularExpression(@"^\+?[0-9 ()\-]{6,20}$", ErrorMessage = "El teléfono debe contener de 6 a 20 caracteres (se admiten dígitos, espacios, guiones y el signo +).")]
    [StringLength(30, ErrorMessage = "El teléfono no puede superar los 30 caracteres.")]
    public string? Telefono { get; set; }

    [Display(Name = "Dirección")]
    [StringLength(200, ErrorMessage = "La dirección no puede superar los 200 caracteres.")]
    public string? Direccion { get; set; }

    [Display(Name = "Ciudad")]
    [StringLength(80, ErrorMessage = "La ciudad no puede superar los 80 caracteres.")]
    public string? Ciudad { get; set; }

    // `[DataType(Date)]` + el formato en modo edición hacen que el tag
    // helper genere <input type="date" value="aaaa-mm-dd">, que es el
    // formato que el modelo acepta al volver del navegador.
    [Display(Name = "Fecha de nacimiento")]
    [DataType(DataType.Date)]
    [DisplayFormat(DataFormatString = "{0:yyyy-MM-dd}", ApplyFormatInEditMode = true)]
    public DateTime? FechaNacimiento { get; set; }

    [Display(Name = "Tipo de cliente")]
    [Required(ErrorMessage = "El tipo de cliente es obligatorio.")]
    [RegularExpression("^(Natural|Jurídica)$", ErrorMessage = "El tipo de cliente debe ser Natural o Jurídica.")]
    public string TipoCliente { get; set; } = TipoClientes.Natural;

    [Display(Name = "Límite de crédito")]
    public decimal LimiteCredito { get; set; }

    [Display(Name = "Cliente activo")]
    public bool Activo { get; set; } = true;

    [Display(Name = "Observaciones")]
    [StringLength(500, ErrorMessage = "Las observaciones no pueden superar los 500 caracteres.")]
    public string? Observaciones { get; set; }

    // ---------- Traducción entre el modelo de entrada y la entidad ----------

    /// <summary>Construye la entidad que se envía al repositorio.</summary>
    public Cliente AEntidad() => new()
    {
        Id = Id,
        Cedula = Requerido(Cedula),
        Nombre = Requerido(Nombre),
        Apellido = Requerido(Apellido),
        Correo = Requerido(Correo),
        Telefono = Opcional(Telefono),
        Direccion = Opcional(Direccion),
        Ciudad = Opcional(Ciudad),
        FechaNacimiento = FechaNacimiento,
        TipoCliente = TipoCliente,
        LimiteCredito = LimiteCredito,
        Activo = Activo,
        Observaciones = Opcional(Observaciones),
    };

    /// <summary>Carga una entidad existente para rellenar el formulario de edición.</summary>
    public static ClienteInput DesdeEntidad(Cliente cliente) => new()
    {
        Id = cliente.Id,
        Cedula = cliente.Cedula,
        Nombre = cliente.Nombre,
        Apellido = cliente.Apellido,
        Correo = cliente.Correo,
        Telefono = cliente.Telefono,
        Direccion = cliente.Direccion,
        Ciudad = cliente.Ciudad,
        FechaNacimiento = cliente.FechaNacimiento,
        TipoCliente = cliente.TipoCliente,
        LimiteCredito = cliente.LimiteCredito,
        Activo = cliente.Activo,
        Observaciones = cliente.Observaciones,
    };

    /// <summary>Recorta un campo obligatorio (garantizado por [Required]).</summary>
    private static string Requerido(string? valor) => valor?.Trim() ?? string.Empty;

    /// <summary>
    /// Recorta un campo opcional: en la base las columnas opcionales
    /// son NULL, no cadena vacía.
    /// </summary>
    private static string? Opcional(string? valor)
    {
        var recortado = valor?.Trim();
        return string.IsNullOrEmpty(recortado) ? null : recortado;
    }
}
