<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportReferensiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $type,
        private readonly string $path,
        private readonly ?int $userId = null,
        private readonly ?string $originalName = null,
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Queue import referensi started', [
                'type' => $this->type,
                'user_id' => $this->userId,
                'stored_path' => $this->path,
                'disk' => 'local',
            ]);
            if (! Storage::disk('local')->exists($this->path)) {
                Log::error('Queue import referensi file missing', [
                    'type' => $this->type,
                    'user_id' => $this->userId,
                    'stored_path' => $this->path,
                    'disk' => 'local',
                ]);

                return;
            }

            $import = $this->resolveReferensiImport($this->type);
            if ($import === null) {
                Log::error('Queue import referensi invalid type', [
                    'type' => $this->type,
                    'user_id' => $this->userId,
                ]);

                return;
            }

            Excel::import($import, Storage::disk('local')->path($this->path));

            Log::info('Queue import referensi finished', [
                'type' => $this->type,
                'user_id' => $this->userId,
            ]);
        } catch (\Throwable $th) {
            Log::error('Queue import referensi failed', [
                'type' => $this->type,
                'exception' => $th,
            ]);
        } finally {
            Storage::disk('local')->delete($this->path);
        }
    }

    private function resolveReferensiImport(string $type)
    {
        return match ($type) {
            'tipe' => new \App\Imports\MasterRefTipeImport,
            'kode-transaksi' => new \App\Imports\MasterRefKodeTransaksiImport,
            'keterangan-tambahan' => new \App\Imports\MasterRefKeteranganTambahanImport,
            'id-pembeli' => new \App\Imports\MasterRefIdPembeliImport,
            'satuan-ukur' => new \App\Imports\MasterRefSatuanUkurImport,
            'kode-negara' => new \App\Imports\MasterRefKodeNegaraImport,
            default => null,
        };
    }
}
