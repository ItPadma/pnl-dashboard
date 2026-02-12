<?php

namespace App\Imports;

use App\Models\MasterRefKodeTransaksi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class MasterRefKodeTransaksiImport implements ToCollection
{
    public function collection(Collection $collection)
    {
        foreach ($collection as $index => $row) {
            if ($index === 0 || ($row[0] ?? null) === 'kode') {
                continue;
            }

            if (! isset($row[0]) || trim((string) $row[0]) === '') {
                continue;
            }

            try {
                $existing = MasterRefKodeTransaksi::where('kode', $row[0])->first();
                $payload = [
                    'kode' => $row[0],
                    'keterangan' => $row[1] ?? null,
                ];

                if ($existing) {
                    $existing->update($payload);
                } else {
                    MasterRefKodeTransaksi::create($payload);
                }
            } catch (\Throwable $th) {
                Log::error("Error on row {$index}: {$th->getMessage()}");
            }
        }
    }
}
