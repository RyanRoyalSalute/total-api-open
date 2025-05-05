<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
/**
 * DiscountService::handleDiscount
 * 1. Fetch Recharge: Validates recharge_id and retrieves data.
 * 
 * 2. Check Active Discount:
 *     Queries user_discounts for an active entry with the same recharge_id.
 * 
 * 3. Extend or Insert:
 *     If active, extends expiry_date by privileged_days.
 *     If no active match (expired or different recharge_id), inserts new entry.
**/
class DiscountService
{
    public function handleDiscount(int $userId, int $rechargeId, ?int $paymentRecordId = null): void
    {
        $recharge = DB::table('recharges')
            ->where('id', $rechargeId)
            ->where('active', true)
            ->first();

        if (!$recharge) {
            throw new \Exception('Invalid or inactive recharge ID.');
        }

        // Check for an active discount with the same recharge_id
        $existingActiveDiscount = DB::table('user_discounts')
            ->where('user_id', $userId)
            ->where('recharge_id', $rechargeId)
            ->where('start_date', '<=', Carbon::today())
            ->where('expiry_date', '>=', Carbon::today())
            ->first();

        if ($existingActiveDiscount) {
            // Extend expiry_date if active discount with same recharge_id exists
            $newExpiryDate = Carbon::today()->addDays($recharge->privileged_days);
            DB::table('user_discounts')
                ->where('id', $existingActiveDiscount->id)
                ->update([
                    'expiry_date' => $newExpiryDate,
                    'updated_at' => now(),
                ]);
        } else {
            // Insert new discount if no active match (including expired same recharge_id)
            DB::table('user_discounts')->insert([
                'user_id' => $userId,
                'recharge_id' => $rechargeId,
                'discount_rate' => $recharge->discount_rate,
                'start_date' => Carbon::today(),
                'expiry_date' => Carbon::today()->addDays($recharge->privileged_days),
                'payment_record_id' => $paymentRecordId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}