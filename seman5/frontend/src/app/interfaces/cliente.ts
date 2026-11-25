export interface Cliente {
  id: number;
  nombre: string;
  apellido: string;
  correo: string;
  telefono: string;
}

export interface ClienteInput {
  nombre: string;
  apellido: string;
  correo: string;
  telefono: string;
}

export interface ResultadoApi {
  estado: boolean;
  id?: number;
}
