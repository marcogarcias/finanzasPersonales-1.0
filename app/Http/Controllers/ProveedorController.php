<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Muestra la vista principal de proveedores.
     */
    public function index()
    {
        $userId = auth()->id();
        $rfcs = \App\Models\Rfc::where('user_id', $userId)->orderBy('rfc', 'asc')->get();
        return view('proveedores.index', compact('rfcs'));
    }

    /**
     * API: Obtiene el listado de todos los proveedores.
     */
    public function getProveedores(Request $request)
    {
        $userId = auth()->id();
        $receptorRfc = $request->query('rfc');

        $query = Proveedor::where('user_id', $userId);

        if ($receptorRfc && $receptorRfc !== 'all') {
            $query->where('rfc_receptor', $receptorRfc);
        }

        $proveedores = $query->orderBy('nombre', 'asc')->get();
        return response()->json($proveedores);
    }

    /**
     * API: Obtener conceptos únicos de comprobantes para un RFC emisor específico.
     */
    public function getConceptos(Request $request)
    {
        $rfcEmisor = $request->query('rfc');
        $rfcReceptor = $request->query('rfc_receptor'); // Nuevo parámetro opcional
        
        if (!$rfcEmisor) return response()->json([]);

        $query = \App\Models\Comprobante::where('user_id', auth()->id())
            ->where('rfc_emisor', $rfcEmisor);
            
        if ($rfcReceptor) {
            $query->where('rfc_receptor', $rfcReceptor);
        }

        $conceptosRaw = $query->pluck('conceptos');
        
        $conceptos = [];
        foreach ($conceptosRaw as $raw) {
            if (empty($raw)) continue;
            
            // Separar por pipe si existe
            $parts = explode('|', $raw);
            foreach ($parts as $p) {
                $trimmed = trim($p);
                if (!empty($trimmed)) {
                    $conceptos[] = $trimmed;
                }
            }
        }

        return response()->json(array_values(array_unique($conceptos)));
    }

    /**
     * API: Actualizar un proveedor.
     */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::where('user_id', auth()->id())->findOrFail($id);
        
        $data = $request->validate([
            'nombre'         => 'required|string|max:255',
            'tipo_de_uso'    => 'nullable|string',
            'efecto_fiscal'  => 'nullable|string',
            'momento_fiscal' => 'nullable|string',
            'categoria'      => 'nullable|string',
            'concepto'       => 'nullable|string',
        ]);

        $proveedor->update($data);

        return response()->json(['success' => true, 'message' => 'Proveedor actualizado correctamente']);
    }

    /**
     * API: Eliminar un proveedor.
     */
    public function destroy($id)
    {
        $proveedor = Proveedor::where('user_id', auth()->id())->findOrFail($id);
        $proveedor->delete();

        return response()->json(['success' => true, 'message' => 'Proveedor eliminado correctamente']);
    }
}
