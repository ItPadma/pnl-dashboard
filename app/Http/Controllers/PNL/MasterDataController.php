<?php

namespace App\Http\Controllers\PNL;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MasterDataController extends Controller
{
    public function getBrands(Request $request)
    {
        try {
            $brands = \App\Models\MasterBrand::all();
            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $brands,
            ])->setStatusCode(200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving data',
                'error' => $th->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function getDepo(Request $request)
    {
        try {
            $depos = \App\Models\MasterDepo::all();
            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $depos,
            ])->setStatusCode(200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving data',
                'error' => $th->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function getCompanies(Request $request)
    {
        try {
            $companies = \App\Models\MasterCompany::all();
            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $companies,
            ])->setStatusCode(200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving data',
                'error' => $th->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function indexMasterPKP()
    {
        return view('pnl.import.pkp');
    }

    public function importMasterPKP(Request $request)
    {
        try {
            $file = $request->file('file');
            $path = $file->store('public/import');
            Excel::import(new \App\Imports\MasterPKPImport, $path);
            return redirect()->back()->with('success', 'Data imported successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
