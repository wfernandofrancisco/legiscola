<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterPortalAlunoRequest;
use App\Models\GlobalPrivacyTerm;
use App\Models\Student;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterPortalAlunoRequest $request): RedirectResponse
    {
        if (TenantContext::getTenantId() === null) {
            throw ValidationException::withMessages([
                'email' => 'O cadastro só está disponível pelo portal do cliente (subdomínio ou contexto de tenant).',
            ]);
        }

        $tenantId = (int) TenantContext::getTenantId();
        $validated = $request->validated();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('students', 'public');
        }

        $escolaridadeValue = $validated['escolaridade'] instanceof \BackedEnum
            ? $validated['escolaridade']->value
            : (string) $validated['escolaridade'];

        $privacyTerm = GlobalPrivacyTerm::currentPublished();

        try {
            $user = DB::transaction(function () use ($validated, $tenantId, $photoPath, $escolaridadeValue, $privacyTerm): User {
                $email = strtolower(trim($validated['email']));

                $userAttrs = [
                    'tenant_id' => $tenantId,
                    'name' => $validated['name'],
                    'email' => $email,
                    'password' => Hash::make($validated['password']),
                    'user_type' => User::TYPE_TENANT_USER,
                    'status' => User::STATUS_ATIVO,
                    'cpf' => $validated['cpf'],
                ];

                if ($privacyTerm !== null) {
                    $userAttrs['accepted_global_privacy_term_version'] = $privacyTerm->version;
                    $userAttrs['accepted_global_privacy_term_at'] = now();
                }

                $user = User::query()->create($userAttrs);

                $user->assignRole(User::TYPE_TENANT_USER);

                $enrollmentNumber = sprintf('WEB-%d-%d', $tenantId, $user->id);

                Student::query()->create([
                    'user_id' => $user->id,
                    'email' => $email,
                    'enrollment_number' => $enrollmentNumber,
                    'birth_date' => $validated['birth_date'],
                    'sexo' => $validated['sexo'],
                    'cpf' => $validated['cpf'],
                    'telefone' => $validated['telefone'] ?? null,
                    'celular' => $validated['celular'] ?? null,
                    'cep' => $validated['cep'] ?? null,
                    'logradouro' => $validated['logradouro'] ?? null,
                    'numero' => $validated['numero'] ?? null,
                    'bairro' => $validated['bairro'] ?? null,
                    'cidade' => $validated['cidade'] ?? null,
                    'uf' => $validated['uf'] ?? null,
                    'profissao' => $validated['profissao'] ?? null,
                    'escolaridade' => $escolaridadeValue,
                    'photo_path' => $photoPath,
                    'status' => 'ativo',
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        try {
            event(new Registered($user));
        } catch (Throwable $e) {
            report($e);
            Auth::login($user);

            return redirect()->route('verification.notice')->with(
                'warning',
                'Conta criada, mas o e-mail de confirmação não pôde ser enviado (servidor de e-mail ou endereço inválido). Corrija o SMTP ou o e-mail e use «Reenviar e-mail» nesta página.'
            );
        }

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
