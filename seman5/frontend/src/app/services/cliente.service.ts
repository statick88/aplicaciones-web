import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Cliente, ClienteInput, ResultadoApi } from '../interfaces/cliente';

@Injectable({ providedIn: 'root' })
export class ClienteService {
  private readonly http = inject(HttpClient);
  private readonly url = '/api/api.php';

  listar() {
    return this.http.get<Cliente[]>(this.url);
  }
  obtener(id: number) {
    return this.http.get<Cliente>(`${this.url}?id=${id}`);
  }
  crear(cliente: ClienteInput) {
    return this.http.post<ResultadoApi>(this.url, cliente);
  }
  actualizar(id: number, cliente: ClienteInput) {
    return this.http.put<ResultadoApi>(`${this.url}?id=${id}`, cliente);
  }
  eliminar(id: number) {
    return this.http.delete<ResultadoApi>(`${this.url}?id=${id}`);
  }
}
