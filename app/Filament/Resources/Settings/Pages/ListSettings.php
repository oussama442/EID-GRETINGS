<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class ListSettings extends EditRecord
{
    protected static string $resource = SettingResource::class;

    public function mount(int | string | null $record = null): void
    {
        $this->record = Setting::current();

        $this->authorizeAccess();
        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    public function getTitle(): string | Htmlable
    {
        return __('Company settings');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
