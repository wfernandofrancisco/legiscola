<x-layouts.admin>
    <x-slot name="title">Contatos do portal</x-slot>

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-page-header title="Contatos do portal"
        subtitle="Mensagens enviadas pelo formulário público /contato. Também é enviada cópia ao e-mail administrativo do tenant, se estiver configurado." />

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/60">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Data</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nome</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">E-mail</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($messages as $msg)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            {{ $msg->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $msg->name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $msg->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($msg->replied_at)
                                <x-badge color="green" text="Respondido" />
                            @elseif ($msg->read_at)
                                <x-badge color="yellow" text="Lido" />
                            @else
                                <x-badge color="blue" text="Novo" />
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.contatos-portal.show', $msg) }}"
                                class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Abrir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            Nenhuma mensagem recebida ainda pelo portal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $messages->links() }}
    </div>
</x-layouts.admin>
