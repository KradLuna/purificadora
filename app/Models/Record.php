<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Record extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'record_type_id',
        'value',
        'created_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @param $data 
     * record_type_id
     * value
     * evidence
     */

    public static function logicalStore(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = auth()->user();
            $rule = self::rules($data['record_type_id'], $user);
            if ($rule == "ok") {
                $record = new Record($data);
                $record->user_id = $user->id;
                if (isset($data['evidence'])) {
                    $record->evidence_path = $data['evidence']->store('evidences', 'public');
                }
                $record->save();

                if ($record->record_type->is_end_work_shift) { // cierre de turno
                    if (!auth()->user()->saleVsCounterIsOk()) {
                        throw new Exception('El contador de litros y el conteo de ventas tiene un desfase mayor a 20L.');
                    }
                }
                return [
                    'success' => true,
                    'sale' => $record
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $rule
                ];
            }
        });
    }

    public function logicalUpdate(array $data)
    {
        DB::transaction(function () use ($data) {
            // Si se sube un nuevo archivo
            if (isset($data['evidence'])) {
                // Eliminar evidencia anterior si existe
                $this->deleteFile();
                $this->evidence_path = $data['evidence']->store('evidences', 'public');
                $this->save();
            }
            $this->update($data);
        });
    }

    public function logicalDelete(): void
    {
        // Eliminar evidencia si existe
        $this->deleteFile();
        $this->delete();
    }

    protected function deleteFile(): void
    {
        if ($this->evidence_path && Storage::disk('public')->exists($this->evidence_path)) {
            Storage::disk('public')->delete($this->evidence_path);
        }
    }

    protected static function rules(String $actual_action, User $user): String
    {
        /*array{
        0: 'Inicio de turno', 
        1: 'Corte de caja', 
        2: 'Purga', 
        3: 'Fin de turno', 
        4: 'Cloro', 
        5: 'PH'
        }*/
        $requiredName = RecordType::find($actual_action)->name;
        // Buscar registros del día actual
        $todayRecords = Record::join('record_types', 'records.record_type_id', '=', 'record_types.id')
            ->where('records.user_id', $user->id)
            ->whereDate('records.created_at', today())
            ->pluck('record_types.name')
            ->toArray();

        if (in_array($requiredName, $todayRecords)) {
            return "El registro $requiredName ya se realizó el día de hoy.";
        }
        /**
         * Si el requisito es ['A','B'] → ambos deben existir.
         * Si el requisito es [['A','B']] → basta con que exista uno.
         */
        $requirements = [
            RecordType::TYPES[1] => [RecordType::TYPES[0]],
            RecordType::TYPES[2] => [RecordType::TYPES[0]],
            RecordType::TYPES[3] => [[RecordType::TYPES[0], RecordType::TYPES[2]]], //
            RecordType::TYPES[4] => [RecordType::TYPES[0]],
            RecordType::TYPES[5] => [RecordType::TYPES[0]],
        ];
        if (isset($requirements[$requiredName])) {
            foreach ($requirements[$requiredName] as $requiredType) {
                if (is_array($requiredType)) {
                    // OR → al menos uno debe estar
                    $ok = false;
                    foreach ($requiredType as $type) {
                        if (in_array($type, $todayRecords)) {
                            $ok = true;
                            break;
                        }
                    }
                    if (!$ok) {
                        return "Debes realizar primero uno de: " . implode(" o ", $requiredType);
                    }
                } else {
                    // AND → debe estar sí o sí
                    if (!in_array($requiredType, $todayRecords)) {
                        return "Debes realizar primero: $requiredType";
                    }
                }
            }
        }
        return "ok";
    }
    /**
     * RELATIONSHIPS
     */
    /**
     * Get the user that owns the Record
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the record_type that owns the Record
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function record_type(): BelongsTo
    {
        return $this->belongsTo(RecordType::class);
    }
}
