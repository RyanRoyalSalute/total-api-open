<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManagePaymentRecords;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PaymentRecordController extends Controller
{
    /**
     * Display a listing of all payment records.
     */
    public function index()
    {
        $paymentRecords = ManagePaymentRecords::with('shopBrand', 'subStore', 'user', 'course')->get();
        return apiResponse(200, $paymentRecords->toArray(), 'Payment records retrieved successfully', 200);
    }

    /**
     * Store a newly created payment record in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'trade_no' => 'required|string|unique:payment_records,trade_no|max:255',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'required|exists:shop_brands,id',
                'sub_store_id' => 'nullable|exists:sub_store,id',
                'payment_date' => 'required|date',
                'user_id' => 'required|exists:users,id',
                'course_id' => 'nullable|exists:courses,id',
                'payment_method' => 'required|in:CREDIT_CARD,LINE_Pay',
                'transaction_amount' => 'required|numeric|min:0',
                'received_amount' => 'required|numeric|min:0',
                'recharge_amount' => 'nullable|numeric|min:0',
                'privileged_level' => 'integer|min:0',
                'pinned' => 'boolean', // New field: 置頂
                'is_paid' => 'boolean',
            ]);

            $paymentRecord = ManagePaymentRecords::create($validated);

            return apiResponse(201, [$paymentRecord->load('shopBrand', 'subStore', 'user', 'course')], 'Payment record created successfully', 201);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the payment record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified payment record.
     */
    public function show($id)
    {
        $paymentRecord = ManagePaymentRecords::with('shopBrand', 'subStore', 'user', 'course')->findOrFail($id);
        return apiResponse(200, [$paymentRecord], 'Payment record retrieved successfully', 200);
    }

    /**
     * Update the specified payment record in storage.
     */
    public function update(Request $request, $id)
    {
        $paymentRecord = ManagePaymentRecords::findOrFail($id);

        try {
            $validated = $request->validate([
                'trade_no' => 'sometimes|string|unique:payment_records,trade_no,' . $id . '|max:255',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'sometimes|exists:shop_brands,id',
                'sub_store_id' => 'nullable|exists:sub_store,id',
                'payment_date' => 'sometimes|date',
                'user_id' => 'sometimes|exists:users,id',
                'course_id' => 'nullable|exists:courses,id',
                'payment_method' => 'sometimes|in:CREDIT_CARD,LINE_Pay',
                'transaction_amount' => 'sometimes|numeric|min:0',
                'received_amount' => 'sometimes|numeric|min:0',
                'recharge_amount' => 'nullable|numeric|min:0',
                'privileged_level' => 'sometimes|integer|min:0',
                'pinned' => 'sometimes|boolean', // New field: 置頂
                'is_paid' => 'sometimes|boolean',
            ]);

            $paymentRecord->update($validated);

            $updatedPaymentRecord = ManagePaymentRecords::with('shopBrand', 'subStore', 'user', 'course')->findOrFail($id);
            return apiResponse(200, [$updatedPaymentRecord], 'Payment record updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the payment record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified payment record from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $paymentRecord = ManagePaymentRecords::findOrFail($id);
            $paymentRecord->delete();

            DB::commit();

            return apiResponse(200, null, 'Payment record deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the payment record: ' . $e->getMessage(), 500);
        }
    }
}