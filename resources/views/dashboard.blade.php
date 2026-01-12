@extends('layouts.app')

@section('content')
<div class="w-full">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Financiero</h1>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Total Ingresos (Mes)</h3>
                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">+12.5%</span>
            </div>
            <p class="text-3xl font-bold text-gray-800">$125,450.00</p>
            <p class="text-xs text-gray-400 mt-2">Vs $110,000.00 mes anterior</p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Gastos Totales</h3>
                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">+2.1%</span>
            </div>
            <p class="text-3xl font-bold text-gray-800">$45,230.50</p>
            <p class="text-xs text-gray-400 mt-2">Principalmente Servicios</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Facturas Descargadas</h3>
                <div class="p-1 bg-blue-100 rounded">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-800">1,240</p>
            <p class="text-xs text-gray-400 mt-2">Última sinc: Hoy 14:00</p>
        </div>
    </div>

    <!-- Empty State / Placeholder Chart -->
    <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100 flex flex-col items-center justify-center min-h-[300px]">
        <div class="bg-gray-50 p-4 rounded-full mb-4">
            <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900">Actividad Reciente</h3>
        <p class="text-gray-500 text-sm mt-1">Aquí se mostrará la gráfica de ingresos vs egresos cuando importes tus XML.</p>
        <a href="{{ route('download.index') }}" class="mt-4 text-blue-600 font-semibold hover:text-blue-800 text-sm">Ir a Descargas &rarr;</a>
    </div>
</div>
@endsection
