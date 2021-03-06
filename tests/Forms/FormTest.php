<?php

namespace Tests\Forms;

use Tests\TestCase;
use Statamic\Facades\Form;
use Statamic\Fields\Blueprint;

class FormTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Form::all()->each->delete();
    }

    /** @test */
    function it_saves_a_form()
    {
        $blueprint = (new Blueprint)->setHandle('post')->save();

        Form::make('contact_us')
            ->title('Contact Us')
            ->blueprint($blueprint)
            ->honeypot('winnie')
            ->save();

        $form = Form::find('contact_us');

        $this->assertEquals('contact_us', $form->handle());
        $this->assertEquals('Contact Us', $form->title());
        $this->assertEquals('post', $form->blueprint()->handle());
        $this->assertEquals('winnie', $form->honeypot());
    }

    /** @test */
    function it_gets_all_forms()
    {
        $this->assertEmpty(Form::all());

        Form::make('contact_us')->save();
        Form::make('vote_for_canada')->save();

        $this->assertEquals(['contact_us', 'vote_for_canada'], Form::all()->map->handle()->all());
    }

    /** @test */
    function it_has_default_honeypot()
    {
        $form = Form::make('contact_us');

        $this->assertEquals('honeypot', $form->honeypot());
    }
}
