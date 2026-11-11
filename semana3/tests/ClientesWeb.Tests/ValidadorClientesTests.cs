// =====================================================================
// Pruebas de ValidadorClientes: las reglas que deciden si un formulario
// llega o no a la base de datos.
// =====================================================================

using ClientesWeb.Data;
using ClientesWeb.Models;
using Xunit;

namespace ClientesWeb.Tests;

public sealed class ValidadorClientesTests
{
    [Fact]
    public void Un_formulario_completo_y_bien_formado_no_tiene_errores()
    {
        var errores = ValidadorClientes.Validar(ClientesFalsos.Validos());

        Assert.Empty(errores);
    }

    [Fact]
    public void Los_campos_opcionales_pueden_quedar_vacios()
    {
        var entrada = ClientesFalsos.Validos();
        entrada.Telefono = null;
        entrada.Direccion = null;
        entrada.Ciudad = null;
        entrada.FechaNacimiento = null;
        entrada.Observaciones = null;

        Assert.Empty(ValidadorClientes.Validar(entrada));
    }

    [Theory]
    [InlineData("", "La cédula es obligatoria.")]
    [InlineData("abc123", "La cédula debe contener entre 6 y 15 dígitos.")]
    [InlineData("12345", "La cédula debe contener entre 6 y 15 dígitos.")]
    public void La_cedula_es_obligatoria_y_solo_acepta_digitos(string cedula, string mensajeEsperado)
    {
        var entrada = ClientesFalsos.Validos(cedula: cedula);

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains(mensajeEsperado, errores);
    }

    [Fact]
    public void Un_correo_mal_formado_se_rechaza_con_mensaje_en_espanol()
    {
        var entrada = ClientesFalsos.Validos(correo: "correo-invalido");

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains("El correo electrónico no es válido.", errores);
    }

    [Fact]
    public void Un_nombre_de_un_solo_caracter_se_rechaza()
    {
        var entrada = ClientesFalsos.Validos();
        entrada.Nombre = "A";

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains("El nombre debe tener entre 2 y 100 caracteres.", errores);
    }

    [Fact]
    public void Un_telefono_con_letras_se_rechaza()
    {
        var entrada = ClientesFalsos.Validos();
        entrada.Telefono = "no-es-telefono";

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains(
            "El teléfono debe contener de 6 a 20 caracteres (se admiten dígitos, espacios, guiones y el signo +).",
            errores);
    }

    [Fact]
    public void Una_fecha_de_nacimiento_futura_se_rechaza()
    {
        var entrada = ClientesFalsos.Validos();
        entrada.FechaNacimiento = DateTime.Today.AddYears(1);

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains("La fecha de nacimiento no puede ser futura.", errores);
    }

    [Fact]
    public void Una_fecha_anterior_a_1900_se_rechaza()
    {
        var entrada = ClientesFalsos.Validos();
        entrada.FechaNacimiento = new DateTime(1850, 1, 1);

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains("La fecha de nacimiento no puede ser anterior a 1900-01-01.", errores);
    }

    [Fact]
    public void Un_limite_de_credito_negativo_se_rechaza()
    {
        var entrada = ClientesFalsos.Validos();
        entrada.LimiteCredito = -1;

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains("El límite de crédito no puede ser negativo.", errores);
    }

    [Fact]
    public void Un_tipo_de_cliente_fuera_del_catalogo_se_rechaza()
    {
        var entrada = ClientesFalsos.Validos();
        entrada.TipoCliente = "Premium";

        var errores = ClientesFalsos.Mensajes(ValidadorClientes.Validar(entrada));

        Assert.Contains("El tipo de cliente debe ser Natural o Jurídica.", errores);
    }

    [Fact]
    public async Task Una_cedula_duplicada_se_detecta_en_el_alta()
    {
        var repositorio = new RepositorioClientesMemoria();
        var original = ClientesFalsos.Validos(cedula: "0102030405", correo: "otro@correo.com");
        await repositorio.RegistrarAsync(original.AEntidad());

        var repetida = ClientesFalsos.Validos(cedula: "0102030405", correo: "distinto@correo.com");
        var errores = ClientesFalsos.Mensajes(
            await ValidadorClientes.ValidarAsync(repetida, repositorio));

        Assert.Contains("Ya existe un cliente con esa cédula.", errores);
    }

    [Fact]
    public async Task Un_correo_duplicado_se_detecta_en_el_alta()
    {
        var repositorio = new RepositorioClientesMemoria();
        var original = ClientesFalsos.Validos(cedula: "0102030405", correo: "ana@correo.com");
        await repositorio.RegistrarAsync(original.AEntidad());

        var repetida = ClientesFalsos.Validos(cedula: "0999999999", correo: "ANA@CORREO.COM");
        var errores = ClientesFalsos.Mensajes(
            await ValidadorClientes.ValidarAsync(repetida, repositorio));

        // El comparador del repositorio no distingue mayúsculas, igual
        // que la collation ci de MySQL.
        Assert.Contains("Ya existe un cliente con ese correo electrónico.", errores);
    }

    [Fact]
    public async Task Al_editar_la_propia_cedula_no_se_marca_como_duplicada()
    {
        var repositorio = new RepositorioClientesMemoria();
        var guardado = ClientesFalsos.Validos(cedula: "0102030405", correo: "ana@correo.com");
        var id = await repositorio.RegistrarAsync(guardado.AEntidad());

        var mismoCliente = ClientesFalsos.Validos(cedula: "0102030405", correo: "ana@correo.com");
        mismoCliente.Id = id;

        var errores = await ValidadorClientes.ValidarAsync(mismoCliente, repositorio, id);

        Assert.Empty(errores);
    }

    [Fact]
    public async Task Los_errores_de_unicidad_no_se_evaluan_si_el_formulario_ya_es_invalido()
    {
        var repositorio = new RepositorioClientesMemoria();
        await repositorio.RegistrarAsync(ClientesFalsos.Validos().AEntidad());

        // Cédula duplicada + correo inválido: solo debe reportarse el
        // problema de formato, porque no tiene sentido ir a preguntar
        // a la base por un dato que ni siquiera es válido.
        var entrada = ClientesFalsos.Validos(correo: "no-es-correo");
        var errores = ClientesFalsos.Mensajes(
            await ValidadorClientes.ValidarAsync(entrada, repositorio));

        Assert.Contains("El correo electrónico no es válido.", errores);
        Assert.DoesNotContain("Ya existe un cliente con esa cédula.", errores);
    }
}
