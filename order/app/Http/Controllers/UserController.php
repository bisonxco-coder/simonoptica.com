<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller {
    private $validOrderBy = ['id', 'email', 'name', 'role'];
    private $validOrder = ['asc', 'desc'];

    private $validatorMessages = [
        'name.required' => 'name.required',
        'name.max' => 'name.max',
        'email.required' => 'email.required',
        'email.email' => 'email.email',
        'email.max' => 'email.max',
        'email.unique' => 'email.unique',
        'password.required' => 'password.required',
        'password.min' => 'password.min',
        'password.max' => 'password.max',
        'agree_terms.required' => 'agree_terms.required',
        'agree_terms.accepted' => 'agree_terms.accepted'
    ];

    /**
     * Get users (admin)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $orderBy = $request->input('orderby', 'name');
        $order = $request->input('order', 'asc');
        $perPage = $request->input('limit', 25);
        $role = $request->input('role', '');
        $search = $request->input('search', '');

        $orderBy = strtolower($orderBy);
        $order = strtolower($order);

        if(!in_array($orderBy, $this->validOrderBy)) $orderBy = 'name';
        if(!in_array($order, $this->validOrder)) $order = 'asc';

        $users = User::orderBy($orderBy, $order);
        if ($role != '') $users->where('role', '=', $role);
        if ($search != '') $users->where('name', 'LIKE', '%' . $search . '%');
        return $users->paginate($perPage);
    }

    /**
     * Get user (admin)
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user) {
        return response()->json($user, 200);
    }

    /**
     * Add user (admin)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|min:6|max:191',
            'role' => 'required'
        ], $this->validatorMessages);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->role = $request->input('role');
        $user->started = false;
        $user->save();

        return response()->json($user, 200);
    }

    /**
     * Update user (admin)
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user) {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|max:191',
            'email' => 'sometimes|required|email|max:191|unique:users,email',
            'password' => 'sometimes|required|min:6|max:191',
            'role' => 'sometimes|required'
        ], $this->validatorMessages);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $newPass = $request->input('password', '');
        $user->name = $request->input('name', $user->name);
        $user->email = $request->input('email', $user->email);
        $user->role = $request->input('role', $user->role);
        if ($newPass != '') $user->password = bcrypt($newPass);
        $user->update();

        return response()->json($user, 200);
    }

    /**
     * Delete user (admin)
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user) {
        if (!$user->isRole('admin')) {
            $user->delete();
        }
        return response()->json(null, 200);
    }

    /**
     * Get own data
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request) {
        $user = $request->user();
        if (!$user) return response()->json([], 400);
        return response()->json($user, 200);
    }

    /**
     * Update own data
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateOwn(Request $request) {
        $user = $request->user();
        if (!$user) return response()->json([], 400);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|max:191',
            'email' => 'sometimes|required|email|max:191|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|confirmed|min:6|max:191'
        ], $this->validatorMessages);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $newPass = $request->input('password', '');
        $user->name = $request->input('name', $user->name);
        $user->email = $request->input('email', $user->email);
        if ($newPass != '') $user->password = bcrypt($newPass);
        $user->update();

        return response()->json($user, 200);
    }
}
