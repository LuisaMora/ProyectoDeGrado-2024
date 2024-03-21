import { Routes } from "@angular/router";
import { RegistrarCategoriaComponent } from "./registrar-categoria/registrar-categoria.component";
import { RegistrarComponent } from "./registrar-platillo/registrar.component";
import { RegistrarEmpleadoComponent } from "./registrar-empleado/registrar-empleado.component";

export const ROUTES_REGISTER:Routes=[
    {
        path:'register-platillo',
        component:RegistrarComponent
    },
    {
        path:'register-categoria',
        component:RegistrarCategoriaComponent
    },
    {
        path:'register-empleado',
        component:RegistrarEmpleadoComponent
    }
    ]  