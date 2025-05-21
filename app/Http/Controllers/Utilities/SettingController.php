<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function usermanIndex()
    {
        return view('settings.userman');
    }

    public function usermanShow(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $user = User::find($request->id);
            return response()->json([
                'status' => true,
                'message' => 'User Found',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'role' => 'required',
                'depo' => 'required'
            ]);
            $new_user = new User();
            $new_user->name = $request->name;
            $new_user->email = $request->email;
            $new_user->password = bcrypt($request->password);
            $new_user->role = $request->role;
            # $depo is array, so we need to convert it to string

            $new_user->depo = implode("|", $request->depo);
            $new_user->save();
            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'data' => $new_user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanUpdate(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'name' => 'required',
                'email' => 'required',
                'role' => 'required',
                'depo' => 'required'
            ]);
            $user = User::find($request->id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->has('password') && $request->password != '') {
                $user->password = bcrypt($request->password);
            }
            $user->role = $request->role;
            $user->depo = implode("|", $request->depo);
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanDelete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $user = User::find($request->id);
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'User Deleted Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanChangePassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required',
            ]);
            $userId = Auth::user()->id;
            $user = User::find($userId);
            $user->password = bcrypt($request->password);
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Password Changed Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}
