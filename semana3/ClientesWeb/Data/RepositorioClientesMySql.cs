// =====================================================================
// Adaptador MySQL del contrato CRUD sobre la tabla `clientes`.
//
// Equivale a semana2/repositories/ClienteRepositorioMysql.php. Es el
// adaptador que usa la aplicación web cuando corre en Docker Compose.
//
// Dos decisiones que se mantienen de la Semana 2:
//
//   * TODAS las consultas son parametrizadas (@cedula, @id, ...). Nunca
//     se concatenan valores del usuario en el SQL: eso es la forma de
//     evitar inyección SQL.
//   * Cada operación abre y cierra su propia conexión. MySqlConnector
//     tiene un pool interno, así que "abrir" es reutilizar una
//     conexión ya establecida y el coste es bajo.
// =====================================================================

using ClientesWeb.Models;
using MySqlConnector;

namespace ClientesWeb.Data;

public sealed class RepositorioClientesMySql : IRepositorioClientes
{
    /// <summary>Columnas en el orden exacto en que se leen y escriben.</summary>
    private const string Columnas =
        "id, cedula, nombre, apellido, correo, telefono, direccion, ciudad, " +
        "fecha_nacimiento, tipo_cliente, limite_credito, activo, observaciones";

    private readonly string _cadenaConexion;

    public RepositorioClientesMySql(string cadenaConexion)
    {
        _cadenaConexion = cadenaConexion;
    }

    public async Task<IReadOnlyList<Cliente>> ListarAsync(CancellationToken cancelacion = default)
    {
        await using var conexion = await AbrirAsync(cancelacion);

        await using var comando = conexion.CreateCommand();
        comando.CommandText = $"SELECT {Columnas} FROM clientes ORDER BY id";

        var clientes = new List<Cliente>();
        await using var lector = await comando.ExecuteReaderAsync(cancelacion);
        while (await lector.ReadAsync(cancelacion))
        {
            clientes.Add(ACliente(lector));
        }

        return clientes;
    }

    public async Task<Cliente?> ConsultarAsync(int id, CancellationToken cancelacion = default)
    {
        await using var conexion = await AbrirAsync(cancelacion);

        await using var comando = conexion.CreateCommand();
        comando.CommandText = $"SELECT {Columnas} FROM clientes WHERE id = @id";
        comando.Parameters.AddWithValue("@id", id);

        await using var lector = await comando.ExecuteReaderAsync(cancelacion);
        // ReadAsync avanzado = false significa "no hay fila": el id no
        // existe. Devolver null es parte del contrato.
        return await lector.ReadAsync(cancelacion) ? ACliente(lector) : null;
    }

    public async Task<int> RegistrarAsync(Cliente cliente, CancellationToken cancelacion = default)
    {
        await using var conexion = await AbrirAsync(cancelacion);

        await using var comando = conexion.CreateCommand();
        comando.CommandText =
            "INSERT INTO clientes (cedula, nombre, apellido, correo, telefono, direccion, ciudad, " +
            "fecha_nacimiento, tipo_cliente, limite_credito, activo, observaciones) " +
            "VALUES (@cedula, @nombre, @apellido, @correo, @telefono, @direccion, @ciudad, " +
            "@fecha_nacimiento, @tipo_cliente, @limite_credito, @activo, @observaciones)";

        // AddWithValue infiere el tipo de MySqlDbType a partir de C#, de
        // modo que el valor nunca se interpreta como parte del SQL.
        comando.Parameters.AddWithValue("@cedula", cliente.Cedula);
        comando.Parameters.AddWithValue("@nombre", cliente.Nombre);
        comando.Parameters.AddWithValue("@apellido", cliente.Apellido);
        comando.Parameters.AddWithValue("@correo", cliente.Correo);
        comando.Parameters.AddWithValue("@telefono", (object?)cliente.Telefono ?? DBNull.Value);
        comando.Parameters.AddWithValue("@direccion", (object?)cliente.Direccion ?? DBNull.Value);
        comando.Parameters.AddWithValue("@ciudad", (object?)cliente.Ciudad ?? DBNull.Value);
        comando.Parameters.AddWithValue("@fecha_nacimiento", (object?)cliente.FechaNacimiento?.Date ?? DBNull.Value);
        comando.Parameters.AddWithValue("@tipo_cliente", cliente.TipoCliente);
        comando.Parameters.AddWithValue("@limite_credito", cliente.LimiteCredito);
        comando.Parameters.AddWithValue("@activo", cliente.Activo);
        comando.Parameters.AddWithValue("@observaciones", (object?)cliente.Observaciones ?? DBNull.Value);

        await comando.ExecuteNonQueryAsync(cancelacion);

        // LastInsertedId es el AUTO_INCREMENT que acaba de asignar la
        // base: se devuelve al llamador y también se refleja en el objeto.
        cliente.Id = checked((int)comando.LastInsertedId);
        return cliente.Id;
    }

