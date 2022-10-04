<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use App\DataTables\Users\UsersDataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Account\SettingsInfoRequest;
use Illuminate\Support\Facades\Storage;
use Faker\Generator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('pages.users.index');

        // $info = User::join('user_infos', 'users.id', '=', 'user_infos.user_id')
        //     ->select([
        //         'users.*',
        //         DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"),
        //         DB::raw("substr(users.first_name, 0, 1) as label"),
        //         "users.updated_at as last_login",
        //         "users.created_at as joined_day",
        //         'user_infos.*'])->get();

        // $config = theme()->getOption('page');
        // return view('pages.users.index', compact('info'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Generator $faker, SettingsInfoRequest $request)
    {
        $user = User::create([
            'first_name'        => $request->first_name,
            'last_name'         => $request->last_name,
            'email'             => $faker->email,
            'password'          => Hash::make('demo'),
            'email_verified_at' => now(),
            'api_token'         => Str::random(60),
            'remember_token'    => Str::random(60),
        ]);

        $requestInfo = [
            'company'  => $request->company,
            'phone'    => $request->phone,
            'phone_2'  => $faker->phoneNumber,
            'website'  => $request->website,
            'language' => $request->language,
            'country'  => $request->country,
            'currency' => $request->currency,
            'position' => $faker->jobTitle,
        ];

        $info = new UserInfo();
        foreach ($requestInfo as $key => $value) {
            $info->$key = $value;
        }
        $info->user()->associate($user);
        $info->save();
        
        return response()->json([
            'message'=>'User Created Successfully!! Password is demo'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //$config = theme()->getOption('page', 'edit');
        $info = User::with('info')->find($id);
        return response()->json([
            'info'=> $info
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */

    public function update(SettingsInfoRequest $request)
    {
        // save user name
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
        ]);

        //auth()->user()->update($validated);
        User::find($request->id)->update($validated);

        // save on user info
        $info = UserInfo::where('user_id', $request->id)->first();

        if ($info === null) {
            // create new model
            $info = new UserInfo();
        }

        foreach ($request->only(array_keys($request->rules())) as $key => $value) {
            if (is_array($value)) {
                $value = serialize($value);
            }
            $info->$key = $value;
        }

        // include to save avatar
        if ($avatar = $this->upload()) {
            $info->avatar = $avatar;
        }

        if ($request->boolean('avatar_remove')) {
            Storage::delete($info->avatar);
            $info->avatar = null;
        }

        $info->save();
        return redirect()->intended('users');
    }

    public function upload($folder = 'images', $key = 'avatar', $validation = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048|sometimes')
    {
        request()->validate([$key => $validation]);

        $file = null;
        if (request()->hasFile($key)) {
            $file = Storage::disk('public')->putFile($folder, request()->file($key), 'public');
        }

        return $file;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $config = theme()->getOption('page');

        return User::find($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        UserInfo::find($id)->delete();;
    }
}
