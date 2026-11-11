// =====================================================================
// Contrato CRUD de clientes compartido por todos los adaptadores.
//
// Equivale a semana2/interfaces/RepositorioClientes.php. El controlador
// depende SOLO de esta interfaz: el adaptador en memoria se usa en las
// pruebas y como referencia de comportamiento, y el adaptador MySQL es
// el que persiste los datos de la aplicación web.
//
// Los métodos son asíncronos porque hacen E/S sobre la red: en un
// servidor web bloquear un hilo mientras MySQL responde consume el
// presupuesto de concurrencia de toda la aplicación.
// =====================================================================

using ClientesWeb.Models;

namespace ClientesWeb.Data;

public interface IRepositorioClientes
{
    /// <summary>Todos los clientes, ordenados por id.</summary>
    Task<IReadOnlyList<Cliente>> ListarAsync(CancellationToken cancelacion = default);

    /// <summary>El cliente con ese id, o null si no existe.</summary>
    Task<Cliente?> ConsultarAsync(int id, CancellationToken cancelacion = default);

    /// <summary>
    /// Da de alta un cliente y devuelve su id definitivo. El adaptador
    /// reasigna <see cref="Cliente.Id"/> con el valor que generó la base.
    /// </summary>
    Task<int> RegistrarAsync(Cliente cliente, CancellationToken cancelacion = default);

    /// <summary>
    /// Sobrescribe los datos de un cliente existente. Devuelve false si
    /// el id no existe; si el id existe pero nada cambió, devuelve true.
    /// </summary>
    Task<bool> ActualizarAsync(Cliente cliente, CancellationToken cancelacion = default);

    /// <summary>Elimina un cliente. false si el id no existe.</summary>
    Task<bool> EliminarAsync(int id, CancellationToken cancelacion = default);

    /// <summary>
    /// true si ya hay un cliente con esa cédula. <paramref name="exceptoId"/>
    /// permite ignorar al propio cliente al editar.
    /// </summary>
    Task<bool> ExisteCedulaAsync(string cedula, int exceptoId = 0, CancellationToken cancelacion = default);

    /// <summary>Misma regla que <see cref="ExisteCedulaAsync"/>, para el correo.</summary>
    Task<bool> ExisteCorreoAsync(string correo, int exceptoId = 0, CancellationToken cancelacion = default);
}
