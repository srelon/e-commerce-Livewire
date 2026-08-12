<?php

namespace Tests\Feature;

use App\Livewire\Admin\Menu\Tree;
use App\Models\Admin;
use App\Models\AdminAccess;
use App\Models\AdminRole;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuTreeTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdminWithMenuAccess(): Admin
    {
        $role = AdminRole::create(['name' => 'editor', 'label' => 'Editor']);
        $access = AdminAccess::create(['key' => 'menus', 'title' => 'Menus']);

        $role->accesses()->attach($access->id, ['type' => 1]);
        $role->accesses()->attach($access->id, ['type' => 2]);

        $admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@test.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);

        $this->actingAs($admin, 'admins');

        return $admin;
    }

    public function test_nesting_a_node_that_already_has_children_does_not_create_a_hidden_third_level(): void
    {
        $this->actingAsAdminWithMenuAccess();

        $root_a = Menu::create(['name' => 'A', 'route' => 'home', 'type' => 'route', 'parent_id' => -1, 'sort_order' => 0, 'location' => 'header']);
        $child_b = Menu::create(['name' => 'B', 'route' => 'about', 'type' => 'route', 'parent_id' => $root_a->id, 'sort_order' => 0, 'location' => 'header']);
        $root_c = Menu::create(['name' => 'C', 'route' => 'contact', 'type' => 'route', 'parent_id' => -1, 'sort_order' => 1, 'location' => 'header']);

        // Simulate the client dragging A (which still carries its own child B) to
        // become a child of C — this is exactly the payload onDrop() would produce.
        $payload = [
            [
                'id' => $root_c->id,
                'children' => [
                    [
                        'id' => $root_a->id,
                        'children' => [
                            ['id' => $child_b->id, 'children' => []],
                        ],
                    ],
                ],
            ],
        ];

        Livewire::test(Tree::class)->call('saveOrder', $payload);

        $root_a->refresh();
        $child_b->refresh();

        $this->assertSame($root_c->id, $root_a->parent_id, 'A should have become a child of C.');
        $this->assertSame(-1, $child_b->parent_id, 'B should be promoted back to root, not left nested three levels deep.');
    }

    public function test_normal_one_level_nesting_still_works(): void
    {
        $this->actingAsAdminWithMenuAccess();

        $root_a = Menu::create(['name' => 'A', 'route' => 'home', 'type' => 'route', 'parent_id' => -1, 'sort_order' => 0, 'location' => 'header']);
        $root_c = Menu::create(['name' => 'C', 'route' => 'contact', 'type' => 'route', 'parent_id' => -1, 'sort_order' => 1, 'location' => 'header']);

        $payload = [
            [
                'id' => $root_c->id,
                'children' => [
                    ['id' => $root_a->id, 'children' => []],
                ],
            ],
        ];

        Livewire::test(Tree::class)->call('saveOrder', $payload);

        $root_a->refresh();

        $this->assertSame($root_c->id, $root_a->parent_id);
    }
}
