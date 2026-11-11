// =====================================================================
// Pruebas de la entidad Cliente y del catálogo de tipos.
// =====================================================================

using ClientesWeb.Models;
using Xunit;

namespace ClientesWeb.Tests;

public sealed class ClienteTests
{
    [Fact]
    public void Un_cliente_nuevo_esta_activo_y_con_tipo_natural()
    {
        var cliente = new Cliente { Cedula = "0102030405", Nombre = "Ana", Apellido = "Pérez", Correo = "a@b.co" };

        Assert.True(cliente.Activo);
        Assert.Equal(TipoClientes.Natural, cliente.TipoCliente);
        Assert.Equal(0, cliente.Id); // 0 = todavía no persistido
        Assert.Equal(0m, cliente.LimiteCredito);
        Assert.Null(cliente.Telefono);
        Assert.Null(cliente.Direccion);
        Assert.Null(cliente.Ciudad);
        Assert.Null(cliente.FechaNacimiento);
        Assert.Null(cliente.Observaciones);
    }

    [Fact]
    public void El_catalogo_de_tipos_expone_los_dos_valores_admitidos()
    {
        // La misma lista alimenta el <select> del formulario y la
        // expresión regular del validador: no pueden divergir.
        Assert.Equal(new[] { "Natural", "Jurídica" }, TipoClientes.Valores);
    }

    [Fact]
    public void NombreCompleto_una_nombre_y_apellido()
    {
        var cliente = new Cliente { Nombre = "Ana", Apellido = "Pérez" };

        Assert.Equal("Ana Pérez", cliente.NombreCompleto);
    }
}
