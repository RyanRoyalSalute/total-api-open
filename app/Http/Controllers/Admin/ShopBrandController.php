<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageShopBrand;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShopBrandController extends Controller
{
    public function index()
    {
        $shopBrands = ManageShopBrand::orderBy('pinned', 'desc')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        return apiResponse(200, $shopBrands, 'Shop brands retrieved successfully', 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'brand_code' => 'required|unique:shop_brands,brand_code',
                'brand_name' => 'required|string|max:255',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'brand_logo' => 'nullable|string',
                'brand_background' => 'nullable|string',
                'teacher_permission' => 'nullable|boolean',
                'active' => 'nullable|boolean',
                'sort' => 'nullable|integer',
                'pinned' => 'nullable|boolean',
            ]);

            $shopBrand = ManageShopBrand::create($validated);
            return apiResponse(201, [$shopBrand], 'Shop brand created successfully', 201);
        } catch (ValidationException $e) {
            $errorMessage = 'The given data was invalid.' . json_encode($e->errors());
            return apiResponse(422, null, $errorMessage, 422);
        }
    }

    public function show($id)
    {
        $shopBrand = ManageShopBrand::findOrFail($id);
        return apiResponse(200, [$shopBrand], 'Shop brand retrieved successfully', 200);
    }

    public function update(Request $request, $id)
    {
        $shopBrand = ManageShopBrand::findOrFail($id);

        try {
            $validated = $request->validate([
                'brand_code' => 'sometimes|unique:shop_brands,brand_code,' . $id . ',id',
                'brand_name' => 'sometimes|string|max:255',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'brand_logo' => 'nullable|string',
                'brand_background' => 'nullable|string',
                'teacher_permission' => 'nullable|boolean',
                'active' => 'nullable|boolean',
                'sort' => 'nullable|integer',
                'pinned' => 'nullable|boolean',
            ]);

            $shopBrand->update($validated);
            
            // Refresh the model to ensure the latest data is returned
            $updatedShopBrand = ManageShopBrand::findOrFail($id);
            return apiResponse(200, [$updatedShopBrand], 'Shop brand updated successfully', 200);
        } catch (ValidationException $e) {
            $errorMessage = 'The given data was invalid.' . json_encode($e->errors());
            return apiResponse(422, null, $errorMessage, 422);
        }
    }

    public function destroy($id)
    {
        $shopBrand = ManageShopBrand::findOrFail($id);
        $shopBrand->delete();
        return apiResponse(200, null, 'Shop brand deleted successfully', 200);
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:shop_brands,id',
        ]);

        $ids = $validated['ids'];
        foreach ($ids as $index => $id) {
            ManageShopBrand::where('id', $id)->update(['sort' => $index]);
        }

        return apiResponse(200, null, 'Shop brand order updated successfully', 200);
    }
}