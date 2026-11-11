// =====================================================================
// Adaptador en memoria del contrato CRUD.
//
// Equivale a semana2/repositories/ClienteRepositorioMemoria.php: es la
// forma "de teoría" del patrón Repositorio. Su doble función es:
//
//   * servir de doble de prueba: las pruebas unitarias ejercitan el
//     contrato completo sin necesidad de MySQL ni de Docker;
//   * documentar el comportamiento esperado (qué devuelve cada método
//     ante un id inexistente, en qué orden se listan, etc.).
//
// NO se registra en compose.yaml: la aplicación web usa el adaptador
// MySQL. Se ofrece como alternativa si DB_PROVIDER=memoria.
// =====================================================================

using ClientesWeb.Models;

namespace ClientesWeb.Data;

public sealed class RepositorioClientesMemoria : IRepositorioClientes
{
    /// <summary>Almacén ordenado de inserción; la clave es el id.</summary>
    private readonly Dictionary<int, Cliente> _clientes = new();

    private int _siguienteId;

    public Task<IReadOnlyList<Cliente>> ListarAsync(CancellationToken cancelacion = default)
    {
        // Orden por id, igual que el adaptador MySQL: la vista no debe
        // cambiar de orden según qué adaptador esté configurado.
        IReadOnlyList<Cliente> lista = _clientes.Values
            .OrderBy(c => c.Id)
            .ToList();

        return Task.FromResult(lista);
    }

    public Task<Cliente?> ConsultarAsync(int id, CancellationToken cancelacion = default)
    {
        // Devolver null (y no lanzar) ante un id inexistente es parte
        // del contrato: el controlador lo traduce en un 404 amigable.
        _clientes.TryGetValue(id, out var cliente);
        return Task.FromResult(cliente);
    }

    public Task<int> RegistrarAsync(Cliente cliente, CancellationToken cancelacion = default)
    {
        // El adaptador es quien asigna el id definitivo, igual que hace
        // AUTO_INCREMENT en MySQL.
        var id = ++_siguienteId;
        cliente.Id = id;

        // Se guarda una copia: si el llamador reutiliza el objeto y lo
        // modifica después, no debe mutar el estado interno del repositorio.
        _clientes[id] = Clonar(cliente);

        return Task.FromResult(id);
    }

    public Task<bool> ActualizarAsync(Cliente cliente, CancellationToken cancelacion = default)
    {
        if (!_clientes.ContainsKey(cliente.Id))
        {
            return Task.FromResult(false);
        }

        _clientes[cliente.Id] = Clonar(cliente);
        return Task.FromResult(true);
    }

    public Task<bool> EliminarAsync(int id, CancellationToken cancelacion = default)
    {
        return Task.FromResult(_clientes.Remove(id));
    }

    public Task<bool> ExisteCedulaAsync(string cedula, int exceptoId = 0, CancellationToken cancelacion = default)
    {
        var existe = _clientes.Values.Any(c => c.Id != exceptoId &&
                                               string.Equals(c.Cedula, cedula, StringComparison.OrdinalIgnoreCase));

        return Task.FromResult(existe);
    }

    public Task<bool> ExisteCorreoAsync(string correo, int exceptoId = 0, CancellationToken cancelacion = default)
    {
        var existe = _clientes.Values.Any(c => c.Id != exceptoId &&
                                               string.Equals(c.Correo, correo, StringComparison.OrdinalIgnoreCase));

        return Task.FromResult(existe);
    }

    /// <summary>Copia defensiva: la entidad que recibe el controlador no es el estado guardado.</summary>
    private static Cliente Clonar(Cliente origen) => new()
    {
        Id = origen.Id,
        Cedula = origen.Cedula,
        Nombre = origen.Nombre,
        Apellido = origen.Apellido,
        Correo = origen.Correo,
        Telefono = origen.Telefono,
        Direccion = origen.Direccion,
        Ciudad = origen.Ciudad,
        FechaNacimiento = origen.FechaNacimiento,
        TipoCliente = origen.TipoCliente,
        LimiteCredito = origen.LimiteCredito,
        Activo = origen.Activo,
        Observaciones = origen.Observaciones,
    };
}
