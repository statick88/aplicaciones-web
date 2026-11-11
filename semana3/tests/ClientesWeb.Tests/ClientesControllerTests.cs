// =====================================================================
// Pruebas del controlador: comprueban que las cuatro acciones del CRUD
// orquestan correctamente el validador, el repositorio y la redirección
// PRG, sin necesidad de HTTP ni de MySQL.
// =====================================================================

using ClientesWeb.Controllers;
using ClientesWeb.Data;
using ClientesWeb.Models;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Abstractions;
using Microsoft.AspNetCore.Mvc.Controllers;
using Microsoft.AspNetCore.Mvc.ModelBinding;
using Microsoft.AspNetCore.Mvc.ViewFeatures;
using Microsoft.AspNetCore.Routing;
using Microsoft.Extensions.DependencyInjection;
using Xunit;

namespace ClientesWeb.Tests;

public sealed class ClientesControllerTests
{
    // ---------- CONSULTAR ----------

    [Fact]
    public async Task Index_devuelve_el_listado_completo()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0102030405").AEntidad());
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0912345678").AEntidad());
        var controlador = CrearControlador(repositorio);

        var resultado = await controlador.Index(default);

        var vista = Assert.IsType<ViewResult>(resultado);
        var lista = Assert.IsAssignableFrom<IReadOnlyList<Cliente>>(vista.Model);
        Assert.Equal(2, lista.Count);
    }

    // ---------- CREAR ----------

    [Fact]
    public async Task Crear_con_datos_invalidos_redevuelve_el_formulario_y_no_registra_nada()
    {
        var repositorio = new RepositorioClientesMemoria();
        var controlador = CrearControlador(repositorio);

        var entrada = ClientesFalsos.Validos(correo: "correo-invalido");
        var resultado = await controlador.Crear(entrada, default);

        var vista = Assert.IsType<ViewResult>(resultado);
        Assert.Equal("Form", vista.ViewName);
        Assert.Same(entrada, vista.Model); // los datos escritos no se pierden
        Assert.False(controlador.ModelState.IsValid);
        Assert.Contains(
            "El correo electrónico no es válido.",
            ClientesFalsos.Mensajes(ResultadosDe(controlador)));
        Assert.Empty(await repositorio.ListarAsync());
    }

    [Fact]
    public async Task Crear_con_errores_de_enlace_no_registra_y_muestra_mensajes_en_espanol()
    {
        var repositorio = new RepositorioClientesMemoria();
        var controlador = CrearControlador(repositorio);
        AgregarErrorDelEnlazador(controlador, nameof(ClienteInput.LimiteCredito), "no-numérico");
        AgregarErrorDelEnlazador(controlador, nameof(ClienteInput.FechaNacimiento), "fecha-inválida");

        var resultado = await controlador.Crear(ClientesFalsos.Validos(), default);

        Assert.IsType<ViewResult>(resultado);
        Assert.Contains("El límite de crédito debe ser un número, por ejemplo 1500.00.",
            ClientesFalsos.Mensajes(ResultadosDe(controlador)));
        Assert.Contains("La fecha de nacimiento no tiene un formato válido (use el selector de fecha).",
            ClientesFalsos.Mensajes(ResultadosDe(controlador)));
        Assert.Empty(await repositorio.ListarAsync());
    }

    [Fact]
    public async Task Crear_con_datos_validos_registra_y_redirige_al_listado()
    {
        var repositorio = new RepositorioClientesMemoria();
        var controlador = CrearControlador(repositorio);

        var resultado = await controlador.Crear(ClientesFalsos.Validos(), default);

        var redireccion = Assert.IsType<RedirectToActionResult>(resultado);
        Assert.Equal(nameof(ClientesController.Index), redireccion.ActionName);
        Assert.Single(await repositorio.ListarAsync());
        Assert.Equal("Cliente registrado correctamente.", controlador.TempData["Mensaje"]);
    }

    [Fact]
    public async Task Crear_con_una_cedula_ya_registrada_no_duplica_el_registro()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0102030405").AEntidad());
        var controlador = CrearControlador(repositorio);

        var repetida = ClientesFalsos.Validos(cedula: "0102030405", correo: "otro@correo.com");
        var resultado = await controlador.Crear(repetida, default);

        Assert.IsType<ViewResult>(resultado);
        Assert.Single(await repositorio.ListarAsync()); // sigue habiendo uno solo
    }

    // ---------- ACTUALIZAR ----------

    [Fact]
    public async Task Editar_con_un_id_mal_formado_no_modifica_nada()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());
        var controlador = CrearControlador(repositorio);

        // Enruta como llega un "/Clientes/Editar/abc" o "/Clientes/Editar/0".
        var resultado = await controlador.Editar(0, ClientesFalsos.Validos(), default);

        var redireccion = Assert.IsType<RedirectToActionResult>(resultado);
        Assert.Equal(nameof(ClientesController.Index), redireccion.ActionName);
        Assert.Equal("El identificador del cliente no es válido.", controlador.TempData["Error"]);

        var original = await repositorio.ConsultarAsync(1);
        Assert.Equal("Ana", original!.Nombre); // intacto
    }

    [Fact]
    public async Task Editar_con_id_inexistente_reporta_que_no_hay_coincidencia()
    {
        var repositorio = new RepositorioClientesMemoria();
        var controlador = CrearControlador(repositorio);

        var resultado = await controlador.Editar(999, ClientesFalsos.Validos(), default);

        Assert.IsType<RedirectToActionResult>(resultado);
        Assert.Equal("No se encontró el cliente a actualizar.", controlador.TempData["Error"]);
    }

    [Fact]
    public async Task Editar_con_error_de_enlace_no_actualiza_el_registro()
    {
        var repositorio = new RepositorioClientesMemoria();
        var id = await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());
        var controlador = CrearControlador(repositorio);
        AgregarErrorDelEnlazador(controlador, nameof(ClienteInput.LimiteCredito), "no-numérico");
        AgregarErrorDelEnlazador(controlador, nameof(ClienteInput.FechaNacimiento), "fecha-inválida");
        var entrada = ClientesFalsos.Validos();
        entrada.Nombre = "Cambio rechazado";

        var resultado = await controlador.Editar(id, entrada, default);

        Assert.IsType<ViewResult>(resultado);
        Assert.Contains("El límite de crédito debe ser un número, por ejemplo 1500.00.",
            ClientesFalsos.Mensajes(ResultadosDe(controlador)));
        Assert.Contains("La fecha de nacimiento no tiene un formato válido (use el selector de fecha).",
            ClientesFalsos.Mensajes(ResultadosDe(controlador)));
        Assert.Equal("Ana", (await repositorio.ConsultarAsync(id))!.Nombre);
    }

    [Fact]
    public async Task Editar_con_datos_validos_actualiza_y_redirige()
    {
        var repositorio = new RepositorioClientesMemoria();
        var id = await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());
        var controlador = CrearControlador(repositorio);

        var cambiado = ClientesFalsos.Validos(cedula: "0998877665", correo: "cambiado@correo.com");
        cambiado.Nombre = "Luis";
        var resultado = await controlador.Editar(id, cambiado, default);

        var redireccion = Assert.IsType<RedirectToActionResult>(resultado);
        Assert.Equal(nameof(ClientesController.Index), redireccion.ActionName);
        Assert.Equal("Cliente actualizado correctamente.", controlador.TempData["Mensaje"]);

        var guardado = await repositorio.ConsultarAsync(id);
        Assert.Equal("Luis", guardado!.Nombre);
        Assert.Equal("cambiado@correo.com", guardado.Correo);
    }

    [Fact]
    public async Task Editar_no_permite_que_el_cuerpo_del_formulario_cambie_de_registro()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0102030405").AEntidad());
        var id2 = await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0912345678").AEntidad());
        var controlador = CrearControlador(repositorio);

        // La ruta dice id=2 pero el cuerpo intenta declarar Id=1: la
        // ruta manda, o se podría sobrescribir el registro equivocado.
        var entrante = ClientesFalsos.Validos(cedula: "0912345678", correo: "segundo@correo.com");
        entrante.Id = 1;
        entrante.Nombre = "Sobrescrito";

        await controlador.Editar(id2, entrante, default);

        Assert.Equal("Ana", (await repositorio.ConsultarAsync(1))!.Nombre);
        Assert.Equal("Sobrescrito", (await repositorio.ConsultarAsync(id2))!.Nombre);
    }

    // ---------- ELIMINAR ----------

    [Fact]
    public async Task Eliminar_borra_el_cliente_y_redirige_al_listado()
    {
        var repositorio = new RepositorioClientesMemoria();
        var id = await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());
        var controlador = CrearControlador(repositorio);

        var resultado = await controlador.Eliminar(id, default);

        var redireccion = Assert.IsType<RedirectToActionResult>(resultado);
        Assert.Equal(nameof(ClientesController.Index), redireccion.ActionName);
        Assert.Empty(await repositorio.ListarAsync());
        Assert.Equal("Cliente eliminado correctamente.", controlador.TempData["Mensaje"]);
    }

    [Fact]
    public async Task Eliminar_un_id_inexistente_no_se_come_un_error_500()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());
        var controlador = CrearControlador(repositorio);

        var resultado = await controlador.Eliminar(777, default);

        Assert.IsType<RedirectToActionResult>(resultado);
        Assert.Equal("No se encontró el cliente a eliminar.", controlador.TempData["Error"]);
        Assert.Single(await repositorio.ListarAsync()); // no borra nada
    }

    // ---------- helpers ----------

    /// <summary>
    /// Monta el controlador con un HttpContext mínimo y un proveedor de
    /// TempData falso: suficiente para leer y escribir los mensajes de
    /// la redirección PRG sin montar todo el pipeline de MVC.
    /// </summary>
    private static ClientesController CrearControlador(IRepositorioClientes repositorio)
    {
        // Se registra el conjunto completo de servicios MVC (UrlHelper,
        // TempData, metadatos de modelo, ...) para que el controlador
        // funcione igual que dentro del contenedor, sin montar el
        // pipeline HTTP completo. El proveedor de TempData se sustituye
        // por uno falso: no hace falta cifrar cookies en una prueba.
        var servicios = new ServiceCollection();
        servicios.AddLogging();
        servicios.AddControllersWithViews();
        servicios.AddSingleton<ITempDataProvider, TempDataProviderFalso>();

        var httpContext = new DefaultHttpContext
        {
            RequestServices = servicios.BuildServiceProvider(),
        };

        // ControllerContext exige un ControllerActionDescriptor (no un
        // ActionDescriptor genérico); con HttpContext + RouteData el
        // controlador puede leer y escribir TempData para la PRG.
        var contexto = new ControllerContext(
            new ActionContext(
                httpContext,
                new RouteData(),
                new ControllerActionDescriptor()));

        return new ClientesController(repositorio)
        {
            ControllerContext = contexto,
        };
    }

    private static void AgregarErrorDelEnlazador(ClientesController controlador, string campo, string valor)
    {
        controlador.ModelState.SetModelValue(campo, new ValueProviderResult(valor));
        controlador.ModelState.AddModelError(campo, $"The value '{valor}' is not valid for {campo}.");
    }

    /// <summary>Convierte el ModelState en la lista de mensajes que ve la vista.</summary>
    private static IReadOnlyList<System.ComponentModel.DataAnnotations.ValidationResult> ResultadosDe(
        ClientesController controlador)
        => controlador.ModelState.Values
            .SelectMany(estado => estado.Errors)
            .Select(error => new System.ComponentModel.DataAnnotations.ValidationResult(error.ErrorMessage))
            .ToList();

    /// <summary>Proveedor de TempData que no persiste nada entre peticiones.</summary>
    private sealed class TempDataProviderFalso : ITempDataProvider
    {
        public IDictionary<string, object> LoadTempData(HttpContext context)
            => new Dictionary<string, object>();

        public void SaveTempData(HttpContext context, IDictionary<string, object> values)
        {
            // Las pruebas viven en una sola "petición": no hace falta guardar.
        }
    }
}
