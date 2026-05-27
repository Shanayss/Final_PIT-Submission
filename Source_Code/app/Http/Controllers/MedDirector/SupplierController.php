<?php

namespace App\Http\Controllers\MedDirector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of the registered suppliers.
     */
    public function index()
    {
        try {
            $suppliersFromDb = DB::table('suppliers')->orderBy('supplier_name', 'asc')->get();
        } catch (\Exception $e) {
            $suppliersFromDb = collect([]);
        }

        $suppliersJsonConfig = $suppliersFromDb->map(function ($sup) {
            return [
                'id' => $sup->supplier_id,
                'name' => $sup->supplier_name ?? 'Unknown Supplier',
                'type' => strtolower($sup->supplier_type ?? 'pharmaceutical'),
                'address' => $sup->address ?? 'No Address Listed',
                'city' => $sup->city ?? 'Local City, PH',
                'tel' => $sup->telephone ?? '—',
                'fax' => $sup->fax ?? '—'
            ];
        })->toJson();

        // FIXED: Changed target string definition layer cleanly to match your singular view filename
        return view('MedDirector.suppliers', compact('suppliersJsonConfig', 'suppliersFromDb'));
    }

    /**
     * Store a newly created resource entity back in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'address'       => 'required|string',
            'telephone'     => 'required|string',
            'fax'           => 'nullable|string',
            'supplier_type' => 'required|string'
        ]);

        DB::table('suppliers')->insert([
            'supplier_name' => $validated['supplier_name'],
            'address'       => $validated['address'],
            'telephone'     => $validated['telephone'],
            'fax'           => $validated['fax'],
            'supplier_type' => $validated['supplier_type'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('meddirector.suppliers.index');
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, string $supplier_id)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'address'       => 'required|string',
            'telephone'     => 'required|string',
            'fax'           => 'nullable|string',
            'supplier_type' => 'required|string',
        ]);

        // Support a "delete during update" workflow from the UI.
        // If request includes delete=1, perform delete instead of update.
        if ($request->boolean('delete')) {
            DB::table('suppliers')->where('supplier_id', $supplier_id)->delete();
            return redirect()->route('meddirector.suppliers.index');
        }

        DB::table('suppliers')->where('supplier_id', $supplier_id)->update([
            'supplier_name' => $validated['supplier_name'],
            'address'       => $validated['address'],
            'telephone'     => $validated['telephone'],
            'fax'           => $validated['fax'],
            'supplier_type' => $validated['supplier_type'],
            'updated_at'    => now(),
        ]);

        return redirect()->route('meddirector.suppliers.index');

    }

    /**
     * Delete supplier (optional to complete CRUD)
     */
    public function destroy(string $supplier_id)
    {
        DB::table('suppliers')->where('supplier_id', $supplier_id)->delete();
        return redirect()->route('meddirector.suppliers.index');
    }
}
