<?php

namespace App\Http\Controllers;

use App\DataTables\RoleDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Flash;
use Response;

class RoleController extends AppBaseController
{
    /** @var  RoleRepository */
    private $roleRepository;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->middleware('auth');
        $this->roleRepository = $roleRepo;
    }

    /**
     * Display a listing of the Role.
     *
     * @param RoleDataTable $roleDataTable
     * @return Response
     */
    public function index(RoleDataTable $roleDataTable)
    {
        try {
            $this->authorize('index', Role::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        return $roleDataTable->render('roles.index');
    }

    /**
     * Show the form for creating a new Role.
     *
     * @return Response
     */
    public function create()
    {
        try {
            $this->authorize('create', Role::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        return view('roles.create');
    }

    /**
     * Store a newly created Role in storage.
     *
     * @param CreateRoleRequest $request
     *
     * @return Response
     */
    public function store(CreateRoleRequest $request)
    {
        try {
            $this->authorize('create', Role::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        $input = $request->all();

        $role = $this->roleRepository->create($input);

        Flash::success('Role saved successfully.');

        return redirect(route('roles.index'));
    }

    /**
     * Display the specified Role.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $role = $this->roleRepository->findWithoutFail($id);

        try {
            $this->authorize('view', $role);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        return view('roles.show')->with('role', $role);
    }

    /**
     * Show the form for editing the specified Role.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $role = $this->roleRepository->findWithoutFail($id);

        try {
            $this->authorize('update', $role);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        return view('roles.edit')->with('role', $role);
    }

    /**
     * Update the specified Role in storage.
     *
     * @param  int              $id
     * @param UpdateRoleRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateRoleRequest $request)
    {
        $role = $this->roleRepository->findWithoutFail($id);
        try {
            $this->authorize('update', $role);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $role = $this->roleRepository->update($request->all(), $id);

        Flash::success('Role updated successfully.');

        return redirect(route('roles.index'));
    }

    /**
     * Remove the specified Role from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $role = $this->roleRepository->findWithoutFail($id);
        try {
            $this->authorize('delete', $role);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $this->roleRepository->delete($id);

        Flash::success('Role deleted successfully.');

        return redirect(route('roles.index'));
    }
}
