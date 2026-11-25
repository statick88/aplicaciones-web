import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';
import { LoginComponent } from './components/login/login.component';
import { ClientesComponent } from './components/clientes/clientes.component';
import { ClienteFormComponent } from './components/cliente-form/cliente-form.component';

export const routes: Routes = [
  { path: 'login', component: LoginComponent },
  {
    path: 'clientes',
    canActivate: [authGuard],
    children: [
      { path: '', component: ClientesComponent },
      { path: 'nuevo', component: ClienteFormComponent },
      { path: ':id/editar', component: ClienteFormComponent },
    ],
  },
  { path: '', pathMatch: 'full', redirectTo: 'clientes' },
  { path: '**', redirectTo: 'clientes' },
];
