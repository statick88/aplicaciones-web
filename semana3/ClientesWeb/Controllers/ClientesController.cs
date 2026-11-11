// =====================================================================
// Controlador de clientes: los cuatro procedimientos del CRUD.
//
// Patrón PRG (Post/Redirect/Get), igual que en la Semana 2: después de
// una mutación exitosa se redirige al listado, de modo que recargar la
// página con F5 no repite el alta, la edición ni el borrado.
//
// El controlador NO conoce MySQL: solo habla con IRepositorioClientes.
// =====================================================================

using System.ComponentModel.DataAnnotations;
using ClientesWeb.Data;
using ClientesWeb.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.ModelBinding;

namespace ClientesWeb.Controllers;

public sealed class ClientesController : Controller
{
    private readonly IRepositorioClientes _repositorio;

    public ClientesController(IRepositorioClientes repositorio)
    {
        _repositorio = repositorio;
    }

    // ---------------------------------------------------------------
    // CONSULTAR — GET /
    // ---------------------------------------------------------------
    public async Task<IActionResult> Index(CancellationToken cancelacion)
    {
        // Se trae la lista completa; la vista la presenta en la tabla.
        var clientes = await _repositorio.ListarAsync(cancelacion);
        return View(clientes);
    }

    // ---------------------------------------------------------------
    // CREAR — GET /Clientes/Crear (formulario vacío)
    // ---------------------------------------------------------------
    [HttpGet]
    public IActionResult Crear()
    {
        // Formulario vacío con los valores por defecto del modelo
        // (tipo Natural, cliente activo, límite 0).
        return View("Form", new ClienteInput());
    }

    // ---------------------------------------------------------------
    // CREAR — POST /Clientes/Crear
    // ---------------------------------------------------------------
    [HttpPost]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> Crear(ClienteInput entrada, CancellationToken cancelacion)
    {
        // 1) Validar antes de tocar la base. Si algo falla se vuelve a
        //    pintar el formulario con los datos ya escritos y los
        //    mensajes en español.
        var errores = await ValidadorClientes.ValidarAsync(entrada, _repositorio, 0, cancelacion);
        if (!ModelState.IsValid || errores.Count > 0)
        {
            return DevolverFormulario(entrada, errores);
        }

        // 2) Persistir. El adaptador reasigna el id con AUTO_INCREMENT.
        await _repositorio.RegistrarAsync(entrada.AEntidad(), cancelacion);

        TempData["Mensaje"] = "Cliente registrado correctamente.";
        return RedirectToAction(nameof(Index));
    }

    // ---------------------------------------------------------------
    // ACTUALIZAR — GET /Clientes/Editar/{id}
    // ---------------------------------------------------------------
    [HttpGet]
    public async Task<IActionResult> Editar(int id, CancellationToken cancelacion)
    {
        if (id <= 0)
        {
            // Un id mal formado (abc, 0, -1) o inexistente cae aquí y
            // nunca selecciona otro cliente por accidente.
            TempData["Error"] = "El identificador del cliente no es válido.";
            return RedirectToAction(nameof(Index));
        }

        var cliente = await _repositorio.ConsultarAsync(id, cancelacion);
        if (cliente is null)
        {
            TempData["Error"] = "No se encontró el cliente a editar.";
            return RedirectToAction(nameof(Index));
        }

        return View("Form", ClienteInput.DesdeEntidad(cliente));
    }

    // ---------------------------------------------------------------
    // ACTUALIZAR — POST /Clientes/Editar/{id}
    // ---------------------------------------------------------------
    [HttpPost]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> Editar(int id, ClienteInput entrada, CancellationToken cancelacion)
    {
        if (id <= 0)
        {
            TempData["Error"] = "El identificador del cliente no es válido.";
            return RedirectToAction(nameof(Index));
        }

        // La ruta es la fuente de verdad de QUÉ registro se edita: se
        // sobreescribe el id que venga en el cuerpo del formulario.
        entrada.Id = id;

        var errores = await ValidadorClientes.ValidarAsync(entrada, _repositorio, id, cancelacion);
        if (!ModelState.IsValid || errores.Count > 0)
        {
            return DevolverFormulario(entrada, errores);
        }

        if (!await _repositorio.ActualizarAsync(entrada.AEntidad(), cancelacion))
        {
            TempData["Error"] = "No se encontró el cliente a actualizar.";
            return RedirectToAction(nameof(Index));
        }

        TempData["Mensaje"] = "Cliente actualizado correctamente.";
        return RedirectToAction(nameof(Index));
    }

