<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'product_id',
        'amount',
        'total'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total' => 'decimal:2',
    ];


    public static function logicalStore(array $data)
    {
        $errorMsg = "";
        if (!auth()->user()->canDoASale($errorMsg)) {
            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }
        $sale = Sale::create([
            'employee_id' => Auth::user()->id,
            'product_id' => $data['product_id'],
            'amount' => $data['amount'],
            'total' => Product::find($data['product_id'])->price * $data['amount'],
        ]);
        return [
            'success' => true,
            'sale' => $sale
        ];
    }

    public function logicalUpdate(array $data)
    {
        $this->fill($data);
        $this->total = Product::find($this->product_id)->price * $this->amount;
        $this->save();
    }

    public static function sumAllDailySales()
    {
        return Sale::whereDate('created_at', today())->sum('total');
    }

    /**
     * RELATIONSHIPS
     */
    /**
     * Get the user that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    /**
     * Get the product that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
