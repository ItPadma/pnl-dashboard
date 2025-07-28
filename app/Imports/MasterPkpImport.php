<?php

namespace App\Imports;

use App\Models\MasterPkp;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class MasterPkpImport implements ToCollection
{
    /**
     * @param \Illuminate\Support\Collection $collection
     *
     * @return void
     */
    public function collection(\Illuminate\Support\Collection $collection)
    {
        foreach ($collection as $index => $row) {
            // jika nilai dari kolom pertama = IDPelanggan maka skip
            if ($row[0] == 'idpelanggan') {
                continue;
            }

            try {
                $existingData = MasterPkp::where('IDPelanggan', $row[0])->first();
                if ($existingData) {
                    $existingData->update([
                        'NamaPKP' => $row[1],
                        'AlamatPKP' => $row[2],
                        'NoPKP' => $row[3],
                        'TypePajak' => $row[4],
                    ]);
                } else {
                    MasterPkp::create([
                        'IDPelanggan' => $row[0],
                        'NamaPKP' => $row[1],
                        'AlamatPKP' => $row[2],
                        'NoPKP' => $row[3],
                        'TypePajak' => $row[4],
                    ]);
                }
                Log::info("Row $index imported or updated successfully.");
            } catch (\Throwable $th) {
                Log::error("Error on row $index: " . $th->getMessage());
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
            // Pastikan data tidak kosong
            if (!isset($row['idpelanggan']) || empty($row['idpelanggan'])) {
                return null;
            }

            $existingData = MasterPkp::where('IDPelanggan', $row['idpelanggan'])->first();
            if ($existingData) {
                $existingData->update([
                    'NamaPKP' => $row['namapkp'] ?? null,
                    'AlamatPKP' => $row['alamatpkp'] ?? null,
                    'NoPKP' => $row['nopkp'] ?? null,
                    'TypePajak' => $row['typepajak'] ?? null,
                ]);
                return null;
            } else {
                return new MasterPkp([
                    'IDPelanggan' => $row['idpelanggan'],
                    'NamaPKP' => $row['namapkp'] ?? null,
                    'AlamatPKP' => $row['alamatpkp'] ?? null,
                    'NoPKP' => $row['nopkp'] ?? null,
                    'TypePajak' => $row['typepajak'] ?? null,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error("Error importing row: " . $th->getMessage());
            return null;
        }
    }
}
