export interface Usuario {
  id: number;
  nombre: string;
  correo: string;
}

export interface SesionRespuesta {
  usuario: Usuario | null;
}

export interface Credenciales {
  correo: string;
  password: string;
}

export interface ApiError {
  mensaje: string;
}
