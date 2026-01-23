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
    private function isKasubagKeuangan($user): bool
    {
        return $user->jabatan && $user->jabatan->jenis_jabatan === 'kasubag_keuangan';
    }

    private function scopeTarget($query, $user)
    {
        if ($user->role === 'superuser' || $this->isKasubagKeuangan($user)) {
            return $query;
        }

        if (!empty($user->bidang_id) && !empty($user->seksi_id)) {
            return $query->where('bidang_id', $user->bidang_id)
                         ->where('seksi_id', $user->seksi_id);
        }

        return $query;
    }

    public function index()
    {
        $user = auth()->user();

        $targets = $this->scopeTarget(Target::query(), $user)
            ->with(['rincian', 'bidang', 'seksi']) // pastikan relasi ada di Model Target
            ->orderBy('tahun','desc')
            ->orderBy('id','desc')
            ->get();

        // supaya blade gampang (tombol approve/reject)
        $isKasubagKeu = $this->isKasubagKeuangan($user);

        return view('target.index', compact('targets', 'isKasubagKeu'));
    }

    private function authorizeKeuangan()
    {
        $user = auth()->user();

        // yang boleh approve/reject: superuser atau kasubag keuangan
        if (!($user->role === 'superuser' || $this->isKasubagKeuangan($user))) {
            abort(403, 'Hanya Kasubag Keuangan yang bisa approve/reject target.');
        }
    }

    public function approve($id)
    {
        $this->authorizeKeuangan();

        $target = Target::findOrFail($id);

        $target->update([
            'approval_status'  => 'approved',
            'approved_at'      => now(),
            'approved_by'      => auth()->id(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Target disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $this->authorizeKeuangan();

        $request->validate([
            'rejection_reason' => ['nullable','string','max:5000'],
        ]);

        $target = Target::findOrFail($id);

        $target->update([
            'approval_status'  => 'rejected',
            'approved_at'      => null,
            'approved_by'      => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Target ditolak.');
    }

    public function create()
    {
        $user = auth()->user();

        // Kasubag keuangan tidak boleh bikin target
        if ($this->isKasubagKeuangan($user)) {
            abort(403, 'Kasubag Keuangan tidak bisa membuat target.');
        }

        if ($user->role === 'superuser') {
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

        // Kasubag keuangan tidak boleh bikin target
        if ($this->isKasubagKeuangan($user)) {
            abort(403, 'Kasubag Keuangan tidak bisa membuat target.');
        }

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

        // Tentukan bidang & seksi
        if ($user->role === 'superuser') {
            $request->validate([
                'bidang_id' => 'required|exists:bidang,id',
                'seksi_id'  => 'required|exists:seksi,id',
            ]);
        } else {
            // kepala seksi / user yang punya bidang+seksi: dipaksa dari akun
            if (empty($user->bidang_id) || empty($user->seksi_id)) {
                abort(403, 'Akun ini tidak memiliki bidang/seksi.');
            }

            $request->merge([
                'bidang_id' => $user->bidang_id,
                'seksi_id'  => $user->seksi_id,
            ]);
        }

        // Validasi: seksi sesuai bidang
        $seksi = Seksi::findOrFail($request->seksi_id);
        if ((int)$seksi->bidang_id !== (int)$request->bidang_id) {
            return back()
                ->withErrors(['seksi_id' => 'Seksi tidak sesuai dengan bidang yang dipilih.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $validated) {
            $target = Target::create([
                'tahun'     => $validated['tahun'],
                'judul'     => $validated['judul'],
                'bidang_id' => $request->bidang_id,
                'seksi_id'  => $request->seksi_id,

                'approval_status'  => 'pending',
                'approved_at'      => null,
                'approved_by'      => null,
                'rejection_reason' => null,
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

            $target->update([
                'tahun' => $validated['tahun'],
                'judul' => $validated['judul'],
            ]);

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
