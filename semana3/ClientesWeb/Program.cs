// =====================================================================
// Punto de entrada de la aplicación web de la Semana 3.
//
// Aquí se ensambla todo:
//   1. MVC (controladores + vistas Razor);
//   2. el adaptador de repositorio (MySQL por defecto);
//   3. la comprobación de salud /health que usa el Dockerfile;
//   4. las rutas convencionales {controller}/{action}/{id?}.
// =====================================================================

using ClientesWeb.Data;
using Microsoft.AspNetCore.Diagnostics.HealthChecks;

var builder = WebApplication.CreateBuilder(args);

// MVC clásico: controladores, vistas Razor y tag helpers.
builder.Services.AddControllersWithViews();

// Cadena de conexión leída de las variables de entorno que inyecta
// compose.yaml (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS).
string cadenaConexion = CadenaConexion.Obtener(builder.Configuration);

// Se elige el adaptador del contrato IRepositorioClientes:
//   * "memoria" → sin base de datos; sirve para probar la interfaz
//     aislada y para leer el código sin levantar Docker;
//   * cualquier otro valor (por defecto) → MySQL, el de la entrega.
if (string.Equals(builder.Configuration["DB_PROVIDER"], "memoria", StringComparison.OrdinalIgnoreCase))
{
    builder.Services.AddSingleton<IRepositorioClientes, RepositorioClientesMemoria>();
}
else
{
    builder.Services.AddSingleton<IRepositorioClientes>(_ => new RepositorioClientesMySql(cadenaConexion));
}

// Comprobación de salud: se registra en DI con la misma cadena que usa
// el repositorio, y se engancha a /health más abajo.
builder.Services.AddSingleton(_ => new ComprobacionBaseDatos(cadenaConexion));
builder.Services.AddHealthChecks()
    .AddCheck<ComprobacionBaseDatos>("mysql", tags: new[] { "db" });

var app = builder.Build();

// Páginas de error amigables fuera de desarrollo (en desarrollo se ve
// la traza completa, que es lo que ayuda a depurar).
if (!app.Environment.IsDevelopment())
{
    app.UseExceptionHandler("/Home/Error");
}

// No hay TLS en este entorno académico: Compose publica HTTP en el
// puerto 8081 y no existe un puerto HTTPS al que redirigir. Activar
// UseHttpsRedirection aquí produciría un redirect imposible de
// completar dentro del contenedor.
app.UseRouting();

// ---------------------------------------------------------------------
// UTF-8 explícito en toda respuesta de texto.
//
// ASP.NET Core ya envía `text/html; charset=utf-8` desde las vistas
// Razor, y el HTML lleva además su <meta charset="utf-8">. Lo que NO
// traía charset eran los archivos estáticos: se servían como `text/css`
// y `text/javascript` a secas, y entonces al navegador le tocaba
// adivinar la codificación (en la práctica casi siempre acierta, pero
// es una decisión que no debería depender del azar).
//
// OnStarting se ejecuta justo antes de que se envíen las cabeceras, así
// que ahí todavía se puede corregir el Content-Type sin reescribir el
// cuerpo de la respuesta. Solo se tocan los tipos de texto: a las
// imágenes no hay qué imponerles un juego de caracteres.
// ---------------------------------------------------------------------
app.Use(async (contexto, siguiente) =>
{
    contexto.Response.OnStarting(() =>
    {
        // Condición en una sola expresión: así el análisis de flujo ve que
        // `tipo` no es null dentro del `if` (con un bool intermedio como
        // `esTexto` el compilador no puede seguir el rastro y avisa CS8602).
        var tipo = contexto.Response.ContentType;

        if (tipo is not null &&
            (tipo.StartsWith("text/", StringComparison.OrdinalIgnoreCase) ||
             string.Equals(tipo, "application/json", StringComparison.OrdinalIgnoreCase)) &&
            !tipo.Contains("charset", StringComparison.OrdinalIgnoreCase))
        {
            contexto.Response.ContentType = tipo + "; charset=utf-8";
        }

        return Task.CompletedTask;
    });

    await siguiente();
});

app.UseAuthorization();

// Archivos estáticos: css/, img/, favicon (wwwroot/).
app.MapStaticAssets();

// Ruta por defecto: la raíz "/" abre directamente el listado de clientes.
app.MapControllerRoute(
    name: "default",
    pattern: "{controller=Clientes}/{action=Index}/{id?}")
    .WithStaticAssets();

// /health responde 200 cuando MySQL responde y 503 cuando no.
app.MapHealthChecks("/health", new HealthCheckOptions
{
    // Solo el código de estado: no se filtra información de
    // infraestructura a quien consulte el endpoint.
    ResponseWriter = async (context, report) =>
    {
        context.Response.ContentType = "application/json";
        await context.Response.WriteAsync(
            $"{{\"status\":\"{report.Status.ToString().ToLowerInvariant()}\"}}");
    },
});

app.Run();
