<?php
namespace App\Http\Controllers\Admin;

use App\Models\ManageProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $products = ManageProduct::query()
            ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->search, fn($q) => $q->where('product_name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->with('subStore')
            ->orderBy('sort', 'asc')
            ->get();

        return apiResponse(2000, $products, 'Products retrieved successfully');
    }

    /**
     * Store a newly created product
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:products',
            'store_id' => 'required|exists:sub_store,id',
            'product_name' => 'required|string|max:255',
            'product_spec' => 'nullable|string',
            'product_costs' => 'required|numeric|min:0',
            'active' => 'sometimes|boolean',
            'pinned' => 'sometimes|boolean',
            'sort' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return apiResponse(4001, null, $validator->errors()->first(), 400);
        }

        $product = ManageProduct::create([
            'code' => $request->code,
            'created_by' => 1,
            'updated_by' => 1,
            'store_id' => $request->store_id,
            'product_name' => $request->product_name,
            'product_spec' => $request->product_spec,
            'product_costs' => $request->product_costs,
            'active' => $request->active ?? 1,
            'pinned' => $request->pinned ?? 0,
            'sort' => $request->sort ?? 0,
        ]);

        return apiResponse(2010, $product->load('subStore'), 'Product created successfully', 201);
    }

    /**
     * Display the specified product
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = ManageProduct::with('subStore')->findOrFail($id);
        return apiResponse(2000, $product, 'Product retrieved successfully');
    }

    /**
     * Update the specified product
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = ManageProduct::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|unique:products,code,' . $id,
            'store_id' => 'sometimes|required|exists:sub_store,id',
            'product_name' => 'sometimes|required|string|max:255',
            'product_spec' => 'nullable|string',
            'product_image' =>  'nullable|string',
            'product_costs' => 'sometimes|required|numeric|min:0',
            'active' => 'sometimes|boolean',
            'pinned' => 'sometimes|boolean',
            'sort' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return apiResponse(4001, null, $validator->errors()->first(), 400);
        }

        $product->update([
            'code' => $request->code ?? $product->code,
            'updated_by' => 1,
            'store_id' => $request->store_id ?? $product->store_id,
            'product_name' => $request->product_name ?? $product->product_name,
            'product_spec' => $request->product_spec ?? $product->product_spec,
            'product_image' =>  $request->product_image ?? $product->product_image,
            'product_costs' => $request->product_costs ?? $product->product_costs,
            'active' => $request->active ?? $product->active,
            'pinned' => $request->pinned ?? $product->pinned,
            'sort' => $request->sort ?? $product->sort,
        ]);

        return apiResponse(2000, $product->load('subStore')->fresh(), 'Product updated successfully');
    }

    /**
     * Remove the specified product
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $product = ManageProduct::findOrFail($id);
        $product->delete();
        return apiResponse(2000, null, 'Product deleted successfully');
    }
}