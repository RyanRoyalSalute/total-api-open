<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageUsers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Services\DiscountService;

class UserController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Display a listing of all users.
     */
    public function index()
    {
        $users = ManageUsers::with('shopBrand')->get();

        // Add best active discount to each user
        $usersData = $users->map(function ($user) {
            $activeDiscount = DB::table('user_discounts')
                ->where('user_id', $user->id)
                ->where('start_date', '<=', now())
                ->where('expiry_date', '>=', now())
                ->orderBy('discount_rate', 'asc') // Lowest rate is best
                ->select('discount_rate', 'expiry_date')
                ->first();

            $userData = $user->toArray();
            $userData['discount_rate'] = $activeDiscount ? $activeDiscount->discount_rate : null;
            $userData['discount_expiry_date'] = $activeDiscount ? $activeDiscount->expiry_date : null;

            return $userData;
        })->all();

        return apiResponse(200, $usersData, 'Users retrieved successfully', 200);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile_phone' => 'required|string|max:15|unique:users,mobile_phone',
                'token' => 'nullable|string',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'nullable|exists:shop_brands,id',
                'line_auth_code' => 'nullable|string',
                'country_calling_code' => 'nullable|string|max:5',
                'last_visited_at' => 'nullable|date',
                'avatar' => 'nullable|string|max:255',
                'name' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'age' => 'nullable|integer|min:0',
                'address' => 'nullable|string|max:255',
                'current_points' => 'integer|min:0',
                'status' => 'integer', // -1: 封鎖, 0: 正常, >=1: VIP
                'permission' => 'integer',
                'latest_payment_record_id' => 'nullable|exists:payment_records,id',
                'latest_point_record_id' => 'nullable|integer',
                'recharge_id' => 'nullable|exists:recharges,id', // Optional for initial discount
            ]);

            DB::beginTransaction();

            $user = ManageUsers::create($validated);

            // Handle initial discount if recharge_id is provided
            if ($request->has('recharge_id')) {
                $this->discountService->handleDiscount($user->id, $validated['recharge_id'], null);
            }

            DB::commit();

            $userWithRelations = $user->load('shopBrand');
            $activeDiscount = DB::table('user_discounts')
                ->where('user_id', $user->id)
                ->where('start_date', '<=', now())
                ->where('expiry_date', '>=', now())
                ->orderBy('discount_rate', 'asc')
                ->select('discount_rate', 'expiry_date')
                ->first();

            $userData = $userWithRelations->toArray();
            $userData['discount_rate'] = $activeDiscount ? $activeDiscount->discount_rate : null;
            $userData['discount_expiry_date'] = $activeDiscount ? $activeDiscount->expiry_date : null;

            return apiResponse(201, [$userData], 'User created successfully', 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while creating the user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = ManageUsers::with('shopBrand')->findOrFail($id);

        $activeDiscount = DB::table('user_discounts')
            ->where('user_id', $user->id)
            ->where('start_date', '<=', now())
            ->where('expiry_date', '>=', now())
            ->orderBy('discount_rate', 'asc')
            ->select('discount_rate', 'expiry_date')
            ->first();

        $userData = $user->toArray();
        $userData['discount_rate'] = $activeDiscount ? $activeDiscount->discount_rate : null;
        $userData['discount_expiry_date'] = $activeDiscount ? $activeDiscount->expiry_date : null;

        return apiResponse(200, [$userData], 'User retrieved successfully', 200);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = ManageUsers::findOrFail($id);

        try {
            $validated = $request->validate([
                'mobile_phone' => 'sometimes|string|max:15|unique:users,mobile_phone,' . $id,
                'token' => 'nullable|string',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'sometimes|nullable|exists:shop_brands,id',
                'line_auth_code' => 'nullable|string',
                'country_calling_code' => 'nullable|string|max:5',
                'last_visited_at' => 'nullable|date',
                'avatar' => 'nullable|string|max:255',
                'name' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'age' => 'nullable|integer|min:0',
                'address' => 'nullable|string|max:255',
                'current_points' => 'sometimes|integer|min:0',
                'status' => 'sometimes|integer', // -1: 封鎖, 0: 正常, >=1: VIP
                'permission' => 'sometimes|integer',
                'latest_payment_record_id' => 'nullable|exists:payment_records,id',
                'latest_point_record_id' => 'nullable|integer',
                'recharge_id' => 'nullable|exists:recharges,id', // Optional for discount update
            ]);

            DB::beginTransaction();

            $user->update($validated);

            // Handle discount update if recharge_id is provided
            if ($request->has('recharge_id')) {
                $this->discountService->handleDiscount($user->id, $validated['recharge_id'], null);
            }

            $updatedUser = ManageUsers::with('shopBrand')->findOrFail($id);
            $activeDiscount = DB::table('user_discounts')
                ->where('user_id', $user->id)
                ->where('start_date', '<=', now())
                ->where('expiry_date', '>=', now())
                ->orderBy('discount_rate', 'asc')
                ->select('discount_rate', 'expiry_date')
                ->first();

            $userData = $updatedUser->toArray();
            $userData['discount_rate'] = $activeDiscount ? $activeDiscount->discount_rate : null;
            $userData['discount_expiry_date'] = $activeDiscount ? $activeDiscount->expiry_date : null;

            DB::commit();

            return apiResponse(200, [$userData], 'User updated successfully', 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while updating the user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = ManageUsers::findOrFail($id);
            $user->delete();

            DB::commit();

            return apiResponse(200, null, 'User deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the user: ' . $e->getMessage(), 500);
        }
    }
}