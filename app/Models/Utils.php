<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageShopBrand extends Model
{
    use HasFactory;

    protected $table = 'shop_brands';
    protected $guarded = [];
}

class ManageSubStore extends Model
{
    use HasFactory;

    protected $table = 'sub_store';
    protected $guarded = [];

    public function shopBrand()
    {
        return $this->belongsTo(ManageShopBrand::class, 'shop_brand_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(ManageClassroom::class, 'classroom_sub_store', 'sub_store_id', 'classroom_id')
                    ->withTimestamps();
    }

    public function courses()
    {
        return $this->belongsToMany(ManageCourse::class, 'course_sub_store', 'sub_store_id', 'course_id')
                    ->withTimestamps();
    }
}

class ManageClassroom extends Model
{
    use HasFactory;

    protected $table = 'classrooms';
    protected $guarded = [];
    protected $casts = [
        'classroom_images' => 'array',
    ];

    public function subStores()
    {
        return $this->belongsToMany(ManageSubStore::class, 'classroom_sub_store', 'classroom_id', 'sub_store_id')
                    ->withTimestamps();
    }

    public function courses()
    {
        return $this->belongsToMany(ManageCourse::class, 'course_classroom', 'classroom_id', 'course_id')
                    ->withTimestamps();
    }
}

class ManageTeacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';
    protected $guarded = [];
    protected $casts = [
        'teacher_portfolio' => 'array',
    ];

    public function shopBrand()
    {
        return $this->belongsTo(ManageShopBrand::class, 'shop_brand_id');
    }

    public function courses()
    {
        return $this->belongsToMany(ManageCourse::class, 'course_teacher', 'teacher_id', 'course_id')
                    ->withTimestamps();
    }
}

class ManageProduct extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $guarded = [];

    protected $casts = [
        'product_costs' => 'decimal:2',
    ];

    public function subStore()
    {
        return $this->belongsTo(ManageSubStore::class, 'store_id');
    }

    public function courses()
    {
        return $this->hasMany(ManageCourse::class, 'material_id');
    }
}

class ManageCoursePrice extends Model
{
    use HasFactory;

    protected $table = 'course_prices';
    protected $guarded = [];

    public function shopBrand()
    {
        return $this->belongsTo(ManageShopBrand::class, 'shop_brand_id');
    }

    public function recharge()
    {
        return $this->belongsTo(ManageRecharge::class, 'recharge_id');
    }
}

class ManageRecharge extends Model
{
    use HasFactory;

    protected $table = 'recharges';
    protected $guarded = [];

    public function shopBrand()
    {
        return $this->belongsTo(ManageShopBrand::class, 'shop_brand_id');
    }
}

class ManageCourse extends Model
{
    use HasFactory;

    protected $table = 'courses';
    protected $guarded = [];

    protected $casts = [
        'course_images' => 'array',
        'course_tab' => 'array',
        'course_colors' => 'array',
        'course_dates' => 'array',
        'course_times' => 'array',
        'material_id' => 'array',
        'on_sale' => 'boolean',
        'pinned' => 'boolean',
    ];

    public function shopBrand()
    {
        return $this->belongsTo(ManageShopBrand::class, 'shop_brand_id');
    }

    public function subStores()
    {
        return $this->belongsToMany(ManageSubStore::class, 'course_sub_store', 'course_id', 'sub_store_id')
                    ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(ManageTeacher::class, 'course_teacher', 'course_id', 'teacher_id')
                    ->withTimestamps();
    }

    public function classrooms()
    {
        return $this->belongsToMany(ManageClassroom::class, 'course_classroom', 'course_id', 'classroom_id')
                    ->withTimestamps();
    }

    public function coursePrice()
    {
        return $this->belongsTo(ManageCoursePrice::class, 'course_price_id');
    }
}

class ManageUsers extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $guarded = [];

    /**
     * Get the shop brand associated with the user.
     */
    public function shopBrand()
    {
        return $this->belongsTo(ManageShopBrand::class, 'shop_brand_id');
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_visited_at' => 'datetime',
        'birth_date' => 'date',
        'current_points' => 'integer',
        'status' => 'integer',
    ];
}

class ManageCourseTickets extends Model
{
    use HasFactory;

    protected $table = 'course_tickets';
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the course associated with this ticket.
     */
    public function course()
    {
        return $this->belongsTo(ManageCourse::class, 'course_id');
    }

    /**
     * Get the user who owns this ticket.
     */
    public function owner()
    {
        return $this->belongsTo(ManageUsers::class, 'owner_user_id');
    }

    /**
     * Get the teacher associated with this ticket.
     */
    public function teacher()
    {
        return $this->belongsTo(ManageTeacher::class, 'teacher_id');
    }

    /**
     * Get the payment record associated with this ticket.
     */
    public function paymentRecord()
    {
        return $this->belongsTo(ManagePaymentRecords::class, 'payment_record_id', 'trade_no');
        // Note: payment_record_id is a string (trade_no), not an integer ID
    }
}


class ManagePaymentRecords extends Model
{
    use HasFactory;

    protected $table = 'payment_records';
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_date' => 'datetime',
        'is_paid' => 'boolean',
        'transaction_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'recharge_amount' => 'decimal:2',
    ];

    /**
     * Get the shop brand associated with this payment record.
     */
    public function shopBrand()
    {
        return $this->belongsTo(ManageShopBrand::class, 'shop_brand_id');
    }

    /**
     * Get the sub-store associated with this payment record.
     */
    public function subStore()
    {
        return $this->belongsTo(ManageSubStore::class, 'sub_store_id');
    }

    /**
     * Get the user associated with this payment record.
     */
    public function user()
    {
        return $this->belongsTo(ManageUsers::class, 'user_id');
    }

    /**
     * Get the course associated with this payment record.
     */
    public function course()
    {
        return $this->belongsTo(ManageCourse::class, 'course_id');
    }
}


class ManagePointRecords extends Model
{
    use HasFactory;

    protected $table = 'point_records';
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points_changed' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user associated with this point record.
     */
    public function user()
    {
        return $this->belongsTo(ManageUsers::class, 'user_id');
    }
}


class ManageFeedbacks extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'latest_feedback_date' => 'date',
        'feedback_images' => 'array',
        'sort' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the course associated with this feedback.
     */
    public function course()
    {
        return $this->belongsTo(ManageCourse::class, 'course_id');
    }

    /**
     * Get the user who provided this feedback.
     */
    public function user()
    {
        return $this->belongsTo(ManageUsers::class, 'user_id');
    }
}