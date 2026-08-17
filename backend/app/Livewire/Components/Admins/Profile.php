<?php

namespace App\Livewire\Components\Admins;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccountFields;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.components.layouts.admin.app')]
class Profile extends Component
{
    use ConfirmsAction, HasAccountFields, WithFileUploads;

    public string $allowed_ip = '';

    public string $moderator_search = '';

    public array $moderator_results = [];

    public string $moderator_password = '';

    public ?int $found_user_id = null;

    public string $found_user_label = '';

    public string $moderator_error = '';

    public function mount(): void {
        $admin = auth('admins')->user();
        $this->fillAccountFields($admin);
        $this->allowed_ip = (string) $admin->allowed_ip;
    }

    #[Computed]
    public function schema(): array {
        $admin = auth('admins')->user();

        return [
            $this->accountAvatarField($admin->avatar),
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            $this->accountPasswordField(true),
            [
                'name' => 'allowed_ip',
                'label' => 'Allowed IP',
                'type' => 'text',
                'placeholder' => 'Leave empty to allow any',
            ],
        ];
    }

    public function save(): void {
        $this->validate([
            'allowed_ip' => ['nullable', 'ip'],
            ...$this->accountValidationRules(true),
        ]);

        $admin = auth('admins')->user();
        $admin->name = $this->name;
        $admin->allowed_ip = filled($this->allowed_ip) ? $this->allowed_ip : null;

        $this->applyPasswordUpdate($admin);
        $this->applyAvatarUpload($admin);

        $admin->save();

        $this->dispatch('profile-updated');
        $this->dispatch('notify', type: 'success', message: 'Profile updated.');
    }

    #[Computed]
    public function moderators(): Collection {
        return auth('admins')->user()->moderators;
    }

    public function updatedModeratorSearch(string $value): void {
        $this->reset(['found_user_id', 'found_user_label', 'moderator_error']);

        $term = trim($value);

        if ($term === '') {
            $this->moderator_results = [];

            return;
        }

        $this->moderator_results = User::query()
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
            )
            ->whereNotIn('id', $this->moderators->pluck('id'))
            ->limit(10)
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email])
            ->all();
    }

    public function selectModeratorCandidate(int $userId, string $label): void {
        $this->found_user_id = $userId;
        $this->found_user_label = $label;
        $this->moderator_search = $label;
        $this->moderator_results = [];
    }

    public function linkModerator(): void {
        $this->validate([
            'moderator_password' => ['required', 'string'],
        ]);

        if (! $this->found_user_id) {
            $this->moderator_error = 'Search for a user first.';

            return;
        }

        $user = User::find($this->found_user_id);

        if (! $user || ! Hash::check($this->moderator_password, $user->password)) {
            $this->moderator_error = 'Incorrect password.';

            return;
        }

        $admin = auth('admins')->user();
        $admin->moderators()->syncWithoutDetaching([$user->id]);

        if (! $user->is_moderator) {
            $user->is_moderator = true;
            $user->save();
        }

        $this->reset(['moderator_search', 'moderator_results', 'moderator_password', 'found_user_id', 'found_user_label', 'moderator_error']);
        unset($this->moderators);

        $this->dispatch('notify', type: 'success', message: 'Moderator linked.');
    }

    #[On('unlinkModerator')]
    public function unlinkModerator(int $userId): void {
        auth('admins')->user()->moderators()->detach($userId);

        unset($this->moderators);
        $this->dispatch('notify', type: 'success', message: 'Moderator unlinked.');
    }

    public function render() {
        return view('livewire.admin.admins.profile');
    }
}