    public async Task<bool> ActualizarAsync(Cliente cliente, CancellationToken cancelacion = default)
    {
        await using var conexion = await AbrirAsync(cancelacion);

        await using var comando = conexion.CreateCommand();
        comando.CommandText =
            "UPDATE clientes SET cedula = @cedula, nombre = @nombre, apellido = @apellido, " +
            "correo = @correo, telefono = @telefono, direccion = @direccion, ciudad = @ciudad, " +
            "fecha_nacimiento = @fecha_nacimiento, tipo_cliente = @tipo_cliente, " +
            "limite_credito = @limite_credito, activo = @activo, observaciones = @observaciones " +
            "WHERE id = @id";

        comando.Parameters.AddWithValue("@cedula", cliente.Cedula);
        comando.Parameters.AddWithValue("@nombre", cliente.Nombre);
        comando.Parameters.AddWithValue("@apellido", cliente.Apellido);
        comando.Parameters.AddWithValue("@correo", cliente.Correo);
        comando.Parameters.AddWithValue("@telefono", (object?)cliente.Telefono ?? DBNull.Value);
        comando.Parameters.AddWithValue("@direccion", (object?)cliente.Direccion ?? DBNull.Value);
        comando.Parameters.AddWithValue("@ciudad", (object?)cliente.Ciudad ?? DBNull.Value);
        comando.Parameters.AddWithValue("@fecha_nacimiento", (object?)cliente.FechaNacimiento?.Date ?? DBNull.Value);
        comando.Parameters.AddWithValue("@tipo_cliente", cliente.TipoCliente);
        comando.Parameters.AddWithValue("@limite_credito", cliente.LimiteCredito);
        comando.Parameters.AddWithValue("@activo", cliente.Activo);
        comando.Parameters.AddWithValue("@observaciones", (object?)cliente.Observaciones ?? DBNull.Value);
        comando.Parameters.AddWithValue("@id", cliente.Id);

        var filasAfectadas = await comando.ExecuteNonQueryAsync(cancelacion);
        if (filasAfectadas > 0)
        {
            return true;
        }

        // 0 filas afectadas es ambiguo: o el id no existe, o existía pero
        // ningún valor cambió (MySQL cuenta filas modificadas, no
        // coincidentes). Se pregunta a la base para distinguirlo.
        return await ExisteIdAsync(conexion, cliente.Id, cancelacion);
    }

    public async Task<bool> EliminarAsync(int id, CancellationToken cancelacion = default)
    {
        await using var conexion = await AbrirAsync(cancelacion);

        await using var comando = conexion.CreateCommand();
        comando.CommandText = "DELETE FROM clientes WHERE id = @id";
        comando.Parameters.AddWithValue("@id", id);

        // Aquí no hace falta el desambiguador: si no había fila que
        // borrar, 0 filas afectadas significa exactamente "no existía".
        var filasAfectadas = await comando.ExecuteNonQueryAsync(cancelacion);
        return filasAfectadas > 0;
    }

