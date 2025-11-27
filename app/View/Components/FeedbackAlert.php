<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\View\Component;

class FeedbackAlert extends Component
{
    /**
     * Supported alert types.
     *
     * @var array<int, string>
     */
    public const TYPES = [
        'primary',
        'secondary',
        'success',
        'danger',
        'warning',
        'info',
        'dark',
    ];

    /**
     * Supported visual variants.
     *
     * @var array<int, string>
     */
    public const VARIANTS = [
        'basic',
        'outline',
        'solid',
    ];

    /**
     * Default icon mapping for each alert type.
     *
     * @var array<string, string>
     */
    protected const DEFAULT_ICONS = [
        'primary' => 'ri-information-line',
        'secondary' => 'ri-question-line',
        'success' => 'ri-checkbox-circle-line',
        'danger' => 'ri-error-warning-line',
        'warning' => 'ri-alarm-warning-line',
        'info' => 'ri-information-2-line',
        'dark' => 'ri-moon-line',
    ];

    /**
     * Types resolved for this component instance.
     *
     * @var array<int, string>
     */
    public array $types = [];

    /**
     * Indicates whether the consumer limited rendering to a specific type.
     */
    public ?string $restrictedType = null;

    /**
     * Explicit messages provided through the component attributes.
     *
     * @var array<int, string>
     */
    public array $manualMessages = [];

    /**
     * Alert payloads resolved for the view.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $alerts = [];

    /**
     * Default variant when a per-type mapping is not provided.
     */
    public string $variant = 'solid';

    /**
     * Optional per-type variant overrides.
     *
     * @var array<string, string>
     */
    protected array $variantMap = [];

    /**
     * Create a new component instance.
     */
    public function __construct(
        string|array|null $type = null,
        public string|array|null $message = null,
        public bool $dismissible = false,
        string|array $variant = 'solid',
        public bool|string|null $icon = true,
        public ?string $heading = null,
        public ?string $linkHref = null,
        public ?string $linkText = null,
        public bool $collect = true,
    ) {
        [$this->variant, $this->variantMap] = $this->normalizeVariantInput($variant);
        $this->types = $this->normalizeTypes($type);
        $this->restrictedType = $this->determineRestrictedType($type);
        $this->manualMessages = $this->normalizeMessages($this->message);

        if ($this->shouldAutoSetRestrictedType()) {
            $this->restrictedType = $this->types[0] ?? self::TYPES[0];
        }

        $this->alerts = $this->buildAlerts();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.feedback-alert');
    }

    /**
     * Get alerts to render, including manual alert if needed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function renderableAlerts(?string $slotContent = null): array
    {
        if (! empty($this->alerts)) {
            return $this->alerts;
        }

        $manualAlert = $this->buildManualAlert($slotContent);

        return $manualAlert !== null ? [$manualAlert] : [];
    }

    /**
     * Check if the component should render anything.
     */
    public function shouldRender(?string $slotContent = null): bool
    {
        return ! empty($this->renderableAlerts($slotContent));
    }

    /**
     * Determine the restricted type from the type parameter.
     */
    protected function determineRestrictedType(string|array|null $type): ?string
    {
        return $type === null ? null : ($this->types[0] ?? null);
    }

    /**
     * Check if we should auto-set the restricted type.
     */
    protected function shouldAutoSetRestrictedType(): bool
    {
        return $this->manualMessages !== [] && $this->restrictedType === null;
    }

    /**
     * Build manual alert when no automatic messages are available.
     */
    protected function buildManualAlert(?string $slotContent = null): ?array
    {
        $hasSlot = $slotContent !== null && trim($slotContent) !== '';
        $hasManualMessages = $this->manualMessages !== [];

        if (! $hasSlot && ! $hasManualMessages) {
            return null;
        }

        $type = $this->restrictedType ?? ($this->types[0] ?? self::TYPES[0]);

        return $this->formatAlert($type, $hasManualMessages ? $this->manualMessages : [], $slotContent);
    }

