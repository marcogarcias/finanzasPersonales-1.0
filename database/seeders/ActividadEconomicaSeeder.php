<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadEconomicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actividades = [
            [
                'actividad' => 'Alimentos y bebidas',
                'categoria' => 'Operación diaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Papelería y artículos de oficina',
                'categoria' => 'Operación diaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Gastos de oficina generales',
                'categoria' => 'Operación diaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Limpieza y mantenimiento',
                'categoria' => 'Operación diaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Seguridad (alarmas, vigilancia, extinguidores)',
                'categoria' => 'Operación diaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Servicios de mensajería y paquetería',
                'categoria' => 'Operación diaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Consultoría y asesoría',
                'categoria' => 'Servicios profesionales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Servicios legales',
                'categoria' => 'Servicios profesionales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Servicios contables y fiscales',
                'categoria' => 'Servicios profesionales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Capacitación y cursos',
                'categoria' => 'Servicios profesionales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Publicidad y marketing',
                'categoria' => 'Servicios profesionales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Software y licencias',
                'categoria' => 'Tecnología',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Hardware y equipos de cómputo',
                'categoria' => 'Tecnología',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Servicios de internet y telecomunicaciones',
                'categoria' => 'Tecnología',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Mantenimiento de sistemas y soporte técnico',
                'categoria' => 'Tecnología',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Renta de oficinas/locales',
                'categoria' => 'Infraestructura',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Servicios públicos (agua, luz, gas)',
                'categoria' => 'Infraestructura',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Mantenimiento de instalaciones',
                'categoria' => 'Infraestructura',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Construcción y remodelación',
                'categoria' => 'Infraestructura',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Combustibles',
                'categoria' => 'Transporte y logística',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Refacciones y mantenimiento vehicular',
                'categoria' => 'Transporte y logística',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Transporte de personal',
                'categoria' => 'Transporte y logística',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Fletes y logística',
                'categoria' => 'Transporte y logística',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Servicios de reclutamiento',
                'categoria' => 'Recursos humanos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Prestaciones y seguros',
                'categoria' => 'Recursos humanos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Uniformes y equipo de protección personal (EPP)',
                'categoria' => 'Recursos humanos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Gastos financieros (bancos, comisiones)',
                'categoria' => 'Otros gastos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Seguros generales (vehículos, inmuebles, responsabilidad civil)',
                'categoria' => 'Otros gastos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Eventos y representación',
                'categoria' => 'Otros gastos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad' => 'Donativos y responsabilidad social',
                'categoria' => 'Otros gastos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('actividades_economicas')->insert($actividades);
    }
}
