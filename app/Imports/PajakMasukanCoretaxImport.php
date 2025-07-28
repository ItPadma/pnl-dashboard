<?php

namespace App\Imports;

use App\Models\PajakMasukanCoretax;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class PajakMasukanCoretaxImport implements ToCollection
{

    /**
     * @param array $collection
     */
    public function collection(\Illuminate\Support\Collection $collection)
    {
        foreach ($collection as $index => $row) {
            if ($row[0] == 'npwp_penjual') {
                continue; // Skip header row
            }

            try {
                $existingData = PajakMasukanCoretax::where('nomor_faktur_pajak', $row[2])->first();
                if ($existingData) {
                    $existingData->update([
                        'npwp_penjual' => $row[0],
                        'nama_penjual' => $row[1],
                        'nomor_faktur_pajak' => $row[2],
                        'tanggal_faktur_pajak' => $row[3],
                        'masa_pajak' => $row[4],
                        'tahun' => $row[5],
                        'masa_pajak_pengkreditkan' => $row[6],
                        'tahun_pajak_pengkreditan' => $row[7],
                        'status_faktur' => $row[8],
                        'harga_jual_dpp' => $row[9],
                        'dpp_nilai_lain' => $row[10],
                        'ppn' => $row[11],
                        'ppnbm' => $row[12],
                        'perekam' => $row[13],
                        'nomor_sp2d' => $row[14],
                        'valid' => isset($row[15]) ? (bool)$row[15] : false,
                        'dilaporkan' => isset($row[16]) ? (bool)$row[16] : false,
                        'dilaporkan_oleh_penjual' => isset($row[17]) ? (bool)$row[17] : false,
                    ]);
                } else {
                    PajakMasukanCoretax::create([
                        'npwp_penjual' => $row[0],
                        'nama_penjual' => $row[1],
                        'nomor_faktur_pajak' => $row[2],
                        'tanggal_faktur_pajak' => $row[3],
                        'masa_pajak' => $row[4],
                        'tahun' => $row[5],
                        'masa_pajak_pengkreditkan' => $row[6],
                        'tahun_pajak_pengkreditan' => $row[7],
                        'status_faktur' => $row[8],
                        'harga_jual_dpp' => $row[9],
                        'dpp_nilai_lain' => $row[10],
                        'ppn' => $row[11],
                        'ppnbm' => $row[12],
                        'perekam' => $row[13],
                        'nomor_sp2d' => $row[14],
                        'valid' => isset($row[15]) ? (bool)$row[15] : false,
                        'dilaporkan' => isset($row[16]) ? (bool)$row[16] : false,
                        'dilaporkan_oleh_penjual' => isset($row[17]) ? (bool)$row[17] : false,
                    ]);
                    Log::info("Row $index imported successfully.");
                }
            } catch (\Throwable $th) {
                Log::error("Error importing row $index: " . $th->getMessage());
            }
        }
    }


    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            if (!isset($row['no_invoice']) || empty($row['no_invoice'])) {
                return null; // Skip if no invoice number
            }

            $existingData = PajakMasukanCoretax::where('no_invoice', $row['no_invoice'])->first();
            if ($existingData) {
                $existingData->update([
                    'npwp_penjual' => $row['npwp_penjual'] ?? null,
                    'nama_penjual' => $row['nama_penjual'] ?? null,
                    'nomor_faktur_pajak' => $row['nomor_faktur_pajak'] ?? null,
                    'tanggal_faktur_pajak' => $row['tanggal_faktur_pajak'] ?? null,
                    'masa_pajak' => $row['masa_pajak'] ?? null,
                    'tahun' => $row['tahun'] ?? null,
                    'masa_pajak_pengkreditkan' => $row['masa_pajak_pengkreditkan'] ?? null,
                    'tahun_pajak_pengkreditan' => $row['tahun_pajak_pengkreditan'] ?? null,
                    'status_faktur' => $row['status_faktur'] ?? null,
                    'harga_jual_dpp' => $row['harga_jual_dpp'] ?? null,
                    'dpp_nilai_lain' => $row['dpp_nilai_lain'] ?? null,
                    'ppn' => $row['ppn'] ?? null,
                    'ppnbm' => $row['ppnbm'] ?? null,
                    'perekam' => $row['perekam'] ?? null,
                    'nomor_sp2d' => $row['nomor_sp2d'] ?? null,
                    'valid' => isset($row['valid']) ? (bool)$row['valid'] : false,
                    'dilaporkan' => isset($row['dilaporkan']) ? (bool)$row['dilaporkan'] : false,
                    'dilaporkan_oleh_penjual' => isset($row['dilaporkan_oleh_penjual']) ? (bool)$row['dilaporkan_oleh_penjual'] : false,
                ]);
                return null;
            } else {
                return new PajakMasukanCoretax([
                    'npwp_penjual' => $row['npwp_penjual'] ?? null,
                    'nama_penjual' => $row['nama_penjual'] ?? null,
                    'nomor_faktur_pajak' => $row['nomor_faktur_pajak'] ?? null,
                    'tanggal_faktur_pajak' => $row['tanggal_faktur_pajak'] ?? null,
                    'masa_pajak' => $row['masa_pajak'] ?? null,
                    'tahun' => $row['tahun'] ?? null,
                    'masa_pajak_pengkreditkan' => $row['masa_pajak_pengkreditkan'] ?? null,
                    'tahun_pajak_pengkreditan' => $row['tahun_pajak_pengkreditan'] ?? null,
                    'status_faktur' => $row['status_faktur'] ?? null,
                    'harga_jual_dpp' => $row['harga_jual_dpp'] ?? null,
                    'dpp_nilai_lain' => $row['dpp_nilai_lain'] ?? null,
                    'ppn' => $row['ppn'] ?? null,
                    'ppnbm' => $row['ppnbm'] ?? null,
                    'perekam' => $row['perekam'] ?? null,
                    'nomor_sp2d' => $row['nomor_sp2d'] ?? null,
                    'valid' => isset($row['valid']) ? (bool)$row['valid'] : false,
                    'dilaporkan' => isset($row['dilaporkan']) ? (bool)$row['dilaporkan'] : false,
                    'dilaporkan_oleh_penjual' => isset($row['dilaporkan_oleh_penjual']) ? (bool)$row['dilaporkan_oleh_penjual'] : false,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error("Error importing row: " . $th->getMessage());
            return null;
        }
    }
}
