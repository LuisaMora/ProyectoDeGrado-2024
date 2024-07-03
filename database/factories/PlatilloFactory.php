<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Platillo>
 */
class PlatilloFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function asignarCategoria($id_categoria){
        return $this->state( [
            'id_categoria' => $id_categoria,
        ]);
    }
    public function asignarMenu($id_menu){
        return $this->state( [
            'id_menu' => $id_menu,
        ]);
    }
    public function definition(): array
    {
        //30 nombres de platillos bolivianos diferentes
        $descripciones = [
            'Pique Macho' => 'Carne y papas fritas con salchicha y huevo.',
            'Silpancho' => 'Carne empanizada con arroz, papas y huevo.',
            'Salteña' => 'Empanada rellena de carne, papa y especias.',
            'Fricase' => 'Cerdo cocido en salsa de ají y especias.',
            'Sopa de Mani' => 'Sopa de maní con carne y vegetales.',
            'Mondongo' => 'Cerdo con maíz y ají rojo.',
            'Chicharron' => 'Carne de cerdo frita, servida con mote.',
            'Chairo' => 'Sopa de carne con chuño y verduras.',
            'Sajta' => 'Pollo en salsa de ají amarillo.',
            'Falso Conejo' => 'Carne empanizada en salsa de ají.',
            'Picante de Pollo' => 'Pollo en salsa picante de ají rojo.',
            'Picante Mixto' => 'Pollo y carne en salsa de ají rojo.',
            'Picante de Res' => 'Carne de res en salsa de ají rojo.',
            'Kalapurca' => 'Sopa espesa de maíz y carne.',
            'Lomito' => 'Filete de res a la parrilla.',
            'Chuleta de Cerdo' => 'Chuleta de cerdo a la parrilla.',
            'Chuleta de Pollo' => 'Chuleta de pollo a la parrilla.',
            'Salpicon' => 'Ensalada de carne desmenuzada con vegetales.',
            'Charque' => 'Carne seca desmenuzada con mote.',
            'Huminta' => 'Pastel de maíz tierno y queso.',
            'Ranga Ranga' => 'Sopa picante de callos.',
            'Anticucho' => 'Brochetas de corazón de res.',
            'Majadito' => 'Arroz con charque y huevo.',
            'Api con pastel' => 'Bebida de maíz morado con pastel frito.',
            'Tamales' => 'Masa de maíz rellena con carne.',
            'Empanadas de queso' => 'Empanadas fritas rellenas de queso.',
            'Llajwa' => 'Salsa picante de tomate y ají.',
            'Camba' => 'Carne de cerdo con yuca y plátano.',
            'Masaco de plátano' => 'Plátano frito con charque.',
            'Locro' => 'Sopa de arroz con carne y papas.',
            'Trancapecho' => 'Sandwich con carne, papas, huevo y ensalada.'
        ];
        
        $nombre = $this->faker->unique()->randomElement(array_keys($descripciones));
        $categorias = [1,2,3,4,5,6];
        return [
            'nombre' => $nombre,
            'descripcion' => $descripciones[$nombre],
            'precio' => $this->faker->numberBetween(10, 120),
            'imagen' => $this->faker->imageUrl(),
            'id_categoria' => $this->faker->randomElement($categorias),
        ];
    }
}

