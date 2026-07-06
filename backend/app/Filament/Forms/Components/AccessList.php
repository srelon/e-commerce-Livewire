<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\DB;

class AccessList extends Field
{
    protected string $view = 'filament.forms.components.access-list';

    protected function setUp(): void {
        parent::setUp();

        $this->afterStateHydrated(function (self $component, $record) {
            if (! $record?->id) {
                $component->state([]);

                return;
            }

            $state = DB::table('admin_role_access')
                ->where('role_id', $record->id)
                ->get()
                ->map(fn ($row) => $row->access_id.':'.$row->type)
                ->toArray();

            $component->state($state);
        });

        $this->dehydrated(false);

        $this->saveRelationshipsUsing(function (self $component, $record, $state) {
            if (! $record?->id) {
                return;
            }

            DB::table('admin_role_access')->where('role_id', $record->id)->delete();

            $rows = collect($state ?? [])
                ->filter(fn ($item) => str_contains($item, ':'))
                ->map(function ($item) use ($record) {
                    [$access_id, $type] = explode(':', $item);

                    return [
                        'role_id' => $record->id,
                        'access_id' => (int) $access_id,
                        'type' => (int) $type,
                    ];
                })
                ->toArray();

            if (! empty($rows)) {
                DB::table('admin_role_access')->insert($rows);
            }
        });
    }
}
