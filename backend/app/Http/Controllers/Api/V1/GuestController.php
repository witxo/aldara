<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Guest\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::where('tenant_id', tenant_id());

        if ($request->has('reservation_id')) {
            $query->where('reservation_id', $request->reservation_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        $guests = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $guests->items(),
            'message' => 'Listado de huéspedes',
            'status' => 200,
            'meta' => [
                'current_page' => $guests->currentPage(),
                'per_page' => $guests->perPage(),
                'total' => $guests->total(),
                'last_page' => $guests->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'document_type' => 'required|in:dni,nie,passport,other',
            'document_number' => 'required|string|max:50',
            'document_support' => 'nullable|string|max:20',
            'nationality' => 'required|string|size:2',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,unspecified',
            'is_main_guest' => 'nullable|boolean',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['tenant_id'] = tenant_id();
        $guest = Guest::create($validated);

        return response()->json([
            'data' => $guest,
            'message' => 'Huésped añadido',
            'status' => 201,
        ], 201);
    }

    public function show(Guest $guest)
    {
        $this->authorize('view', $guest);
        $guest->load(['reservation.property', 'documents']);

        return response()->json([
            'data' => $guest,
            'message' => 'Detalle del huésped',
            'status' => 200,
        ]);
    }

    public function update(Request $request, Guest $guest)
    {
        $this->authorize('update', $guest);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'document_type' => 'sometimes|in:dni,nie,passport,other',
            'document_number' => 'sometimes|string|max:50',
            'document_support' => 'nullable|string|max:20',
            'nationality' => 'sometimes|string|size:2',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,unspecified',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
        ]);

        $guest->update($validated);

        return response()->json([
            'data' => $guest,
            'message' => 'Huésped actualizado',
            'status' => 200,
        ]);
    }

    public function destroy(Guest $guest)
    {
        $this->authorize('delete', $guest);
        $guest->delete();

        return response()->json([
            'data' => null,
            'message' => 'Huésped eliminado',
            'status' => 200,
        ]);
    }
}
