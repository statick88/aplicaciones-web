# =====================================================================
# Semana 3 — imagen de la aplicación ASP.NET Core (CRUD de clientes).
#
# Build multi-stage, el patrón estándar de .NET en Docker:
#   1. `build`   → SDK completo: restore + publish.
#   2. `runtime` → solo ASP.NET Runtime: la imagen final no lleva el
#                   SDK, ni el código fuente, ni los binarios intermedios.
#
# El contexto de build es el directorio semana3/ (ver compose.yaml) y
# .dockerignore excluye artefactos de gentle-ai, bin/, obj/ y docs/.
# =====================================================================

# ---------- Etapa 1: compilación ----------
FROM mcr.microsoft.com/dotnet/sdk:10.0 AS build
WORKDIR /src

# Copiar primero solo el .csproj permite cachear la capa de restore:
# mientras no cambien las dependencias, no se vuelve a descargar NuGet.
COPY ClientesWeb/ClientesWeb.csproj ClientesWeb/
RUN dotnet restore ClientesWeb/ClientesWeb.csproj

# Ahora sí, el código. `--no-restore` reutiliza el restore anterior.
COPY ClientesWeb/ ClientesWeb/
RUN dotnet publish ClientesWeb/ClientesWeb.csproj \
    -c Release \
    -o /app/publish \
    --no-restore

# ---------- Etapa 2: ejecución ----------
FROM mcr.microsoft.com/dotnet/aspnet:10.0 AS runtime
WORKDIR /app

# curl solo se instala para que HEALTHCHECK pueda consultar /health:
# la imagen aspnet no trae ni curl ni wget.
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/*

# Publicación única: DLLs + wwwroot + runtimeconfig.
COPY --from=build /app/publish .

# 8080 es el puerto por defecto de ASPNETCORE_URLS en las imágenes
# oficiales de .NET 8+. compose.yaml lo publica como 8081.
EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=3s --start-period=20s --retries=5 \
    CMD curl -fsS http://127.0.0.1:8080/health || exit 1

ENTRYPOINT ["dotnet", "ClientesWeb.dll"]
