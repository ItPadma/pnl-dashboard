<?php

namespace App\Imports;

use App\Models\MasterRefKeteranganTambahan;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class MasterRefKeteranganTambahanImport implements OnEachRow, WithChunkReading
{
    public function onRow(Row $row): void
    {
        try {
            if ($row->getIndex() === 1 || $row->getIndex() % 250 === 0) {
                Log::info('Import row received', [
                    'row_number' => $row->getIndex(),
                    'import' => 'keterangan-tambahan',
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

            $kodeTransaksi = isset($rowData[1]) ? trim((string) $rowData[1]) : '';
            $kodeTransaksi = $kodeTransaksi === '' ? null : $kodeTransaksi;

            if ($kodeTransaksi && ! \App\Models\MasterRefKodeTransaksi::where('kode', $kodeTransaksi)->exists()) {
                Log::warning('Invalid kode_transaksi_id on row', [
                    'row_number' => $row->getIndex(),
                    'kode_transaksi_id' => $kodeTransaksi,
                ]);

                return;
            }

            $keterangan = isset($rowData[2]) ? trim((string) $rowData[2]) : null;
            $keterangan = $keterangan === '' ? null : $keterangan;

            MasterRefKeteranganTambahan::updateOrCreate(
                [
                    'kode' => $kode,
                    'kode_transaksi_id' => $kodeTransaksi,
                ],
                [
                    'keterangan' => $keterangan,
                ],
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
