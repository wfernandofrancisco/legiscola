<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantAdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantAdminSettingController extends Controller
{
    public function edit(): View
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $tenant = Tenant::query()->findOrFail($tenantId);
        $settings = TenantAdminSetting::query()->firstOrCreate(
            ['tenant_id' => $tenantId],
            []
        );

        return view('admin.settings.index', compact('settings', 'tenant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $validated = $request->validate([
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'nome_camara' => ['nullable', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:12'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:30'],
            'bairro' => ['nullable', 'string', 'max:120'],
            'uf' => ['nullable', 'string', 'regex:/^$|^[A-Za-z]{2}$/'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'horario_funcionamento' => ['nullable', 'string', 'max:500'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'x' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],

            'portal_nome_cidade' => ['nullable', 'string', 'max:120'],
            'logo_prefeitura' => ['nullable', 'image', 'max:4096'],
            'remove_logo_prefeitura' => ['nullable', 'boolean'],

            'foto_capa' => ['nullable', 'image', 'max:8192'],
            'remove_foto_capa' => ['nullable', 'boolean'],

            'primary_color' => ['nullable', 'string', 'regex:/^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'tertiary_color' => ['nullable', 'string', 'regex:/^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $settings = TenantAdminSetting::query()->firstOrCreate(['tenant_id' => $tenantId]);
        $disk = Storage::disk('public');
        $prefix = "tenant-branding/{$tenantId}";

        $emptyToNull = static fn (?string $v): ?string => ($v !== null && trim($v) !== '') ? trim($v) : null;

        $themeHex = static function (?string $raw) use ($emptyToNull): ?string {
            $t = $emptyToNull($raw);
            if ($t === null) {
                return null;
            }

            return str_starts_with($t, '#') ? strtolower($t) : '#'.strtolower($t);
        };

        $updates = [
            'primary_color' => $themeHex($validated['primary_color'] ?? null),
            'secondary_color' => $themeHex($validated['secondary_color'] ?? null),
            'tertiary_color' => $themeHex($validated['tertiary_color'] ?? null),

            'whatsapp' => $emptyToNull($validated['whatsapp'] ?? null),
            'email' => $emptyToNull($validated['email'] ?? null),
            'nome_camara' => $emptyToNull($validated['nome_camara'] ?? null),
            'cep' => $emptyToNull($validated['cep'] ?? null),
            'logradouro' => $emptyToNull($validated['logradouro'] ?? null),
            'numero' => $emptyToNull($validated['numero'] ?? null),
            'bairro' => $emptyToNull($validated['bairro'] ?? null),
            'cidade' => $emptyToNull($validated['cidade'] ?? null),
            'uf' => (static function () use ($validated): ?string {
                $raw = isset($validated['uf']) ? trim((string) $validated['uf']) : '';

                return $raw !== '' ? strtoupper(substr($raw, 0, 2)) : null;
            })(),
            'horario_funcionamento' => $emptyToNull($validated['horario_funcionamento'] ?? null),
            'instagram' => $emptyToNull($validated['instagram'] ?? null),
            'x' => $emptyToNull($validated['x'] ?? null),
            'facebook' => $emptyToNull($validated['facebook'] ?? null),
            'telefone' => $emptyToNull($validated['telefone'] ?? null),
        ];

        if ($request->hasFile('logo_prefeitura')) {
            if ($settings->logo_prefeitura_path) {
                $disk->delete($settings->logo_prefeitura_path);
            }
            $updates['logo_prefeitura_path'] = $request->file('logo_prefeitura')->store($prefix, 'public');
        } elseif ($request->boolean('remove_logo_prefeitura')) {
            if ($settings->logo_prefeitura_path) {
                $disk->delete($settings->logo_prefeitura_path);
            }
            $updates['logo_prefeitura_path'] = null;
        }

        if ($request->hasFile('foto_capa')) {
            if ($settings->foto_capa_path) {
                $disk->delete($settings->foto_capa_path);
            }
            $updates['foto_capa_path'] = $request->file('foto_capa')->store("{$prefix}/hero", 'public');
        } elseif ($request->boolean('remove_foto_capa')) {
            if ($settings->foto_capa_path) {
                $disk->delete($settings->foto_capa_path);
            }
            $updates['foto_capa_path'] = null;
        }

        $settings->update($updates);

        $portalNome = isset($validated['portal_nome_cidade']) ? trim((string) $validated['portal_nome_cidade']) : '';
        Tenant::query()->whereKey($tenantId)->update([
            'portal_nome_cidade' => $portalNome !== '' ? $portalNome : null,
        ]);

        return back()->with('success', 'Configurações atualizadas com sucesso.');
    }
}
