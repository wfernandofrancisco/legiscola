<x-mail::message>
# Olá, {{ $contactMessage->name }}

{!! nl2br(e($replyBody)) !!}

---

**Sua mensagem original** (trecho):

> {!! nl2br(e(\Illuminate\Support\Str::limit($contactMessage->message, 800))) !!}

<x-mail::panel>
Este e-mail é a resposta oficial de **{{ $tenant->display_name }}**. Em caso de dúvida, responda a este e-mail.
</x-mail::panel>

{{ $tenant->display_name }}

</x-mail::message>
