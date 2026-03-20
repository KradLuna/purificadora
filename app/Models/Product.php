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
    public function reduceStock(int $amount): void
    {
        if (isset($this->stock)) {
            switch ($this->id) {
                case 6: //Venta Garrafón 20L
                case 12: //Venta Garrafon 20L vacío
                    Product::whereIn('id', [6, 12])->where('stock', '>', 0)->decrement('stock', $amount);
                    break;
                case 7: //Venta Garrafón 11L
                case 13: //Venta Garrafon 11L vacío
                    Product::whereIn('id', [7, 13])->where('stock', '>', 0)->decrement('stock', $amount);
                    break;
                case 15: //Venta Garrafon 20L Chupón
                    //case 16: pendiente en dar de alta
                    //Product::whereIn('id', [15, 16])->where('stock', '>',0)->decrement('stock', $amount);
                    Product::where('id', 15)->where('stock', '>', 0)->decrement('stock', $amount);
                    break;
                case 18: //Bolsa hielo 5kg
                    Product::where('id', 18)->where('stock', '>', 0)->decrement('stock', $amount);
                    break;
                case 19: //Bolsa hielo 3kg
                    Product::where('id', 19)->where('stock', '>', 0)->decrement('stock', $amount);
                    break;
                default:
                    # code...
                    break;
            }
        }
    }
    /**
     * reducimos el stock con cada venta
     * tenemos una situacion, compartimos stock de los mismos garrafones
     * asi que si se vende uno, se reducen en sus 2 presentaciones
     */
    public function increaseStock(int $amount): void
    {
        logger('increaseStock: ' . json_encode($amount));
        logger('increaseStock2: ' . json_encode($this));
        if (is_null($this->stock)) {
            logger('increaseStock3: ' . json_encode($this));
            switch ($this->id) {
                case 20: //Bolsa hielo 3kg
                    Product::where('id', 19)->increment('stock', $amount);
                    break;
                case 21: //Bolsa hielo 5kg
                    Product::where('id', 18)->increment('stock', $amount);
                    break;
                case 22: //Compra de Garrafón 11L
                    Product::whereIn('id', [7, 13])->increment('stock', $amount);
                    break;
                case 23: //Compro de Garrafón 20L
                    Product::whereIn('id', [6, 12])->increment('stock', $amount);
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
        return Product::where('is_active', true)->orderBy('liters', 'desc')->get();
    }
}
