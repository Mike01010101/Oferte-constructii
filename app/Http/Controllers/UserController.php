<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Afișează lista utilizatorilor din firmă.
     */
    public function index()
    {
        $users = Auth::user()->company->users()->with('roles')->paginate(15);
        return view('users.index', compact('users'));
    }

    /**
     * Afișează formularul pentru crearea unui utilizator nou.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Owner')->pluck('name', 'name');
        return view('users.create', compact('roles'));
    }

    /**
     * Salvează utilizatorul nou în baza de date.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'company_id' => Auth::user()->company_id,
                ]);
                $user->assignRole($request->role);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage());
        }

        return redirect()->route('utilizatori.index')->with('success', 'Utilizatorul a fost creat cu succes.');
    }

    /**
     * Afișează formularul pentru editarea unui utilizator.
     */
    public function edit(User $utilizatori)
    {
        // Redenumim variabila pentru claritate
        $user = $utilizatori;

        // Regula de securitate: Nu poți edita un Owner
        if ($user->hasRole('Owner')) {
            abort(403, 'Acțiunea nu este permisă.');
        }

        $roles = Role::where('name', '!=', 'Owner')->pluck('name', 'name');
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Actualizează datele utilizatorului în baza de date.
     */
    public function update(Request $request, User $utilizatori)
    {
        $user = $utilizatori;

        if ($user->hasRole('Owner')) {
            abort(403, 'Acțiunea nu este permisă.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $dataToUpdate = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Actualizăm parola doar dacă a fost introdusă una nouă
        if (!empty($request->password)) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        $user->update($dataToUpdate);
        $user->syncRoles($request->role);

        return redirect()->route('utilizatori.index')->with('success', 'Utilizatorul a fost actualizat cu succes.');
    }

    /**
     * Șterge un utilizator din baza de date.
     */
    public function destroy(User $utilizatori)
    {
        $user = $utilizatori;

        // Reguli de securitate
        if ($user->hasRole('Owner')) {
            return back()->with('error', 'Contul de Proprietar nu poate fi șters.');
        }
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Nu vă puteți șterge propriul cont.');
        }

        $user->delete();

        return redirect()->route('utilizatori.index')->with('success', 'Utilizatorul a fost șters cu succes.');
    }
}