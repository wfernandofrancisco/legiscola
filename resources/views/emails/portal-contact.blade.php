<x-mail::message>
# Novo contato pelo portal

**Instituição:** {{ $tenant->display_name }}

**Nome:** {{ $payload['name'] }}  
**E-mail:** {{ $payload['email'] }}  
@if(!empty($payload['phone']))
**Telefone:** {{ $payload['phone'] }}
@endif

---

{{ $payload['message'] }}

</x-mail::message>
