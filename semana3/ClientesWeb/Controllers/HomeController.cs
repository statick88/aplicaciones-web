// =====================================================================
// Controlador residual heredado de la plantilla `dotnet new mvc`.
//
// Solo queda la página de error: la ruta por defecto de la
// aplicación apunta a ClientesController (ver Program.cs).
// =====================================================================

using System.Diagnostics;
using ClientesWeb.Models;
using Microsoft.AspNetCore.Mvc;

namespace ClientesWeb.Controllers;

public sealed class HomeController : Controller
{
    /// <summary>Página de error invocada por UseExceptionHandler.</summary>
    [ResponseCache(Duration = 0, Location = ResponseCacheLocation.None, NoStore = true)]
    public IActionResult Error()
    {
        return View(new ErrorViewModel
        {
            RequestId = Activity.Current?.Id ?? HttpContext.TraceIdentifier,
        });
    }
}
