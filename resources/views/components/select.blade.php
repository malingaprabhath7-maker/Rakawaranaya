@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false, 'hint' => null])
@php
    $id = $attributes->get('id', 'f-'.$name);
    $err = $errors->first($name);
    $val = old($name, $value);
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }} @if($required)<span class="text-red-500" aria-hidden="true">*</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except(['class', 'id'])->merge(['class' => 'input'.($err ? ' input-error' : '')]) }} @required($required)>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $k => $v)
            <option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $v }}</option>
        @endforeach
    </select>
    @if ($err)<p class="error">{{ $err }}</p>@elseif($hint)<p class="hint">{{ $hint }}</p>@endif
</div>
