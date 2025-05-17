<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    /**
     * Upload Avatar API.
     */
    public function uploadAvatar(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobilePhone = $request->header('mobile');

        if (!$request->hasFile('avatar') || !$request->file('avatar')->isValid()) {
            return apiResponse(4001, null, 'Invalid or missing avatar file.', 400);
        }

        $file = $request->file('avatar');

        if ($file->getSize() > 5242880) { // 5MB limit in bytes
            return apiResponse(4002, null, 'File size exceeds the limit of 5MB.', 400);
        }

        $userFolder = 'Saabisu/users';
        if (!Storage::disk('public')->exists($userFolder)) {
            Storage::disk('public')->makeDirectory($userFolder);
        }

        $currentAvatar = DB::table('users')
            ->where('mobile_phone', $mobilePhone)
            ->value('avatar');

        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp; // Use current timestamp for uniqueness
        $fileName = "{$mobilePhone}_avatar_{$timestamp}.{$extension}";
        $path = $file->storeAs($userFolder, $fileName, 'public');

        if (!$path || !Storage::disk('public')->exists($path)) {
            return apiResponse(5001, null, 'Failed to store the new avatar file.', 500);
        }

        DB::table('users')
            ->where('mobile_phone', $mobilePhone)
            ->update(['avatar' => "storage/{$path}", 'updated_at' => now()]);

        if ($currentAvatar !== "storage/users/default") { 
            $normalizedCurrentAvatar = str_starts_with($currentAvatar, 'storage/') 
                ? substr($currentAvatar, 8)
                : $currentAvatar;
            if (Storage::disk('public')->exists($normalizedCurrentAvatar)) {
                Storage::disk('public')->delete($normalizedCurrentAvatar);
            }
        }

        return apiResponse(2001, ['avatar_path' => asset("storage/{$path}")], 'Avatar uploaded successfully.', 200);
    }

    /**
     * Get User Profile API.
     */
    public function getProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobilePhone = $request->header('mobile');

        $user = DB::table('users')
            ->where('mobile_phone', $mobilePhone)
            ->select(
                'id as user_id',
                'mobile_phone',
                'name',
                'gender',
                'birth_date',
                'age',
                'address',
                'country_calling_code',
                'avatar',
                'current_points',
                'last_visited_at',
                'permission'
            )
            ->first();

        if (!$user) {
            return apiResponse(404, null, 'User not found.', 404);
        }

        $activeDiscount = DB::table('user_discounts')
            ->where('user_id', $user->user_id)
            ->where('start_date', '<=', Carbon::today())
            ->where('expiry_date', '>=', Carbon::today())
            ->orderBy('discount_rate', 'asc') // Lowest rate is best
            ->select('discount_rate', 'expiry_date')
            ->first();
        
        $userData = (array) $user;
        $userData['discount_rate'] = $activeDiscount ? $activeDiscount->discount_rate : null;
        $userData['discount_expiry_date'] = $activeDiscount ? $activeDiscount->expiry_date : null;

        return apiResponse(2000, $userData, 'User profile retrieved successfully.', 200);
    }

    /**
     * Update User Profile API.
     */
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobilePhone = $request->header('mobile');

        $validFields = [
            'mobile_phone' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer|min:0',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|string|max:500',
        ];

        $request->validate($validFields);

        $updateData = array_filter($request->only(array_keys($validFields)), function ($value) {
            return !is_null($value);
        });

        if (empty($updateData)) {
            return apiResponse(4003, null, 'No valid fields provided for update.', 400);
        }

        
        $pointsAwarded = false;
        if (array_key_exists('address', $updateData)) {
            $currentAddress = DB::table('users')
                ->where('mobile_phone', $mobilePhone)
                ->value('address');

            
            if (is_null($currentAddress) && !is_null($updateData['address']) && trim($updateData['address']) !== '') {
                $pointsAwarded = true;
                $updateData['current_points'] = DB::raw('current_points + 10');
            }
        }

        $updateData['updated_at'] = now();

        $updated = DB::table('users')
            ->where('mobile_phone', $mobilePhone)
            ->update($updateData);

        if (!$updated) {
            return apiResponse(5001, null, 'No changes detected or update failed.', 500);
        }

        $message = 'Profile updated successfully.';
        if ($pointsAwarded) {
            $message .= ' Awarded 10 points for providing address.';
        }

        return apiResponse(2002, null, $message, 200);
    }
}