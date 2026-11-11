// =====================================================================
// Validación de los datos del formulario ANTES de tocar el repositorio.
//
// Equivale a semana2/models/ValidadorClientes.php. Se divide en dos
// capas para que cada regla viva en un solo sitio:
//
//   1. `Validar(ClienteInput)`  → ejecuta los DataAnnotations del
//      modelo (requeridos, longitudes, correo, patrones) y añade las
//      reglas de rango que allí quedarían opacas.
//   2. `ValidarAsync(entrada, repositorio, ...)` → añade las reglas
//      que necesitan consultar datos: unicidad de cédula y correo.
//
// Devuelve objetos `ValidationResult` y no cadenas sueltas: cada
// resultado lleva los `MemberNames` (el nombre de la propiedad), de
// modo que el controlador puede cargar el error en la clave exacta y
// la vista lo pinta junto al campo que lo originó. Devuelve siempre la
// lista completa, nunca solo el primer error.
// =====================================================================

using System.ComponentModel.DataAnnotations;

namespace ClientesWeb.Models;

public static class ValidadorClientes
{
    /// <summary>Fecha mínima admitida para fecha de nacimiento.</summary>
    public static readonly DateTime FechaMinima = new(1900, 1, 1);

    /// <summary>Límite superior del campo limite_credito (DECIMAL(12,2)).</summary>
    public const decimal LimiteCreditoMaximo = 9999999999.99m;

    /// <summary>
    /// Reglas que no dependen de la base de datos.
    /// </summary>
    public static IReadOnlyList<ValidationResult> Validar(ClienteInput entrada)
    {
        var errores = new List<ValidationResult>();

        // --- 1) Atributos declarados en ClienteInput ---
        var contexto = new ValidationContext(entrada);
        var resultados = new List<ValidationResult>();
        Validator.TryValidateObject(entrada, contexto, resultados, validateAllProperties: true);

        foreach (var resultado in resultados)
        {
            if (!string.IsNullOrWhiteSpace(resultado.ErrorMessage))
            {
                errores.Add(resultado);
            }
        }

        // Si los atributos ya rechazaron el formulario, no tiene sentido
        // evaluar rangos sobre un valor que ni siquiera es interpretable:
        // se evita enseñar dos mensajes contradictorios para el mismo dato.
        if (errores.Count > 0)
        {
            return errores;
        }

        // --- 2) Reglas de rango con mensaje explícito ---

        if (entrada.FechaNacimiento is { } fecha)
        {
            if (fecha.Date < FechaMinima)
            {
                errores.Add(new ValidationResult(
                    $"La fecha de nacimiento no puede ser anterior a {FechaMinima:yyyy-MM-dd}.",
                    new[] { nameof(ClienteInput.FechaNacimiento) }));
            }
            else if (fecha.Date > DateTime.Today)
            {
                errores.Add(new ValidationResult(
                    "La fecha de nacimiento no puede ser futura.",
                    new[] { nameof(ClienteInput.FechaNacimiento) }));
            }
        }

        if (entrada.LimiteCredito < 0)
        {
            errores.Add(new ValidationResult(
                "El límite de crédito no puede ser negativo.",
                new[] { nameof(ClienteInput.LimiteCredito) }));
        }
        else if (entrada.LimiteCredito > LimiteCreditoMaximo)
        {
            errores.Add(new ValidationResult(
                $"El límite de crédito no puede superar {LimiteCreditoMaximo:N2}.",
                new[] { nameof(ClienteInput.LimiteCredito) }));
        }

        return errores;
    }

    /// <summary>
    /// Reglas de campo + reglas que dependen de los datos ya guardados
    /// (cédula y correo únicos). <paramref name="idActual"/> es 0 en el
    /// alta y el propio id al editar, para no chocar consigo mismo.
    /// </summary>
    public static async Task<IReadOnlyList<ValidationResult>> ValidarAsync(
        ClienteInput entrada,
        Data.IRepositorioClientes repositorio,
        int idActual = 0,
        CancellationToken cancelacion = default)
    {
        var errores = Validar(entrada).ToList();

        // Solo tiene sentido consultar unicidad si la cédula/correo ya
        // pasaron el formato: una cédula mal formada no puede estar
        // "duplicada" de forma significativa.
        if (errores.Count == 0)
        {
            if (await repositorio.ExisteCedulaAsync(entrada.Cedula.Trim(), idActual, cancelacion))
            {
                errores.Add(new ValidationResult(
                    "Ya existe un cliente con esa cédula.",
                    new[] { nameof(ClienteInput.Cedula) }));
            }

            if (await repositorio.ExisteCorreoAsync(entrada.Correo.Trim(), idActual, cancelacion))
            {
                errores.Add(new ValidationResult(
                    "Ya existe un cliente con ese correo electrónico.",
                    new[] { nameof(ClienteInput.Correo) }));
            }
        }

        return errores;
    }
}
