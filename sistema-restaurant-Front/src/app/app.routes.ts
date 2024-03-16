import { Routes } from '@angular/router';
//import { RegistrarComponent } from './modulos/menu/registrar/registrar.component';
//import { ListaComponent } from './modulos/menu/lista/lista.component';
import { FormRegistroComponent } from './modulos/form-registro/form-registro.component';
import { LoginComponent } from './modulos/login/login.component';
export const routes: Routes = [
    {
        path:'login',
        component:LoginComponent
        
    } ,
    {
        path:'formulario-registro',
        component:FormRegistroComponent
        
    } 
];
