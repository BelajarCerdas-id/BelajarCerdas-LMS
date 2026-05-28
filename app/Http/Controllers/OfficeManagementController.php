<?php

namespace App\Http\Controllers;

use App\Events\OfficeAccountManagement;
use App\Models\OfficeProfile;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OfficeManagementController extends Controller
{
    public function manageUserView($role)
    {
        return view('features.lms.administrator.office-management.manage-user', compact('role'));
    }

    public function paginateManageUser(Request $request, $role)
    {
        $search = $request->search_account;

        $data = OfficeProfile::with(['UserAccount'])
            ->when($search, function ($query) use ($search) {
                $query->where('nama_lengkap', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'links' => (string) $data->links(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
        ]);
    }

    public function manageUserStore(Request $request, $role)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'email' => 'required|email|unique:user_accounts,email|regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
            'no_hp' => 'required',
            'password' => 'required',
            'role' => 'required',
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email harus @belajarcerdas.id.',
            'email.regex' => 'Format email harus @belajarcerdas.id.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.required' => 'No HP harus diisi.',
            'password.required' => 'Password harus diisi.',
            'role.required' => 'Role harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userAccount = UserAccount::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'no_hp' => $request->no_hp,
            'role' => $request->role,
        ]);

        OfficeProfile::create([
            'user_id' => $userAccount->id,
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        broadcast(new OfficeAccountManagement('UserAccount', 'create', $userAccount))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    public function manageUserEdit(Request $request, $role, $userAccountId)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
                Rule::unique('user_accounts', 'email')->ignore($userAccountId),
            ],
            'no_hp' => 'required',
            'role' => 'required',
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email harus @belajarcerdas.id.',
            'email.regex' => 'Format email harus @belajarcerdas.id.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.required' => 'No HP harus diisi.',
            'role.required' => 'Role harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = UserAccount::findOrFail($userAccountId);

        $data->update([
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'role' => $request->role,
        ]);

        OfficeProfile::where('user_id', $userAccountId)->update([
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        broadcast(new OfficeAccountManagement('UserAccount', 'edit', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diubah.',
        ]);
    }

    public function manageUserActivate(Request $request, $role, $userAccountId)
    {
        $data = UserAccount::findOrFail($userAccountId);

        $data->update([
            'status_akun' => $request->status_akun,
        ]);

        broadcast(new OfficeAccountManagement('UserAccount', 'activate', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diubah.',
        ]);
    }
}
