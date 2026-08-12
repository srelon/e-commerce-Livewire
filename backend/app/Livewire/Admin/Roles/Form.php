<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Traits\HasAccessControl;
use App\Models\AdminAccess;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin.app')]
class Form extends Component
{
    use HasAccessControl;

    protected string $accessKey = 'roles';

    public ?AdminRole $role = null;

    public string $name = '';

    public string $label = '';

    /** @var string[] */
    public array $selected_accesses = [];

    public bool $show_create_access = false;

    public string $new_access_key = '';

    public string $new_access_title = '';

    public function mount(?AdminRole $role = null): void
    {
        if ($role) {
            $this->guardView();

            $this->role = $role;
            $this->name = $role->name;
            $this->label = $role->label;
            $this->selected_accesses = DB::table('admin_role_access')
                ->where('role_id', $role->id)
                ->get()
                ->map(fn ($row) => $row->access_id.':'.$row->type)
                ->all();
        } else {
            abort_unless($this->hasAccess('edit'), 403);
        }
    }

    #[Computed]
    public function accesses(): Collection
    {
        return AdminAccess::orderBy('title')->get();
    }

    public function createAccess(): void
    {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate([
            'new_access_key' => ['required', 'string', 'max:255', Rule::unique('admin_accesses', 'key')],
            'new_access_title' => ['required', 'string', 'max:255'],
        ]);

        AdminAccess::create([
            'key' => $this->new_access_key,
            'title' => $this->new_access_title,
        ]);

        unset($this->accesses);

        $this->reset(['new_access_key', 'new_access_title']);
        $this->show_create_access = false;

        $this->dispatch('notify', type: 'success', message: 'Access created.');
    }

    public function save(): void
    {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('admin_roles', 'name')->ignore($this->role?->id)],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $isCreating = ! $this->role;
        $role = $this->role ?? new AdminRole();
        $role->name = $this->name;
        $role->label = $this->label;
        $role->save();

        $rows = collect($this->selected_accesses)
            ->map(function (string $item) use ($role) {
                [$accessId, $type] = explode(':', $item);

                return [
                    'role_id' => $role->id,
                    'access_id' => (int) $accessId,
                    'type' => (int) $type,
                ];
            })
            ->all();

        DB::table('admin_role_access')->where('role_id', $role->id)->delete();

        if (filled($rows)) {
            DB::table('admin_role_access')->insert($rows);
        }

        session()->flash('notify', ['type' => 'success', 'message' => $isCreating ? 'Role created.' : 'Role updated.']);

        $this->redirectRoute('admin.roles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.roles.form');
    }
}
