<?php

namespace App\Imports;

use App\Models\MasterPkp;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class MasterPkpImport implements ToCollection
{
    private function normalizeDigitsOnly(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }

    /**
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
                $isNewTemplate = isset($row[5]);
                $nikValue = $isNewTemplate ? ($row[3] ?? null) : null;
                $noPkpValue = $isNewTemplate ? ($row[4] ?? null) : ($row[3] ?? null);
                $typePajakValue = $isNewTemplate ? ($row[5] ?? null) : ($row[4] ?? null);

                $existingData = MasterPkp::where('IDPelanggan', $row[0])->first();
                if ($existingData) {
                    $payload = [
                        'NamaPKP' => $row[1],
                        'AlamatPKP' => $row[2],
                        'NoPKP' => $this->normalizeDigitsOnly($noPkpValue),
                        'TypePajak' => $typePajakValue,
                    ];

                    // Untuk template lama (tanpa kolom NIK), jangan overwrite NIK existing.
                    if ($isNewTemplate) {
                        $payload['NIK'] = $this->normalizeDigitsOnly($nikValue);
                    }

                    $existingData->update($payload);
                } else {
                    MasterPkp::create([
                        'IDPelanggan' => $row[0],
                        'NamaPKP' => $row[1],
                        'AlamatPKP' => $row[2],
                        'NIK' => $this->normalizeDigitsOnly($nikValue),
                        'NoPKP' => $this->normalizeDigitsOnly($noPkpValue),
                        'TypePajak' => $typePajakValue,
                    ]);
                }
                Log::info("Row $index imported or updated successfully.");
            } catch (\Throwable $th) {
                Log::error("Error on row $index: ".$th->getMessage());
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Pastikan data tidak kosong
            if (! isset($row['idpelanggan']) || empty($row['idpelanggan'])) {
                return null;
            }

            $existingData = MasterPkp::where('IDPelanggan', $row['idpelanggan'])->first();
            if ($existingData) {
                $existingData->update([
                    'NamaPKP' => $row['namapkp'] ?? null,
                    'AlamatPKP' => $row['alamatpkp'] ?? null,
                    'NIK' => $this->normalizeDigitsOnly($row['nik'] ?? null),
                    'NoPKP' => $this->normalizeDigitsOnly($row['nopkp'] ?? null),
                    'TypePajak' => $row['typepajak'] ?? null,
                ]);

                return null;
            } else {
                return new MasterPkp([
                    'IDPelanggan' => $row['idpelanggan'],
                    'NamaPKP' => $row['namapkp'] ?? null,
                    'AlamatPKP' => $row['alamatpkp'] ?? null,
                    'NIK' => $this->normalizeDigitsOnly($row['nik'] ?? null),
                    'NoPKP' => $this->normalizeDigitsOnly($row['nopkp'] ?? null),
                    'TypePajak' => $row['typepajak'] ?? null,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('Error importing row: '.$th->getMessage());

            return null;
        }
    }
}
