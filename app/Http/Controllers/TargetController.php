<?php

namespace App\Http\Controllers;

use App\Models\Target;
use App\Models\Seksi;
use App\Models\Bidang;
use Illuminate\Http\Request;
use App\Models\TargetRincian;
use Illuminate\Support\Facades\DB;

class TargetController extends Controller
{
    private function scopeTarget($query, $user)
    {
        if ($user->role === 'superuser') return $query;

        return $query->where('bidang_id', $user->bidang_id)
                     ->where('seksi_id', $user->seksi_id);
    }

    public function index()
    {
        $user = auth()->user();

        $targets = $this->scopeTarget(Target::query(), $user)
            ->with('rincian')
            ->orderBy('tahun','desc')
            ->orderBy('id','desc')
            ->get();


        return view('target.index', compact('targets'));
    }

    public function create()
    {
        if (auth()->user()->role === 'superuser') {
            $bidangs = Bidang::orderBy('nama')->get();
            $seksis  = Seksi::orderBy('nama')->get();
        } else {
            $bidangs = [];
            $seksis  = [];
        }

        return view('target.create', compact('bidangs', 'seksis'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'tahun' => ['required','integer'],
            'judul' => ['required','string','max:255'],

            'output_uraian' => ['required','string'],
            'output_target' => ['required','string'],

            'outcome_uraian' => ['required','string'],
            'outcome_target' => ['required','string'],

            'sasaran_uraian' => ['required','string'],
            'sasaran_target' => ['required','string'],

            'keuangan_uraian' => ['required','string'],
            'keuangan_target' => ['required','string'],
        ]);

        // 2) Tentukan bidang_id & seksi_id berdasarkan role
        if ($user->role === 'superuser') {
            $request->validate([
                'bidang_id' => 'required|exists:bidang,id',
                'seksi_id'  => 'required|exists:seksi,id',
            ]);
        } else {
            // user/kepala seksi: paksa dari akun
            $request->merge([
                'bidang_id' => $user->bidang_id,
                'seksi_id'  => $user->seksi_id,
            ]);

            // optional: kalau ada role yang tidak punya bidang/seksi, cegah
            if (empty($user->bidang_id) || empty($user->seksi_id)) {
                abort(403, 'Akun ini tidak memiliki bidang/seksi.');
            }
        }

        // 3) Validasi relasi: seksi harus sesuai bidang
        $seksi = Seksi::findOrFail($request->seksi_id);
        if ((int)$seksi->bidang_id !== (int)$request->bidang_id) {
            return back()
                ->withErrors(['seksi_id' => 'Seksi tidak sesuai dengan bidang yang dipilih.'])
                ->withInput();
        }

        // 4) Simpan target + rincian dalam transaksi
        DB::transaction(function () use ($request, $validated) {

            $target = Target::create([
                'tahun'     => $validated['tahun'],
                'judul'     => $validated['judul'],
                'bidang_id' => $request->bidang_id,
                'seksi_id'  => $request->seksi_id,
            ]);

            $now = now();

            TargetRincian::insert([
                ['target_id'=>$target->id,'jenis'=>'output','uraian'=>$validated['output_uraian'],'target'=>$validated['output_target'],'created_at'=>$now,'updated_at'=>$now],
                ['target_id'=>$target->id,'jenis'=>'outcome','uraian'=>$validated['outcome_uraian'],'target'=>$validated['outcome_target'],'created_at'=>$now,'updated_at'=>$now],
                ['target_id'=>$target->id,'jenis'=>'sasaran','uraian'=>$validated['sasaran_uraian'],'target'=>$validated['sasaran_target'],'created_at'=>$now,'updated_at'=>$now],
                ['target_id'=>$target->id,'jenis'=>'keuangan','uraian'=>$validated['keuangan_uraian'],'target'=>$validated['keuangan_target'],'created_at'=>$now,'updated_at'=>$now],
            ]);
        });

        return redirect()->route('target.index')->with('success','Target rencana berhasil dibuat.');
    }

    public function edit($id)
    {
        $user = auth()->user();

        $target = $this->scopeTarget(Target::query(), $user)
            ->with('rincian')
            ->where('id', $id)
            ->firstOrFail();

        return view('target.edit', compact('target'));
    }

public function update(Request $request, $id)
{
    $user = auth()->user();

    $target = $this->scopeTarget(Target::query(), $user)
        ->with('rincian')
        ->where('id', $id)
        ->firstOrFail();

    $validated = $request->validate([
        'tahun' => ['required','integer'],
        'judul' => ['required','string','max:255'],

        'output_uraian' => ['required','string'],
        'output_target' => ['required','string'],

        'outcome_uraian' => ['required','string'],
        'outcome_target' => ['required','string'],

        'sasaran_uraian' => ['required','string'],
        'sasaran_target' => ['required','string'],

        'keuangan_uraian' => ['required','string'],
        'keuangan_target' => ['required','string'],
    ]);

    DB::transaction(function () use ($target, $validated) {

        // update header target
        $target->update([
            'tahun' => $validated['tahun'],
            'judul' => $validated['judul'],
        ]);

        // update / create rincian
        TargetRincian::updateOrCreate(
            ['target_id'=>$target->id,'jenis'=>'output'],
            ['uraian'=>$validated['output_uraian'],'target'=>$validated['output_target']]
        );

        TargetRincian::updateOrCreate(
            ['target_id'=>$target->id,'jenis'=>'outcome'],
            ['uraian'=>$validated['outcome_uraian'],'target'=>$validated['outcome_target']]
        );

        TargetRincian::updateOrCreate(
            ['target_id'=>$target->id,'jenis'=>'sasaran'],
            ['uraian'=>$validated['sasaran_uraian'],'target'=>$validated['sasaran_target']]
        );

        TargetRincian::updateOrCreate(
            ['target_id'=>$target->id,'jenis'=>'keuangan'],
            ['uraian'=>$validated['keuangan_uraian'],'target'=>$validated['keuangan_target']]
        );
    });

    return redirect()->route('target.index')->with('success', 'Target berhasil diupdate.');
}

    public function destroy($id)
    {
        $user = auth()->user();

        $target = $this->scopeTarget(Target::query(), $user)
            ->where('id', $id)
            ->firstOrFail();

        $target->delete();

        return redirect()->route('target.index')->with('success', 'Target berhasil dihapus.');
    }
}