    public Task<bool> ExisteCedulaAsync(string cedula, int exceptoId = 0, CancellationToken cancelacion = default)
    {
        return ExisteValorAsync("cedula", cedula, exceptoId, cancelacion);
    }

    public Task<bool> ExisteCorreoAsync(string correo, int exceptoId = 0, CancellationToken cancelacion = default)
    {
        return ExisteValorAsync("correo", correo, exceptoId, cancelacion);
    }

    // ---------- helpers privados ----------

    private async Task<MySqlConnection> AbrirAsync(CancellationToken cancelacion)
    {
        var conexion = new MySqlConnection(_cadenaConexion);
        await conexion.OpenAsync(cancelacion);
        return conexion;
    }

    /// <summary>Consulta única de unicidad reutilizada por cédula y correo.</summary>
    private async Task<bool> ExisteValorAsync(
        string columna,
        string valor,
        int exceptoId,
        CancellationToken cancelacion)
    {
        await using var conexion = await AbrirAsync(cancelacion);

        await using var comando = conexion.CreateCommand();
        // `columna` es un identificador fijo del código (no datos del
        // usuario), por eso puede interpolarse; el VALOR va en parámetro.
        comando.CommandText =
            $"SELECT COUNT(*) FROM clientes WHERE {columna} = @valor AND id <> @exceptoId";
        comando.Parameters.AddWithValue("@valor", valor);
        comando.Parameters.AddWithValue("@exceptoId", exceptoId);

        var cuenta = Convert.ToInt64(await comando.ExecuteScalarAsync(cancelacion));
        return cuenta > 0;
    }

    private static async Task<bool> ExisteIdAsync(MySqlConnection conexion, int id, CancellationToken cancelacion)
    {
        await using var comando = conexion.CreateCommand();
        comando.CommandText = "SELECT COUNT(*) FROM clientes WHERE id = @id";
        comando.Parameters.AddWithValue("@id", id);

        var cuenta = Convert.ToInt64(await comando.ExecuteScalarAsync(cancelacion));
        return cuenta > 0;
    }

    /// <summary>Mapea la fila actual del lector a la entidad de dominio.</summary>
    private static Cliente ACliente(MySqlDataReader lector) => new()
    {
        Id = lector.GetInt32(lector.GetOrdinal("id")),
        Cedula = lector.GetString(lector.GetOrdinal("cedula")),
        Nombre = lector.GetString(lector.GetOrdinal("nombre")),
        Apellido = lector.GetString(lector.GetOrdinal("apellido")),
        Correo = lector.GetString(lector.GetOrdinal("correo")),
        Telefono = Texto(lector, "telefono"),
        Direccion = Texto(lector, "direccion"),
        Ciudad = Texto(lector, "ciudad"),
        FechaNacimiento = Fecha(lector, "fecha_nacimiento"),
        TipoCliente = lector.GetString(lector.GetOrdinal("tipo_cliente")),
        LimiteCredito = lector.GetDecimal(lector.GetOrdinal("limite_credito")),
        Activo = lector.GetBoolean(lector.GetOrdinal("activo")),
        Observaciones = Texto(lector, "observaciones"),
    };

    /// <summary>Lee una columna nullable como string? sin lanzar si es NULL.</summary>
    private static string? Texto(MySqlDataReader lector, string columna)
    {
        var orden = lector.GetOrdinal(columna);
        return lector.IsDBNull(orden) ? null : lector.GetString(orden);
    }

    /// <summary>Lee una columna DATE nullable como DateTime?.</summary>
    private static DateTime? Fecha(MySqlDataReader lector, string columna)
    {
        var orden = lector.GetOrdinal(columna);
        return lector.IsDBNull(orden) ? null : lector.GetDateTime(orden);
    }
}
