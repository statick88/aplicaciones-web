import { Component, OnInit, inject, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { Cliente } from '../../interfaces/cliente';
import { ClienteService } from '../../services/cliente.service';

@Component({
  selector: 'app-clientes',
  imports: [RouterLink],
  styleUrl: './clientes.component.css',
  template: ` <section class="page" aria-labelledby="clientes-title">
    <div class="page-heading">
      <div>
        <p class="eyebrow">Registros</p>
        <h1 id="clientes-title">Clientes</h1>
        <p>Consultá y actualizá los datos de contacto.</p>
      </div>
      <a routerLink="/clientes/nuevo" class="button">Nuevo cliente</a>
    </div>
    @if (cargando()) {
      <p class="state" role="status">Cargando clientes…</p>
    } @else if (error()) {
      <div class="state error" role="alert">
        <p>{{ error() }}</p>
        <button type="button" (click)="cargar()">Reintentar</button>
      </div>
    } @else if (!clientes().length) {
      <div class="state">
        <h2>Aún no hay clientes</h2>
        <p>Creá el primer registro para comenzar.</p>
        <a routerLink="/clientes/nuevo" class="button">Crear cliente</a>
      </div>
    } @else {
      <div class="table-wrap">
        <table>
          <caption class="sr-only">
            Lista de clientes
          </caption>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Teléfono</th>
              <th><span class="sr-only">Acciones</span></th>
            </tr>
          </thead>
          <tbody>
            @for (cliente of clientes(); track cliente.id) {
              <tr>
                <td data-label="Nombre">{{ cliente.nombre }} {{ cliente.apellido }}</td>
                <td data-label="Correo">{{ cliente.correo }}</td>
                <td data-label="Teléfono">{{ cliente.telefono }}</td>
                <td class="actions-cell">
                  <div class="actions">
                    <a [routerLink]="['/clientes', cliente.id, 'editar']">Editar</a>
                    <button type="button" class="link danger" (click)="confirmarEliminar(cliente)">
                      {{ confirmando() === cliente.id ? 'Confirmar eliminación' : 'Eliminar' }}
                    </button>
                  </div>
                </td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    }
    @if (exito()) {
      <p class="notice success" role="status">{{ exito() }}</p>
    }
  </section>`,
})
export class ClientesComponent implements OnInit {
  private readonly service = inject(ClienteService);
  readonly clientes = signal<Cliente[]>([]);
  readonly cargando = signal(true);
  readonly error = signal('');
  readonly exito = signal('');
  readonly confirmando = signal<number | null>(null);
  ngOnInit() {
    this.cargar();
  }
  cargar() {
    this.cargando.set(true);
    this.error.set('');
    this.service.listar().subscribe({
      next: (clientes) => {
        this.clientes.set(clientes);
        this.cargando.set(false);
      },
      error: () => {
        this.error.set('No pudimos cargar los clientes.');
        this.cargando.set(false);
      },
    });
  }
  confirmarEliminar(cliente: Cliente) {
    if (this.confirmando() !== cliente.id) {
      this.confirmando.set(cliente.id);
      return;
    }
    this.service.eliminar(cliente.id).subscribe({
      next: () => {
        this.clientes.update((items) => items.filter((item) => item.id !== cliente.id));
        this.confirmando.set(null);
        this.exito.set('Cliente eliminado.');
      },
      error: (error: HttpErrorResponse) => {
        this.confirmando.set(null);
        this.error.set(error.error?.mensaje ?? 'No se pudo eliminar el cliente.');
      },
    });
  }
}
