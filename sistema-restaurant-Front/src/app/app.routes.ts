import { Routes } from '@angular/router';
import { HomeComponent } from './modulos/home/home.component';
import { RegistrarComponent } from './modulos/registro/registrar-platillo/registrar.component';
export const routes: Routes = [
    {
        path:'usuario',
        loadChildren:()=>import('../app/modulos/auth/auth.routes').then(m=>m.AUTH_ROUTES)
    },
    {
        path:'',
        component:HomeComponent
    },
    { 
        path:'registro',
        component:RegistrarComponent
    }
];
