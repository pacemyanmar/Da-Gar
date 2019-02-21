<?php
namespace App\Traits;

use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Laracasts\Flash\Flash;
use Validator;

trait UserRegisterTraits
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'username' => 'required|alpha_num|max:255|unique:users',
            'password' => 'required|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function storeUser(Request $request, $register = false)
    {
        $input = $request->all();
        $this->validator($request->all())->validate();

        $confirmation_code = str_random(30);
        $input['confirmation_code'] = $confirmation_code;

        if ($register) {
            $guest = Role::where('role_name', 'guest')->first();
            $input['role_id'] = $guest->id;
        }

        $user = $this->userRepository->create($input);

        if ($user->id === 1) {
            $admin_role = Role::where('role_name', 'admin')->first();
            $admin_role->user()->save($user);
        }

        Mail::send('email.verify', compact('user', $user), function ($message) use ($user) {
            $message->from('no-reply@site.com', "Test Site");
            $message->subject("Welcome to site name");
            $message->to($user->email);
        });
        Flash::info('We sent you an activation code. Check your email.');
        if (isset($redirectTo)) {

            return redirect($this->redirectPath());
        }
        return redirect()->back();
    }

    /**
     * confirm user email
     * @param  string $confirmation_code confirmation code string from user email
     * @return Redirect::class
     */
    public function confirm($confirmation_code)
    {
        if (!$confirmation_code) {
            Flash::message('Error');

            return redirect($this->redirectTo);
            // throw new InvalidConfirmationCodeException;
        }

        $user = User::whereConfirmationCode($confirmation_code)->first();

        if (!$user) {
            Flash::message('Invalid your Confirmation Code', 'danger');

            return redirect($this->redirectTo);
            // throw new InvalidConfirmationCodeException;
        }

        $user->confirmed = 1;
        $user->confirmation_code = null;
        $user->save();

        Flash::message('You have successfully verified your account.Please login again.');

        return redirect($this->redirectTo);
    }
}
