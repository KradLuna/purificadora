<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordRequest;
use App\Models\Record;
use App\Models\RecordType;
use App\Models\User;
use Carbon\Carbon;

class RecordController extends Controller
{

    public function data()
    {
        $user = auth()->user();
        $query = Record::with(['user', 'record_type']);
        // Si es empleado, solo ve sus registros y del día
        if ($user->hasRole(User::ROLES[1])) {
            $query->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->latest();
        }
        return datatables()->eloquent($query)
            ->addColumn('id', function ($row) {
                return $row->id;
            })
            ->addColumn('user.full_name', function ($row) use ($user) {
                // Solo enviar el nombre si es admin
                return $user->hasRole(User::ROLES[0]) ? $row->user->full_name : '';
            })->addColumn('actions', function ($row) {
                // Renderiza el partial blade y pasa la fila actual 
                return view('records.actions', compact('row'))->render();
            })->editColumn('created_at', function ($record) {
                return $record->created_at ? $record->created_at->locale('es')->isoFormat('dddd hh:mm A | D MMMM YYYY') : '';
            })->addColumn('evidence', function ($row) {
                return view('records.evidence', compact('row'))->render();
            })->editColumn('value', function ($row) {
                return $row->value ?? 'N/A';
            })->rawColumns(['actions', 'evidence'])->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->isAdmin) {
            // Admin can see all records
            $records = Record::with(['user', 'record_type'])
                ->latest()
                ->paginate(10);
        } else {
            // Employees can only see their own records
            $records = Record::with(['user', 'record_type'])
                ->where('user_id', auth()->id())
                ->whereDate('created_at', today())
                ->latest()
                ->paginate(10);
        }

        return view('records.index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $recordTypes = RecordType::getActiveTypes();
        return view('records.create', compact('recordTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RecordRequest $request)
    {
        try {
            $result = Record::logicalStore($request->validated());

            if (!$result['success']) {
                return redirect()
                    ->route('records.index')
                    ->withErrors(['error' => $result['message']])
                    ->withInput(); // Esto preserva los valores del formulario
            }
            return redirect()
                ->route('records.index')
                ->with('success', 'Registro creado correctamente');
        } catch (\Exception $e) {
            return redirect()
                ->route('records.index')
                ->withErrors(['error' => $e->getMessage()])
                ->withInput(); // Esto preserva los valores del formulario
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function show(Record $record)
    {
        // Solo admin o dueño puede ver
        logger(auth()->user()->isAdmin);
        if (!auth()->user()->isAdmin && auth()->id() !== $record->user_id) {
            abort(403);
        }
        return view('records.show', compact('record'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function edit(Record $record)
    {
        // Solo admin o dueño puede editar
        if (!auth()->user()->isAdmin && auth()->id() !== $record->user_id) {
            abort(403);
        }

        $recordTypes = RecordType::getActiveTypes();
        return view('records.edit', compact('record', 'recordTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function update(RecordRequest $request, Record $record)
    {
        // Solo admin o dueño puede actualizar
        $data = $request->validated();
        if (!auth()->user()->isAdmin && auth()->id() !== $record->user_id) {
            abort(403);
        }
        // Solo si viene record_date (admins)
        if ($request->filled('record_date')) {
            $data['created_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $request->record_date)
                ->format('Y-m-d H:i:s');
        }
        $record->logicalUpdate($data);

        return redirect()->route('records.index')->with('success', 'Registro actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function destroy(Record $record)
    {
        if (!auth()->user()->isAdmin) {
            abort(403);
        }

        $record->logicalDelete();

        return redirect()->route('records.index')->with('success', 'Registro eliminado correctamente.');
    }
}
