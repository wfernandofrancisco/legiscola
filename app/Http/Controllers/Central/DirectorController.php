<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreDirectorRequest;
use App\Http\Requests\Central\UpdateDirectorRequest;
use App\Mail\PasswordResetMail;
use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use App\Support\BrazilianStates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Gestão dos diretores regionais.
 *
 * Fica só na Central de propósito: o tipo tenant_director não aparece no cadastro de
 * usuários do cliente, então um tenant_admin não consegue criar um diretor.
 */
class DirectorController extends Controller
{
    public function index(): View
    {
        $directors = User::query()
            ->where('user_type', User::TYPE_TENANT_DIRECTOR)
            ->with('directorUfs')
            ->orderBy('name')
            ->paginate(15);

        // Quantos clientes cada UF tem hoje, para mostrar o alcance real de cada diretor.
        $tenantsPorUf = Tenant::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->whereNotNull('estado')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('central.directors.index', compact('directors', 'tenantsPorUf'));
    }

    public function create(): View
    {
        return view('central.directors.create');
    }

    public function store(StoreDirectorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $director = DB::transaction(function () use ($data): User {
            $director = User::create([
                'tenant_id' => null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Str::random(40),
                'user_type' => User::TYPE_TENANT_DIRECTOR,
                'status' => User::STATUS_ATIVO,
                // O diretor não passa pelo fluxo de verificação do tenant (que exige subdomínio),
                // então o e-mail já entra verificado e o acesso é feito pelo link de definição de senha.
                'email_verified_at' => now(),
            ]);

            $this->assignRole($director);
            $this->syncUfs($director, $data['ufs']);

            return $director;
        });

        $this->sendPasswordSetupEmail($director);

        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($director)
            ->log('Diretor regional criado');

        return redirect()
            ->route('central.directors.index')
            ->with('success', "Diretor \"{$director->name}\" criado. Convite enviado para {$director->email}.");
    }

    public function edit(User $director): View
    {
        abort_unless($director->isTenantDirector(), 404);

        $director->load('directorUfs');

        return view('central.directors.edit', compact('director'));
    }

    public function update(UpdateDirectorRequest $request, User $director): RedirectResponse
    {
        abort_unless($director->isTenantDirector(), 404);

        $data = $request->validated();

        DB::transaction(function () use ($director, $data): void {
            $director->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'],
            ]);

            $this->assignRole($director);
            $this->syncUfs($director, $data['ufs']);
        });

        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($director)
            ->log('Diretor regional atualizado');

        return redirect()
            ->route('central.directors.index')
            ->with('success', "Diretor \"{$director->name}\" atualizado.");
    }

    public function destroy(User $director): RedirectResponse
    {
        abort_unless($director->isTenantDirector(), 404);

        $name = $director->name;

        DB::transaction(function () use ($director): void {
            $director->directorUfs()->delete();
            $director->delete();
        });

        activity('central')
            ->causedBy(auth()->user())
            ->log("Diretor regional \"{$name}\" removido");

        return redirect()
            ->route('central.directors.index')
            ->with('success', "Diretor \"{$name}\" removido.");
    }

    /**
     * @param  list<string>  $ufs
     */
    private function syncUfs(User $director, array $ufs): void
    {
        $normalized = collect($ufs)
            ->map(fn (string $uf): string => BrazilianStates::normalize($uf))
            ->filter(fn (string $uf): bool => BrazilianStates::isValid($uf))
            ->unique()
            ->values();

        $director->directorUfs()->delete();

        $normalized->each(fn (string $uf) => DirectorUf::create([
            'user_id' => $director->id,
            'uf' => $uf,
        ]));
    }

    private function assignRole(User $director): void
    {
        if (! Role::query()->where('name', User::TYPE_TENANT_DIRECTOR)->where('guard_name', 'web')->exists()) {
            return;
        }

        $director->syncRoles([User::TYPE_TENANT_DIRECTOR]);
    }

    private function sendPasswordSetupEmail(User $director): void
    {
        $token = Password::createToken($director);

        // Diretor não tem tenant, então o link de senha usa o domínio raiz da aplicação.
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $director->email,
        ]);

        Mail::to($director->email)->send(new PasswordResetMail($director, $resetUrl));
    }
}
