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
        $authenticatedUser = $request->user();
        if (!$authenticatedUser) {
            return apiResponse(401, null, 'User not authenticated.', 401);
        }
        $mobilePhone = $authenticatedUser->mobile_phone; // 從認證用戶獲取

        if (!$request->hasFile('avatar') || !$request->file('avatar')->isValid()) {
            return apiResponse(4001, null, 'Invalid or missing avatar file.', 400);
        }

        $file = $request->file('avatar');

        if ($file->getSize() > 5242880) { // 5MB limit in bytes
            return apiResponse(4002, null, 'File size exceeds the limit of 5MB.', 400);
        }

        $userFolder = 'Saabisu/users/' . $authenticatedUser->id; // 使用用戶 ID 建立更唯一的路徑
        if (!Storage::disk('public')->exists($userFolder)) {
            Storage::disk('public')->makeDirectory($userFolder);
        }

        $currentAvatar = DB::table('users')
            ->where('id', $authenticatedUser->id) // 使用認證用戶的 ID
            ->value('avatar');

        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp;
        $fileName = "avatar_{$timestamp}.{$extension}";
        $path = $file->storeAs($userFolder, $fileName, 'public');

        if (!$path || !Storage::disk('public')->exists($path)) {
            return apiResponse(5001, null, 'Failed to store the new avatar file.', 500);
        }

        DB::table('users')
            ->where('id', $authenticatedUser->id) // 使用認證用戶的 ID
            ->update(['avatar' => "storage/{$path}", 'updated_at' => now()]);

        // 考慮預設頭像的路徑，避免誤刪
        $defaultAvatarPathPrefix = 'storage/users/default';
        if ($currentAvatar && !str_starts_with($currentAvatar, $defaultAvatarPathPrefix)) {
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
        $userFromToken = $request->user();

        if (!$userFromToken) {
            return apiResponse(401, null, 'User authentication failed or user not resolved from token.', 401);
        }

        $user = DB::table('users')
            ->where('id', $userFromToken->id)
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
            return apiResponse(404, null, 'User data not found in database despite valid token.', 404);
        }

        $activeDiscount = DB::table('user_discounts')
            ->where('user_id', $user->user_id)
            ->where('start_date', '<=', Carbon::today())
            ->where('expiry_date', '>=', Carbon::today())
            ->orderBy('discount_rate', 'asc')
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
        $authenticatedUser = $request->user();
        if (!$authenticatedUser) {
            return apiResponse(401, null, 'User not authenticated.', 401);
        }
        $userIdentifier = $authenticatedUser->id;

        $validFields = [
            'name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            // 'age' => 'nullable|integer|min:0', // age 應由 birth_date 計算
            'address' => 'nullable|string|max:500',
            // 'avatar' 通常由 uploadAvatar 方法處理
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
                ->where('id', $userIdentifier)
                ->value('address');
            
            if (is_null($currentAddress) && !is_null($updateData['address']) && trim($updateData['address']) !== '') {
                $pointsAwarded = true;
            }
        }

        $updateData['updated_at'] = now();

        $pointsToUpdate = null;
        if (isset($updateData['current_points'])) {
            $pointsToUpdate = $updateData['current_points'];
            unset($updateData['current_points']);
        }
        $updatedCount = DB::table('users')
            ->where('id', $userIdentifier)
            ->update($updateData);

        if ($pointsAwarded && $pointsToUpdate) { 
             DB::table('users')->where('id', $userIdentifier)->increment('current_points', 10);
             $updatedCount = 1;
        }
        if (!$updatedCount && !$pointsAwarded) { 
             $noChangesMade = true;
             // 再次檢查是否有實際數據變更，因為 update 返回 0 也可能意味著提交的數據與現有數據相同
             if ($updatedCount === 0) {
                $currentUserData = DB::table('users')->where('id', $userIdentifier)->first();
                foreach ($updateData as $key => $value) {
                    if ($key !== 'updated_at' && data_get($currentUserData, $key) != $value) {
                        $noChangesMade = false;
                        break;
                    }
                }
             }
             if ($noChangesMade) {
                return apiResponse(200, null, 'Profile data is already up to date. No changes made.', 200);
             }
            return apiResponse(5001, null, 'Update failed or no changes detected.', 500);
        }

        $message = 'Profile updated successfully.';
        if ($pointsAwarded) {
            $message .= ' Awarded 10 points for providing address.';
        }

        return apiResponse(2002, null, $message, 200);
    }
}