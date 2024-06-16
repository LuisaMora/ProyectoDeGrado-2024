<?php

namespace App\Utils;

use Illuminate\Support\Facades\Storage;

class ImageHandler
{
    public static function guardarImagen($imagen,$nombreCarpeta)
    {
        $categoriaImg = md5_file($imagen->getRealPath()).time() .'.'. $imagen->getClientOriginalExtension();
        $path = $imagen->storeAs('public/'.$nombreCarpeta, $categoriaImg);
        return Storage::url($path);
    }

    public static function eliminarImagenes(array $directions)
{
    $eliminados = true;

    foreach ($directions as $direction) {
        $path = str_replace('storage', 'public', $direction); // Remueve 'storage' y lo reemplaza por 'public'
        if (Storage::exists($path)) {
            Storage::delete($path);
            // Verificar si el archivo todavía existe después de eliminarlo
            if (Storage::exists($path)) {
                $eliminados = false;
            }
        } else {
            $eliminados = false;
        }
    }

    return $eliminados;
}
}