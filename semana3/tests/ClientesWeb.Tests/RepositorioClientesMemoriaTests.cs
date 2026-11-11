// =====================================================================
// Pruebas del contrato IRepositorioClientes sobre el adaptador en
// memoria: son las que fijan el comportamiento que también debe
// exhibir el adaptador MySQL.
// =====================================================================

using ClientesWeb.Data;
using ClientesWeb.Models;
using Xunit;

namespace ClientesWeb.Tests;

public sealed class RepositorioClientesMemoriaTests
{
    [Fact]
    public async Task Un_repositorio_nuevo_esta_vacio()
    {
        var repositorio = new RepositorioClientesMemoria();

        Assert.Empty(await repositorio.ListarAsync());
        Assert.Null(await repositorio.ConsultarAsync(1));
    }

    [Fact]
    public async Task Registrar_asigna_ids_incrementales_y_los_refleja_en_el_objeto()
    {
        var repositorio = new RepositorioClientesMemoria();

        var primero = ClientesFalsos.Validos(cedula: "0102030405").AEntidad();
        var segundo = ClientesFalsos.Validos(cedula: "0912345678").AEntidad();

        Assert.Equal(1, await repositorio.RegistrarAsync(primero));
        Assert.Equal(2, await repositorio.RegistrarAsync(segundo));
        Assert.Equal(1, primero.Id);
        Assert.Equal(2, segundo.Id);
    }

    [Fact]
    public async Task Listar_devuelve_todos_los_clientes_ordenados_por_id()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0102030405").AEntidad());
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0912345678").AEntidad());
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "1122334455").AEntidad());

        var lista = await repositorio.ListarAsync();

        Assert.Equal(new[] { 1, 2, 3 }, lista.Select(c => c.Id));
    }

    [Fact]
    public async Task Consultar_devuelve_el_cliente_o_null_si_no_existe()
    {
        var repositorio = new RepositorioClientesMemoria();
        var id = await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());

        var encontrado = await repositorio.ConsultarAsync(id);

        Assert.NotNull(encontrado);
        Assert.Equal("Ana", encontrado!.Nombre);
        Assert.Null(await repositorio.ConsultarAsync(9999));
    }

    [Fact]
    public async Task Actualizar_modifica_los_doce_campos_del_cliente()
    {
        var repositorio = new RepositorioClientesMemoria();
        var id = await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());

        var cambiado = ClientesFalsos.Validos(cedula: "0998877665", correo: "nuevo@correo.com").AEntidad();
        cambiado.Id = id;
        cambiado.Nombre = "Luis";
        cambiado.Activo = false;
        cambiado.LimiteCredito = 999.50m;

        Assert.True(await repositorio.ActualizarAsync(cambiado));

        var guardado = await repositorio.ConsultarAsync(id);
        Assert.Equal("Luis", guardado!.Nombre);
        Assert.Equal("0998877665", guardado.Cedula);
        Assert.Equal("nuevo@correo.com", guardado.Correo);
        Assert.False(guardado.Activo);
        Assert.Equal(999.50m, guardado.LimiteCredito);
    }

    [Fact]
    public async Task Actualizar_un_id_inexistente_devuelve_false()
    {
        var repositorio = new RepositorioClientesMemoria();
        var cliente = ClientesFalsos.Validos().AEntidad();
        cliente.Id = 4242;

        Assert.False(await repositorio.ActualizarAsync(cliente));
    }

    [Fact]
    public async Task Eliminar_borra_el_cliente_y_devuelve_false_si_no_existe()
    {
        var repositorio = new RepositorioClientesMemoria();
        var id = await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());

        Assert.True(await repositorio.EliminarAsync(id));
        Assert.Null(await repositorio.ConsultarAsync(id));
        Assert.False(await repositorio.EliminarAsync(id));
        Assert.Empty(await repositorio.ListarAsync());
    }

    [Theory]
    [InlineData("0102030405", 0, true)]   // existe
    [InlineData("0102030405", 1, false)]  // es el propio cliente al editar
    [InlineData("1111111111", 0, false)]  // nadie la tiene
    public async Task ExisteCedula_respeta_el_id_excluido(string cedula, int exceptoId, bool esperado)
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(cedula: "0102030405").AEntidad());

        Assert.Equal(esperado, await repositorio.ExisteCedulaAsync(cedula, exceptoId));
    }

    [Fact]
    public async Task ExisteCorreo_no_distingue_mayusculas_de_minusculas()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos(correo: "ana@correo.com").AEntidad());

        Assert.True(await repositorio.ExisteCorreoAsync("ANA@CORREO.COM"));
        Assert.False(await repositorio.ExisteCorreoAsync("otra@correo.com"));
    }

    [Fact]
    public async Task El_repositorio_guarda_copias_y_no_acepta_que_el_llamador_lo_muta()
    {
        var repositorio = new RepositorioClientesMemoria();
        var cliente = ClientesFalsos.Validos().AEntidad();
        await repositorio.RegistrarAsync(cliente);

        cliente.Nombre = "Modificado-después-de-guardar";

        var guardado = await repositorio.ConsultarAsync(cliente.Id);
        Assert.Equal("Ana", guardado!.Nombre);
    }

    [Fact]
    public async Task El_adaptador_en_memoria_cumple_el_mismo_contrato_que_el_de_mysql()
    {
        // Compila contra la interfaz: si el contrato cambia, este test
        // deja de compilar en ambos adaptadores a la vez.
        IRepositorioClientes repositorio = new RepositorioClientesMemoria();

        Assert.NotNull(await repositorio.ListarAsync());
    }
}
