<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageCoursePrice;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CoursePriceController extends Controller
{
    /**
     * Display a listing of all course prices, sorted by 'sort' column.
     */
    public function index()
    {
        $coursePrices = ManageCoursePrice::with('shopBrand', 'recharge')
            ->orderBy('sort', 'asc')
            ->get();
        return apiResponse(200, $coursePrices->toArray(), 'Course prices retrieved successfully', 200);
    }

    /**
     * Store a newly created course price in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'required|exists:shop_brands,id',
                'original_price' => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
                'early_bird_price' => 'nullable|numeric|min:0',
                'price_group' => 'required|in:none,Prime',
                'recharge_id' => 'nullable|exists:recharges,id',
                'active' => 'boolean',
                'sort' => 'integer|min:0',
                'pinned' => 'boolean',
            ]);

            $coursePrice = ManageCoursePrice::create($validated);

            return apiResponse(201, [$coursePrice->load('shopBrand', 'recharge')], 'Course price created successfully', 201);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the course price: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified course price.
     */
    public function show($id)
    {
        $coursePrice = ManageCoursePrice::with('shopBrand', 'recharge')->findOrFail($id);
        return apiResponse(200, [$coursePrice], 'Course price retrieved successfully', 200);
    }

    /**
     * Update the specified course price in storage.
     */
    public function update(Request $request, $id)
    {
        $coursePrice = ManageCoursePrice::findOrFail($id);

        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'sometimes|exists:shop_brands,id',
                'original_price' => 'sometimes|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
                'early_bird_price' => 'nullable|numeric|min:0',
                'price_group' => 'sometimes|in:none,Prime',
                'recharge_id' => 'nullable|exists:recharges,id',
                'active' => 'sometimes|boolean', // New field: 營業中
                'sort' => 'sometimes|integer|min:0', // New field: 排序
                'pinned' => 'sometimes|boolean', // New field: 置頂
            ]);

            $coursePrice->update($validated);

            $updatedCoursePrice = ManageCoursePrice::with('shopBrand', 'recharge')->findOrFail($id);
            return apiResponse(200, [$updatedCoursePrice], 'Course price updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the course price: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified course price from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $coursePrice = ManageCoursePrice::findOrFail($id);
            $coursePrice->delete();

            DB::commit();

            return apiResponse(200, null, 'Course price deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the course price: ' . $e->getMessage(), 500);
        }
    }
}