    /**
     * Gather alert data for the view based on flash data and validation errors.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildAlerts(): array
    {
        $alerts = [];

        foreach ($this->types as $type) {
            $messages = $this->collectedMessages($type);

            if (empty($messages)) {
                continue;
            }

            $alerts[] = $this->formatAlert($type, $messages);
        }

        return $alerts;
    }

    /**
     * Shape an alert payload for the Blade template.
     *
     * @param  array<int, string>  $messages
     * @return array<string, mixed>
     */
    protected function formatAlert(string $type, array $messages, ?string $slotContent = null): array
    {
        $messages = $this->cleanMessages($messages);
        $icon = $this->resolveIconValue($type, $slotContent);

        return [
            'type' => $type,
            'variant' => $this->variantForType($type),
            'classes' => $this->buildAlertClasses($type),
            'messages' => $messages,
            'icon' => $icon,
            'hasIcon' => $icon !== null && $icon !== '' && $slotContent === null,
            'heading' => $this->heading,
            'link' => $this->buildLinkData(),
            'dismissible' => $this->dismissible,
            'slot' => $slotContent,
        ];
    }

    /**
     * Clean and deduplicate messages array.
     *
     * @param  array<int, string>  $messages
     * @return array<int, string>
     */
    protected function cleanMessages(array $messages): array
    {
        return array_values(
            array_unique(
                array_filter($messages, fn ($value) => $value !== '')
            )
        );
    }

    /**
     * Resolve messages collected from the session or validation errors.
     *
     * @return array<int, string>
     */
    protected function collectedMessages(string $type): array
    {
        $messages = [];

        if ($this->collect) {
            if ($type === 'danger') {
                $messages = $this->collectValidationErrors();
            }

            $messages = array_merge($messages, $this->collectFlashMessages($type));
        }

        if ($this->shouldIncludeManualMessages($type)) {
            $messages = array_merge($messages, $this->manualMessages);
        }

        return $messages;
    }

    /**
     * Check if manual messages should be included for this type.
     */
    protected function shouldIncludeManualMessages(string $type): bool
    {
        return $this->manualMessages !== []
            && ($this->restrictedType === null || $this->restrictedType === $type);
    }

    /**
     * Determine the icon that should be displayed for the alert.
     */
    protected function resolveIconValue(string $type, ?string $slotContent = null): ?string
    {
        if ($this->icon === false) {
            return null;
        }

        if (is_string($this->icon)) {
            return $this->normalizeIconClass($this->icon);
        }

        if ($this->shouldUseDefaultIcon($slotContent)) {
            return $this->normalizeIconClass($this->getDefaultIcon($type));
        }

        return null;
    }

    /**
     * Check if default icon should be used.
     */
    protected function shouldUseDefaultIcon(?string $slotContent): bool
    {
        if ($this->icon === true) {
            return true;
        }

        return $this->collect && $slotContent === null && empty($this->manualMessages);
    }

    /**
     * Get default icon for the alert type.
     */
    protected function getDefaultIcon(string $type): string
    {
        return self::DEFAULT_ICONS[$type] ?? self::DEFAULT_ICONS['primary'];
    }

    /**
     * Build the Tailwind / Bootstrap alert classes for the given type.
     */
    protected function buildAlertClasses(string $type): string
    {
        $variant = $this->variantForType($type);

        $base = match ($variant) {
            'outline' => "alert alert-outline-{$type}",
            'basic' => "alert alert-{$type}",
            default => "alert alert-solid-{$type}",
        };

        if ($this->dismissible) {
            $base .= ' alert-dismissible fade show';
        }

        return trim($base);
    }

    /**
     * Reduce arbitrary message inputs to a trimmed array.
     *
     * @param  string|array|null  $messages
     * @return array<int, string>
     */
    protected function normalizeMessages(string|array|null $messages): array
    {
        if ($messages === null) {
            return [];
        }

        return array_filter(array_map('trim', Arr::wrap($messages)));
    }

    /**
     * Limit variants to the supported list.
     */
    protected function normalizeVariant(string $variant): string
    {
        $variant = strtolower(trim($variant));

        return in_array($variant, self::VARIANTS, true) ? $variant : 'solid';
    }

