
export interface Usuario{
    id:number;
    nombre:string;
    apellido_paterno:string;
    apellido_materno:string;
    correo:string;
    nickname:string;
    foto_perfil:string;
    access_token:string;
}

export interface Administrador{
    id:number;
    usuario: Usuario;
}

export interface Propietario{
    id:number;
    ci:number;
    fecha_registro:Date;
    pais:string;
    departamento:string;
    usuario: Usuario;
}

export interface Empleado{
    id:number;
    ci:number;
    fecha_nacimiento:Date;
    fecha_contratacion:Date;
    direccion:string;
    usuario: Usuario;
}