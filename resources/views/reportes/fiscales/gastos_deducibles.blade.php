@extends('layouts.app')

@section('content')
<div x-data="{ sidebarOpen: true }" class="w-full h-full flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <h1 class="text-2xl font-bold text-gray-800">Reporte: Gastos Deducibles</h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 flex-1 overflow-hidden relative">
        
        <!-- Botón para reabrir sidebar cuando está cerrado (flotante a la izquierda) -->
        <button 
            x-show="!sidebarOpen" 
            @click="sidebarOpen = true"
            class="absolute left-0 top-4 z-20 bg-blue-600 text-white p-2 rounded-r-lg shadow-lg hover:bg-blue-700 transition-all border border-blue-500 border-l-0"
            style="display: none;"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>

        <!-- Sidebar / Filtros -->
        <aside 
            :class="sidebarOpen ? 'w-full lg:w-1/3 opacity-100' : 'w-0 opacity-0 overflow-hidden lg:mr-[-1.5rem] pointer-events-none'"
            class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col h-full overflow-y-auto transition-all duration-300 ease-in-out relative origin-left"
        >
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-lg font-semibold text-gray-700">Filtros</h2>
                <!-- Icono para contraer -->
                <button @click="sidebarOpen = false" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Contraer filtros">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                </button>
            </div>
            
            <form id="filtrosForm" class="space-y-5 flex-1">
                <div class="p-4 bg-blue-50/50 rounded-lg border border-blue-100">
                    <!-- 1. Contribuyentes (RFC) -->
                    <div class="mt-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Contribuyentes (RFC)</label>
                        <select id="rfcSelect" class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white shadow-sm transition">
                            <option value="">Cargando RFCs...</option>
                        </select>
                    </div>

                    <!-- 2. Año Fiscal -->
                    <div class="mt-3">
                        <label class="block text-xs font-medium mb-1 text-gray-700 font-bold uppercase tracking-wider">Año Fiscal</label>
                        <select id="yearSelect" disabled class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white disabled:bg-gray-100 shadow-sm transition">
                            <option value="">Seleccione Año...</option>
                        </select>
                    </div>

                    <!-- 3. Meses Checkboxes -->
                    <div id="monthsContainer" class="hidden mt-3">
                        <label class="block text-xs font-medium mb-2 text-gray-700 font-bold uppercase tracking-wider">Meses</label>
                        <div class="grid grid-cols-2 gap-2 bg-gray-50 p-3 rounded-md border border-gray-200 max-h-40 overflow-y-auto" id="monthsList">
                            <!-- Checkboxes via JS -->
                        </div>
                    </div>

                    <!-- 4. Efecto Fiscal -->
                    <div class="mt-3">
                        <label class="block text-xs font-medium mb-1 text-gray-700 font-bold uppercase tracking-wider">Efecto fiscal</label>
                        <select id="efectoSelect" class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white shadow-sm transition">
                            <option value="Deducible">Deducible</option>
                            <option value="No deducible">No deducible</option>
                        </select>
                    </div>

                    <!-- 5. Tipo de Uso -->
                    <div class="mt-3">
                        <label class="block text-xs font-medium mb-1 text-gray-700 font-bold uppercase tracking-wider">Tipo de uso</label>
                        <select id="usoSelect" class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white shadow-sm transition">
                            <option value="A. Empresarial">A. Empresarial</option>
                            <option value="Deducciones Personales">Deducciones Personales</option>
                            <option value="Sin uso">Sin uso</option>
                        </select>
                    </div>
                </div>

                <!-- Botones -->
                <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col gap-2">
                    <button type="button" id="btnFiltrar"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg font-bold text-sm hover:bg-blue-700 transition shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        FILTRAR
                    </button>
                    <button type="button" id="btnLimpiar"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg font-bold text-xs hover:bg-gray-50 transition">
                        LIMPIAR
                    </button>
                </div>
            </form>
        </aside>

        <!-- Main Content -->
         <section :class="sidebarOpen ? '' : 'pl-8'" class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full transition-all duration-300">
            <div id="headerResultados" class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center shrink-0 min-h-[90px]">
                <div id="infoCliente" class="hidden">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest leading-none mb-1">RFC Seleccionado</span>
                        <span id="displayRfc" class="text-xl font-extrabold text-gray-900 bg-blue-100 px-3 py-1 rounded inline-block w-fit"></span>
                    </div>
                </div>
                <div id="placeholderHeader" class="text-sm font-bold text-gray-400 uppercase tracking-wide">
                    Resultados del Reporte
                </div>
            </div>

            <div class="overflow-auto flex-1 p-6 relative" id="resultadosContenedor">
                <!-- Placeholder Inicial -->
                <div id="placeholderBody" class="flex flex-col items-center justify-center h-full text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 italic text-sm">Configure los filtros para visualizar la información.</p>
                </div>

                <!-- Área donde se inyectarán las tablas -->
                <div id="listaResultados" class="space-y-12"></div>
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
    const rfcSelect = document.getElementById('rfcSelect');
    const yearSelect = document.getElementById('yearSelect');
    const effectSelect = document.getElementById('efectoSelect');
    const usageSelect = document.getElementById('usoSelect');
    const monthsContainer = document.getElementById('monthsContainer');
    const monthsList = document.getElementById('monthsList');
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const listaResultados = document.getElementById('listaResultados');
    const placeholderBody = document.getElementById('placeholderBody');

    // UI Result Elements
    const infoCliente = document.getElementById('infoCliente');
    const placeholderHeader = document.getElementById('placeholderHeader');
    const displayRfc = document.getElementById('displayRfc');

    // Inicializar carga de RFCs
    loadRfcs();

    rfcSelect.addEventListener('change', () => {
        const rfcValue = rfcSelect.value;
        yearSelect.innerHTML = '<option value="">Seleccione Año...</option>';
        yearSelect.disabled = true;
        monthsContainer.classList.add('hidden');
        monthsList.innerHTML = '';

        if(rfcValue) {
            loadYears(rfcValue);
            displayRfc.textContent = rfcValue;
            infoCliente.classList.remove('hidden');
            placeholderHeader.classList.add('hidden');
        } else {
            infoCliente.classList.add('hidden');
            placeholderHeader.classList.remove('hidden');
        }
    });

    yearSelect.addEventListener('change', () => {
        const rfc = rfcSelect.value;
        const year = yearSelect.value;
        monthsContainer.classList.add('hidden');
        monthsList.innerHTML = '';

        if(rfc && year) loadMonths(rfc, year);
    });

    btnLimpiar.addEventListener('click', () => {
        rfcSelect.value = '';
        yearSelect.innerHTML = '<option value="">Seleccione Año...</option>';
        yearSelect.disabled = true;
        monthsContainer.classList.add('hidden');
        monthsList.innerHTML = '';
        effectSelect.value = 'Deducible';
        usageSelect.value = 'A. Empresarial';
        infoCliente.classList.add('hidden');
        placeholderHeader.classList.remove('hidden');
        listaResultados.innerHTML = '';
        placeholderBody.classList.remove('hidden');
    });

    btnFiltrar.addEventListener('click', async () => {
        const rfc = rfcSelect.value;
        const year = yearSelect.value;
        const checkedMonths = Array.from(document.querySelectorAll('input[name="months[]"]:checked')).map(cb => cb.value);

        if(!rfc || !year || checkedMonths.length === 0) {
            alert('Por favor selecciona RFC, Año y al menos un mes.');
            return;
        }

        btnFiltrar.disabled = true;
        btnFiltrar.innerHTML = 'PROCESANDO...';

        try {
            const query = new URLSearchParams({
                level: 'data',
                rfc: rfc,
                year: year,
                efecto: effectSelect.value,
                uso: usageSelect.value
            });
            checkedMonths.forEach(m => query.append('months[]', m));

            const res = await fetch(`{{ route('api.reportes.filters') }}?${query.toString()}`);
            const data = await res.json();

            renderReport(data);
        } catch (e) {
            console.error('Error al generar reporte:', e);
            alert('Error al generar el reporte.');
        } finally {
            btnFiltrar.disabled = false;
            btnFiltrar.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg> FILTRAR';
        }
    });

    function renderReport(data) {
        listaResultados.innerHTML = '';
        placeholderBody.classList.add('hidden');

        if(data.length === 0) {
            listaResultados.innerHTML = '<div class="text-center py-10 text-gray-500">No se encontraron datos para los filtros seleccionados.</div>';
            return;
        }

        data.forEach(mes => {
            const totalBase0 = mes.proveedores.reduce((acc, p) => acc + p.base_iva_0, 0);
            const totalBase16 = mes.proveedores.reduce((acc, p) => acc + p.base_iva_16, 0);
            const totalIva16 = mes.proveedores.reduce((acc, p) => acc + p.suma_iva_16, 0);
            const totalRetIva = mes.proveedores.reduce((acc, p) => acc + p.suma_ret_iva, 0);
            const totalRetIsr = mes.proveedores.reduce((acc, p) => acc + p.suma_ret_isr, 0);
            const totalGral = mes.proveedores.reduce((acc, p) => acc + p.total, 0);

            const tableHtml = `
                <div x-data="{ open: true }" class="mb-8 overflow-hidden rounded-xl border border-gray-100 shadow-sm bg-white">
                    <!-- Header / Toggle -->
                    <div @click="open = !open" class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center cursor-pointer hover:bg-gray-100 transition-colors group">
                        <h3 class="text-sm font-extrabold text-blue-800 uppercase tracking-widest flex items-center gap-3">
                            <span class="p-1.5 bg-blue-100 text-blue-600 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </span>
                            ${mes.periodo}
                        </h3>
                        
                        <div class="flex items-center gap-4">
                            <span x-show="!open" class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                                $${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(totalGral)}
                            </span>
                            <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300 group-hover:text-blue-500" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <div x-show="open" x-collapse x-cloak>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white border-b border-gray-50">
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">RFC Emisor</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nombre Emisor</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipo Erogación</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right whitespace-nowrap">Base IVA 0%</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right whitespace-nowrap">Base IVA 16%</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right whitespace-nowrap">IVA 16%</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right whitespace-nowrap">Ret. IVA</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right whitespace-nowrap">Ret. ISR</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right whitespace-nowrap">Suma Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    ${mes.proveedores.map(p => `
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4 text-xs font-bold text-blue-600">${p.rfc_emisor}</td>
                                            <td class="px-6 py-4 text-xs text-gray-700">${p.nombre_emisor}</td>
                                            <td class="px-6 py-4 text-xs italic text-gray-500">${p.tipo_erogacion}</td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-700 text-right whitespace-nowrap">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(p.base_iva_0)}</td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-700 text-right whitespace-nowrap">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(p.base_iva_16)}</td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-700 text-right whitespace-nowrap">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(p.suma_iva_16)}</td>
                                            <td class="px-6 py-4 text-xs font-bold text-red-600 text-right whitespace-nowrap">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(p.suma_ret_iva)}</td>
                                            <td class="px-6 py-4 text-xs font-bold text-red-600 text-right whitespace-nowrap">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(p.suma_ret_isr)}</td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-900 text-right whitespace-nowrap">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(p.total)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        <div class="bg-gray-50/50 px-6 py-4 border-t border-gray-100 flex flex-wrap justify-end gap-x-8 gap-y-2">
                             <div class="flex flex-col items-end">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Base 0%</span>
                                <span class="text-xs font-extrabold text-gray-700">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(totalBase0)}</span>
                             </div>
                             <div class="flex flex-col items-end">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Base 16%</span>
                                <span class="text-xs font-extrabold text-gray-700">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(totalBase16)}</span>
                             </div>
                             <div class="flex flex-col items-end">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">IVA 16%</span>
                                <span class="text-xs font-extrabold text-gray-700">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(totalIva16)}</span>
                             </div>
                             <div class="flex flex-col items-end">
                                <span class="text-[9px] font-bold text-red-400 uppercase tracking-tighter">Ret. IVA</span>
                                <span class="text-xs font-extrabold text-red-700">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(totalRetIva)}</span>
                             </div>
                             <div class="flex flex-col items-end">
                                <span class="text-[9px] font-bold text-red-400 uppercase tracking-tighter">Ret. ISR</span>
                                <span class="text-xs font-extrabold text-red-700">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(totalRetIsr)}</span>
                             </div>
                             <div class="flex flex-col items-end border-l pl-8 border-gray-200">
                                <span class="text-[9px] font-bold text-blue-600 uppercase tracking-tighter">Total Mes</span>
                                <span class="text-sm font-black text-blue-900">$${new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2}).format(totalGral)}</span>
                             </div>
                        </div>
                    </div>
                </div>
            `;
            listaResultados.insertAdjacentHTML('beforeend', tableHtml);
        });
    }

    async function loadRfcs() {
        try {
            const res = await fetch(`{{ route('api.reportes.filters') }}?level=rfc`);
            const rfcs = await res.json();
            
            rfcSelect.innerHTML = '<option value="">Seleccione RFC...</option>';
            rfcs.forEach(c => {
                rfcSelect.innerHTML += `<option value="${c.rfc}">${c.rfc}</option>`;
            });
        } catch (e) {
            console.error('Error cargando RFCs', e);
        }
    }

    async function loadYears(rfc) {
        try {
            const res = await fetch(`{{ route('api.reportes.filters') }}?level=year&rfc=${rfc}`);
            const years = await res.json();
            
            yearSelect.innerHTML = '<option value="">Seleccione Año...</option>';
            years.forEach(year => {
                yearSelect.innerHTML += `<option value="${year}">${year}</option>`;
            });
            yearSelect.disabled = false;
        } catch (e) {
            console.error('Error cargando años', e);
        }
    }

    async function loadMonths(rfc, year) {
        try {
            const res = await fetch(`{{ route('api.reportes.filters') }}?level=month&rfc=${rfc}&year=${year}`);
            const months = await res.json();
            
            const monthNames = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
            monthsList.innerHTML = '';
            
            months.forEach(m => {
                const html = `
                    <label class="flex items-center space-x-2 bg-white p-2 border rounded cursor-pointer hover:bg-blue-50 transition shadow-sm">
                        <input type="checkbox" name="months[]" value="${m}" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                        <span class="text-[10px] font-bold text-gray-700">${monthNames[m]}</span>
                    </label>
                `;
                monthsList.insertAdjacentHTML('beforeend', html);
            });
            
            monthsContainer.classList.remove('hidden');
        } catch (e) {
            console.error('Error cargando meses', e);
        }
    }
</script>
@endpush
@endsection
