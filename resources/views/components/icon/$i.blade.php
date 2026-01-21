@props(['class' => 'w-5 h-5'])
<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor']) }}
     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="12" cy="12" r="9"/>
  <path d="M12 8v5"/>
  <path d="M12 16h.01"/>
</svg>
BLADE
  fi
done