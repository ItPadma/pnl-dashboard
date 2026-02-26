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

    private function hasExactSixteenDigits(?string $value): bool
    {
        return $value !== null && strlen($value) === 16;
    }

    private function validateNikOrNoPkp(?string $nikDigits, ?string $noPkpDigits, ?int $excelRow = null): void
    {
        if ($this->hasExactSixteenDigits($nikDigits) || $this->hasExactSixteenDigits($noPkpDigits)) {
            return;
        }

        if ($excelRow !== null) {
            throw new \InvalidArgumentException("Baris {$excelRow}: NIK atau NoPKP wajib 16 digit.");
        }

        throw new \InvalidArgumentException('NIK atau NoPKP wajib 16 digit.');
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
                $normalizedNik = $this->normalizeDigitsOnly($nikValue);
                $normalizedNoPkp = $this->normalizeDigitsOnly($noPkpValue);

                $this->validateNikOrNoPkp($normalizedNik, $normalizedNoPkp, $index + 1);

                $existingData = MasterPkp::where('IDPelanggan', $row[0])->first();
                if ($existingData) {
                    $payload = [
                        'NamaPKP' => $row[1],
                        'AlamatPKP' => $row[2],
                        'NoPKP' => $normalizedNoPkp,
                        'TypePajak' => $typePajakValue,
                    ];

                    // Untuk template lama (tanpa kolom NIK), jangan overwrite NIK existing.
                    if ($isNewTemplate) {
                        $payload['NIK'] = $normalizedNik;
                    }

                    $existingData->update($payload);
                } else {
                    MasterPkp::create([
                        'IDPelanggan' => $row[0],
                        'NamaPKP' => $row[1],
                        'AlamatPKP' => $row[2],
                        'NIK' => $normalizedNik,
                        'NoPKP' => $normalizedNoPkp,
                        'TypePajak' => $typePajakValue,
                    ]);
                }
                Log::info("Row $index imported or updated successfully.");
            } catch (\Throwable $th) {
                Log::error("Error on row $index: ".$th->getMessage());

                if ($th instanceof \InvalidArgumentException) {
                    throw $th;
                }
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

            $normalizedNik = $this->normalizeDigitsOnly($row['nik'] ?? null);
            $normalizedNoPkp = $this->normalizeDigitsOnly($row['nopkp'] ?? null);
            $this->validateNikOrNoPkp($normalizedNik, $normalizedNoPkp);

            $existingData = MasterPkp::where('IDPelanggan', $row['idpelanggan'])->first();
            if ($existingData) {
                $existingData->update([
                    'NamaPKP' => $row['namapkp'] ?? null,
                    'AlamatPKP' => $row['alamatpkp'] ?? null,
                    'NIK' => $normalizedNik,
                    'NoPKP' => $normalizedNoPkp,
                    'TypePajak' => $row['typepajak'] ?? null,
                ]);

                return null;
            } else {
                return new MasterPkp([
                    'IDPelanggan' => $row['idpelanggan'],
                    'NamaPKP' => $row['namapkp'] ?? null,
                    'AlamatPKP' => $row['alamatpkp'] ?? null,
                    'NIK' => $normalizedNik,
                    'NoPKP' => $normalizedNoPkp,
                    'TypePajak' => $row['typepajak'] ?? null,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('Error importing row: '.$th->getMessage());

            if ($th instanceof \InvalidArgumentException) {
                throw $th;
            }

            return null;
        }
    }
}
