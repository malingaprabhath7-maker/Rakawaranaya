@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false, 'bag' => 'default'])
@php
    $id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name));
    $err = $errors->getBag($bag)->first(str_replace(['[', ']'], ['.', ''], $name));
    $val = $type === 'password' ? null : old(str_replace(['[', ']'], ['.', ''], $name), $value);
    if (is_array($val)) { $val = implode(', ', $val); }
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }} @if($required)<span class="text-red-500" aria-hidden="true">*</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->except(['class', 'id'])->merge(['rows' => 3, 'class' => 'input'.($err ? ' input-error' : '')]) }} @required($required)>{{ $val }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $val }}" {{ $attributes->except(['class', 'id'])->merge(['class' => 'input'.($err ? ' input-error' : '')]) }} @required($required) @if($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>
    @endif
    @if ($err)<p id="{{ $id }}-err" class="error">{{ $err }}</p>@elseif($hint)<p class="hint">{{ $hint }}</p>@endif
</div>
