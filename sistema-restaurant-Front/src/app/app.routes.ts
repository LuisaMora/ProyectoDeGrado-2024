import { Routes } from '@angular/router';
import { HomeComponent } from './modulos/home/home.component';
import { RegistrarComponent } from './modulos/registro/registrar-platillo/registrar.component';
import { HomeRestaurantComponent } from './modulos/home-restaurant/home-restaurant.component';
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
        path:'home-restaurant',
        component:HomeRestaurantComponent
    },
    { 
        path:'propietario', 
        loadChildren:()=>import('../app/modulos/registro/registro.routes').then(m=>m.ROUTES_REGISTER)
    },
    { 
        path:'menu', 
        loadChildren:()=>import('../app/modulos/menu/menu.routes').then(m=>m.ROUTES_MENU)
    }
  
]; 
