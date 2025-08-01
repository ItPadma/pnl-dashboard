<?php

namespace App\Http\Controllers\PNL;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilities\LogController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MasterDataController extends Controller
{
    public function getBrands(Request $request)
    {
        try {
            if ($request->has('companies') && !empty($request->companies)) {
                $companies = $request->companies;
                $brands = \App\Models\MasterBrand::whereHas('multiCompProdMappings', function ($query) use ($companies) {
                    $query->whereIn('szCompanyID', $companies);
                })->get();
                $msg = 'Data retrieved successfully for specified companies: ' . implode(', ', $companies);
            } elseif ($request->has('company') && !empty($request->company)) {
                $brands = \App\Models\MasterBrand::whereHas('multiCompProdMappings', function ($query) use ($request) {
                    $query->where('szCompanyID', $request->company);
                })->get();
                $msg = 'Data retrieved successfully for company: ' . $request->company;
            } else {
                $brands = \App\Models\MasterBrand::all();
                $msg = 'All brands retrieved successfully';
            }
            return response()->json([
                'status' => true,
                'message' => $msg,
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

    /**
     * Handle DataTables AJAX request for users data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request)
    {
        try {
            $query = \App\Models\User::query();

            // Get total count before filtering
            $totalRecords = $query->count();

            // Search functionality
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('email', 'like', "%{$searchValue}%");
                    // Add more searchable columns as needed
                });
            }

            // Get count after filtering
            $filteredRecords = $query->count();

            // Ordering
            if ($request->has('order') && !empty($request->order)) {
                $columnIndex = $request->order[0]['column'];
                $columnName = $request->columns[$columnIndex]['data'];
                $columnDirection = $request->order[0]['dir'];

                $query->orderBy($columnName, $columnDirection);
            } else {
                // Default ordering
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $query->skip($request->start)->take($request->length);

            // Get results
            $users = $query->get();

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $users,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $th->getMessage()
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
            LogController::createLog($request->user()->id, 'Import Master PKP', 'Import Master PKP', '-', 'master_pkp', 'info', $request);
            return redirect()->back()->with('success', 'Data imported successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
