import { Routes } from '@angular/router';
import { RegistrarComponent } from './modulos/menu/registrar/registrar.component';
import { ListaComponent } from './modulos/menu/lista/lista.component';
export const routes: Routes = [
    {
        path:'registrar-platillo',
        component:RegistrarComponent
    },
    {
        path:'lista-platillos',
        component:ListaComponent
    } 
];
