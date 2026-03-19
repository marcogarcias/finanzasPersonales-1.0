@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto" x-data="proveedoresHandler()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Catálogo de Proveedores</h1>
            <p class="mt-1 text-sm text-gray-500">Gestiona y consulta la información técnica de tus emisores.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center bg-white border border-gray-300 rounded-xl px-3 py-1.5 shadow-sm focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-1 transition">
                <label for="rfc-selector" class="text-[10px] uppercase font-bold text-gray-400 mr-2">Elegir RFC:</label>
                <select id="rfc-selector" 
                        x-model="selectedRfc" 
                        @change="loadProveedores"
                        class="bg-transparent border-none p-0 text-sm font-bold text-gray-700 focus:ring-0 cursor-pointer">
                    <option value="">-- Selecciona un RFC --</option>
                    @foreach($rfcs as $r)
                        <option value="{{ $r->rfc }}">{{ $r->rfc }}</option>
                    @endforeach
                    <option value="all">Ver todos mis proveedores</option>
                </select>
            </div>

            <button @click="loadProveedores" 
                    :disabled="!selectedRfc"
                    :class="!selectedRfc ? 'opacity-50 cursor-not-allowed' : ''"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Actualizar
            </button>
        </div>
    </div>

    <!-- Stats / Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" x-show="selectedRfc" x-cloak x-transition>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-blue-50 rounded-xl">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Proveedores</p>
                <h3 class="text-2xl font-bold text-gray-900" x-text="proveedores.length">0</h3>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-show="selectedRfc" x-cloak x-transition>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">RFC / Nombre</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tipo de Uso</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Efecto / Momento</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Concepto</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-sm text-gray-500 font-medium">Cargando proveedores...</span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <template x-if="!loading && proveedores.length === 0">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-lg font-medium">No se encontraron proveedores</p>
                                <p class="text-sm">No hay proveedores registrados para este RFC o no se han realizado descargas.</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="prov in proveedores" :key="prov.id">
                        <tr class="hover:bg-blue-50/30 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-blue-600 tracking-tight" x-text="prov.rfc"></span>
                                    <span class="text-xs font-medium text-gray-600 truncate max-w-[200px]" x-text="prov.nombre"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold border" 
                                      :class="prov.tipo_de_uso ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-gray-50 text-gray-400 border-gray-100'"
                                      x-text="prov.tipo_de_uso || 'No definido'">
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        <span class="text-xs font-semibold text-gray-700" x-text="prov.efecto_fiscal || 'N/A'"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-tighter" x-text="prov.momento_fiscal || 'N/A'"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-gray-700 italic" x-text="prov.categoria || 'Sin categoría'"></span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 line-clamp-2 max-w-xs" x-text="prov.concepto || '--'"></p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(prov)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button @click="confirmDelete(prov)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Editar -->
    <div x-show="showEditModal" 
         class="fixed inset-0 z-[60] overflow-y-auto" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showEditModal = false"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Editar Proveedor</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="updateProveedor">
                    <div class="px-6 py-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">RFC</label>
                                <input type="text" x-model="editingProv.rfc" readonly class="w-full bg-gray-50 border-gray-200 rounded-xl text-sm font-bold text-gray-500 focus:ring-0 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nombre</label>
                                <input type="text" x-model="editingProv.nombre" class="w-full border-gray-200 rounded-xl text-sm font-medium focus:border-blue-500 focus:ring-blue-500 transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tipo de uso</label>
                                <select x-model="editingProv.tipo_de_uso" class="w-full border-gray-200 rounded-xl text-sm font-medium focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Elige una opción</option>
                                    <option value="A. Empresarial">A. Empresarial</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Efecto fiscal</label>
                                <select x-model="editingProv.efecto_fiscal" class="w-full border-gray-200 rounded-xl text-sm font-medium focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Elige una opción</option>
                                    <option value="Deducible">Deducible</option>
                                    <option value="No deducible">No deducible</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Momento fiscal</label>
                                <select x-model="editingProv.momento_fiscal" class="w-full border-gray-200 rounded-xl text-sm font-medium focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Elige una opción</option>
                                    <option value="Mensual">Mensual</option>
                                    <option value="Bimestral">Bimestral</option>
                                    <option value="Trimestral">Trimestral</option>
                                    <option value="Anual">Anual</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Categoría</label>
                                <select x-model="editingProv.categoria" class="w-full border-gray-200 rounded-xl text-sm font-medium focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Elige una opción</option>
                                    <option value="Alimentos">Alimentos</option>
                                    <option value="Papelería">Papelería</option>
                                    <option value="Gastos de oficina">Gastos de oficina</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Concepto</label>
                            <div class="relative">
                                <select x-model="editingProv.concepto" 
                                        :disabled="conceptosLoading"
                                        class="w-full border-gray-200 rounded-xl text-sm font-medium focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-50">
                                    <option value="">Elige una opción</option>
                                    <template x-for="c in availableConceptos" :key="c">
                                        <option :value="c" x-text="c" :selected="c === editingProv.concepto"></option>
                                    </template>
                                </select>
                                <div x-show="conceptosLoading" class="absolute right-8 top-1/2 -translate-y-1/2">
                                    <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-400">Los conceptos se cargan automáticamente desde tus facturas históricas.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition">Cancelar</button>
                        <button type="submit" 
                                :disabled="updating"
                                class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition duration-150">
                            <span x-show="!updating">Guardar Cambios</span>
                            <span x-show="updating" class="flex items-center gap-2">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Empty State / Placeholder -->
    <div x-show="!selectedRfc" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 py-20 text-center">
        <div class="max-w-md mx-auto">
            <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 text-blue-500 shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Consulta de Proveedores</h2>
            <p class="text-gray-500 mb-8">Por favor, selecciona uno de tus RFCs registrados para listar los proveedores que te han emitido facturas.</p>
            <div class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-600">
                <span class="animate-bounce">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </span>
                <span>Usa el selector de arriba para comenzar</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function proveedoresHandler() {
        return {
            proveedores: [],
            loading: false,
            selectedRfc: '',
            
            // Modal editar
            showEditModal: false,
            editingProv: {},
            availableConceptos: [],
            conceptosLoading: false,
            updating: false,
            
            init() {
                
            },

            async loadProveedores() {
                if (!this.selectedRfc) {
                    this.proveedores = [];
                    return;
                }

                this.loading = true;
                try {
                    const url = new URL('{{ route("api.proveedores.index") }}', window.location.origin);
                    if (this.selectedRfc && this.selectedRfc !== 'all') {
                        url.searchParams.append('rfc', this.selectedRfc);
                    }
                    
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Error al cargar proveedores');
                    this.proveedores = await response.json();
                } catch (error) {
                    console.error('Error:', error);
                    alert('No se pudo cargar la lista de proveedores.');
                } finally {
                    this.loading = false;
                }
            },

            async openEditModal(prov) {
                // Copia profunda para no afectar la tabla hasta guardar
                this.editingProv = JSON.parse(JSON.stringify(prov));
                this.showEditModal = true;
                this.availableConceptos = [];
                this.conceptosLoading = true;

                try {
                    // Petición AJAX para obtener conceptos del back
                    const response = await fetch(`/api/proveedores/conceptos?rfc=${prov.rfc}`);
                    if (!response.ok) throw new Error('Error al obtener conceptos');
                    this.availableConceptos = await response.json();
                } catch (error) {
                    console.error('Error fetching concepts:', error);
                } finally {
                    this.conceptosLoading = false;
                }
            },

            async updateProveedor() {
                this.updating = true;
                try {
                    const response = await fetch(`/api/proveedores/${this.editingProv.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.editingProv)
                    });

                    if (!response.ok) throw new Error('Error al actualizar');
                    
                    const result = await response.json();
                    if (result.success) {
                        this.showEditModal = false;
                        await this.loadProveedores(); // Recargar tabla
                        alert('Proveedor actualizado con éxito');
                    }
                } catch (error) {
                    console.error('Error updating:', error);
                    alert('Error al actualizar el proveedor.');
                } finally {
                    this.updating = false;
                }
            },

            async confirmDelete(prov) {
                if (!confirm(`¿Estás seguro de que deseas eliminar al proveedor ${prov.nombre}?`)) {
                    return;
                }

                try {
                    const response = await fetch(`/api/proveedores/${prov.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (!response.ok) throw new Error('Error al eliminar');
                    
                    await this.loadProveedores();
                    alert('Proveedor eliminado correctamente');
                } catch (error) {
                    console.error('Error deleting:', error);
                    alert('Ocurrió un error al intentar eliminar el proveedor.');
                }
            }
        };
    }
</script>
@endpush
@endsection
