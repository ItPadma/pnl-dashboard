<?php

namespace App\Http\Controllers\PNL;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilities\LogController;
use App\Jobs\ImportReferensiJob;
use App\Models\AccessGroup;
use App\Models\MasterPkp;
use App\Models\MasterRefIdPembeli;
use App\Models\MasterRefKeteranganTambahan;
use App\Models\MasterRefKodeNegara;
use App\Models\MasterRefKodeTransaksi;
use App\Models\MasterRefSatuanUkur;
use App\Models\MasterRefTipe;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class MasterDataController extends Controller
{
    public function getBrands(Request $request)
    {
        try {
            if ($request->has('companies') && ! empty($request->companies)) {
                $companies = $request->companies;
                $brands = \App\Models\MasterBrand::whereHas('multiCompProdMappings', function ($query) use ($companies) {
                    $query->whereIn('szCompanyID', $companies);
                })->get();
                $msg = 'Data retrieved successfully for specified companies: '.implode(', ', $companies);
            } elseif ($request->has('company') && ! empty($request->company)) {
                $brands = \App\Models\MasterBrand::whereHas('multiCompProdMappings', function ($query) use ($request) {
                    $query->where('szCompanyID', $request->company);
                })->get();
                $msg = 'Data retrieved successfully for company: '.$request->company;
            } else {
                $brands = \App\Models\MasterBrand::all();
                $msg = 'All brands retrieved successfully';
            }

            return response()->json([
                'status' => true,
                'message' => $msg,
                'data' => $brands,
            ])->setStatusCode(200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving data',
                'error' => $th->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function getDepo(Request $request)
    {
        try {
            $depos = \App\Models\MasterDepo::all();

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $depos,
            ])->setStatusCode(200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving data',
                'error' => $th->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function getCompanies(Request $request)
    {
        try {
            $companies = \App\Models\MasterCompany::all();

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $companies,
            ])->setStatusCode(200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving data',
                'error' => $th->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Handle DataTables AJAX request for users data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request)
    {
        try {
            $query = \App\Models\User::query();

            // Get total count before filtering
            $totalRecords = $query->count();

            // Search functionality
            if ($request->has('search') && ! empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('email', 'like', "%{$searchValue}%");
                    // Add more searchable columns as needed
                });
            }

            // Get count after filtering
            $filteredRecords = $query->count();

            // Ordering
            if ($request->has('order') && ! empty($request->order)) {
                $columnIndex = $request->order[0]['column'];
                $columnName = $request->columns[$columnIndex]['data'];
                $columnDirection = $request->order[0]['dir'];

                $query->orderBy($columnName, $columnDirection);
            } else {
                // Default ordering
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $query->skip($request->start)->take($request->length);

            // Get results
            $users = $query->get();

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $users,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $th->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function indexMasterPKP()
    {
        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-import-pkp',
                AccessGroup::LEVEL_READ_WRITE,
            )
            && ! Auth::user()?->canAccessMenu(
                'master-data-import-pkp',
                AccessGroup::LEVEL_READ,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $query = MasterPkp::query();
        $this->applyPkpDepoFilter($query);
        $pkpList = $query->orderByDesc('is_active')->orderBy('IDPelanggan')->get();

        return view('pnl.import.pkp', [
            'pkpList' => $pkpList,
            'canEdit' => Auth::user()?->canAccessMenu(
                'master-data-import-pkp',
                AccessGroup::LEVEL_READ_WRITE,
            ) ?? false,
        ]);
    }

    public function showMasterPKP($id)
    {
        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-import-pkp',
                AccessGroup::LEVEL_READ_WRITE,
            )
            && ! Auth::user()?->canAccessMenu(
                'master-data-import-pkp',
                AccessGroup::LEVEL_READ,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $query = MasterPkp::query();
        $this->applyPkpDepoFilter($query);
        $pkp = $query->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $pkp,
        ]);
    }

    public function updateMasterPKP(Request $request, $id)
    {
        try {
            if (
                ! Auth::user()?->canAccessMenu(
                    'master-data-import-pkp',
                    AccessGroup::LEVEL_READ_WRITE,
                )
            ) {
                abort(403, 'Unauthorized action.');
            }

            $validated = $request->validate([
                'NamaPKP' => 'required|string|max:255',
                'AlamatPKP' => 'nullable|string|max:255',
                'NoPKP' => 'nullable|string|max:255',
                'TypePajak' => 'nullable|string|max:255',
            ]);

            $query = MasterPkp::query();
            $this->applyPkpDepoFilter($query);
            $pkp = $query->findOrFail($id);
            $pkp->update($validated);

            LogController::createLog(
                Auth::user()->id,
                'Update Master PKP',
                'Update Master PKP',
                '{id: '.$pkp->id.', IDPelanggan: '.$pkp->IDPelanggan.'}',
                'master_pkp',
                'info',
                $request,
            );

            return redirect()
                ->route('pnl.master-data.index.master-pkp')
                ->with('success', 'Data PKP berhasil diperbarui.');
        } catch (\Throwable $th) {
            Log::error('Update master PKP failed', [
                'id' => $id,
                'error' => $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function deleteMasterPKP(Request $request, $id)
    {
        try {
            if (
                ! Auth::user()?->canAccessMenu(
                    'master-data-import-pkp',
                    AccessGroup::LEVEL_READ_WRITE,
                )
            ) {
                abort(403, 'Unauthorized action.');
            }

            $query = MasterPkp::query();
            $this->applyPkpDepoFilter($query);
            $pkp = $query->findOrFail($id);
            $pkp->update(['is_active' => false]);

            LogController::createLog(
                Auth::user()->id,
                'Disable Master PKP',
                'Disable Master PKP',
                '{id: '.$pkp->id.', IDPelanggan: '.$pkp->IDPelanggan.'}',
                'master_pkp',
                'info',
                $request,
            );

            return redirect()
                ->route('pnl.master-data.index.master-pkp')
                ->with('success', 'Data PKP berhasil dinonaktifkan.');
        } catch (\Throwable $th) {
            Log::error('Disable master PKP failed', [
                'id' => $id,
                'error' => $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function toggleMasterPKP(Request $request, $id)
    {
        try {
            if (
                ! Auth::user()?->canAccessMenu(
                    'master-data-import-pkp',
                    AccessGroup::LEVEL_READ_WRITE,
                )
            ) {
                abort(403, 'Unauthorized action.');
            }

            $request->validate([
                'is_active' => 'required|boolean',
            ]);

            $query = MasterPkp::query();
            $this->applyPkpDepoFilter($query);
            $pkp = $query->findOrFail($id);
            $pkp->update(['is_active' => (bool) $request->is_active]);

            $actionLabel = $pkp->is_active ? 'Enable' : 'Disable';

            LogController::createLog(
                Auth::user()->id,
                $actionLabel.' Master PKP',
                $actionLabel.' Master PKP',
                '{id: '.$pkp->id.', IDPelanggan: '.$pkp->IDPelanggan.'}',
                'master_pkp',
                'info',
                $request,
            );

            return redirect()
                ->route('pnl.master-data.index.master-pkp')
                ->with(
                    'success',
                    $pkp->is_active ? 'Data PKP berhasil diaktifkan.' : 'Data PKP berhasil dinonaktifkan.',
                );
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }
    }

    public function importMasterPKP(Request $request)
    {
        try {
            if (
                ! Auth::user()?->canAccessMenu(
                    'master-data-import-pkp',
                    AccessGroup::LEVEL_READ_WRITE,
                )
            ) {
                abort(403, 'Unauthorized action.');
            }

            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:5120',
            ]);

            $file = $request->file('file');
            $path = $file->store('public/import');
            Excel::import(new \App\Imports\MasterPKPImport, $path);
            LogController::createLog($request->user()->id, 'Import Master PKP', 'Import Master PKP', '-', 'master_pkp', 'info', $request);

            return redirect()->back()->with('success', 'Data imported successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function indexReferensi()
    {
        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ_WRITE,
            )
            && ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        return view('pnl.import.referensi', [
            'refTipe' => MasterRefTipe::orderByDesc('is_active')->orderBy('kode')->get(),
            'refKodeTransaksi' => MasterRefKodeTransaksi::orderByDesc('is_active')->orderBy('kode')->get(),
            'refKeteranganTambahan' => MasterRefKeteranganTambahan::with('kodeTransaksi')
                ->orderByDesc('is_active')
                ->orderBy('kode')
                ->get(),
            'refIdPembeli' => MasterRefIdPembeli::orderByDesc('is_active')->orderBy('kode')->get(),
            'refSatuanUkur' => MasterRefSatuanUkur::orderByDesc('is_active')->orderBy('kode')->get(),
            'refKodeNegara' => MasterRefKodeNegara::orderByDesc('is_active')->orderBy('kode')->get(),
            'canEdit' => Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ_WRITE,
            ) ?? false,
        ]);
    }

    public function showReferensi(string $type, $id)
    {
        $this->assertReferensiType($type);

        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ_WRITE,
            )
            && ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $model = $this->resolveReferensiModel($type);
        $query = $model->newQuery();

        if ($type === 'keterangan-tambahan') {
            $query->with('kodeTransaksi');
        }

        $item = $query->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $item,
        ]);
    }

    public function updateReferensi(Request $request, string $type, $id)
    {
        $this->assertReferensiType($type);

        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ_WRITE,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $this->validateReferensi($request, $type, (int) $id);

        try {
            $model = $this->resolveReferensiModel($type);
            $item = $model->findOrFail($id);
            $item->update($validated);

            LogController::createLog(
                Auth::user()->id,
                'Update Master Referensi',
                'Update Master Referensi',
                '{id: '.$item->id.', tipe: '.$type.'}',
                'master_referensi',
                'info',
                $request,
            );

            return redirect()
                ->route('pnl.master-data.index.referensi')
                ->with('success', 'Data referensi berhasil diperbarui.');
        } catch (\Throwable $th) {
            Log::error('Update referensi failed', [
                'type' => $type,
                'id' => $id,
                'exception' => $th,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function storeReferensi(Request $request, string $type)
    {
        $this->assertReferensiType($type);

        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ_WRITE,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $this->validateReferensi($request, $type);

        try {
            $validated['is_active'] = true;

            $model = $this->resolveReferensiModel($type);
            $item = $model->create($validated);

            LogController::createLog(
                Auth::user()->id,
                'Create Master Referensi',
                'Create Master Referensi',
                '{id: '.$item->id.', tipe: '.$type.'}',
                'master_referensi',
                'info',
                $request,
            );

            return redirect()
                ->route('pnl.master-data.index.referensi')
                ->with('success', 'Data referensi berhasil ditambahkan.');
        } catch (\Throwable $th) {
            Log::error('Store referensi failed', [
                'type' => $type,
                'exception' => $th,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function toggleReferensi(Request $request, string $type, $id)
    {
        $this->assertReferensiType($type);

        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ_WRITE,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        try {

            $request->validate([
                'is_active' => 'required|boolean',
            ]);

            $model = $this->resolveReferensiModel($type);
            $item = $model->findOrFail($id);
            $item->update(['is_active' => (bool) $request->is_active]);

            $actionLabel = $item->is_active ? 'Enable' : 'Disable';

            LogController::createLog(
                Auth::user()->id,
                $actionLabel.' Master Referensi',
                $actionLabel.' Master Referensi',
                '{id: '.$item->id.', tipe: '.$type.'}',
                'master_referensi',
                'info',
                $request,
            );

            return redirect()
                ->route('pnl.master-data.index.referensi')
                ->with(
                    'success',
                    $item->is_active ? 'Data referensi berhasil diaktifkan.' : 'Data referensi berhasil dinonaktifkan.',
                );
        } catch (\Throwable $th) {
            Log::error('Toggle referensi failed', [
                'type' => $type,
                'id' => $id,
                'exception' => $th,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function importReferensi(Request $request, string $type)
    {
        if (
            ! Auth::user()?->canAccessMenu(
                'master-data-referensi',
                AccessGroup::LEVEL_READ_WRITE,
            )
        ) {
            abort(403, 'Unauthorized action.');
        }

        $this->assertReferensiType($type);

        try {
            Log::info('Import referensi started', [
                'type' => $type,
                'user_id' => $request->user()?->id,
            ]);

            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:5120',
            ]);

            $file = $request->file('file');
            $path = $file->store('import', ['disk' => 'local']);
            if (! $path) {
                Log::error('Import referensi store failed', [
                    'type' => $type,
                    'disk' => 'local',
                ]);

                return redirect()->back()->with('error', 'Gagal menyimpan file import.');
            }

            $absolutePath = Storage::disk('local')->path($path);
            $importDir = dirname($absolutePath);
            if (! chmod($importDir, 0755)) {
                Log::error('Import referensi chmod failed', [
                    'type' => $type,
                    'path' => $importDir,
                    'target' => 'dir',
                    'error' => error_get_last(),
                ]);
            }

            if (! chmod($absolutePath, 0644)) {
                Log::error('Import referensi chmod failed', [
                    'type' => $type,
                    'path' => $absolutePath,
                    'target' => 'file',
                    'error' => error_get_last(),
                ]);
            }

            if (! is_readable($absolutePath)) {
                Log::error('Import referensi file not readable', [
                    'type' => $type,
                    'path' => $absolutePath,
                ]);

                return redirect()->back()->with('error', 'File import tidak dapat dibaca.');
            }
            Log::info('Import referensi file received', [
                'type' => $type,
                'stored_path' => $path,
                'disk' => 'local',
            ]);

            if (! Storage::disk('local')->exists($path)) {
                Log::error('Import referensi stored file missing', [
                    'type' => $type,
                    'stored_path' => $path,
                    'disk' => 'local',
                ]);

                return redirect()->back()->with('error', 'File import tidak ditemukan setelah diunggah.');
            }

            LogController::createLog(
                $request->user()->id,
                'Queue Import Master Referensi',
                'Queue Import Master Referensi',
                '{tipe: '.$type.'}',
                'master_referensi',
                'info',
                $request,
            );

            ImportReferensiJob::dispatch(
                $type,
                $path,
                $request->user()?->id,
                $file?->getClientOriginalName(),
            );

            return redirect()->back()->with('success', 'Import sedang diproses di background.');
        } catch (\Throwable $th) {
            Log::error('Import referensi failed', [
                'type' => $type,
                'exception' => $th,
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    private function applyPkpDepoFilter($query): void
    {
        $userInfo = getLoggedInUserInfo();
        $userDepos = $userInfo ? $userInfo->depo : [];
        $userDepos = $this->normalizeUserDepos($userDepos);

        if ($userDepos === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        if (! in_array('all', $userDepos, true)) {
            $query->whereIn(DB::raw('LEFT(IDPelanggan, 3)'), $userDepos);
        }
    }

    private function normalizeUserDepos($value): array
    {
        $value = array_values(array_filter(array_map(function ($item) {
            return is_string($item) ? trim($item) : $item;
        }, Arr::wrap($value)), function ($item) {
            return $item !== null && $item !== '';
        }));

        if (in_array('all', $value, true)) {
            return ['all'];
        }

        return $value;
    }

    private function resolveReferensiModel(string $type)
    {
        return match ($type) {
            'tipe' => new MasterRefTipe,
            'kode-transaksi' => new MasterRefKodeTransaksi,
            'keterangan-tambahan' => new MasterRefKeteranganTambahan,
            'id-pembeli' => new MasterRefIdPembeli,
            'satuan-ukur' => new MasterRefSatuanUkur,
            'kode-negara' => new MasterRefKodeNegara,
            default => abort(404, 'Tipe referensi tidak ditemukan.'),
        };
    }

    private function assertReferensiType(string $type): void
    {
        abort_unless(in_array($type, $this->allowedReferensiTypes(), true), 404, 'Tipe referensi tidak ditemukan.');
    }

    private function allowedReferensiTypes(): array
    {
        return [
            'tipe',
            'kode-transaksi',
            'keterangan-tambahan',
            'id-pembeli',
            'satuan-ukur',
            'kode-negara',
        ];
    }

    private function validateReferensi(Request $request, string $type, ?int $id = null): array
    {
        $request->merge([
            'kode' => is_string($request->input('kode')) ? trim($request->input('kode')) : $request->input('kode'),
        ]);

        $table = $this->resolveReferensiModel($type)->getTable();
        $kodeUniqueRule = Rule::unique($table, 'kode');
        if ($id !== null) {
            $kodeUniqueRule = $kodeUniqueRule->ignore($id);
        }

        $rules = [
            'kode' => [
                'required',
                'string',
                'max:255',
                $kodeUniqueRule,
            ],
            'keterangan' => 'nullable|string|max:255',
        ];

        if ($type === 'keterangan-tambahan') {
            $rules['kode_transaksi_id'] = 'nullable|string|max:255|exists:master_ref_kode_transaksi,kode';
        }

        return $request->validate($rules);
    }
}
