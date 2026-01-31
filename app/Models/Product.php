<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'is_active',
        'liters',
        'stock',
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
        'price' => 'decimal:2',
        'liters' => 'decimal:2',
    ];

    /**
     * tenemos que agregar la logica cuando se crean stocks compartidos
     */
    public function updateModel(array $data): void
    {
        $this->update($data);
        if ($this->wasChanged('stock')) { //verificamos que han actualizado el stock
            switch ($this->id) {
                case 6:
                case 12:
                    Product::whereIn('id', [6, 12])->update(['stock' => $data['stock']]);
                    break;
                case 7:
                case 13:
                    Product::whereIn('id', [7, 13])->update(['stock' => $data['stock']]);
                    break;
                case 15:
                    //case 16: pendiente en dar de alta
                    //Product::whereIn('id', [15, 16])->decrement('stock');
                    Product::where('id', 15)->update(['stock' => $data['stock']]);
                    break;
                default:
                    //to do
                    break;
            }
        }
    }

    /**
     * reducimos el stock con cada venta
     * tenemos una situacion, compartimos stock de los mismos garrafones
     * asi que si se vende uno, se reducen en sus 2 presentaciones
     */
    public function reduceStock(): void
    {
        if (isset($this->stock) && $this->stock > 0) {
            switch ($this->id) {
                case 6:
                case 12:
                    Product::whereIn('id', [6, 12])->decrement('stock');
                    break;
                case 7:
                case 13:
                    Product::whereIn('id', [7, 13])->decrement('stock');
                    break;
                case 15:
                    //case 16: pendiente en dar de alta
                    //Product::whereIn('id', [15, 16])->decrement('stock');
                    Product::where('id', 15)->decrement('stock');
                    break;
                default:
                    # code...
                    break;
            }
        }
    }

    /**
     * retorna todos los productos activos para poder realizar ventas
     */
    public static function getActivedProducts()
    {
        return Product::where('is_active', true)->get();
    }
}
