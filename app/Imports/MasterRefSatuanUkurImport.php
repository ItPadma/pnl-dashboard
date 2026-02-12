<?php

namespace App\Imports;

use App\Models\MasterRefSatuanUkur;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class MasterRefSatuanUkurImport implements OnEachRow, WithChunkReading
{
    public function onRow(Row $row): void
    {
        try {
            if ($row->getIndex() === 1 || $row->getIndex() % 250 === 0) {
                Log::info('Import row received', [
                    'row_number' => $row->getIndex(),
                    'import' => 'satuan-ukur',
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

            MasterRefSatuanUkur::updateOrCreate(
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
