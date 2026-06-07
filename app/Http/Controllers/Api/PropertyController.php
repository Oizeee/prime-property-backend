<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Models\AuditLog;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Property listing CRUD API for Prime Property.
 */
class PropertyController extends Controller
{
  private const ALLOWED_PER_PAGE = [25, 50, 100];

  private const ALLOWED_SORT_COLUMNS = [
    'nama_property',
    'price',
    'created_at',
    'status',
  ];

  /**
   * Paginated property index with search, filters, and sorting.
   */
  public function index(Request $request): JsonResponse
  {
    $perPage = (int) $request->input('per_page', 50);
    if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
      $perPage = 50;
    }

    $sortBy = $request->input('sort_by', 'created_at');
    if (! in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true)) {
      $sortBy = 'created_at';
    }

    $sortDir = strtolower((string) $request->input('sort_dir', 'desc'));
    $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

    $query = Property::query();

  // Soft-deleted rows excluded by default via SoftDeletes global scope.

    if ($search = $request->input('search')) {
      $searchTerm = '%'.addcslashes($search, '%_\\').'%';
      $query->where(function ($builder) use ($searchTerm) {
        $builder->where('nama_property', 'ilike', $searchTerm)
          ->orWhere('group', 'ilike', $searchTerm)
          ->orWhereRaw('kawasan::text ILIKE ?', [$searchTerm]);
      });
    }

    if ($kawasan = $request->input('kawasan')) {
      $tags = is_array($kawasan) ? $kawasan : explode(',', (string) $kawasan);
      $query->where(function ($builder) use ($tags) {
        foreach ($tags as $tag) {
          $builder->orWhereJsonContains('kawasan', trim((string) $tag));
        }
      });
    }

    if ($request->filled('lebar_min')) {
      $query->where('lebar', '>=', (float) $request->input('lebar_min'));
    }

    if ($hadap = $request->input('hadap')) {
      $directions = is_array($hadap) ? $hadap : explode(',', (string) $hadap);
      $query->where(function ($builder) use ($directions) {
        foreach ($directions as $direction) {
          $builder->orWhereJsonContains('hadap', trim((string) $direction));
        }
      });
    }

    if ($request->filled('price_max')) {
      $query->where('price', '<=', (int) $request->input('price_max'));
    }

    if ($request->filled('tipe')) {
      $query->where('tipe', $request->input('tipe'));
    }

    if ($request->filled('status')) {
      $query->where('status', $request->input('status'));
    }

    if ($siap = $request->input('siap')) {
      $siapValues = is_array($siap) ? $siap : explode(',', (string) $siap);
      $query->whereIn('siap', array_map('trim', $siapValues));
    }

    if ($request->has('carport')) {
      $query->where('carport', filter_var($request->input('carport'), FILTER_VALIDATE_BOOLEAN));
    }

    $query->orderBy($sortBy, $sortDir);

    $properties = $query->paginate($perPage)->withQueryString();

    return response()->json($properties);
  }

  /**
   * Create a new property listing (superadmin only).
   */
  public function store(PropertyRequest $request): JsonResponse
  {
    $data = $request->validated();
    $data['created_by'] = (string) $request->user()->id;
    $data['carport'] = $data['carport'] ?? false;
    $data['status'] = $data['status'] ?? 'in stock';

    $property = Property::create($data);

    return response()->json([
      'message' => 'Property berhasil dibuat.',
      'data' => $property,
    ], Response::HTTP_CREATED);
  }

  /**
   * Update a property and persist an audit log entry.
   */
  public function update(PropertyRequest $request, int $id): JsonResponse
  {
    $property = Property::findOrFail($id);
    $validated = $request->validated();

    $oldValues = $property->getAttributes();

    DB::transaction(function () use ($property, $validated, $oldValues, $request) {
      $property->update($validated);

      AuditLog::create([
        'property_id' => $property->id,
        'user_id' => (string) $request->user()->id,
        'action' => 'updated',
        'old_values' => $oldValues,
        'new_values' => $property->fresh()->getAttributes(),
      ]);
    });

    return response()->json([
      'message' => 'Property berhasil diperbarui.',
      'data' => $property->fresh(),
    ]);
  }

  /**
   * Soft-delete a property listing.
   */
  public function destroy(int $id): JsonResponse
  {
    $property = Property::findOrFail($id);
    $property->delete();

    return response()->json([
      'message' => 'Property berhasil dihapus.',
    ]);
  }
}
