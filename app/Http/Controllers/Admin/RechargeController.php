<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageRecharge;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class RechargeController extends Controller
{
    /**
     * Display a listing of all recharges, sorted by 'sort' column.
     */
    public function index()
    {
        $recharges = ManageRecharge::with('shopBrand')
            ->orderBy('sort', 'asc')
            ->get();
        return apiResponse(200, $recharges->toArray(), 'Recharges retrieved successfully', 200);
    }

    /**
     * Store a newly created recharge in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'required|exists:shop_brands,id',
                'recharge_amount' => 'required|numeric|min:0',
                'free_count' => 'required|integer|min:0',
                'discount_rate' => 'required|numeric|min:0|max:1',
                'privileged_days' => 'required|integer|min:0',
                'active' => 'boolean', // New field: 營業中
                'sort' => 'integer|min:0', // New field: 排序
                'pinned' => 'boolean', // New field: 置頂
            ]);

            $recharge = ManageRecharge::create($validated);

            return apiResponse(201, [$recharge->load('shopBrand')], 'Recharge created successfully', 201);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the recharge: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified recharge.
     */
    public function show($id)
    {
        $recharge = ManageRecharge::with('shopBrand')->findOrFail($id);
        return apiResponse(200, [$recharge], 'Recharge retrieved successfully', 200);
    }

    /**
     * Update the specified recharge in storage.
     */
    public function update(Request $request, $id)
    {
        $recharge = ManageRecharge::findOrFail($id);

        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'sometimes|exists:shop_brands,id',
                'recharge_amount' => 'sometimes|numeric|min:0',
                'free_count' => 'sometimes|integer|min:0',
                'discount_rate' => 'sometimes|numeric|min:0|max:1',
                'privileged_days' => 'sometimes|integer|min:0',
                'active' => 'sometimes|boolean', // New field: 營業中
                'sort' => 'sometimes|integer|min:0', // New field: 排序
                'pinned' => 'sometimes|boolean', // New field: 置頂
            ]);

            $recharge->update($validated);

            $updatedRecharge = ManageRecharge::with('shopBrand')->findOrFail($id);
            return apiResponse(200, [$updatedRecharge], 'Recharge updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the recharge: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified recharge from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $recharge = ManageRecharge::findOrFail($id);
            $recharge->delete();

            DB::commit();

            return apiResponse(200, null, 'Recharge deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the recharge: ' . $e->getMessage(), 500);
        }
    }
}