    // ---------------------------------------------------------------
    // ELIMINAR — POST /Clientes/Eliminar/{id}
    // ---------------------------------------------------------------
    // Solo acepta POST: un GET no debe borrar nada, ni por un enlace
    // mal previsualizado ni por un rastreador que siga vínculos.
    [HttpPost]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> Eliminar(int id, CancellationToken cancelacion)
    {
        if (id <= 0 || !await _repositorio.EliminarAsync(id, cancelacion))
        {
            TempData["Error"] = "No se encontró el cliente a eliminar.";
            return RedirectToAction(nameof(Index));
        }

        TempData["Mensaje"] = "Cliente eliminado correctamente.";
        return RedirectToAction(nameof(Index));
    }

    // ---------- helpers ----------

    /// <summary>
    /// Reutiliza la vista Form para el alta y la edición. Cada
    /// resultado del validador se carga en la clave de su propiedad,
    /// de modo que `asp-validation-for` lo pinta exactamente junto al
    /// campo que originó el problema (y el resumen lo lista también).
    /// </summary>
    private IActionResult DevolverFormulario(ClienteInput entrada, IReadOnlyList<ValidationResult> errores)
    {
        EspanolizarErroresDeEnlace();

        foreach (var error in errores)
        {
            var miembros = error.MemberNames?.ToArray() ?? Array.Empty<string>();

            if (miembros.Length == 0)
            {
                // Error sin campo asociado → va al resumen general.
                ModelState.AddModelError(string.Empty, error.ErrorMessage ?? "Los datos enviados no son válidos.");
                continue;
            }

            foreach (var miembro in miembros)
            {
                ModelState.AddModelError(miembro, error.ErrorMessage ?? "Los datos enviados no son válidos.");
            }
        }

        return View("Form", entrada);
    }

    /// <summary>
    /// Los fallos de conversión de tipo (por ejemplo "abc" en el límite
    /// de crédito) los genera el enlazador de modelos con mensajes en
    /// inglés. Aquí se sustituyen por mensajes en español, para que la
    /// interfaz no mezcle idiomas según el tipo de error.
    /// </summary>
    private void EspanolizarErroresDeEnlace()
    {
        foreach (var clave in ModelState.Keys)
        {
            var estado = ModelState[clave];
            if (estado is null)
            {
                continue;
            }

            for (var indice = estado.Errors.Count - 1; indice >= 0; indice--)
            {
                var error = estado.Errors[indice];
                if (error.Exception is null &&
                    !error.ErrorMessage.StartsWith("The value '", StringComparison.Ordinal))
                {
                    continue; // Ya es un mensaje nuestro (DataAnnotations).
                }

                estado.Errors.RemoveAt(indice);
                estado.Errors.Add(new ModelError(MensajeDeEnlace(clave)));
            }
        }
    }

    /// <summary>Mensaje en español para cada campo con error de conversión.</summary>
    private static string MensajeDeEnlace(string clave) => clave switch
    {
        nameof(ClienteInput.LimiteCredito) =>
            "El límite de crédito debe ser un número, por ejemplo 1500.00.",
        nameof(ClienteInput.FechaNacimiento) =>
            "La fecha de nacimiento no tiene un formato válido (use el selector de fecha).",
        nameof(ClienteInput.Id) =>
            "El identificador del cliente no es válido.",
        "" =>
            "El formulario contiene datos con un formato no válido.",
        _ => "El valor enviado no tiene un formato válido.",
    };
}
