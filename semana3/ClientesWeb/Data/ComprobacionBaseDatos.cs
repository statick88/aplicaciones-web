// =====================================================================
// Comprobación de salud del servicio `db` para /health.
//
// El contenedor expone /health y el HEALTHCHECK del Dockerfile lo
// consulta: si MySQL no responde, Docker marca el contenedor `web` como
// `unhealthy` y `docker compose ps` lo muestra, en lugar de dejar una
// página en blanco sin diagnóstico.
// =====================================================================

using Microsoft.Extensions.Diagnostics.HealthChecks;
using MySqlConnector;

namespace ClientesWeb.Data;

public sealed class ComprobacionBaseDatos : IHealthCheck
{
    private readonly string _cadenaConexion;

    public ComprobacionBaseDatos(string cadenaConexion)
    {
        _cadenaConexion = cadenaConexion;
    }

    public async Task<HealthCheckResult> CheckHealthAsync(
        HealthCheckContext contexto,
        CancellationToken cancelacion = default)
    {
        try
        {
            await using var conexion = new MySqlConnection(_cadenaConexion);
            await conexion.OpenAsync(cancelacion);

            await using var comando = conexion.CreateCommand();
            comando.CommandText = "SELECT 1";
            await comando.ExecuteScalarAsync(cancelacion);

            return HealthCheckResult.Healthy("Conexión con MySQL disponible.");
        }
        catch (Exception excepcion)
        {
            // El detalle viaja en la comprobación, no en la respuesta
            // HTTP: /health solo devuelve Healthy/Unhealthy al cliente.
            return HealthCheckResult.Unhealthy("No se pudo consultar MySQL.", excepcion);
        }
    }
}
