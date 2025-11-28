<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Double;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'phone_number',
        'password',
        'work_start_time',
        'work_end_time',
        'rest_day',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
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
        'email_verified_at' => 'datetime',
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
    ];

    public const ROLES = ['administrador', 'empleado'];


    public static function logicStore(array $data)
    {
        $user = User::create($data);
        $user->syncRoles(self::ROLES[1]);
    }

    /**
     * creamos el usuario sin tomar en cuenta el role, este se asigna por medio
     * de otro método
     */
    public static function storeModel(array $data)
    {
        $user = User::create(Arr::except($data, ['role']));
        if (array_key_exists('role', $data)) {
            $user->assignRole($data['role']);
        }
    }

    /**
     * ATTRIBUTES
     */
    /**
     * Cuando se quiera asignar el password de este modelo, vendra directamente a esta atributo
     * hara la accion de aqui y asignara el valor.
     *********** SI SON VARIAS PALABRAS SEPARAR POR PASCALCASE Y NO USAR GUION BAJO
     * https://laravel.com/docs/9.x/eloquent-mutators#defining-a-mutator
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            // get: fn ($value) => $value, ?? no afecta si va o no
            set: fn($value) => Hash::make($value),
        );
    }

    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->hasRole(self::ROLES[0]) ? true : false,
        );
    }

    /**
     * atributo que valida si ya existe corte de caja para poder hacer una venta
     */
    public function canDoASale(String &$errorMsg): bool
    {
        $endWorkShift = $this->records()
            ->whereDate('created_at', today())
            ->whereHas(
                'record_type',
                fn($q) =>
                $q->where('name', RecordType::TYPES[3]) //fin de turno
            )
            ->exists();
        if ($endWorkShift) {
            $type = RecordType::TYPES[3]; //fin de turno
            $errorMsg = "Ya realizaste $type, ya no puedes realizar ventas.";
            return false;
        }
        $cashOut = $this->records()
            ->whereDate('created_at', today())
            ->whereHas(
                'record_type',
                fn($q) =>
                $q->where('name', RecordType::TYPES[1]) //corte de caja
            )
            ->exists();
        if (!$cashOut) {
            $type = RecordType::TYPES[1];
            $errorMsg = "Debes registrar el $type antes de realizar una venta.";
            return false;
        }
        return true;
    }

    /**
     * hace el calculo entre lo que se esta registrando con lo que se esta vendiendo y da un margen de venta de
     * 
     */
    public function saleVsCounterIsOk(): bool
    {
        $allowed_range = config('constants.liters.allowed_range');
        logger('$allowed_range>>>>' . $allowed_range);
        logger('this->getLitersPerWorkShiftSale()>>>>' . $this->getLitersPerWorkShiftSale());
        logger('this->getCounterLiters()>>>>' . $this->getCounterLiters());
        logger('abs($this->getLitersPerWorkShiftSale() - $this->getCounterLiters()) < $allowed_range ? true : false>>>>' . (abs($this->getLitersPerWorkShiftSale() - $this->getCounterLiters()) < $allowed_range ? true : false));
        return abs($this->getLitersPerWorkShiftSale() - $this->getCounterLiters()) < $allowed_range ? true : false;
    }

    /**
     * obtenemos los litros que vendio segun su relacion de garrafones por la cantidad vendida
     */
    protected function getLitersPerWorkShiftSale()
    {
        return $this->sales()
            ->whereDate('sales.created_at', today())
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(products.liters * sales.amount) as total'))
            ->value('total');
    }

    /**
     * obtenemos el valor del contador de litros de inicio de dia
     */
    protected function getInitLitersToday()
    {
        $init_liters = $this->records()
            ->whereDate('created_at', today())
            ->whereHas(
                'record_type',
                fn($q) =>
                $q->where('name', RecordType::TYPES[0]) //inicio de dia, 
            )
            ->first();
        return $init_liters->value;
    }

    /**
     * retorna la diferencia de litros entre el fin de turno y el inicio de turno del usuario
     */
    protected function getCounterLiters()
    {
        $init_liters = $this->getInitLitersToday();
        $end_score = $this->records()
            ->whereDate('created_at', today())
            ->whereHas(
                'record_type',
                fn($q) =>
                $q->where('name', RecordType::TYPES[3]) //fin de turno
            )
            ->first();

        if (!$end_score) {
            return null; // no hay datos suficientes
        }

        $end_value  = $end_score->value;
        $max_counter = 9999;
        // Lógica de diferencia con reinicio
        if ($end_value >= $init_liters) {
            logger('getCounterLiters: $end_value - $init_liters: >>>>' . ($end_value - $init_liters));
            return $end_value - $init_liters;
        } else {
            logger('getCounterLiters: $max_counter - $init_liters) + $end_value: >>>>' . (($max_counter - $init_liters) + $end_value));
            return ($max_counter - $init_liters) + $end_value;
        }
    }

    /**
     * obtiene el valor del ultimo registro de inicio de turno
     */
    protected function getLastShiftStartValue(): float
    {
        return $this->records()
            ->whereHas('record_type', function ($q) {
                $q->where('name', RecordType::TYPES[0]); //inicio de turno
            })
            ->whereDate('created_at', today())
            ->latest()
            ->first()?->value ?? 0;
    }

    /**
     * obtiene las ventas del empleado, las une con el proudcto para obtener los litros
     * los suma y regresa el total
     */
    public function getCurrentLitersCounter(): float
    {
        $sales_liters = $this->sales()
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->whereDate('sales.created_at', today())
            ->sum(DB::raw('products.liters * sales.amount'));

        return ($this->getLastShiftStartValue() + $sales_liters) % 9999;
    }

    /**
     * obtiene las ventas del empleado, las une con el proudcto para obtener los litros
     * los suma y regresa el total
     */
    public function getCurrentTotalSales(): float
    {
        return $this->sales()
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->whereDate('sales.created_at', today())
            ->sum(DB::raw('products.price * sales.amount'));
    }

    /**
     * @param true para obtenerlo como collection // para obtenerlo como builder
     */
    public function getCurrentSales(bool $inArray)
    {
        if ($inArray) {
            return $this->sales()->whereDate('created_at', today())->latest()->get();
        }
        return $this->sales()->with('product')->whereDate('created_at', today())->latest(); //lo retorna como builder para los datas
    }

    /**
     * The branch that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    /**
     * Get all of the records for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
    }

    /**
     * Get all of the sales for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'employee_id', 'id');
    }
}
