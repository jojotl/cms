<?php

namespace Tests\Feature\Fieldsets;

use Statamic\Facades;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Fieldset;
use Tests\Fakes\FakeFieldsetRepository;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Statamic\Fields\FieldsetRepository;

class EditFieldsetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        FieldsetRepository::swap(new FakeFieldsetRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($fieldset->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function it_provides_the_fieldset()
    {
        $this->withoutExceptionHandling();
        $user = Facades\User::make()->makeSuper()->save();
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->get($fieldset->editUrl())
            ->assertStatus(200)
            ->assertViewHas('fieldset', $fieldset);
    }
}