    /**
     * Normalize the variant input, supporting per-type overrides.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    protected function normalizeVariantInput(string|array $variant): array
    {
        if (is_string($variant)) {
            return [$this->normalizeVariant($variant), []];
        }

        $default = 'solid';
        $map = [];

        foreach ($variant as $key => $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            if (is_string($key) && $key !== '') {
                if ($key === 'default') {
                    $default = $this->normalizeVariant($value);
                    continue;
                }

                $typeKey = $this->normalizeTypeValue($key);

                if ($typeKey !== null) {
                    $map[$typeKey] = $this->normalizeVariant($value);
                }

                continue;
            }

            if ($default === 'solid') {
                $default = $this->normalizeVariant($value);
            }
        }

        return [$default, $map];
    }

    /**
     * Get the variant for a specific alert type.
     */
    protected function variantForType(string $type): string
    {
        return $this->variantMap[$type] ?? $this->variant;
    }

    /**
     * Normalize the requested types list.
     *
     * @param  string|array|null  $type
     * @return array<int, string>
     */
    protected function normalizeTypes(string|array|null $type): array
    {
        if ($type === null) {
            return self::TYPES;
        }

        $types = array_filter(
            array_map(
                fn ($value) => $this->normalizeTypeValue((string) $value),
                Arr::wrap($type)
            )
        );

        return $types === [] ? [self::TYPES[0]] : array_values(array_unique($types));
    }

    /**
     * Normalize a potential type value.
     */
    protected function normalizeTypeValue(string $type): ?string
    {
        $type = strtolower(trim($type));

        return in_array($type, self::TYPES, true) ? $type : null;
    }

    /**
     * Collect validation errors from the current session.
     *
     * @return array<int, string>
     */
    protected function collectValidationErrors(): array
    {
        $errors = session()->get('errors');

        if ($errors instanceof MessageBag) {
            return $this->translateErrorMessages($errors->all());
        }

        if ($errors instanceof MessageProvider) {
            return $this->translateErrorMessages($errors->getMessageBag()->all());
        }

        // ViewErrorBag handling
        if ($errors instanceof \Illuminate\Support\ViewErrorBag) {
            $defaultBag = $errors->getBag('default');
            return $this->translateErrorMessages($defaultBag->all());
        }

        return [];
    }

    /**
     * Translate error messages that are translation keys.
     *
     * @param  array<int, string>  $messages
     * @return array<int, string>
     */
    protected function translateErrorMessages(array $messages): array
    {
        return array_map(function ($message) {
            // Jika message adalah translation key (mengandung titik), translate
            if (str_contains($message, '.')) {
                $translated = __($message);

                // Jika translation berhasil (tidak sama dengan key), gunakan hasil translate
                return $translated !== $message ? $translated : $message;
            }

            return $message;
        }, $messages);
    }

    /**
     * Retrieve flash messages for a given alert type.
     *
     * @return array<int, string>
     */
    protected function collectFlashMessages(string $type): array
    {
        $keys = $this->buildFlashKeys($type);

        foreach ($keys as $key) {
            if (session()->has($key)) {
                return $this->normalizeMessages(session()->get($key));
            }
        }

        return [];
    }

    /**
     * Build possible flash message keys for the given type.
     *
     * @return array<int, string>
     */
    protected function buildFlashKeys(string $type): array
    {
        $keys = [
            $type,
            "alert.{$type}",
            "alert_{$type}",
            "flash.{$type}",
            "flash_{$type}",
        ];

        if ($type === 'success') {
            $keys[] = 'status';
        }

        return $keys;
    }

    /**
     * Resolve link metadata for the alert, if provided.
     *
     * @return array<string, string>|null
     */
    protected function buildLinkData(): ?array
    {
        if ($this->linkHref === null || $this->linkText === null) {
            return null;
        }

        return [
            'href' => $this->linkHref,
            'text' => $this->linkText,
        ];
    }

    /**
     * Normalize icon definitions to the expected class string.
     */
    protected function normalizeIconClass(string $icon): string
    {
        $icon = trim($icon);

        if ($icon === '') {
            return '';
        }

        if (str_contains($icon, ' ')) {
            return $icon;
        }

        $icon = str_replace(':', '-', $icon);

        if (! str_starts_with($icon, 'ri-')) {
            $icon = 'ri-' . ltrim($icon, '-');
        }

        return "ri {$icon}";
    }
}
