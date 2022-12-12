<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use App\Notifications\WelcomeEmailNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\Return_;

class UserController extends Controller
{
    public function signup(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:100',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation fails',
                'errors' => $validator->errors()
            ], 422);
        }



        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

        ]);

        // $admins = User::where('is_admin', 1)->get();

        $user->notify(new WelcomeEmailNotification);

        // Notification::send($admins, new RegisteredUserNotification($user));

        // event(new Registered($user));

        $token = $user->createToken('authtoken');
        return response()->json([
            'message' => 'Registration Sucessfull',
            'data' => ['token' => $token->plainTextToken, 'user' => $user],
            'errors' => $user
        ], 200);
    }


    public function validateRegister(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return \redirect(route('home'))->with('success', 'registered successfully');
        }

        return response()->json([
            'message' => ' verification Sucessfull',

        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'email' => 'required|email',
            'password' => 'required'

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation fails',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('auth-token')->plainTextToken;

                return response()->json([
                    'message' => 'Login Successfull',
                    'token' => $token,
                    'data' => $user
                ], 200);
            } else {
                return response()->json([
                    'message' => 'incorrect credentials'
                ], 400);
            }
        }
    }


    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'profile_photo' => 'nullable|image|mimes:jpg,png,bmp',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation fails',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        if (!$request->has('profile_photo')) {
            return response()->json(['message' => 'Missing file'], 422);
        }
        $file = $request->file('image');
        $url = Storage::putFileAs('profile_photo', $file . '.' . $file->extension());



        $product = $user->update([
            'name' => $request->name,
            'profile_photo' => env('APP_URL') . '/' . $url,
        ]);


        return response()->json([
            'message' => 'profile successfully updates',
            'photo' => $product
        ], 200);
    }


    public function forgetPassword(Request $request)
    {
        try {
            $user=User::where('email',$request->email)->get();

            if(count($user)>0){


        $token = $user->createToken('authtoken');
                $domain =URL::to('/');
                $url =$domain.'./reset-password?token='.$token;

                $data['url']=$url;
                $data['email']=$request->email;
                $data['title']="password reset";
                $data['body']="please click on link below to reset your password";


                Mail::send('forgetPasswordMail',['data'=>$data],function($message)use ($data){
                    $message->to($data['email'])->subject($data['title']);
                });

                $datetime = Carbon::now()->format('Y-m-d H:i:s');
                PasswordReset::updateOrCreate(
                    [
                        'email'=>$request->email
                    ],
                    [
                        'email'=>$request->email,
                        'token'=>$token,
                        'created_at'=>$datetime
                    ]
                );

                return response()->json(['succcess'=>true,'msg'=>'please check your email to reset password']);

            }

            else{
                return response()->json(['succcess'=>false,'msg'=>'user not found']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e]);
        }
    }
}
