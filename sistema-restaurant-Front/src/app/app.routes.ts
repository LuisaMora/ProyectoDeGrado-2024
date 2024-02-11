import { Routes } from '@angular/router';
import { RegistrarPlatilloComponent } from './modulos/menu/registrar-platillo/registrar-platillo.component';
import { EditarPlatilloComponent } from './modulos/menu/editar-platillo/editar-platillo.component';
//importar el modulo creado
export const routes: Routes = [
    {
        path:'registrar-platillo', 
        component: RegistrarPlatilloComponent
    },
    {
        path:'editar-platillo', 
        component: EditarPlatilloComponent
    }
];
