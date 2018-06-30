<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Traits\UserRegisterTraits;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Flash\Flash;
use League\Csv\Reader;
use Response;

class UserController extends AppBaseController
{
    use UserRegisterTraits;
    /** @var  UserRepository */
    private $userRepository;

    /** @var RoleRepository */
    private $role;

    public function __construct(UserRepository $userRepo, Role $roleModel)
    {
        $this->middleware('auth');
        $this->userRepository = $userRepo;
        $this->role = $roleModel;
    }

    /**
     * Display a listing of the User.
     *
     * @param UserDataTable $userDataTable
     * @return Response
     */
    public function index(UserDataTable $userDataTable)
    {
        try {
            $this->authorize('index', User::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        return $userDataTable->render('users.index');
    }

    /**
     * Show the form for creating a new User.
     *
     * @return Response
     */
    public function create()
    {
        try {
            $this->authorize('create', User::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        $roles = $this->role->pluck('description', 'id');
        return view('users.create')->with('roles', $roles);
    }

    /**
     * Store a newly created User in storage.
     *
     * @param CreateUserRequest $request
     *
     * @return Response
     */
    public function store(CreateUserRequest $request)
    {
        try {
            $this->authorize('create', User::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if ($request->submit == 'Import CSV'){

            $this->importUser($request);

            Flash::success('User Uploaded successfully.');

            return redirect(route('users.index'));
        }

        $input = $request->all();

        $input['password'] = bcrypt($input['password']);
        $user = $this->userRepository->create($input);
        Flash::success('User saved successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Display the specified User.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $user = $this->userRepository->findWithoutFail($id);
        try {
            $this->authorize('view', $user);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        return view('users.show')->with('user', $user);
    }

    /**
     * Show the form for editing the specified User.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $user = $this->userRepository->findWithoutFail($id);
        try {
            $this->authorize('update', $user);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        $roles = $this->role->pluck('description', 'id');

        return view('users.edit')->with('user', $user)->with('roles', $roles);
    }

    /**
     * Update the specified User in storage.
     *
     * @param  int              $id
     * @param UpdateUserRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateUserRequest $request)
    {
        $user = $this->userRepository->findWithoutFail($id);
        try {
            $this->authorize('update', $user);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        if (empty($user)) {
            Flash::error('User not found');

            return redirect()->back()->withInput();
        }

        $input = $request->all();

        $new_role = $this->role->find($request->input('role_id'));

        if (empty($new_role)) {
            Flash::error('Updating role not found');

            return redirect()->back()->withInput();
        }
        $auth = Auth::user();
        if ($new_role->level > $auth->role->level) {
            Flash::error("You are not allowed to update to " . $new_role->description);

            return redirect()->back()->withInput();
        }

        if (array_key_exists('password', $input) && !empty($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        } else {
            unset($input['password']);
        }

        $user = $this->userRepository->update($input, $id);

        Flash::success('User updated successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Remove the specified User from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $user = $this->userRepository->findWithoutFail($id);
        try {
            $this->authorize('delete', $user);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        $this->userRepository->delete($id);

        Flash::success('User deleted successfully.');

        return redirect(route('users.index'));
    }

    /**
     * import from csv
     * @param string $request
     */
    public function importUser($request)
    {

        if ( $request->file('usercsv'))
        {

            $file = $request->file('usercsv');

            $fileName = $file->getClientOriginalName();

            $saveFile    = $file->storeAs('user',$fileName);

            $path = storage_path('app/'.$saveFile );

            $csv = Reader::createFromPath($path, 'r')
                ->setHeaderOffset(0);

            foreach($csv as $row){


                User::firstOrcreate([
                    'name' => $row['name'],
                    'code' => $row['code'],
                    'username' => $row['username'],
                    'password' => bcrypt($row['password']),
                    'email' => $row['email'],
                    'role_id'   => 5 //Data Entry Clerk Role_Id 5
                ]);

            }
        }



    }
}
