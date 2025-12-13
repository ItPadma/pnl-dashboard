<?php

namespace App\Imports;

use App\Models\PajakMasukanCoretax;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class PajakMasukanCoretaxImport implements ToCollection
{
    private $insertedCount = 0;
    private $updatedCount = 0;
    private $errorCount = 0;
    private $errorMessages = [];

    /**
     * @param \Illuminate\Support\Collection $collection
     */
    public function collection(\Illuminate\Support\Collection $collection)
    {
        foreach ($collection as $index => $row) {
            if ($row[0] == 'NPWP Pembeli / Identitas lainnya') {
                continue; // Skip header row
            }

            try {
                // Parse date with multiple formats
                $dateValue = $row[4];
                $parsedDate = null;
                $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'm-d-Y', 'Y-m-d\TH:i:s'];

                foreach ($formats as $format) {
                    try {
                        $parsedDate = Carbon::createFromFormat($format, $dateValue);
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if (!$parsedDate) {
                    // Try to parse as Carbon parse for other formats
                    try {
                        $parsedDate = Carbon::parse($dateValue);
                    } catch (\Exception $e) {
                        throw new \Exception("Invalid date format for tanggal_faktur_pajak: {$dateValue}");
                    }
                }

                // Helper function to sanitize numeric values
                $sanitizeNumeric = function($value) {
                    if (is_numeric($value)) {
                        return $value;
                    }
                    return null;
                };

                // Helper function to sanitize boolean values
                $sanitizeBoolean = function($value) {
                    if (is_numeric($value)) {
                        return (bool) $value;
                    }
                    return false;
                };

                $existingData = PajakMasukanCoretax::where('nomor_faktur_pajak', $row[3])->first();

                if ($existingData) {
                    $existingData->update([
                        'npwp_penjual' => (string) $row[0],
                        'nama_penjual' => $row[1],
                        'kode_transaksi' => (string) $row[2],
                        'nomor_faktur_pajak' => $row[3],
                        'tanggal_faktur_pajak' => $parsedDate,
                        'masa_pajak' => $row[5],
                        'tahun' => $sanitizeNumeric($row[6]),
                        'masa_pajak_pengkreditkan' => $row[7],
                        'tahun_pajak_pengkreditan' => $sanitizeNumeric($row[8]),
                        'status_faktur' => $row[9],
                        'harga_jual_dpp' => $sanitizeNumeric($row[10]),
                        'dpp_nilai_lain' => $sanitizeNumeric($row[11]),
                        'ppn' => $sanitizeNumeric($row[12]),
                        'ppnbm' => $sanitizeNumeric($row[13]),
                        'perekam' => $row[14],
                        'nomor_sp2d' => $row[15],
                        'valid' => isset($row[16]) ? $sanitizeBoolean($row[16]) : false,
                        'dilaporkan' => isset($row[17]) ? $sanitizeBoolean($row[17]) : false,
                        'dilaporkan_oleh_penjual' => isset($row[18]) ? $sanitizeBoolean($row[18]) : false,
                    ]);
                    $this->updatedCount++;
                    Log::info("Row $index updated (duplicate found).");
                } else {
                    PajakMasukanCoretax::create([
                        'npwp_penjual' => (string) $row[0],
                        'nama_penjual' => $row[1],
                        'kode_transaksi' => (string) $row[2],
                        'nomor_faktur_pajak' => $row[3],
                        'tanggal_faktur_pajak' => $parsedDate,
                        'masa_pajak' => $row[5],
                        'tahun' => $sanitizeNumeric($row[6]),
                        'masa_pajak_pengkreditkan' => $row[7],
                        'tahun_pajak_pengkreditan' => $sanitizeNumeric($row[8]),
                        'status_faktur' => $row[9],
                        'harga_jual_dpp' => $sanitizeNumeric($row[10]),
                        'dpp_nilai_lain' => $sanitizeNumeric($row[11]),
                        'ppn' => $sanitizeNumeric($row[12]),
                        'ppnbm' => $sanitizeNumeric($row[13]),
                        'perekam' => $row[14],
                        'nomor_sp2d' => $row[15],
                        'valid' => isset($row[16]) ? $sanitizeBoolean($row[16]) : false,
                        'dilaporkan' => isset($row[17]) ? $sanitizeBoolean($row[17]) : false,
                        'dilaporkan_oleh_penjual' => isset($row[18]) ? $sanitizeBoolean($row[18]) : false,
                    ]);
                    $this->insertedCount++;
                    Log::info("Row $index imported successfully.");
                }
            } catch (\Throwable $th) {
                $this->errorCount++;
                $this->errorMessages[] = "Row " . ($index + 1) . ": " . $th->getMessage();
                Log::error("Error importing row $index: " . $th->getMessage());
            }
        }

        // Log summary
        Log::info("Import Summary - Inserted: {$this->insertedCount}, Updated (Duplicates): {$this->updatedCount}, Errors: {$this->errorCount}");
    }

    /**
     * Get the number of successfully inserted records
     */
    public function getInsertedCount()
    {
        return $this->insertedCount;
    }

    /**
     * Get the number of updated records (duplicates)
     */
    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }

    /**
     * Get the number of errors
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * Get the error messages
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }
}
