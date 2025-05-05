<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageSubStore;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class SubStoreController extends Controller
{
    /**
     * Display a listing of all sub-stores, sorted by 'sort' column.
     */
    public function index()
    {
        $subStores = ManageSubStore::with('shopBrand', 'classrooms')
            ->orderBy('sort', 'asc')
            ->get();
        return apiResponse(200, $subStores->toArray(), 'Sub stores retrieved successfully', 200);
    }

    /**
     * Store a newly created sub-store in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'sometimes|exists:shop_brands,id',
                'sub_store_name' => 'required|string|max:255',
                'sub_store_address' => 'required|string|max:255',
                'line_chat_id' => 'nullable|string|max:255',
                'active' => 'boolean',
                'sort' => 'integer|min:0',
                'pinned' => 'boolean',
                'classroom_ids' => 'nullable|array',
                'classroom_ids.*' => 'exists:classrooms,id',
            ]);

            $classroomIds = $validated['classroom_ids'] ?? [];
            unset($validated['classroom_ids']);

            $subStore = ManageSubStore::create($validated);

            if (!empty($classroomIds)) {
                $subStore->classrooms()->sync($classroomIds);
            }

            return apiResponse(201, [$subStore->load('shopBrand', 'classrooms')], 'Sub store created successfully', 201);
        } catch (ValidationException $e) {
            $errorMessage = 'The given data was invalid: ' . json_encode($e->errors());
            return apiResponse(422, null, $errorMessage, 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the sub-store: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified sub-store.
     */
    public function show($id)
    {
        $subStore = ManageSubStore::with('shopBrand', 'classrooms')->findOrFail($id);
        return apiResponse(200, [$subStore], 'Sub store retrieved successfully', 200);
    }

    /**
     * Update the specified sub-store in storage.
     */
    public function update(Request $request, $id)
    {
        $subStore = ManageSubStore::findOrFail($id);

        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'sometimes|exists:shop_brands,id',
                'sub_store_name' => 'sometimes|string|max:255',
                'sub_store_address' => 'sometimes|string|max:255',
                'line_chat_id' => 'nullable|string|max:255',
                'active' => 'sometimes|boolean', 
                'sort' => 'sometimes|integer|min:0',
                'pinned' => 'sometimes|boolean',
                'classroom_ids' => 'nullable|array',
                'classroom_ids.*' => 'exists:classrooms,id',
            ]);

            $classroomIds = $validated['classroom_ids'] ?? [];
            unset($validated['classroom_ids']);

            $subStore->update($validated);

            if (array_key_exists('classroom_ids', $request->all())) {
                $syncData = [];
                $now = now();
                foreach ($classroomIds as $classroomId) {
                    $syncData[$classroomId] = [
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
                $subStore->classrooms()->sync($syncData);
            }

            $updatedSubStore = ManageSubStore::with('shopBrand', 'classrooms')->findOrFail($id);
            return apiResponse(200, [$updatedSubStore], 'Sub store updated successfully', 200);
        } catch (ValidationException $e) {
            $errorMessage = 'The given data was invalid: ' . json_encode($e->errors());
            return apiResponse(422, null, $errorMessage, 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the sub-store: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified sub-store from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $subStore = ManageSubStore::with('classrooms')->findOrFail($id);
            $subStore->classrooms()->detach();
            $subStore->delete();

            DB::commit();

            return apiResponse(200, null, 'Sub store deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the sub-store: ' . $e->getMessage(), 500);
        }
    }
}