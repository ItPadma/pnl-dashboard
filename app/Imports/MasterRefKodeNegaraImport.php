<?php

namespace App\Imports;

use App\Models\MasterRefKodeNegara;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class MasterRefKodeNegaraImport implements OnEachRow, WithChunkReading
{
    public function onRow(Row $row): void
    {
        try {
            if ($row->getIndex() === 1 || $row->getIndex() % 250 === 0) {
                Log::info('Import row received', [
                    'row_number' => $row->getIndex(),
                    'import' => 'kode-negara',
                ]);
            }

            $rowData = $row->toArray();
            if ($row->getIndex() === 1) {
                $firstCell = isset($rowData[0]) ? trim((string) $rowData[0]) : '';
                if (strtolower($firstCell) === 'kode') {
                    return;
                }
            }

            $kode = isset($rowData[0]) ? trim((string) $rowData[0]) : '';
            if ($kode === '') {
                return;
            }

            $keterangan = isset($rowData[1]) ? trim((string) $rowData[1]) : null;
            $keterangan = $keterangan === '' ? null : $keterangan;

            MasterRefKodeNegara::updateOrCreate(
                ['kode' => $kode],
                ['keterangan' => $keterangan],
            );
        } catch (\Throwable $th) {
            Log::error('Import row failed', [
                'row_number' => $row->getIndex(),
                'exception' => $th,
            ]);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
