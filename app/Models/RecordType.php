<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecordType extends Model
{
    use HasFactory;

    public const TYPES = [
        'Inicio de turno', //id_1
        'Corte de caja',
        'Purga',
        'Fin de turno',
        'Cloro',
        'PH',
    ];



    /**
     * retorna todos los tipos de eventos activos para poder realizar registros
     */
    public static function getActiveTypes()
    {
        return RecordType::where('is_active', true)->get();
    }

    /**
     * atributo que valida si ya existe corte de caja para poder hacer una venta
     */
    public function isEndWorkShift(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name == self::TYPES[3] ? true : false,
        );
    }

    /**
     * Get all of the Records for the RecordType
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Records(): HasMany
    {
        return $this->hasMany(Record::class);
    }
}
