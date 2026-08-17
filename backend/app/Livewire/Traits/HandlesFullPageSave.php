<?php

namespace App\Livewire\Traits;

trait HandlesFullPageSave
{
    protected function renderResourceForm(
        string $pageTitle,
        bool $disabled,
        string $cancelRoute,
        ?string $siteUrl = null,
        ?string $deleteAction = null,
        ?string $deleteMessage = null,
        ?string $extra = null,
        array $extraData = [],
        string $view = 'livewire.admin.partials.resource-form',
        array $viewData = [],
    ) {
        return view($view, array_merge([
            'page_title' => $pageTitle,
            'fields' => $this->schema(),
            'disabled' => $disabled,
            'cancel_route' => $cancelRoute,
            'site_url' => $siteUrl,
            'delete_action' => $deleteAction,
            'delete_message' => $deleteMessage,
            'extra' => $extra,
            'extra_data' => $extraData,
        ], $viewData));
    }

    public function save(): void {
        $model = $this->persist();

        if (! $model) {
            return;
        }

        if ($model->wasRecentlyCreated) {
            session()->flash('notify', ['type' => 'success', 'message' => "{$this->saveMessageName} created."]);
            $this->redirectRoute("admin.{$this->routePrefix}.edit", [$this->routeParamKey => $model->id], navigate: true);

            return;
        }

        $this->dispatch('notify', type: 'success', message: "{$this->saveMessageName} updated.");
    }

    public function saveAndExit(): void {
        $model = $this->persist();

        if (! $model) {
            return;
        }

        session()->flash('notify', ['type' => 'success', 'message' => $model->wasRecentlyCreated ? "{$this->saveMessageName} created." : "{$this->saveMessageName} updated."]);

        $this->redirectRoute("admin.{$this->routePrefix}.index", navigate: true);
    }
}
