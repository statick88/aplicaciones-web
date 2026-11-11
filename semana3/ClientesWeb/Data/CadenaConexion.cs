// =====================================================================
// Cadena de conexión a MySQL construida desde variables de entorno.
//
// Equivale a semana2/config/database.php: la configuración no vive en
// el código ni en appsettings.json, llega desde compose.yaml. Así el
// mismo binario sirve para el contenedor, para las pruebas locales y
// para cualquier otro entorno sin recompilar.
// =====================================================================

using Microsoft.Extensions.Configuration;
using MySqlConnector;

namespace ClientesWeb.Data;

public static class CadenaConexion
{
    /// <summary>
    /// Lee DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS y arma la
    /// cadena con MySqlConnectionStringBuilder, que escapa los valores
    /// por nosotros (nunca concatenarlos a mano).
    /// </summary>
    public static string Obtener(IConfiguration configuracion)
    {
        var builder = new MySqlConnectionStringBuilder
        {
            // Dentro de Compose el servicio MySQL se llama `db`; fuera
            // de él se puede sobreescribir con DB_HOST=127.0.0.1.
            Server = configuracion["DB_HOST"] ?? "db",
            Port = uint.TryParse(configuracion["DB_PORT"], out var puerto) ? puerto : 3306u,
            Database = configuracion["DB_NAME"] ?? "semana3",
            UserID = configuracion["DB_USER"] ?? "root",
            Password = configuracion["DB_PASS"] ?? "root",
            CharacterSet = "utf8mb4",
            // La base arranca en el mismo host que la app, así que 15 s
            // es un techo generoso para detectar un problema real.
            ConnectionTimeout = 15,
            DefaultCommandTimeout = 30,
            // ConvertUnknownDateTimeIsZero evita que una columna DATE
            // nula o mal formada reviente al mapear a DateTime?.
            ConvertZeroDateTime = false,
            AllowUserVariables = false,
        };

        return builder.ConnectionString;
    }
}
